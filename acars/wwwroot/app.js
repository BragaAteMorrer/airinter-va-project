const $ = selector => document.querySelector(selector);
const $$ = selector => [...document.querySelectorAll(selector)];
const unwrap = value => value?.data ?? value;
const setText = (node, value) => { if (node) node.textContent = value ?? ''; };
const showMessage = (selector, value, error = false) => {
  const node = $(selector);
  if (!node) return;
  node.hidden = !value;
  node.classList.toggle('error', error);
  setText(node, value || '');
};
const hermesErrorEntries = [];
const HERMES_ERROR_LIMIT = 20;
const classifyHermesError = (error, fallback = 'Action impossible pour le moment.', context = '') => {
  const technical = String(error?.message ?? error ?? '').trim();
  const lowered = technical.toLowerCase();
  let family = 'local';
  let code = 'UNEXPECTED';
  let summary = technical || fallback;
  let impact = 'L’action demandée n’a pas pu être terminée.';
  let action = 'Réessayez. Si le problème persiste, ouvrez les détails techniques.';

  if (lowered.includes('simbrief')) {
    family = 'service'; code = 'SIMBRIEF_UNAVAILABLE';
    summary = 'SimBrief est temporairement indisponible.';
    impact = 'Votre opération et l’appareil sélectionné restent conservés dans Hermès.';
    action = 'Réessayez plus tard ou utilisez une autre source OFP.';
  } else if (lowered.includes('argos')) {
    family = 'service'; code = 'ARGOS_UNAVAILABLE';
    summary = 'Argos ne répond pas pour le moment.';
    impact = 'La connexion SSO ne peut pas être terminée, sans effet sur un vol déjà enregistré localement.';
    action = 'Réessayez la connexion ou utilisez la connexion Prométhée de secours.';
  } else if (lowered.includes('failed to fetch') || lowered.includes('network') || lowered.includes('timeout') || /\b50[234]\b/.test(technical)) {
    family = 'service'; code = 'SERVICE_UNAVAILABLE';
    summary = 'Service temporairement indisponible.';
    impact = 'Hermès conserve votre contexte et le tracking local continue lorsqu’un vol est en cours.';
    action = 'La synchronisation reprendra automatiquement ; réessayez l’action si nécessaire.';
  } else if (/\b401\b/.test(technical) || lowered.includes('unauthorized') || (lowered.includes('session') && (lowered.includes('expir') || lowered.includes('token')))) {
    family = 'user'; code = 'SESSION_EXPIRED';
    summary = 'Votre session Air Inter doit être renouvelée.';
    impact = 'Les actions serveur sont suspendues tant que la session n’est pas renouvelée.';
    action = 'Reconnectez-vous puis reprenez l’action.';
  } else if (/\b409\b/.test(technical) || lowered.includes('conflict')) {
    family = 'operation'; code = 'OPERATION_CONFLICT';
    summary = 'L’opération a changé côté Prométhée.';
    impact = 'Hermès refuse d’écraser un état plus récent.';
    action = 'Actualisez l’état de l’opération avant de réessayer.';
  } else if (lowered.includes('simulateur') || lowered.includes('simconnect') || lowered.includes('fsuipc') || lowered.includes('x-plane') || lowered.includes('xplane')) {
    family = 'local'; code = 'SIMULATOR_CONNECTION';
    summary = technical || 'Connexion simulateur indisponible.';
    impact = 'Le départ peut être bloqué ; un enregistrement déjà actif reste protégé par la récupération locale.';
    action = 'Vérifiez le simulateur et le connecteur, puis laissez Hermès reprendre automatiquement.';
  }

  return { family, code, summary, impact, action, technical: technical || fallback, context, at: new Date() };
};
const renderHermesErrorCenter = () => {
  const center = $('#errorCenter');
  const list = $('#errorCenterList');
  const count = $('#errorCenterCount');
  if (!center || !list || !count) return;
  center.hidden = hermesErrorEntries.length === 0;
  count.textContent = String(hermesErrorEntries.length);
  list.replaceChildren();
  hermesErrorEntries.forEach(entry => {
    const article = document.createElement('article');
    article.className = 'error-center-entry ' + entry.family;
    const meta = document.createElement('span');
    meta.className = 'error-center-meta';
    meta.textContent = [entry.family.toUpperCase(), entry.code, entry.context].filter(Boolean).join(' · ');
    const title = document.createElement('strong');
    title.textContent = entry.summary;
    const impact = document.createElement('p');
    impact.textContent = entry.impact;
    const action = document.createElement('small');
    action.textContent = entry.action;
    const details = document.createElement('details');
    const summary = document.createElement('summary');
    summary.textContent = 'Détails techniques';
    const detailCode = document.createElement('code');
    detailCode.textContent = entry.technical;
    details.append(summary, detailCode);
    article.append(meta, title, impact, action, details);
    list.append(article);
  });
};
const registerHermesError = (error, fallback = 'Action impossible pour le moment.', context = '') => {
  const entry = classifyHermesError(error, fallback, context);
  const fingerprint = [entry.family, entry.code, entry.summary, context].join('|');
  const duplicateIndex = hermesErrorEntries.findIndex(item => item.fingerprint === fingerprint);
  if (duplicateIndex >= 0) hermesErrorEntries.splice(duplicateIndex, 1);
  hermesErrorEntries.unshift({ ...entry, fingerprint });
  hermesErrorEntries.splice(HERMES_ERROR_LIMIT);
  renderHermesErrorCenter();
  return entry;
};
const friendlyError = (error, fallback = 'Action impossible pour le moment.', context = '') =>
  registerHermesError(error, fallback, context).summary;

$('#errorCenterClearBtn')?.addEventListener('click', event => {
  event.preventDefault();
  hermesErrorEntries.splice(0);
  renderHermesErrorCenter();
});
window.addEventListener('hermes:ui-error', event => {
  registerHermesError(event.detail?.error || event.detail?.message || 'Erreur interface Hermès', 'Erreur interface Hermès.', 'Interface');
});
(window.__hermesBootErrors || []).forEach(error => registerHermesError(error, 'Erreur interface Hermès.', 'Démarrage'));

const call = (path, body) => new Promise((resolve, reject) => {
  if (!globalThis.chrome?.webview) {
    reject(new Error('Pont Hermès/WebView2 indisponible. Redémarrez Hermès après reconstruction.'));
    return;
  }
  const id = crypto.randomUUID();
  const timeoutMs = path === '/api/login/argos' ? 5 * 60 * 1000 : 15000;
  const timer = setTimeout(() => {
    chrome.webview.removeEventListener('message', onMessage);
    reject(new Error(path === '/api/login/argos'
      ? 'La connexion Argos a expiré. Relancez-la puis terminez l’authentification dans votre navigateur.'
      : 'Hermès n’a reçu aucune réponse du backend local après 15 secondes.'));
  }, timeoutMs);
  const onMessage = event => {
    if (event.data.id !== id) return;
    clearTimeout(timer);
    chrome.webview.removeEventListener('message', onMessage);
    event.data.ok ? resolve(event.data.data) : reject(new Error(event.data.data));
  };
  chrome.webview.addEventListener('message', onMessage);
  chrome.webview.postMessage({ id, path, body });
});

let selectedOperation = null;
let selectedAircraft = null;
let selectedVariant = null;
let aircraftVariantState = null;
let aircraftEligibility = null;
let pirepId = null;
let flightPlan = null;
let linkedSimBrief = null;
let serverDispatch = null;
let connected = false;
let lastStatus = null;
let lastFiledReview = null;
let serverCompanyScore = null;
let serverCompanyScoreKey = null;
let lastDatalinkSnapshot = null;
let datalinkRefreshing = false;
let recoveryWasVisible = false;
let readiness = { operation: false, aircraft: false, ofp: false, pirep: false, simulator: false };

let efbContextSyncTimer = null;
function scheduleEfbContextSync() {
  clearTimeout(efbContextSyncTimer);
  efbContextSyncTimer = setTimeout(syncEfbContext, 120);
}
async function syncEfbContext() {
  const operationRef = selectedOperation?.operation_id || selectedOperation?.id || selectedOperation?.bid_id || null;
  if (!operationRef) {
    try { await call('/api/efb/context', null); } catch {}
    return;
  }
  const flight = normalizeFlight(selectedOperation?.flight || selectedOperation || {});
  const form = $('#prefileForm');
  const context = {
    operation_id: String(operationRef),
    pirep_id: pirepId ? String(pirepId) : null,
    flight_ident: displayFlightIdent(flight),
    departure: flight.departure || null,
    arrival: flight.arrival || null,
    alternate: form?.elements?.alt_airport_id?.value || flight.alternate || null,
    route: flightPlan?.route || form?.elements?.route?.value || flight.route || null,
    aircraft_registration: selectedAircraft?.registration || null,
    aircraft_icao: selectedAircraft?.icao || selectedAircraft?.type || null,
    aircraft_model: selectedAircraft?.type_label || selectedAircraft?.subfleet || selectedAircraft?.model || null,
    passengers: Number.isFinite(Number(flightPlan?.passengers ?? selectedAircraft?.passengers))
      ? Math.max(0, Math.round(Number(flightPlan?.passengers ?? selectedAircraft?.passengers)))
      : null,
    flight_level: normalizeFlightLevel(flightPlan?.level || form?.elements?.level?.value || flight.level) || null,
    cost_index: flightPlan?.cost_index ?? form?.elements?.civalue?.value ?? null,
    block_fuel: Number.isFinite(Number(flightPlan?.block_fuel ?? form?.elements?.block_fuel?.value))
      ? Number(flightPlan?.block_fuel ?? form?.elements?.block_fuel?.value)
      : null,
    estimated_time_enroute: Number.isFinite(Number(flightPlan?.estimated_time_enroute))
      ? Math.max(0, Math.round(Number(flightPlan.estimated_time_enroute)))
      : null,
    ofp_source: flightPlan?.source || null,
    dispatch_status: serverDispatch?.status || null,
    weather: serverDispatch?.weather || null
  };
  try { await call('/api/efb/context', context); } catch {}
}

function setAuthenticated(value) {
  connected = Boolean(value);
  document.body.classList.toggle('auth-locked', !connected);
  document.querySelectorAll('.protected-tab').forEach(tab => { tab.disabled = !connected; });
  if (!connected) window.HermesIdentity?.clear?.();
  window.dispatchEvent(new CustomEvent('hermes:auth-changed', { detail: { authenticated: connected } }));
}
setAuthenticated(false);

const settingsForm = $('#settingsForm');
const defaultSettings = { autoDetection: 'true', forcedSimulator: '', preferredSimulator: 'msfs2024', timeFormat: 'local', notifications: 'true', simbriefUsername: '', simbriefPilotId: '', vatsimCid: '', ivaoVid: '', preferredNetwork: '', flightPlanMode: 'account' };
let savedSettings = {};
try { savedSettings = JSON.parse(localStorage.prometheeAcarsSettings || '{}'); } catch {}
let localSettings = { ...defaultSettings, ...savedSettings };

const era = $('#era');
const appearance = $('#appearance');
const language = $('#language');
const hermesI18n = window.HermesI18n || { defaultLanguage: 'fr', supportedLanguages: ['fr'], normalize: () => 'fr', messages: { fr: {} } };
const allowedLanguages = hermesI18n.supportedLanguages;
const baseEras = ['modern', '2000', 'minitel'];
let allowedEras = [...baseEras];
let hermesEntitlements = [];

const entitlementAssetKey = entitlement => entitlement?.metadata?.asset_key || entitlement?.metadata?.assetKey || entitlement?.target_id || entitlement?.targetId || '';
const hasHermesEntitlement = key => hermesEntitlements.some(item => entitlementAssetKey(item) === key);
const entitlementLabels = { hermes_theme:'Thème', hermes_sound_pack:'Pack sons', hermes_efb_skin:'Skin EFB', livery:'Livrée' };

function applyHermesEntitlements(configuration) {
  hermesEntitlements = Array.isArray(configuration?.entitlements ?? configuration?.Entitlements)
    ? (configuration.entitlements ?? configuration.Entitlements) : [];
  const premiumEraMap = { 'theme.airinter-1978':'airinter-1978', 'theme.minitel-ambre':'minitel-ambre' };
  allowedEras = [...baseEras, ...Object.entries(premiumEraMap).filter(([key]) => hasHermesEntitlement(key)).map(([,era]) => era)];

  document.querySelectorAll('[data-premium-key]').forEach(option => {
    const owned = hasHermesEntitlement(option.dataset.premiumKey);
    option.disabled = !owned;
    const raw = option.textContent.replace(/^🔒\s*/, '').replace(/^✓\s*/, '');
    option.textContent = owned ? '✓ ' + raw : '🔒 ' + raw;
    option.title = owned ? 'Débloqué dans la boutique Air Inter' : 'À débloquer dans la boutique Prométhée';
  });

  const library = $('#hermesEntitlementLibrary');
  if (library) {
    const assets = hermesEntitlements.filter(item => ['hermes_theme','hermes_sound_pack','hermes_efb_skin','livery'].includes(item.type ?? item.Type));
    library.innerHTML = assets.length ? assets.map(item => {
      const type = item.type ?? item.Type;
      const key = entitlementAssetKey(item);
      const expires = item.expires_at ?? item.expiresAt ?? item.ExpiresAt;
      return `<article class="observation shop-entitlement"><strong>${escapeHtml(entitlementLabels[type] || type)}</strong><span>${escapeHtml(key || 'Contenu Air Inter')}</span><small>${expires ? 'Jusqu’au ' + new Date(expires).toLocaleString() : 'Permanent'}</small></article>`;
    }).join('') : '<p class="empty">Aucun contenu boutique Hermès débloqué.</p>';
  }

  const savedEra = localStorage.hermesEra;
  if (savedEra && !allowedEras.includes(savedEra)) applyDisplay('modern', document.body.dataset.appearance || 'light');

  const sound = $('#hermesSoundPack');
  const efb = $('#hermesEfbSkin');
  if (sound) {
    const saved = localStorage.hermesSoundPack || 'default';
    sound.value = [...sound.options].some(o => o.value === saved && !o.disabled) ? saved : 'default';
    localStorage.hermesSoundPack = sound.value;
  }
  if (efb) {
    const saved = localStorage.hermesEfbSkin || 'default';
    efb.value = [...efb.options].some(o => o.value === saved && !o.disabled) ? saved : 'default';
    localStorage.hermesEfbSkin = efb.value;
    document.body.dataset.efbSkin = efb.value;
  }
}

window.applyHermesEntitlements = applyHermesEntitlements;
const allowedAppearances = ['light', 'dark'];
const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

function translateHermes(key, languageCode = document.documentElement.lang || hermesI18n.defaultLanguage) {
  const lang = hermesI18n.normalize(languageCode);
  return hermesI18n.messages[lang]?.[key]
    ?? hermesI18n.messages[hermesI18n.defaultLanguage]?.[key]
    ?? key;
}

function applyLanguage(value, persist = true) {
  const nextLanguage = hermesI18n.normalize(value);
  document.documentElement.lang = nextLanguage;
  if (language) language.value = nextLanguage;

  document.querySelectorAll('[data-i18n]').forEach(node => {
    node.textContent = translateHermes(node.dataset.i18n, nextLanguage);
  });
  document.querySelectorAll('[data-i18n-placeholder]').forEach(node => {
    node.setAttribute('placeholder', translateHermes(node.dataset.i18nPlaceholder, nextLanguage));
  });
  document.querySelectorAll('[data-i18n-title]').forEach(node => {
    node.setAttribute('title', translateHermes(node.dataset.i18nTitle, nextLanguage));
  });

  if (persist) localStorage.hermesLanguage = nextLanguage;
}

window.hermesT = translateHermes;

function applyDisplay(eraValue, appearanceValue, persist = true) {
  const nextEra = allowedEras.includes(eraValue) ? eraValue : 'modern';
  const nextAppearance = allowedAppearances.includes(appearanceValue) ? appearanceValue : 'light';
  document.body.dataset.era = nextEra;
  document.body.dataset.appearance = nextAppearance;
  era.value = nextEra;
  appearance.value = nextAppearance;
  if (persist) {
    if (!allowedEras.includes(nextEra)) return;
    localStorage.hermesEra = nextEra;
    localStorage.hermesAppearance = nextAppearance;
    if (!reduceMotion) {
      document.body.dataset.themeTransition = 'true';
      setTimeout(() => delete document.body.dataset.themeTransition, 230);
    }
  }
}

const storedEra = localStorage.hermesEra || localStorage.prometheeEra || 'modern';
const storedAppearance = localStorage.hermesAppearance || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
applyDisplay(storedEra, storedAppearance, false);
era.onchange = () => {
  const nextEra = era.value;
  applyDisplay(nextEra, document.body.dataset.appearance);
  if (nextEra === 'minitel') {
    Promise.resolve(window.loadHermesMinitel?.()).then(() => window.HermesMinitel?.start?.()).catch(error => {
      showMessage('#settingsMessage', friendlyError(error, 'Le mode Minitel n’a pas pu être chargé.', 'Minitel'), true);
    });
  } else {
    window.HermesMinitel?.stop?.();
  }
};
appearance.onchange = () => applyDisplay(document.body.dataset.era, appearance.value);
const hermesSoundPack = $('#hermesSoundPack');
const hermesEfbSkin = $('#hermesEfbSkin');
if (hermesSoundPack) hermesSoundPack.onchange = () => {
  if (hermesSoundPack.selectedOptions[0]?.disabled) { hermesSoundPack.value='default'; return; }
  localStorage.hermesSoundPack = hermesSoundPack.value;
  document.body.dataset.soundPack = hermesSoundPack.value;
};
if (hermesEfbSkin) hermesEfbSkin.onchange = () => {
  if (hermesEfbSkin.selectedOptions[0]?.disabled) { hermesEfbSkin.value='default'; return; }
  localStorage.hermesEfbSkin = hermesEfbSkin.value;
  document.body.dataset.efbSkin = hermesEfbSkin.value;
};
const storedLanguage = localStorage.hermesLanguage || navigator.language || hermesI18n.defaultLanguage;
applyLanguage(storedLanguage, false);
if (language) language.onchange = () => applyLanguage(language.value);
Object.entries(localSettings).forEach(([key, value]) => {
  if (settingsForm.elements[key]) settingsForm.elements[key].value = value;
});
settingsForm.onsubmit = async event => {
  event.preventDefault();
  localSettings = { ...defaultSettings, ...localSettings, ...Object.fromEntries(new FormData(settingsForm)) };
  localStorage.prometheeAcarsSettings = JSON.stringify(localSettings);
  showMessage('#settingsMessage', 'Réglages locaux enregistrés.');
  if (connected) {
    await refreshAircraftVariantLibrary();
    await refreshOperations();
    if (selectedAircraft?.id) await refreshAircraftVariants();
  }
};

const hermesTabs = $$('.tab');
const enabledHermesTabs = () => hermesTabs.filter(button => !button.disabled && !button.hidden);
const activateHermesTab = button => {
  if (!button || (button.classList.contains('protected-tab') && !connected)) return;
  $$('.tab,.panel').forEach(node => node.classList.remove('active'));
  hermesTabs.forEach(node => {
    const selected = node === button;
    node.setAttribute('aria-selected', String(selected));
    node.tabIndex = selected ? 0 : -1;
  });
  button.classList.add('active');
  $('#' + button.dataset.tab)?.classList.add('active');
  if (button.dataset.tab === 'map') drawMap(flightMapState.lastTrack, lastStatus?.latest || {});
  if (button.dataset.tab === 'journal') refreshJournal();
  if (button.dataset.tab === 'datalink') refreshDatalink();
  if (button.dataset.tab === 'network') refreshNetwork();
};
hermesTabs.forEach(button => {
  button.onclick = () => activateHermesTab(button);
  button.onkeydown = event => {
    if (!['ArrowDown','ArrowUp','Home','End'].includes(event.key)) return;
    const tabs = enabledHermesTabs();
    const index = tabs.indexOf(button);
    if (index < 0 || !tabs.length) return;
    event.preventDefault();
    const next = event.key === 'Home' ? 0
      : event.key === 'End' ? tabs.length - 1
      : (index + (event.key === 'ArrowDown' ? 1 : -1) + tabs.length) % tabs.length;
    tabs[next]?.focus();
  };
});
$$('.message').forEach(node => {
  if (!node.hasAttribute('role')) node.setAttribute('role', 'status');
  if (!node.hasAttribute('aria-live')) node.setAttribute('aria-live', 'polite');
});

function pilotIdentity(value) {
  window.HermesIdentity?.apply?.(value);
}

async function login(form) {
  const body = Object.fromEntries(new FormData(form));
  try {
    const response = await call('/api/login', body);
    pilotIdentity(response);
    applyHermesEntitlements(response.configuration || response.Configuration || {});
    setAuthenticated(true);
    showMessage('#loginMessage', 'Connexion réussie. Chargement de vos opérations…');
    await refreshOperations();
    await refreshAircraftVariantLibrary();
    await refreshNetwork();
    if (lastStatus?.recoveryAvailable) document.querySelector('[data-tab="record"]').click();
    else document.querySelector('[data-tab="flight"]').click();
  } catch (error) {
    showMessage('#loginMessage', friendlyError(error) || 'Impossible de se connecter à Prométhée.', true);
  }
}
$('#loginForm').onsubmit = event => { event.preventDefault(); login(event.currentTarget); };

async function loginWithArgos() {
  const button = $('#argosLoginBtn');
  if (button) button.disabled = true;
  showMessage('#loginMessage', 'Ouverture d’Argos dans votre navigateur…');
  try {
    const response = await call('/api/login/argos');
    pilotIdentity(response);
    applyHermesEntitlements(response.configuration || response.Configuration || {});
    setAuthenticated(true);
    showMessage('#loginMessage', 'Connexion Argos réussie. Chargement de vos opérations…');
    await refreshOperations();
    await refreshAircraftVariantLibrary();
    await refreshNetwork();
    if (lastStatus?.recoveryAvailable) document.querySelector('[data-tab="record"]').click();
    else document.querySelector('[data-tab="flight"]').click();
  } catch (error) {
    showMessage('#loginMessage', friendlyError(error) || 'Impossible de se connecter avec Argos.', true);
  } finally {
    if (button) button.disabled = false;
  }
}

const argosLoginBtn = $('#argosLoginBtn');
if (argosLoginBtn) argosLoginBtn.onclick = loginWithArgos;

window.HermesIdentity?.initialize?.(call, registerHermesError);

const logoutBtn = $('#logoutBtn');
if (logoutBtn) {
  logoutBtn.onclick = async () => {
    if (!confirm('Se déconnecter d’Hermès ?\n\nLa session ACARS sera révoquée et la connexion Argos mémorisée sur ce PC sera oubliée.')) return;
    logoutBtn.disabled = true;
    try {
      await call('/api/logout', {});
      selectedOperation = null;
      selectedAircraft = null;
      selectedVariant = null;
      aircraftVariantState = null;
      aircraftEligibility = null;
      pirepId = null;
      flightPlan = null;
      linkedSimBrief = null;
      serverDispatch = null;
      lastDatalinkSnapshot = null;
      readiness = { operation: false, aircraft: false, ofp: false, pirep: false, simulator: Boolean(lastStatus?.latest) };
      clearOperationalWeather();
      $('#flightList')?.replaceChildren();
      const selected = $('#selectedOperation');
      if (selected) selected.hidden = true;
      try { await call('/api/efb/context', null); } catch {}
      setText($('#serverState'), 'Identité pilote à venir');
      setAuthenticated(false);
      document.querySelector('[data-tab="connect"]')?.click();
      showMessage('#loginMessage', 'Déconnexion terminée. La prochaine connexion Argos redemandera votre identité.');
    } catch (error) {
      showMessage('#loginMessage', friendlyError(error, 'Impossible de terminer la déconnexion.', 'Déconnexion'), true);
    } finally {
      logoutBtn.disabled = false;
    }
  };
}

function setIndicator(selector, state, label) {
  const node = $(selector);
  if (!node) return;
  node.classList.remove('ok', 'warn', 'bad', 'pending');
  node.classList.add(state || 'pending');
  if (label) {
    const dot = node.querySelector('b');
    node.replaceChildren(document.createTextNode(label + ' '));
    const nextDot = dot || document.createElement('b');
    nextDot.textContent = '●';
    node.append(nextDot);
  }
}












































const showBidsBtn = $('#showBidsBtn');
if (showBidsBtn) showBidsBtn.onclick = async () => {
  await refreshOperations();
  document.querySelector('.operations-browser')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};
const showSearchBtn = $('#showSearchBtn');
if (showSearchBtn) showSearchBtn.onclick = () => {
  setOperationsMode('search');
  renderOperations([], 'search');
};
$('#flightSearchForm').onsubmit = event => {
  event.preventDefault();
  searchFlights();
};





























async function refreshOperationalWeather() {
  const operationRef = selectedOperation?.operation_id || selectedOperation?.id || selectedOperation?.bid_id;
  if (!operationRef || !connected) {
    clearOperationalWeather();
    if (serverDispatch) serverDispatch.weather = null;
    scheduleEfbContextSync();
    return null;
  }

  const weather = unwrap(await call(`/api/v1/operations/${encodeURIComponent(operationRef)}/weather`));
  if (serverDispatch) serverDispatch.weather = weather;
  renderOperationalWeather(weather);
  scheduleEfbContextSync();
  return weather;
}





















$$('[data-plan-mode]').forEach(button => button.onclick = () => setPlanMode(button.dataset.planMode));
$('#simbriefUsername').value = localSettings.simbriefUsername || '';
$('#simbriefPilotId').value = localSettings.simbriefPilotId || '';
['simbriefUsername', 'simbriefPilotId'].forEach(key => {
  const node = $('#' + key);
  node.onchange = () => {
    localSettings[key] = node.value.trim();
    localStorage.prometheeAcarsSettings = JSON.stringify(localSettings);
    updateRecommendedPlanSource();
  };
});
setPlanMode(localSettings.flightPlanMode || 'account');
updateRecommendedPlanSource();
const prepareOfpBtn = $('#prepareOfpBtn');
if (prepareOfpBtn) prepareOfpBtn.onclick = () => {
  if (!requireAircraftForSimBrief(prepareOfpBtn)) return;
  const mode = recommendedPlanMode();
  setPlanMode(mode);
  updateRecommendedPlanSource();
  if (mode === 'account') $('#simbriefAccountOpenBtn')?.click();
  else if (mode === 'api') $('#simbriefBtn')?.click();
  else $('#planFile')?.click();
};

$('#vatsimPrefileBtn').onclick = async () => {
  const prefile = flightPlan?.network_prefiles?.vatsim;
  if (!prefile?.url) return showMessage('#networkPrefileMessage', 'Importez d’abord un OFP SimBrief.', true);
  try {
    await call('/api/open-external', { url: prefile.url });
    showMessage('#networkPrefileMessage', 'VATSIM ouvert avec le plan ICAO prérempli. Validez le dépôt sur myVATSIM ; vPilot le récupérera ensuite depuis le réseau.');
  } catch (error) {
    showMessage('#networkPrefileMessage', friendlyError(error), true);
  }
};

$('#ivaoPrefileBtn').onclick = async () => {
  const prefile = flightPlan?.network_prefiles?.ivao;
  const icaoPlan = flightPlan?.network_prefiles?.icao_flightplan || '';
  if (!prefile?.url) return showMessage('#networkPrefileMessage', 'Importez d’abord un OFP SimBrief.', true);
  try {
    let copied = false;
    if (icaoPlan && navigator.clipboard?.writeText) {
      try {
        await navigator.clipboard.writeText(icaoPlan);
        copied = true;
      } catch {}
    }
    await call('/api/open-external', { url: prefile.url });
    showMessage(
      '#networkPrefileMessage',
      copied
        ? 'IVAO Flight Plan System ouvert. Le plan ICAO a été copié dans le presse-papiers : collez-le dans le formulaire puis validez.'
        : 'IVAO Flight Plan System ouvert. Reprenez le plan ICAO affiché ci-dessus puis validez-le sur IVAO.'
    );
  } catch (error) {
    showMessage('#networkPrefileMessage', friendlyError(error), true);
  }
};

$('#simbriefAccountOpenBtn').onclick = async event => {
  if (!requireAircraftForSimBrief(event.currentTarget)) return;
  const form = $('#prefileForm');
  const flightId = form.elements.flight_id.value;
  const aircraftId = form.elements.aircraft_id.value;
  const username = $('#simbriefUsername').value.trim();
  const pilotId = $('#simbriefPilotId').value.trim();
  if (!flightId || !aircraftId) return showMessage('#simbriefState', 'Sélectionnez un vol et un appareil.', true);
  if (!username && !pilotId) return showMessage('#simbriefState', 'Ajoutez votre alias Navigraph / SimBrief ou votre Pilot ID avant d’envoyer le vol.', true);
  localSettings.simbriefUsername = username;
  localSettings.simbriefPilotId = pilotId;
  localStorage.prometheeAcarsSettings = JSON.stringify(localSettings);
  try {
    showMessage('#simbriefState', 'Vérification des données Air Inter en BDD…');
    await assertSimBriefReady(form, 'account');
    showMessage('#simbriefState', 'Envoi de la préparation Air Inter vers SimBrief…');
    const payload = unwrap(await call(simbriefPath('redirect'), simBriefPlanningPayload(form)));
    linkedSimBrief = payload;
    renderSimBriefPreparationSummary(payload.resolved || null);
    const editButton = $('#simbriefAccountEditBtn');
    if (editButton) editButton.hidden = !payload.edit_url;
    await call('/api/open-external', { url: payload.url });
    showMessage('#simbriefState', 'SimBrief est ouvert avec les données Air Inter. Personnalisez puis générez l’OFP, revenez ensuite dans Hermès pour l’importer.');
  } catch (error) {
    showMessage('#simbriefState', friendlyError(error), true);
  }
};

$('#simbriefAccountEditBtn').onclick = async () => {
  if (!linkedSimBrief?.edit_url) return showMessage('#simbriefState', 'Préparez d’abord ce vol dans SimBrief.', true);
  try { await call('/api/open-external', { url: linkedSimBrief.edit_url }); }
  catch (error) { showMessage('#simbriefState', friendlyError(error), true); }
};

$('#simbriefAccountImportBtn').onclick = async event => {
  if (!requireAircraftForSimBrief(event.currentTarget)) return;
  const form = $('#prefileForm');
  const flightId = form.elements.flight_id.value;
  const aircraftId = form.elements.aircraft_id.value;
  const username = $('#simbriefUsername').value.trim();
  const pilotId = $('#simbriefPilotId').value.trim();
  if (!flightId || !aircraftId) return showMessage('#simbriefState', 'Sélectionnez un vol et un appareil.', true);
  if (!username && !pilotId) return showMessage('#simbriefState', 'Renseignez votre alias Navigraph ou votre Pilot ID SimBrief.', true);
  localSettings.simbriefUsername = username;
  localSettings.simbriefPilotId = pilotId;
  localStorage.prometheeAcarsSettings = JSON.stringify(localSettings);
  try {
    showMessage('#simbriefState', 'Import du dernier OFP de votre compte SimBrief…');
    const briefing = unwrap(await call(simbriefPath('account/import'), {
      aircraft_id: aircraftId,
      username: username || null,
      pilot_id: pilotId || null
    }));
    linkedSimBrief = { ...(linkedSimBrief || {}), static_id: briefing.static_id, edit_url: briefing.edit_url };
    const editButton = $('#simbriefAccountEditBtn');
    if (editButton) editButton.hidden = !briefing.edit_url;
    await applyBriefing(briefing, 'le vol SimBrief lié à cette opération');
  } catch (error) {
    showMessage('#simbriefState', friendlyError(error), true);
  }
};

$('#aircraftTypeId').onchange = event => {
  const typeKey = event.target.value || '';
  selectedAircraft = null;
  const form = $('#prefileForm');
  if (form?.elements?.aircraft_id) form.elements.aircraft_id.value = '';
  if (selectedOperation) selectedOperation.aircraft = null;
  renderOperationLoad(null);
  populateAircraftInstances(typeKey, null);
  updateWorkflow();
  updateAircraftSelectionStatus();
  showMessage('#pirepMessage', typeKey
    ? 'Type sélectionné. Choisissez maintenant l’immatriculation précise.'
    : '');
};

$('#aircraftId').onchange = async event => {
  const select = event.target;
  const option = select.selectedOptions[0];
  let nextAircraft = null;
  try { nextAircraft = option?.dataset.aircraft ? JSON.parse(option.dataset.aircraft) : null; } catch {}

  if (!nextAircraft?.id) {
    selectedAircraft = null;
    const form = $('#prefileForm');
    if (form?.elements?.aircraft_id) form.elements.aircraft_id.value = '';
    if (selectedOperation) selectedOperation.aircraft = null;
    selectedVariant = null;
    renderAircraftVariants({ variants: [] });
    renderOperationLoad(null);
    updateAircraftSelectionStatus();
    updateWorkflow();
    return;
  }

  const operationRef = selectedOperation?.operation_id || selectedOperation?.id || selectedOperation?.bid_id;
  if (!operationRef) {
    select.value = selectedAircraft?.id || '';
    return showMessage('#pirepMessage', 'Impossible d’affecter l’appareil : opération Prométhée introuvable.', true);
  }

  const requestedLabel = nextAircraft.registration || nextAircraft.type_label || nextAircraft.subfleet || 'l’appareil';
  const typeSelect = $('#aircraftTypeId');
  select.disabled = true;
  if (typeSelect) typeSelect.disabled = true;
  showMessage('#pirepMessage', 'Affectation de ' + requestedLabel + ' à l’opération…');

  try {
    const assignment = unwrap(await call('/api/v1/operations/' + encodeURIComponent(operationRef) + '/aircraft', {
      _method: 'PUT',
      aircraft_id: nextAircraft.id
    }));

    selectedAircraft = assignment?.aircraft || nextAircraft;
    scheduleEfbContextSync();
    if (!selectedAircraft?.id) throw new Error('Prométhée n’a pas retourné l’appareil affecté.');
    if (selectedOperation) selectedOperation.aircraft = selectedAircraft;

    const form = $('#prefileForm');
    if (form?.elements?.aircraft_id) form.elements.aircraft_id.value = selectedAircraft.id;
    renderOperationLoad(selectedAircraft);
    updateAircraftSelectionStatus();

    if (typeSelect) typeSelect.value = selectedAircraft.type_key || nextAircraft.type_key || '';
    populateAircraftInstances(typeSelect?.value || selectedAircraft.type_key || '', selectedAircraft.id);

    await refreshDispatch();
    // The aircraft assignment changes the applicable add-on/SimBrief profile.
    // Refresh immediately instead of leaving a stale or empty variant selector.
    await refreshAircraftVariants();
    showMessage(
      '#pirepMessage',
      (selectedAircraft.registration || requestedLabel)
        + ' affecté · '
        + (selectedAircraft.type_label || selectedAircraft.subfleet || '')
        + ' · '
        + (selectedAircraft.passengers ?? '—') + '/' + (selectedAircraft.capacity ?? '—')
        + ' passagers prévus.'
    );
  } catch (error) {
    select.value = selectedAircraft?.id || '';
    showMessage('#pirepMessage', 'Affectation impossible : ' + friendlyError(error), true);
  } finally {
    if (typeSelect) typeSelect.disabled = false;
    select.disabled = false;
    updateWorkflow();
  }
};


$('#aircraftVariantId').onchange = async event => {
  const select = event.target;
  const option = select.selectedOptions[0];
  let variant = null;
  try { variant = option?.dataset.variant ? JSON.parse(option.dataset.variant) : null; } catch {}
  if (!variant?.id) return;

  const operationRef = selectedOperation?.operation_id || selectedOperation?.id || selectedOperation?.bid_id;
  if (!operationRef) return showMessage('#pirepMessage', 'Opération Prométhée introuvable.', true);

  select.disabled = true;
  try {
    const result = unwrap(await call('/api/v1/operations/' + encodeURIComponent(operationRef) + '/simulator-profile', {
      _method: 'PUT',
      variant_id: variant.id,
      simulator: simulatorCode()
    }));
    aircraftVariantState = result;
    selectedVariant = result.selected_variant || variant;
    renderVariantPreview(selectedVariant);
    if (selectedOperation) {
      selectedOperation.simbrief = {
        ...(selectedOperation.simbrief || {}),
        variant: selectedVariant,
        selected_variant_id: selectedVariant.id,
        type: selectedVariant.simbrief_strategy === 'proxy'
          ? (selectedAircraft?.icao || selectedOperation.simbrief?.type)
          : (selectedAircraft?.icao || selectedOperation.simbrief?.type),
        calculation_type: selectedVariant.simbrief_type || selectedOperation.simbrief?.calculation_type,
        strategy: selectedVariant.simbrief_strategy || selectedOperation.simbrief?.strategy,
        addon: selectedVariant.label
      };
    }
    setText($('#operationBrief'), 'Variante ' + (selectedVariant.label || selectedVariant.id)
      + (selectedVariant.simbrief_strategy === 'proxy'
        ? ' · compatible via profil Air Inter'
        : ' · profil SimBrief ' + (selectedVariant.simbrief_type || 'auto')));
    await refreshDispatch();
    updateWorkflow();
    showMessage('#pirepMessage', (selectedVariant.label || selectedVariant.id) + ' sélectionné pour cette opération.');
  } catch (error) {
    showMessage('#pirepMessage', 'Variante impossible : ' + friendlyError(error), true);
    await refreshAircraftVariants();
  } finally {
    select.disabled = false;
  }
};

const routeSuggestion = $('#routeSuggestion');
if (routeSuggestion) routeSuggestion.onchange = event => {
  const form = $('#prefileForm');
  if (!form?.elements?.route) return;
  form.elements.route.value = event.target.value || '';
  showMessage('#simbriefState', event.target.value
    ? 'Route compagnie sélectionnée. Vous pouvez encore la modifier manuellement.'
    : 'Route AUTO : SimBrief calculera la proposition lors de la génération.');
};

$('#resetDraftBtn').onclick = () => {
  const form = $('#prefileForm');
  let draft = {};
  try { draft = JSON.parse(form.dataset.programDraft || '{}'); } catch {}
  ['alt_airport_id', 'route', 'level'].forEach(name => {
    if (form.elements[name]) form.elements[name].value = draft[name] || '';
  });
  [
    'simbrief_type', 'callsign', 'units', 'planformat', 'maps', 'navlog',
    'tlr', 'notams', 'firnot', 'stepclimbs', 'etops', 'find_sidstar',
    'cruise', 'civalue', 'contpct', 'resvrule', 'selcal', 'deprwy',
    'arrrwy', 'taxiout', 'taxiin', 'pax', 'manualrmk'
  ].forEach(name => {
    if (form.elements[name]) form.elements[name].value = '';
  });
  form.elements.block_fuel.value = '';
  form.elements.notes.value = '';
  flightPlan = null;
  renderNetworkPrefiles(null);
  $('#planFile').value = '';
  $('#planBox').textContent = 'Aucun plan chargé.';
  showMessage('#simbriefState', 'Brouillon réinitialisé aux données du programme.');
};

$('#simbriefBtn').onclick = async event => {
  if (!requireAircraftForSimBrief(event.currentTarget)) return;
  const form = $('#prefileForm');
  const flightId = form.elements.flight_id.value;
  const aircraftId = form.elements.aircraft_id.value;
  if (!flightId || !aircraftId) return showMessage('#simbriefState', 'Sélectionnez un vol et un appareil.', true);
  try {
    showMessage('#simbriefState', 'Vérification des données Air Inter en BDD…');
    await assertSimBriefReady(form, 'company');
    showMessage('#simbriefState', 'Préparation de la demande SimBrief…');
    const session = unwrap(await call(simbriefPath('session'), simBriefPlanningPayload(form)));
    if (!session.state) throw new Error('Prométhée n’a pas créé de session SimBrief valide.');
    const popup = window.open('about:blank', 'PrometheeSimBrief', 'width=900,height=720');
    if (!popup) throw new Error('Autorisez les fenêtres contextuelles pour ouvrir SimBrief.');
    const dispatch = document.createElement('form');
    dispatch.method = 'GET';
    dispatch.action = session.worker_url;
    dispatch.target = 'PrometheeSimBrief';
    Object.entries(session.parameters || {}).forEach(([name, value]) => {
      if (value === null || value === undefined || value === '') return;
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = value;
      dispatch.append(input);
    });
    document.body.append(dispatch);
    dispatch.submit();
    dispatch.remove();
    showMessage('#simbriefState', 'Générez l’OFP puis fermez la fenêtre SimBrief.');

    const waitForClose = setInterval(async () => {
      if (!popup.closed) return;
      clearInterval(waitForClose);
      showMessage('#simbriefState', 'Import de l’OFP dans Prométhée…');
      for (let attempt = 1; attempt <= 5; attempt += 1) {
        try {
          const briefing = unwrap(await call(simbriefPath('import'), {
            aircraft_id: aircraftId,
            state: session.state
          }));
          await applyBriefing(briefing, 'l’API SimBrief');
          return;
        } catch (error) {
          if (attempt === 5) return showMessage('#simbriefState', friendlyError(error), true);
          await new Promise(resolve => setTimeout(resolve, 1500));
        }
      }
    }, 500);
  } catch (error) {
    showMessage('#simbriefState', friendlyError(error), true);
  }
};

$('#planFile').onchange = async event => {
  const file = event.target.files[0];
  if (!file) return;
  await file.text();
  // The local plan is displayed for the pilot only. phpVMS receives the
  // normalized route/OFP fields, never an arbitrary local file payload.
  flightPlan = {};
  renderNetworkPrefiles(null);
  $('#planBox').textContent = `${file.name} chargé localement (${Math.round(file.size / 1024)} Ko).`;
};
$('#clearPlanBtn').onclick = () => {
  flightPlan = null;
  $('#planFile').value = '';
  $('#planBox').textContent = 'Aucun plan chargé.';
  showMessage('#simbriefState', 'Sélectionnez un vol et un appareil.');
};



$('#prefileForm').onsubmit = async event => {
  event.preventDefault();
  await prefilePreparedOperation({ navigate: true, automatic: false });
};

async function action(path, success) {
  try {
    await call(path, path === '/api/start' ? { pirepId, operationId: selectedOperation?.operation_id || selectedOperation?.id || null } : {});
    showMessage('#recordMessage', success);
  } catch (error) {
    showMessage('#recordMessage', friendlyError(error), true);
  }
}
$('#startBtn').onclick = async () => {
  try {
    const dispatch = await assertDispatchCanStart();
    await action(
      '/api/start',
      dispatch?.resumable
        ? 'Vol Prométhée repris et Hermès rattaché au PIREP existant.'
        : 'Enregistrement démarré.'
    );
  } catch (error) {
    showMessage('#recordMessage', friendlyError(error), true);
  }
};
$('#pauseBtn').onclick = () => action('/api/pause', 'Enregistrement en pause.');
$('#resumeBtn').onclick = () => {
  const activeLocalFlight = lastStatus?.flight || lastStatus?.Flight || null;
  const localRecording = Boolean(activeLocalFlight?.recording ?? activeLocalFlight?.Recording);
  const localRecovery = Boolean(lastStatus?.recoveryAvailable);
  if (!activeLocalFlight || localRecording || localRecovery) {
    return showMessage(
      '#recordMessage',
      localRecovery
        ? 'Ce vol est en récupération : utilisez le Recovery Center.'
        : 'Aucun vol local en pause. Pour cette nouvelle opération, cliquez sur « Démarrer l’enregistrement ».',
      true
    );
  }
  return action('/api/resume', 'Enregistrement repris.');
};
$('#syncBtn').onclick = () => action('/api/sync', 'Données synchronisées.');
$('#fileBtn').onclick = () => {
  document.querySelector('[data-tab="review"]')?.click();
  renderReview(lastStatus?.review || lastStatus?.Review || lastFiledReview);
};

$('#abortPirepBtn').onclick = async () => {
  const activeLocalFlight = lastStatus?.flight || lastStatus?.Flight || null;
  const currentPirepId = activeLocalFlight?.pirepId || activeLocalFlight?.PirepId || pirepId;
  if (!currentPirepId) return showMessage('#recordMessage', 'Aucun PIREP à abandonner.', true);

  if (!confirm(
    `Abandonner et supprimer le PIREP ${currentPirepId} ?\n\n`
    + 'Prométhée supprimera cette tentative et sa télémétrie. Hermès archivera son état local avant nettoyage. '
    + 'Cette action est irréversible.'
  )) return;

  const button = $('#abortPirepBtn');
  if (button) button.disabled = true;
  try {
    if (activeLocalFlight) await call('/api/abandon-pirep', {});
    else await call('/api/delete-pirep?pirep=' + encodeURIComponent(currentPirepId));

    pirepId = null;
    readiness.pirep = false;
    serverDispatch = null;
    lastFiledReview = null;
    serverCompanyScore = null;
    serverCompanyScoreKey = null;
    scheduleEfbContextSync();
    showMessage('#recordMessage', 'PIREP abandonné et supprimé de Prométhée. Vous pouvez recommencer cette opération.');

    try { await refreshDispatch(); } catch {}
    await refreshStatus();
    await refreshOperations();
    updateWorkflow();
  } catch (error) {
    showMessage('#recordMessage', friendlyError(error, 'Impossible d’abandonner ce PIREP.', 'Abandon PIREP'), true);
  } finally {
    if (button) button.disabled = false;
  }
};















$('#datalinkCancelReplyBtn').onclick = () => {
  const form = $('#datalinkForm');
  form.elements.reply_to.value = '';
  form.elements.body.placeholder = 'Message à Air Inter OPS…';
  $('#datalinkCancelReplyBtn').hidden = true;
};

$('#datalinkRefreshBtn').onclick = refreshDatalink;
$('#datalinkForm').onsubmit = async event => {
  event.preventDefault();
  const operationId = currentDatalinkOperation();
  if (!operationId) return showMessage('#datalinkMessage', 'Sélectionnez une opération Air Inter.', true);
  const form = event.currentTarget;
  const body = {
    body: form.elements.body.value.trim(),
    category: form.elements.category.value,
    priority: form.elements.priority.value,
    requires_ack: form.elements.requires_ack.checked,
    reply_to: form.elements.reply_to.value || null
  };
  try {
    const snapshot = await call('/api/datalink/send?operation=' + encodeURIComponent(operationId), body);
    renderDatalink(snapshot);
    form.elements.body.value = '';
    form.elements.reply_to.value = '';
    form.elements.requires_ack.checked = false;
    $('#datalinkCancelReplyBtn').hidden = true;
    const queued = Number(datalinkRead(snapshot, 'pendingOutbound', 'PendingOutbound') || 0);
    showMessage('#datalinkMessage', queued ? 'Message conservé dans la file locale ; Hermès le renverra automatiquement.' : 'Message transmis à Air Inter OPS.');
  } catch (error) {
    showMessage('#datalinkMessage', friendlyError(error), true);
  }
};






let networkRefreshing = false;


/* Flight map rendering lives in hermes-map.js. */

initializeFlightMapControls();









const journalRefreshBtn = $('#journalRefreshBtn');
if (journalRefreshBtn) journalRefreshBtn.onclick = refreshJournal;

/* Flight Review rendering lives in hermes-review.js. */

initializeReviewActions();

const capabilityLabels = {
  Position: 'Position', AltitudeMsl: 'Altitude MSL', AltitudeAgl: 'Altitude AGL',
  IndicatedAirspeed: 'IAS', GroundSpeed: 'Vitesse sol', VerticalSpeed: 'Vitesse verticale',
  Heading: 'Cap', Track: 'Route sol', Pitch: 'Assiette', Bank: 'Inclinaison', Fuel: 'Carburant', GrossWeight: 'Masse totale',
  OnGround: 'Au sol', ParkingBrake: 'Frein de parc', Gear: 'Train', Flaps: 'Volets', Spoilers: 'Spoilers',
  Engines: 'Moteurs', BeaconLight: 'Beacon', NavigationLight: 'Feux NAV', StrobeLight: 'Strobes',
  LandingLight: 'Feux atterrissage', TaxiLight: 'Feux taxi', SeatBeltSign: 'Ceintures', Doors: 'Portes',
  Transponder: 'Transpondeur', Autopilot: 'Pilote automatique', ThrustStable: 'Poussée stable', Slew: 'Slew', Pause: 'Pause', SimulationRate: 'Vitesse simulation',
  TouchdownRate: 'Taux toucher', AircraftTitle: 'Titre appareil', AircraftIcao: 'ICAO appareil', AircraftModel: 'Modèle appareil'
};





$('#recoveryReviewBtn').onclick = async () => {
  const review = $('#recoveryReview');
  if (!review.hidden) { review.hidden = true; return; }
  try {
    const data = await call('/api/recovery');
    renderTimeline('#recoveryTimeline', data.journal || data.timeline || []);
    review.hidden = false;
  } catch (error) {
    showMessage('#recoveryMessage', friendlyError(error), true);
  }
};

$('#recoveryResumeBtn').onclick = async () => {
  try {
    // Rehydrate the authoritative server operation BEFORE re-enabling local
    // recording. A CTD/restart must never resume against a stale UI selection
    // or silently lose the physical aircraft assigned to the active PIREP.
    await refreshOperations();

    const localFlight = lastStatus?.flight || lastStatus?.Flight || {};
    const recovery = lastStatus?.recovery || lastStatus?.Recovery || {};
    const recoveryOperationId = localFlight.operationId || localFlight.OperationId
      || recovery.operationId || recovery.OperationId || null;
    const recoveryPirepId = localFlight.pirepId || localFlight.PirepId
      || recovery.pirepId || recovery.PirepId || null;
    const selectedOperationId = selectedOperation?.operation_id || selectedOperation?.operationId
      || selectedOperation?.bid_id || selectedOperation?.id || null;
    const selectedPirepId = selectedOperation?.pirep_id || selectedOperation?.pirep?.id || pirepId || null;
    const assignedAircraft = selectedOperation?.aircraft || null;

    if (!selectedOperation || (recoveryOperationId && String(selectedOperationId) !== String(recoveryOperationId))) {
      throw new Error('Reprise refusée : l’opération Prométhée du vol interrompu n’a pas pu être restaurée.');
    }
    if (recoveryPirepId && selectedPirepId && String(selectedPirepId) !== String(recoveryPirepId)) {
      throw new Error('Reprise refusée : le PIREP restauré ne correspond pas au vol local interrompu.');
    }
    if (!assignedAircraft?.id) {
      throw new Error('Reprise refusée : l’appareil affecté à cette opération est introuvable dans Prométhée.');
    }

    await call('/api/recovery/resume', {});
    showMessage(
      '#recoveryMessage',
      'Vol repris'
        + (assignedAircraft.registration ? ' sur ' + assignedAircraft.registration : '')
        + '. Hermès continue à partir de l’état local sauvegardé.'
    );
    $('#recoveryCenter').hidden = true;
    document.querySelector('[data-tab="record"]')?.click();
    await refreshStatus();
  } catch (error) {
    showMessage('#recoveryMessage', friendlyError(error), true);
  }
};

$('#recoveryAbandonBtn').onclick = async () => {
  const pirep = lastStatus?.recovery?.pirepId ?? lastStatus?.recovery?.PirepId ?? 'ce vol';
  if (!confirm(
    `Abandonner ${pirep} ?\n\n`
    + 'Le PIREP sera supprimé de Prométhée et son état local sera archivé dans Hermès avant nettoyage. '
    + 'Cette action est irréversible.'
  )) return;
  try {
    await call('/api/recovery/abandon', {});
    pirepId = null;
    readiness.pirep = false;
    serverDispatch = null;
    scheduleEfbContextSync();
    $('#recoveryCenter').hidden = true;
    showMessage('#recordMessage', 'Vol interrompu abandonné : PIREP supprimé de Prométhée et récupération locale archivée.');
    await refreshStatus();
    await refreshOperations();
    if (selectedOperation) {
      try { await refreshDispatch(); } catch {}
    }
  } catch (error) {
    showMessage('#recoveryMessage', friendlyError(error, 'Impossible d’abandonner ce PIREP.', 'Recovery Center'), true);
  }
};

function updateRemotePolicy(configuration) {
  const node = $('#remotePolicy');
  if (!configuration) { node.hidden = true; return; }
  const interval = configuration.positionIntervalSeconds ?? configuration.PositionIntervalSeconds;
  const minimum = configuration.minimumVersion ?? configuration.MinimumVersion;
  node.textContent = `Politique Prométhée : position toutes les ${interval} s${minimum ? ' · version minimale ' + minimum : ''}.`;
  node.hidden = false;
}

async function refreshStatus() {
  try {
    const status = await call('/api/status');
    lastStatus = status;
    const flight = status.flight;
    const latest = status.latest || {};
    const value = (...names) => {
      for (const name of names) {
        if (latest[name] !== undefined && latest[name] !== null) return latest[name];
      }
      return null;
    };
    if (status.connected && !connected) setAuthenticated(true);
    readiness.simulator = Boolean(status.latest);
    const recording = Boolean(flight?.recording ?? flight?.Recording);
    const recovery = Boolean(status.recoveryAvailable);
    const pendingCount = Number(status.pending || 0);
    const syncState = String(status.syncState || 'IDLE').toUpperCase();
    const networkDegraded = syncState === 'RETRYING' && pendingCount > 0;
    setIndicator('#prometheeIndicator', !status.connected ? 'bad' : (networkDegraded ? 'warn' : 'ok'),
      !status.connected ? 'PROMÉTHÉE OFFLINE' : (networkDegraded ? 'PROMÉTHÉE RETRY' : 'PROMÉTHÉE'));
    const simReconnecting = String(status.simLinkState || '').toUpperCase() === 'RECONNECTING';
    setIndicator('#simIndicator', status.latest ? 'ok' : (simReconnecting ? 'warn' : ((status.detectedSimulators || []).length ? 'warn' : 'bad')),
      status.latest ? 'SIM' : (simReconnecting ? 'SIM RECONNECT' : 'SIM WAIT'));
    setIndicator('#trackingIndicator', recording ? 'ok' : (recovery ? 'warn' : 'pending'), recording ? 'TRACKING' : (recovery ? 'RECOVERY' : 'TRACKING'));
    setIndicator('#syncIndicator', pendingCount === 0 ? 'ok' : (networkDegraded ? 'bad' : 'warn'), pendingCount === 0 ? 'SYNC' : `SYNC ${pendingCount}`);
    updateWorkflow();
    renderRecovery(status);
    renderAircraftCapabilities(status.aircraftCapabilities || status.AircraftCapabilities);
    const simulators = (status.detectedSimulators || []).map(item => item.displayName || item.DisplayName).filter(Boolean);
    setText($('#simState'), simulators.length ? `${simulators.join(' · ')} — ${status.sim || 'connexion en attente'}` : status.sim || 'Simulateur non détecté');
    setText($('#pending'), String(status.pending ?? 0));
    setText($('#phase'), flight?.phase || flight?.Phase || '—');
    setText($('#distance'), flight ? `${Number(flight.distance ?? flight.Distance ?? 0).toFixed(1)} NM` : '—');
    setText($('#airborne'), flight ? `${Math.floor(Number(flight.airborneSeconds ?? flight.AirborneSeconds ?? 0) / 60)} min` : '—');
    const simulatorWarning = recording && simReconnecting
      ? 'Liaison simulateur perdue — Hermès conserve le vol et tente une reconnexion automatique. Aucune donnée absente n’est inventée.'
      : '';
    const networkWarning = recording && networkDegraded
      ? `Prométhée indisponible — le vol continue d’être enregistré localement. ${pendingCount} message${pendingCount > 1 ? 's' : ''} en attente ; votre vol reste sauvegardé.`
      : '';
    setText($('#warning'), simulatorWarning || networkWarning || status.warning || '');
    const altitude = value('altitude', 'Altitude', 'altitudeMslFeet', 'AltitudeMslFeet');
    const groundSpeed = value('gs', 'Gs', 'groundSpeedKnots', 'GroundSpeedKnots');
    const fuel = value('fuel', 'Fuel', 'fuelWeight', 'FuelWeight');
    setText($('#altitude'), altitude == null ? '—' : `${Math.round(altitude)} ft`);
    setText($('#groundSpeed'), groundSpeed == null ? '—' : `${Math.round(groundSpeed)} kt`);
    setText($('#fuel'), fuel == null ? '—' : `${Math.round(fuel)} lb`);
    updateRemotePolicy(status.remoteConfiguration || status.RemoteConfiguration);
    if (flight?.pirepId || flight?.PirepId) pirepId = flight.pirepId || flight.PirepId;
    drawMap(status.track || [], latest);
    renderTimeline('#timeline', flight?.timeline || flight?.Timeline || []);
    renderTimeline('#journalEntries', flight?.journal || flight?.Journal || flight?.timeline || flight?.Timeline || []);
    const journalPirep = flight?.pirepId || flight?.PirepId;
    const journalPhase = flight?.phase || flight?.Phase;
    setText($('#journalCurrentMeta'), journalPirep
      ? [journalPirep, journalPhase || 'EN COURS'].filter(Boolean).join(' · ')
      : 'Aucun vol en cours');
    renderReview(status.review || status.Review || lastFiledReview);
  } catch {}
}

const hermesPolling = {
  status: { timer: null, delay: 1000, enabled: () => true, run: refreshStatus },
  datalink: { timer: null, delay: 5000, enabled: () => $('#datalink')?.classList.contains('active'), run: refreshDatalink },
  network: { timer: null, delay: 15000, enabled: () => $('#network')?.classList.contains('active'), run: refreshNetwork },
  weather: { timer: null, delay: 300000, enabled: () => connected && Boolean(selectedOperation), run: refreshOperationalWeather }
};
const stopHermesPolling = () => {
  Object.values(hermesPolling).forEach(poller => {
    if (poller.timer) clearTimeout(poller.timer);
    poller.timer = null;
  });
};
const scheduleHermesPoller = poller => {
  if (poller.timer) clearTimeout(poller.timer);
  poller.timer = null;
  if (document.hidden) return;
  poller.timer = setTimeout(async () => {
    try {
      if (!document.hidden && poller.enabled()) await poller.run();
    } finally {
      scheduleHermesPoller(poller);
    }
  }, poller.delay);
};
const startHermesPolling = () => {
  stopHermesPolling();
  Object.values(hermesPolling).forEach(scheduleHermesPoller);
};

initializeOperationalWeather(() => refreshOperationalWeather());
updateWorkflow();
drawMap([]);
refreshStatus();
startHermesPolling();
document.addEventListener('visibilitychange', async () => {
  if (document.hidden) return stopHermesPolling();
  await refreshStatus();
  if ($('#datalink')?.classList.contains('active')) await refreshDatalink();
  if ($('#network')?.classList.contains('active')) await refreshNetwork();
  drawMap(flightMapState.lastTrack, lastStatus?.latest || {});
  startHermesPolling();
});
window.addEventListener('pagehide', stopHermesPolling, {once:true});
call('/api/about').then(info => {
  setText($('#build'), 'Version ' + info.version);
}).catch(() => setText($('#build'), 'Version inconnue'));



const saveAircraftVariantsBtn = $('#saveAircraftVariantsBtn');
if (saveAircraftVariantsBtn) saveAircraftVariantsBtn.onclick = async () => {
  if (!connected) return setText($('#aircraftVariantLibraryMessage'), 'Connectez-vous d’abord.');
  const checked = Array.from(document.querySelectorAll('#aircraftVariantLibrary input[type="checkbox"][data-variant-id]:checked'))
    .map(node => node.dataset.variantId);
  const preferredNode = document.querySelector('#aircraftVariantLibrary input[name="preferredAircraftVariant"]:checked');
  const preferred = preferredNode && checked.includes(preferredNode.value) ? preferredNode.value : null;
  try {
    await call('/api/v1/me/simulator-profiles', {
      _method: 'PUT',
      variant_ids: checked,
      preferred_variant_id: preferred
    });
    setText($('#aircraftVariantLibraryMessage'), 'Bibliothèque enregistrée sur votre compte Air Inter.');
    await refreshAircraftVariantLibrary();
    if (selectedAircraft?.id) await refreshAircraftVariants();
  } catch (error) {
    setText($('#aircraftVariantLibraryMessage'), friendlyError(error));
  }
};

const checkUpdateBtn = $('#checkUpdateBtn');
if (checkUpdateBtn) checkUpdateBtn.onclick = async () => {
  setText($('#updateMessage'), 'Vérification auprès de Prométhée…');
  try {
    const result = await call('/api/update/check');
    if (!result.ok) {
      setText($('#updateMessage'), `Échec de la vérification : ${result.error || 'serveur indisponible'}`);
    } else if (result.updateAvailable) {
      setText($('#updateMessage'), `Mise à jour disponible : ${result.currentVersion} → ${result.latestVersion} (${result.channel || 'stable'}).`);
    } else {
      setText($('#updateMessage'), `Hermès est à jour (${result.currentVersion}).`);
    }
  } catch (error) {
    setText($('#updateMessage'), `Échec de la vérification : ${friendlyError(error)}`);
  }
};
