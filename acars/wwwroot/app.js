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
let lastDatalinkSnapshot = null;
let datalinkRefreshing = false;
let recoveryWasVisible = false;
let readiness = { operation: false, aircraft: false, ofp: false, pirep: false, simulator: false };

function setAuthenticated(value) {
  connected = Boolean(value);
  document.body.classList.toggle('auth-locked', !connected);
  document.querySelectorAll('.protected-tab').forEach(tab => { tab.disabled = !connected; });
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
const allowedEras = ['modern', '2000', 'minitel'];
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

$$('.tab').forEach(button => {
  button.onclick = () => {
    if (button.classList.contains('protected-tab') && !connected) return;
    $$('.tab,.panel').forEach(node => node.classList.remove('active'));
    button.classList.add('active');
    $('#' + button.dataset.tab).classList.add('active');
    if (button.dataset.tab === 'datalink') refreshDatalink();
    if (button.dataset.tab === 'network') refreshNetwork();
  };
});

function pilotIdentity(value) {
  const user = unwrap(value)?.user ?? unwrap(value) ?? {};
  const first = user.first_name || user.firstname || user.firstName || '';
  const last = user.last_name || user.lastname || user.lastName || '';
  const name = [first, last].filter(Boolean).join(' ') || user.name || user.name_private || '';
  const callsign = user.ident || user.pilot_id || user.pilotId || '';
  setText($('#serverState'), name && callsign ? `${name} · ${callsign}` : name || callsign || 'Pilote connecté');
}

async function login(form) {
  const body = Object.fromEntries(new FormData(form));
  try {
    const response = await call('/api/login', body);
    pilotIdentity(response);
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

function snapshotValue(snapshot, camel, pascal = camel) {
  return snapshot?.[camel] ?? snapshot?.[pascal] ?? null;
}

function buildWorkflowState() {
  const server = serverDispatch?.server_checks;
  return server ? {
    operation: Boolean(server.operation || selectedOperation),
    aircraft: Boolean(server.aircraft || selectedAircraft?.id),
    // The desktop already owns the imported briefing before Prométhée's next
    // dispatch refresh. Do not visually regress to step 03 during that window.
    ofp: Boolean(server.ofp || flightPlan || selectedOperation?.simbrief?.available),
    pirep: Boolean(server.pirep || pirepId)
  } : {
    operation: Boolean(selectedOperation),
    aircraft: Boolean(selectedAircraft?.id),
    ofp: Boolean(flightPlan || selectedOperation?.simbrief?.available),
    pirep: Boolean(pirepId)
  };
}

function simBriefPreparationData(resolved = null) {
  const form = $('#prefileForm');
  const flight = normalizeFlight(selectedOperation?.flight || selectedOperation || {});
  const authoritative = resolved || {};
  const demand = authoritative.demand || {};
  const params = authoritative.parameters || {};
  const aircraft = authoritative.aircraft || selectedAircraft || {};
  const paxOverride = form?.elements?.pax?.value?.trim();
  const plannedPax = paxOverride !== ''
    ? Number(paxOverride)
    : Number(params.pax ?? demand.passengers ?? aircraft.passengers);
  return {
    ident: authoritative.flight?.ident || displayFlightIdent(flight),
    departure: authoritative.origin?.icao || flight.departure || '—',
    arrival: authoritative.destination?.icao || flight.arrival || '—',
    registration: aircraft.registration || '—',
    type: aircraft.simbrief_display_type || aircraft.icao || selectedOperation?.simbrief?.type || 'AUTO',
    calculationType: aircraft.simbrief_type || selectedOperation?.simbrief?.calculation_type || selectedVariant?.simbrief_type || null,
    strategy: aircraft.simbrief_strategy || selectedOperation?.simbrief?.strategy || selectedVariant?.simbrief_strategy || 'native',
    historicalVariant: aircraft.historical_variant?.name
      || aircraft.resolved_profile?.variant?.name
      || selectedAircraft?.historical_variant?.name
      || '',
    configuration: aircraft.configuration?.name
      || aircraft.resolved_profile?.configuration?.name
      || selectedAircraft?.configuration?.name
      || '',
    addon: selectedVariant?.label || aircraft.simulator_profile?.label || selectedOperation?.simbrief?.addon || '',
    variant: selectedVariant?.label || aircraft.simulator_profile?.label || selectedOperation?.simbrief?.addon || '',
    pax: Number.isFinite(plannedPax) ? plannedPax : null,
    capacity: Number(demand.capacity ?? aircraft.capacity) || null,
    loadFactor: Number(demand.load_factor_percent ?? aircraft.load_factor_percent),
    level: params.fl || normalizeFlightLevel(form?.elements?.level?.value || flight.level) || null
  };
}

function renderSimBriefPreparationSummary(resolved = null) {
  const data = simBriefPreparationData(resolved);
  const nodes = [$('#simbriefAccountSummary'), $('#simbriefApiSummary')].filter(Boolean);
  const hasAircraft = Boolean(selectedAircraft?.id || resolved?.aircraft?.id);
  nodes.forEach(node => {
    node.hidden = !hasAircraft;
    if (!hasAircraft) { node.replaceChildren(); return; }

    const entries = [
      ['VOL', data.ident],
      ['ROUTE', data.departure + ' → ' + data.arrival],
      ['APPAREIL', data.registration + ' · ' + data.type],
      ['VARIANTE RÉELLE', data.historicalVariant || 'Non renseignée dans sb-airframe'],
      ['CONFIGURATION', data.configuration || 'Configuration héritée'],
      ['ADD-ON', data.addon || 'AUTO / générique'],
      ['PAX ENVOYÉS', data.pax === null ? 'AUTO' : String(data.pax) + (data.capacity ? ' / ' + data.capacity : '')],
      ['REMPLISSAGE', Number.isFinite(data.loadFactor) ? data.loadFactor.toFixed(1).replace('.0','') + ' %' : '—'],
      ['NIVEAU', data.level ? 'FL' + String(data.level).padStart(3, '0') : 'AUTO']
    ];

    node.replaceChildren();
    entries.forEach(([label, value]) => {
      const item = document.createElement('span');
      const key = document.createElement('small');
      const strong = document.createElement('strong');
      key.textContent = label;
      strong.textContent = value;
      item.append(key, strong);
      node.append(item);
    });
  });
}

function showActionTooltip(button, message) {
  if (!button) return;
  document.querySelectorAll('.hermes-action-tooltip').forEach(node => node.remove());
  const bubble = document.createElement('div');
  bubble.className = 'hermes-action-tooltip';
  bubble.setAttribute('role', 'status');
  bubble.textContent = message;
  document.body.append(bubble);
  const rect = button.getBoundingClientRect();
  const width = Math.min(380, Math.max(250, bubble.offsetWidth || 300));
  const left = Math.min(window.innerWidth - width - 12, Math.max(12, rect.left + rect.width / 2 - width / 2));
  bubble.style.width = width + 'px';
  bubble.style.left = left + 'px';
  bubble.style.top = Math.min(window.innerHeight - bubble.offsetHeight - 12, rect.bottom + 10) + 'px';
  button.classList.add('needs-attention');
  setTimeout(() => {
    bubble.classList.add('closing');
    button.classList.remove('needs-attention');
    setTimeout(() => bubble.remove(), 180);
  }, 3600);
}

function requireAircraftForSimBrief(button) {
  if (selectedAircraft?.id && $('#prefileForm')?.elements?.aircraft_id?.value) return true;
  showActionTooltip(button, 'Sélectionnez d’abord un type puis une immatriculation. Hermès doit connaître l’appareil précis avant d’envoyer ou récupérer un OFP SimBrief.');
  $('#aircraftTypeId')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
  setTimeout(() => $('#aircraftTypeId')?.focus(), 250);
  return false;
}

function updateAircraftSelectionStatus() {
  const node = $('#aircraftSelectionStatus');
  if (!node) return;
  const typeSelected = Boolean($('#aircraftTypeId')?.value);
  const registration = selectedAircraft?.registration || selectedAircraft?.name || '';
  const label = selectedAircraft?.type_label || selectedAircraft?.subfleet || selectedAircraft?.icao || '';
  const historicalVariant = selectedAircraft?.historical_variant?.name
    || selectedAircraft?.resolved_profile?.variant?.name
    || '';
  const configuration = selectedAircraft?.configuration?.name
    || selectedAircraft?.resolved_profile?.configuration?.name
    || '';

  node.classList.toggle('ready', Boolean(selectedAircraft?.id));
  node.classList.toggle('pending', !selectedAircraft?.id);
  const strong = node.querySelector('strong');
  const detail = node.querySelector('span');

  if (selectedAircraft?.id) {
    setText(strong, 'APPAREIL PRÊT');
    setText(detail, [registration, label, historicalVariant, configuration].filter(Boolean).join(' · ')
      + ' — configuration résolue automatiquement, SimBrief peut maintenant être préparé.');
  } else if (typeSelected) {
    setText(strong, 'IMMATRICULATION REQUISE');
    setText(detail, 'Type sélectionné. Choisissez maintenant l’appareil précis / l’immatriculation.');
  } else {
    setText(strong, 'APPAREIL REQUIS');
    setText(detail, 'Choisissez un type d’appareil puis une immatriculation avant de préparer SimBrief.');
  }
  renderSimBriefPreparationSummary();
}

function updateNextAction(state, ready) {
  const title = $('#nextActionTitle');
  const text = $('#nextActionText');
  const button = $('#nextActionBtn');
  if (!title || !text || !button) return;

  const dispatchStatus = String(serverDispatch?.status || '').toUpperCase();
  const terminal = ['COMPLETED', 'CANCELLED'].includes(dispatchStatus);
  const awaitingFiling = dispatchStatus === 'AWAITING_FILING';
  let action = () => {};
  if (terminal) {
    setText(title, dispatchStatus === 'COMPLETED' ? 'Vol déjà terminé' : 'Opération annulée');
    setText(text, dispatchStatus === 'COMPLETED'
      ? 'Ce PIREP a déjà été déposé. Sélectionnez une autre réservation ou un nouveau vol du programme.'
      : 'Cette opération ne peut plus être démarrée. Sélectionnez une autre réservation.');
    setText(button, 'Choisir un autre vol');
    action = () => document.querySelector('[data-tab="flight"]')?.click();
  } else if (awaitingFiling) {
    setText(title, 'Vol arrivé · PIREP à déposer');
    setText(text, 'Hermès a reçu l’événement IN. Ouvrez Flight Review, vérifiez la synthèse puis déposez le rapport final.');
    setText(button, 'Ouvrir Flight Review');
    action = () => document.querySelector('[data-tab="review"]')?.click();
  } else if (!state.operation) {
    setText(title, 'Choisissez votre vol');
    setText(text, 'Sélectionnez une réservation ou recherchez une ligne du programme Air Inter.');
    setText(button, 'Choisir un vol');
    action = () => { setOperationsMode('bids'); document.querySelector('.operations-browser')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); };
  } else if (!state.aircraft) {
    setText(title, 'Affectez un appareil');
    setText(text, 'Hermès n’affiche que les appareils autorisés et disponibles pour cette opération.');
    setText(button, 'Choisir l’appareil');
    action = () => { $('#aircraftTypeId')?.focus(); $('#aircraftTypeId')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); };
  } else if (!state.ofp) {
    const mode = localSettings.flightPlanMode || 'account';
    const hasSimBriefIdentity = Boolean($('#simbriefUsername')?.value.trim() || $('#simbriefPilotId')?.value.trim());
    setText(title, mode === 'account' && !hasSimBriefIdentity ? 'Reliez votre compte SimBrief' : 'Préparez le briefing');
    setText(text, mode === 'account'
      ? (hasSimBriefIdentity
          ? 'Envoyez les données Air Inter et vos ajustements vers SimBrief, puis importez l’OFP généré.'
          : 'Renseignez votre alias Navigraph / SimBrief ou votre Pilot ID. Hermès ne demande jamais votre mot de passe.')
      : 'Préparez l’OFP avant le pré-dépôt du PIREP.');
    setText(button, mode === 'account' ? (hasSimBriefIdentity ? 'Envoyer vers SimBrief' : 'Ajouter mon alias') : (mode === 'api' ? 'Générer l’OFP' : 'Charger un plan'));
    action = () => {
      if (mode === 'account' && !hasSimBriefIdentity) {
        $('#simbriefUsername')?.focus();
        $('#simbriefUsername')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } else if (mode === 'account') $('#simbriefAccountOpenBtn')?.click();
      else if (mode === 'api') $('#simbriefBtn')?.click();
      else $('#planFile')?.click();
    };
  } else if (!state.pirep) {
    setText(title, 'Finalisez la préparation');
    setText(text, 'Le briefing est prêt. Hermès va enregistrer la préparation opérationnelle avant le départ.');
    setText(button, 'Finaliser la préparation');
    action = () => $('#prefileForm')?.requestSubmit();
  } else if (!readiness.simulator) {
    setText(title, 'Connectez le simulateur');
    setText(text, 'La préparation est terminée. Hermès attend maintenant une télémétrie valide du simulateur.');
    setText(button, 'Voir l’état simulateur');
    action = () => document.querySelector('[data-tab="record"]')?.click();
  } else {
    setText(title, ready ? 'Prêt pour le départ' : 'Contrôles avant départ');
    setText(text, ready
      ? 'Prométhée, le simulateur et la préparation sont alignés. Vous pouvez démarrer l’enregistrement.'
      : 'Un contrôle obligatoire reste à satisfaire avant le démarrage.');
    setText(button, ready ? 'Passer au vol' : 'Voir les contrôles');
    action = () => document.querySelector('[data-tab="record"]')?.click();
  }
  button.onclick = action;
}

function updatePreflight(status, state, ready) {
  const container = $('#preflightChecks');
  if (!container) return;
  const latest = status?.latest || {};
  const onGround = snapshotValue(latest, 'onGround', 'OnGround');
  const parkingBrake = snapshotValue(latest, 'parkingBrake', 'ParkingBrake');
  const engines = snapshotValue(latest, 'enginesRunning', 'EnginesRunning');
  const enginesKnown = Array.isArray(engines) && engines.length > 0;
  const enginesStopped = enginesKnown ? !engines.some(Boolean) : null;
  const active = status?.activeConnector;
  const connectorName = active?.name || active?.Name || status?.sim || 'Simulateur';

  const capabilityReport = status?.aircraftCapabilities || status?.AircraftCapabilities || {};
  const detectedAdapter = capabilityReport.adapterId || capabilityReport.AdapterId || null;
  const acceptedAdapters = Array.isArray(selectedVariant?.adapter_ids) ? selectedVariant.adapter_ids : [];
  const variantMatch = acceptedAdapters.length === 0 ? null : (detectedAdapter ? acceptedAdapters.includes(detectedAdapter) : null);

  const checks = [
    ['VOL', state.operation, state.operation ? 'opération sélectionnée' : 'à sélectionner', 'blocking'],
    ['APPAREIL', state.aircraft, state.aircraft ? 'appareil affecté' : 'à sélectionner', 'blocking'],
    ['OFP', state.ofp, state.ofp ? 'briefing disponible' : 'à préparer', 'blocking'],
    ['PRÉPARATION', state.pirep, state.pirep ? 'enregistrée' : 'à finaliser', 'blocking'],
    ['SIMULATEUR', readiness.simulator, readiness.simulator ? connectorName : 'télémétrie en attente', 'blocking'],
    ['ADD-ON', selectedVariant ? true : null, !selectedVariant
      ? 'non sélectionné · profil simulateur facultatif'
      : (variantMatch === true
          ? (selectedVariant.label + ' · add-on Prométhée · détecté')
          : variantMatch === false
            ? (selectedVariant.label + ' · add-on Prométhée · simulateur détecté '
                + (capabilityReport.adapterName || capabilityReport.AdapterName || detectedAdapter || 'inconnu')
                + ' · comparaison informative')
            : (selectedVariant.label + ' · add-on sélectionné · identité simulateur informative')), 'informational'],
    ['AU SOL', onGround === null ? null : onGround === true, onGround === null ? 'information indisponible' : (onGround ? 'confirmé' : 'avion en vol'), 'blocking'],
    ['FREIN DE PARC', parkingBrake === null ? null : parkingBrake === true, parkingBrake === null ? 'information indisponible' : (parkingBrake ? 'serré' : 'desserré'), 'verify'],
    ['MOTEURS', enginesStopped, enginesStopped === null ? 'information indisponible' : (enginesStopped ? 'arrêtés' : 'en fonctionnement'), 'verify']
  ];

  container.replaceChildren();
  checks.forEach(([name, passed, detail, level]) => {
    const item = document.createElement('span');
    const stateClass = passed === true ? 'ok' : passed === null ? 'unknown' : 'pending';
    const effectiveLevel = level === 'informational' ? 'informational' : (passed === false ? 'blocking' : (passed === null ? 'verify' : level));
    item.className = 'preflight-check ' + stateClass + ' ' + effectiveLevel;
    item.dataset.level = effectiveLevel;
    const mark = passed === true ? '✓' : passed === null ? '?' : '•';
    item.textContent = `${mark} ${name} — ${detail}`;
    container.append(item);
  });

  const safety = $('#flightSafetyState');
  if (safety) {
    const recording = Boolean(status?.flight?.recording ?? status?.flight?.Recording);
    setText(safety, recording ? 'TRACKING' : (ready ? 'READY' : 'STANDBY'));
    safety.classList.toggle('ready', ready || recording);
  }
}

function updateWorkflow() {
  const state = buildWorkflowState();
  const phases = {
    flight: state.operation && state.aircraft,
    briefing: state.operation && state.aircraft && state.ofp && state.pirep,
    ready: state.operation && state.aircraft && state.ofp && state.pirep && readiness.simulator
  };
  const currentPhase = !phases.flight ? 'flight' : (!phases.briefing ? 'briefing' : 'ready');
  $$('#workflow [data-phase]').forEach(node => {
    const key = node.dataset.phase;
    const passed = Boolean(phases[key]);
    node.classList.toggle('done', passed);
    node.classList.toggle('current', key === currentPhase && !passed);
  });
  const latest = lastStatus?.latest || {};
  const onGround = snapshotValue(latest, 'onGround', 'OnGround');
  const parkingBrake = snapshotValue(latest, 'parkingBrake', 'ParkingBrake');
  const engines = snapshotValue(latest, 'enginesRunning', 'EnginesRunning');
  const enginesStoppedOrUnknown = !Array.isArray(engines) || engines.length === 0 || !engines.some(Boolean);
  const preflightSafe = onGround === true && parkingBrake !== false && enginesStoppedOrUnknown;
  const acceptedAdapters = Array.isArray(selectedVariant?.adapter_ids) ? selectedVariant.adapter_ids : [];
  const capabilityReport = lastStatus?.aircraftCapabilities || lastStatus?.AircraftCapabilities || {};
  const detectedAdapter = capabilityReport.adapterId || capabilityReport.AdapterId || null;
  const serverChecksReady = serverDispatch
    ? ['operation', 'aircraft', 'ofp', 'pirep'].every(key => serverDispatch?.server_checks?.[key] === true)
    : false;
  const dispatchReady = serverDispatch
    ? (serverDispatch.can_start === true || (serverDispatch.status === 'IN_PROGRESS' && serverChecksReady))
    : (state.operation && state.aircraft && state.ofp && state.pirep);

  // The visible three-phase preparation state is allowed to unlock the action
  // even if a stale dispatch snapshot still exposes can_start=false. Clicking
  // the button ALWAYS refreshes and re-validates the authoritative dispatch in
  // assertDispatchCanStart(), so this removes a UI deadlock without bypassing
  // any Prométhée safety/server rule.
  const visiblePreparationReady = state.operation && state.aircraft && state.ofp && state.pirep;

  // Adapter/variant detection is useful diagnostics, but add-on title strings
  // are not reliable enough to hard-block an otherwise valid flight. Keep the
  // READY badge strict on real simulator safety data and let /api/start remain
  // the final authority for refusal reasons.
  const ready = dispatchReady && readiness.simulator && preflightSafe;
  const status = String(serverDispatch?.status || '').toUpperCase();
  const terminal = ['COMPLETED', 'CANCELLED'].includes(status);
  const awaitingFiling = status === 'AWAITING_FILING';

  // A stale PREPARATION_REQUIRED snapshot may still be revalidated on click,
  // but a server-confirmed terminal/arrival state must never offer a new START.
  // The pilot files the report from Flight Review after ARRIVAL/IN.
  const canAttemptStart = visiblePreparationReady
    && readiness.simulator
    && !terminal
    && !awaitingFiling;

  const node = $('#readyState');
  if (node) {
    node.textContent = status === 'COMPLETED'
      ? 'FLIGHT COMPLETED'
      : status === 'CANCELLED'
        ? 'CANCELLED'
        : status === 'AWAITING_FILING'
          ? 'ARRIVED · PIREP TO FILE'
          : status === 'IN_PROGRESS'
            ? 'FLIGHT IN PROGRESS'
            : (ready ? 'READY FOR DEPARTURE' : 'NOT READY');
    node.classList.toggle('ready', ready || awaitingFiling || status === 'IN_PROGRESS');
  }
  const startButton = $('#startBtn');
  if (startButton) {
    startButton.disabled = !canAttemptStart;
    startButton.textContent = serverDispatch?.status === 'IN_PROGRESS'
      ? 'Reprendre l’enregistrement'
      : (ready ? 'Démarrer l’enregistrement' : 'Vérifier et démarrer');
    startButton.title = awaitingFiling
      ? 'Le vol est arrivé. Déposez maintenant le PIREP final depuis Flight Review.'
      : (terminal
          ? 'Cette opération est terminée et ne peut pas être redémarrée.'
          : (canAttemptStart && !ready
              ? 'Hermès vérifiera les contrôles au clic et affichera précisément ce qui bloque.'
              : ''));
  }

  const activeLocalFlight = lastStatus?.flight || lastStatus?.Flight || null;
  const localRecording = Boolean(activeLocalFlight?.recording ?? activeLocalFlight?.Recording);
  const localRecovery = Boolean(lastStatus?.recoveryAvailable);
  const pauseButton = $('#pauseBtn');
  const resumeButton = $('#resumeBtn');

  if (pauseButton) {
    pauseButton.disabled = !localRecording;
    pauseButton.title = localRecording ? 'Mettre l’enregistrement ACARS en pause.' : 'Aucun enregistrement actif à mettre en pause.';
  }

  if (resumeButton) {
    const canResumeLocalPause = Boolean(activeLocalFlight) && !localRecording && !localRecovery;
    resumeButton.disabled = !canResumeLocalPause;
    resumeButton.textContent = 'Reprendre une pause';
    resumeButton.title = canResumeLocalPause
      ? 'Reprendre un enregistrement ACARS local mis en pause.'
      : (localRecovery
          ? 'Utilisez le Recovery Center pour un vol interrompu.'
          : 'Aucun vol local en pause. Utilisez « Démarrer l’enregistrement » pour cette nouvelle opération.');
  }

  updateAircraftSelectionStatus();
  updateNextAction(state, ready);
  updatePreflight(lastStatus, state, ready);
}

function renderEligibility(payload) {
  const box = $('#aircraftEligibility');
  if (!box) return;
  const types = payload?.types || [];
  box.replaceChildren();
  box.hidden = false;

  const heading = document.createElement('div');
  heading.className = 'eligibility-heading';
  heading.innerHTML = '<div><span class="kicker">DISPATCH</span><h3>Types d’appareil disponibles</h3><p class="hint">Affichez un type à la demande pour consulter sa disponibilité sans dérouler toute la flotte.</p></div>';
  const count = document.createElement('strong');
  count.textContent = types.length + ' type' + (types.length > 1 ? 's' : '');
  heading.append(count);
  box.append(heading);

  if (!types.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = 'Aucun type d’appareil n’est actuellement disponible pour cette opération.';
    box.append(empty);
    return;
  }

  const picker = document.createElement('select');
  picker.className = 'eligibility-type-picker';
  picker.setAttribute('aria-label', 'Afficher les disponibilités par type d’appareil');
  const placeholder = document.createElement('option');
  placeholder.value = '';
  placeholder.textContent = 'Afficher un type d’appareil…';
  picker.append(placeholder);
  types.forEach(type => {
    const option = document.createElement('option');
    option.value = String(type.type_key || type.type_label || '');
    option.textContent = (type.type_label || type.type_key || 'Appareil')
      + ' · ' + (type.available_count || 0) + ' disponible' + (Number(type.available_count || 0) > 1 ? 's' : '');
    picker.append(option);
  });
  box.append(picker);

  const detail = document.createElement('div');
  detail.className = 'eligibility-selected-detail';
  detail.innerHTML = '<p class="hint">Choisissez un type dans le menu pour afficher sa prévision passagers et son remplissage.</p>';
  box.append(detail);

  picker.onchange = () => {
    detail.replaceChildren();
    const type = types.find(item => String(item.type_key || item.type_label || '') === picker.value);
    if (!type) {
      detail.innerHTML = '<p class="hint">Choisissez un type dans le menu pour afficher sa prévision passagers et son remplissage.</p>';
      return;
    }

    const card = document.createElement('article');
    card.className = 'aircraft-card eligible';
    const title = document.createElement('div');
    const name = document.createElement('strong');
    name.textContent = type.type_label || type.type_key || 'Appareil';
    const status = document.createElement('em');
    status.textContent = (type.available_count || 0) + ' DISPONIBLE' + (Number(type.available_count || 0) > 1 ? 'S' : '');
    title.append(name, status);
    card.append(title);

    const details = document.createElement('ul');
    const band = String(type.pricing_band || 'rouge').toUpperCase();
    [
      'Prévision passagers : ' + (type.passengers ?? '—') + ' / ' + (type.capacity ?? '—'),
      'Remplissage : ' + (type.load_factor_percent ?? '—') + ' %',
      'Vol ' + band + ' · tarif ' + (type.fare_percent ?? 100) + ' % du plein tarif'
    ].forEach(value => {
      const li = document.createElement('li');
      li.textContent = '✓ ' + value;
      details.append(li);
    });
    card.append(details);
    detail.append(card);
  };
}

function simulatorCode() {
  const forced = localSettings.forcedSimulator;
  return ({ xplane: 'xplane', fs2004: 'fs2004', fsx: 'fsx', p3d: 'p3d', msfs: 'msfs2024' })[forced]
    || localSettings.preferredSimulator
    || 'msfs2024';
}

function normalizeFlight(raw) {
  const airline = raw.airline || {};
  return {
    id: raw.id,
    ident: raw.ident || [airline.icao || raw.airline_icao || '', raw.flight_number || ''].join(''),
    airline_id: raw.airline_id || airline.id,
    flight_number: raw.flight_number,
    departure: raw.departure || raw.dpt_airport_id || raw.dpt_airport?.icao,
    arrival: raw.arrival || raw.arr_airport_id || raw.arr_airport?.icao,
    alternate: raw.alternate || raw.alt_airport_id,
    route: raw.route,
    level: raw.level
  };
}

function displayFlightIdent(flight) {
  const number = String(flight.flight_number || '').trim().toUpperCase();
  if (/^[A-Z]{3}\d/.test(number)) return number;
  const ident = String(flight.ident || '').trim().toUpperCase().replace(/^([A-Z]{3})\1/, '$1');
  return ident || number || 'Vol Air Inter';
}

function operationCard(operation) {
  const flight = normalizeFlight(operation.flight || operation);
  const aircraft = operation.aircraft || {};
  const wrapper = document.createElement('div');
  wrapper.className = 'operation-entry';

  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'operation';
  if (selectedOperation && (selectedOperation.bid_id || selectedOperation.flight?.id) === (operation.bid_id || operation.flight?.id)) {
    button.classList.add('selected');
  }
  const title = document.createElement('strong');
  const route = document.createElement('span');
  const detail = document.createElement('small');
  setText(title, displayFlightIdent(flight));
  setText(route, `${flight.departure || '?'} → ${flight.arrival || '?'}`);
  setText(detail, aircraft.type_label || aircraft.subfleet || aircraft.name || 'Type d’appareil à sélectionner');
  const badge = document.createElement('em');
  badge.textContent = operation.bid_id ? 'RÉSERVÉ' : 'PROGRAMME';
  button.append(badge, title, route, detail);
  button.onclick = () => selectOperation({ ...operation, flight });
  wrapper.append(button);

  if (operation.bid_id || operation.operation_id) {
    const cancel = document.createElement('button');
    cancel.type = 'button';
    cancel.className = 'operation-cancel';
    cancel.textContent = 'Annuler la réservation';
    cancel.onclick = () => cancelReservation(operation, flight, cancel);
    wrapper.append(cancel);
  }

  return wrapper;
}

async function cancelReservation(operation, flight, button) {
  const operationId = operation.operation_id || operation.bid_id || operation.id;
  if (!operationId) return;

  const ident = displayFlightIdent(flight);
  if (!confirm(`Annuler la réservation ${ident} (${flight.departure || '?'} → ${flight.arrival || '?'}) ?\n\nCette action est possible uniquement tant qu’aucun PIREP n’a été créé.`)) return;

  button.disabled = true;
  button.textContent = 'Annulation…';
  try {
    await call('/api/cancel-operation?operation=' + encodeURIComponent(operationId));

    const selectedId = selectedOperation?.operation_id || selectedOperation?.bid_id || selectedOperation?.id;
    if (String(selectedId || '') === String(operationId)) {
      selectedOperation = null;
      selectedAircraft = null;
      selectedVariant = null;
      aircraftEligibility = null;
      pirepId = null;
      flightPlan = null;
      linkedSimBrief = null;
      serverDispatch = null;
      lastDatalinkSnapshot = null;
      $('#selectedOperation').hidden = true;
      $('#prefileForm').hidden = true;
      updateWorkflow();
    }

    showMessage('#flightMessage', `Réservation ${ident} annulée.`);
    await refreshOperations();
  } catch (error) {
    button.disabled = false;
    button.textContent = 'Annuler la réservation';
    showMessage('#flightMessage', friendlyError(error) || 'Impossible d’annuler cette réservation.', true);
  }
}

function setOperationsMode(mode) {
  const searchMode = mode === 'search';
  const searchPanel = $('#operationsSearchPanel');
  const bidsButton = $('#showBidsBtn');
  const searchButton = $('#showSearchBtn');
  if (searchPanel) searchPanel.hidden = !searchMode;
  if (bidsButton) {
    bidsButton.classList.toggle('active', !searchMode);
    bidsButton.setAttribute('aria-selected', String(!searchMode));
  }
  if (searchButton) {
    searchButton.classList.toggle('active', searchMode);
    searchButton.setAttribute('aria-selected', String(searchMode));
  }
  setText($('#operationsListTitle'), searchMode ? 'Résultats du programme' : 'Mes réservations');
  if (searchMode) {
    setText($('#operationsListMeta'), 'Renseignez un ou plusieurs critères puis lancez la recherche.');
    setTimeout(() => $('#flightNumberSearch')?.focus(), 0);
  }
}

function renderOperations(value, source = 'reservations') {
  const payload = unwrap(value);
  const operations = Array.isArray(payload) ? payload : (payload?.operations || payload?.data || []);
  const list = $('#flightList');
  list.replaceChildren();
  setText($('#operationsListMeta'), operations.length
    ? operations.length + ' opération' + (operations.length > 1 ? 's' : '') + (source === 'search' ? ' trouvée' + (operations.length > 1 ? 's' : '') : ' réservée' + (operations.length > 1 ? 's' : ''))
    : (source === 'search' ? 'Aucun vol ne correspond à cette recherche.' : 'Aucune réservation active.'));
  if (!operations.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    setText(empty, source === 'search'
      ? 'Aucun vol trouvé. Modifiez les critères de recherche.'
      : 'Aucune réservation active. Utilisez « Rechercher un vol » pour parcourir le programme.');
    list.append(empty);
    return;
  }
  operations.forEach(operation => list.append(operationCard(operation)));
}

async function refreshOperations() {
  setOperationsMode('bids');
  if (!connected) {
    renderOperations([], 'reservations');
    showMessage('#flightMessage', 'Connectez-vous d’abord à votre compte pilote.', true);
    return;
  }
  try {
    showMessage('#flightMessage', 'Chargement de vos réservations…');
    const simulator = simulatorCode();
    renderOperations(await call('/api/v1/operations' + (simulator ? '?simulator=' + encodeURIComponent(simulator) : '')), 'reservations');
    showMessage('#flightMessage', '');
  } catch (error) {
    showMessage('#flightMessage', friendlyError(error), true);
  }
}

function normalizedSearchValue(selector) {
  return $(selector).value.trim().toUpperCase();
}

async function searchFlights() {
  if (!connected) return showMessage('#flightMessage', 'Connectez-vous d’abord.', true);
  const params = new URLSearchParams();
  let number = normalizedSearchValue('#flightNumberSearch');
  const departure = normalizedSearchValue('#departureSearch');
  const arrival = normalizedSearchValue('#arrivalSearch');
  const aircraftType = normalizedSearchValue('#aircraftTypeSearch');
  // Keep the airline prefix (ITF, ACF, etc.) so Prométhée can scope the
  // search to the matching company. The API also accepts a bare number.
  if (number) params.set('flight_number', number);
  if (departure) params.set('dep_icao', departure);
  if (arrival) params.set('arr_icao', arrival);
  if (aircraftType) params.set('icao_type', aircraftType);
  try {
    showMessage('#flightMessage', 'Recherche dans le programme…');
    setOperationsMode('search');
    renderOperations(await call('/api/v1/flights' + (params.size ? '?' + params.toString() : '')), 'search');
    showMessage('#flightMessage', '');
  } catch (error) {
    showMessage('#flightMessage', friendlyError(error), true);
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

function addAircraftOption(select, aircraft) {
  const option = document.createElement('option');
  option.value = aircraft.id || '';
  const registration = aircraft.registration || aircraft.name || aircraft.icao || 'Appareil';
  const type = aircraft.type_label || aircraft.subfleet || aircraft.icao || '';
  const airport = aircraft.airport ? ' · ' + aircraft.airport : '';
  option.textContent = registration + (type ? ' · ' + type : '') + airport;
  option.dataset.aircraft = JSON.stringify(aircraft);
  select.append(option);
}

function addAircraftTypeOption(select, type) {
  const option = document.createElement('option');
  option.value = type.type_key || '';
  const count = Number(type.available_count || 0);
  option.textContent = (type.type_label || type.type_key)
    + (count ? ' · ' + count + ' appareil' + (count > 1 ? 's' : '') + ' disponible' + (count > 1 ? 's' : '') : '')
    + ' · ' + (type.passengers ?? '—') + '/' + (type.capacity ?? '—') + ' pax'
    + ' · ' + String(type.pricing_band || 'rouge').toUpperCase();
  option.dataset.aircraftType = JSON.stringify(type);
  select.append(option);
}

function populateAircraftInstances(typeKey, preferredAircraftId = null) {
  const select = $('#aircraftId');
  if (!select) return;

  const available = Array.isArray(aircraftEligibility?.available) ? aircraftEligibility.available : [];
  const filtered = available
    .filter(aircraft => String(aircraft.type_key || '') === String(typeKey || ''))
    .sort((a, b) => String(a.registration || '').localeCompare(String(b.registration || ''), 'fr'));

  select.replaceChildren();
  const choose = document.createElement('option');
  choose.value = '';
  choose.textContent = typeKey
    ? (filtered.length ? 'Sélectionnez un appareil précis' : 'Aucun appareil disponible pour ce type')
    : 'Choisissez d’abord un type';
  select.append(choose);
  filtered.forEach(aircraft => addAircraftOption(select, aircraft));
  select.disabled = !typeKey || filtered.length === 0;

  if (preferredAircraftId && filtered.some(aircraft => String(aircraft.id) === String(preferredAircraftId))) {
    select.value = String(preferredAircraftId);
  } else {
    select.value = '';
  }
}


function renderVariantPreview(variant = selectedVariant) {
  const preview = $('#aircraftVariantPreview');
  if (!preview) return;
  preview.replaceChildren();
  if (!variant) { preview.hidden = true; return; }

  if (variant.image_url) {
    const image = document.createElement('img');
    image.src = variant.image_url;
    image.alt = variant.label || 'Airframe SimBrief';
    image.loading = 'lazy';
    preview.append(image);
  }
  const text = document.createElement('small');
  const sourceLabels = {
    phpvms_admin_airframe: 'Airframe phpVMS / Prométhée',
    simbrief_airframe: 'Catalogue SimBrief',
    hermes_catalog: 'Catalogue Hermès'
  };
  const proxy = variant.simbrief_strategy === 'proxy';
  text.textContent = (sourceLabels[variant.source] || variant.vendor || 'Profil appareil')
    + (proxy
      ? ' · Compatible via profil Air Inter · ' + (variant.icao || 'appareil réel')
      : ' · ' + (variant.simbrief_type || 'AUTO') + (variant.icao ? ' · ' + variant.icao : ''));
  preview.append(text);
  preview.hidden = false;
}

function renderRouteSuggestions(payload, initialRoute = '') {
  const select = $('#routeSuggestion');
  const input = $('#prefileForm')?.elements?.route;
  if (!select || !input) return;
  const options = Array.isArray(payload?.route_options) ? payload.route_options : [];
  select.replaceChildren();

  const auto = document.createElement('option');
  auto.value = '';
  auto.textContent = 'AUTO — laisser SimBrief calculer';
  select.append(auto);

  const seen = new Set();
  options.forEach(item => {
    const route = String(item?.route || '').trim();
    if (!route || seen.has(route.toUpperCase())) return;
    seen.add(route.toUpperCase());
    const option = document.createElement('option');
    option.value = route;
    option.textContent = (item.route_code ? item.route_code + ' — ' : '')
      + route
      + (item.source === 'scheduled_flight' ? ' · programme' : ' · variante compagnie');
    select.append(option);
  });

  const current = String(initialRoute || input.value || '').trim();
  if (current && !seen.has(current.toUpperCase())) {
    const option = document.createElement('option');
    option.value = current;
    option.textContent = 'PROGRAMME — ' + current;
    select.append(option);
  }
  select.value = Array.from(select.options).some(option => option.value === current) ? current : '';
  select.disabled = false;
}

async function loadRouteSuggestions(operationRef, initialRoute = '') {
  const select = $('#routeSuggestion');
  if (select) {
    select.disabled = true;
    select.replaceChildren(new Option('Chargement des routes…', ''));
  }
  try {
    const briefing = unwrap(await call('/api/v1/operations/' + encodeURIComponent(operationRef) + '/briefing'));
    renderRouteSuggestions(briefing, initialRoute);
  } catch {
    renderRouteSuggestions({ route_options: [] }, initialRoute);
  }
}

function renderAircraftVariants(payload) {
  aircraftVariantState = payload || { variants: [] };
  const select = $('#aircraftVariantId');
  if (!select) return;
  const variants = Array.isArray(aircraftVariantState.variants) ? aircraftVariantState.variants : [];
  select.replaceChildren();

  if (!selectedAircraft?.id || !variants.length) {
    const option = document.createElement('option');
    option.value = '';
    option.textContent = selectedAircraft?.id ? 'Aucun add-on spécifique configuré pour cet appareil' : 'Choisissez d’abord un appareil';
    select.append(option);
    select.disabled = true;
    selectedVariant = null;
    renderVariantPreview(null);
    return;
  }

  const owned = variants.filter(item => item.owned);
  const visible = owned.length
    ? [...owned, ...variants.filter(item => !item.owned)]
    : variants;

  visible.forEach(variant => {
    const option = document.createElement('option');
    option.value = variant.id;
    option.textContent = (variant.owned ? '★ ' : '')
      + (variant.label || variant.id)
      + (variant.vendor ? ' · ' + variant.vendor : '')
      + (variant.simbrief_strategy === 'proxy'
        ? ' · Compatible via profil Air Inter'
        : (variant.simbrief_type ? ' · SimBrief ' + variant.simbrief_type : ''));
    option.dataset.variant = JSON.stringify(variant);
    select.append(option);
  });

  const selectedId = aircraftVariantState.selected_variant_id
    || selectedOperation?.simbrief?.selected_variant_id
    || visible[0]?.id;
  if (selectedId && visible.some(item => item.id === selectedId)) select.value = selectedId;
  selectedVariant = visible.find(item => item.id === select.value) || null;
  renderVariantPreview(selectedVariant);
  select.disabled = false;
}

async function refreshAircraftVariants() {
  const operationRef = selectedOperation?.operation_id || selectedOperation?.id || selectedOperation?.bid_id;
  if (!operationRef || !selectedAircraft?.id) {
    renderAircraftVariants({ variants: [] });
    return;
  }
  try {
    const simulator = simulatorCode();
    const payload = unwrap(await call('/api/v1/operations/' + encodeURIComponent(operationRef)
      + '/simulator-profiles?simulator=' + encodeURIComponent(simulator)));
    renderAircraftVariants(payload);
  } catch (error) {
    renderAircraftVariants({ variants: [] });
    showMessage('#pirepMessage', 'Add-ons indisponibles : ' + friendlyError(error), true);
  }
}

async function refreshAircraftVariantLibrary() {
  const container = $('#aircraftVariantLibrary');
  if (!container) return;
  if (!connected) {
    container.innerHTML = '<p class="empty">Connectez-vous pour charger votre bibliothèque.</p>';
    return;
  }

  try {
    const simulator = simulatorCode();
    const payload = unwrap(await call('/api/v1/me/simulator-profiles?simulator=' + encodeURIComponent(simulator)));
    const variants = (payload?.variants || []).filter(item => item.vendor !== 'Generic');
    container.replaceChildren();

    if (!variants.length) {
      const empty = document.createElement('p');
      empty.className = 'empty';
      empty.textContent = 'Aucun add-on configuré pour ce simulateur.';
      container.append(empty);
      return;
    }

    variants.forEach(variant => {
      const row = document.createElement('article');
      row.className = 'variant-library-item';

      const identity = document.createElement('div');
      identity.className = 'variant-library-identity';
      const name = document.createElement('strong');
      name.textContent = variant.label || variant.id;
      const detail = document.createElement('small');
      detail.textContent = [variant.type_key, variant.vendor, variant.simbrief_strategy === 'proxy'
        ? 'Compatible via profil Air Inter'
        : (variant.simbrief_type ? 'SimBrief ' + variant.simbrief_type : null)]
        .filter(Boolean).join(' · ');
      identity.append(name, detail);

      const controls = document.createElement('div');
      controls.className = 'variant-library-controls';

      const ownedLabel = document.createElement('label');
      ownedLabel.className = 'variant-toggle';
      const owned = document.createElement('input');
      owned.type = 'checkbox';
      owned.dataset.variantId = variant.id;
      owned.checked = Boolean(variant.owned);
      const switchUi = document.createElement('span');
      switchUi.className = 'variant-toggle-ui';
      const ownedText = document.createElement('b');
      ownedText.textContent = 'Possédé';
      ownedLabel.append(owned, switchUi, ownedText);

      const preferredLabel = document.createElement('label');
      preferredLabel.className = 'variant-preferred';
      const preferred = document.createElement('input');
      preferred.type = 'radio';
      preferred.name = 'preferredAircraftVariant';
      preferred.value = variant.id;
      preferred.checked = Boolean(variant.preferred);
      preferred.disabled = !owned.checked;
      const preferredText = document.createElement('span');
      preferredText.textContent = '★ Préférée';
      preferredLabel.append(preferred, preferredText);

      owned.onchange = () => {
        preferred.disabled = !owned.checked;
        if (!owned.checked && preferred.checked) preferred.checked = false;
        row.classList.toggle('owned', owned.checked);
      };

      controls.append(ownedLabel, preferredLabel);
      row.append(identity, controls);
      row.classList.toggle('owned', owned.checked);
      container.append(row);
    });
  } catch (error) {
    container.innerHTML = '<p class="empty">Bibliothèque indisponible.</p>';
    setText($('#aircraftVariantLibraryMessage'), friendlyError(error));
  }
}


function syncAircraftSelectors(aircraft = selectedAircraft) {
  const typeSelect = $('#aircraftTypeId');
  if (!typeSelect || !aircraftEligibility) return;

  const typeKey = aircraft?.type_key || '';
  if (typeKey && Array.from(typeSelect.options).some(option => option.value === String(typeKey))) {
    typeSelect.value = String(typeKey);
    populateAircraftInstances(typeKey, aircraft?.id || null);
  } else if (!typeSelect.value) {
    populateAircraftInstances('', null);
  }
}

function renderOperationLoad(aircraft, briefing = flightPlan) {
  const node = $('#operationLoad');
  if (!node) return;
  if (!aircraft?.id) {
    node.hidden = true;
    node.textContent = '';
    return;
  }
  const label = aircraft.type_label || aircraft.subfleet || aircraft.name || 'Appareil';
  const band = String(aircraft.band || aircraft.pricing_band || 'rouge').toUpperCase();
  const cabin = aircraft.cabin_profile?.label || aircraft.cabinProfile?.label || '';
  const simbriefPax = briefing?.passengers ?? briefing?.pax ?? null;
  const plannedPax = aircraft.passengers ?? null;
  const paxText = Number.isFinite(Number(simbriefPax))
    ? Number(simbriefPax) + ' pax OFP SimBrief'
    : ((plannedPax ?? '—') + ' pax prévus');
  node.textContent = label
    + (cabin ? ' · ' + cabin : '')
    + ' · ' + paxText + ' / ' + (aircraft.capacity ?? '—') + ' sièges'
    + ' · ' + (aircraft.load_factor_percent ?? '—') + ' % prévision commerciale · vol ' + band;
  node.hidden = false;
  renderSimBriefPreparationSummary();
}

async function selectOperation(operation) {
  if (!operation?.operation_id && !operation?.bid_id && operation?.id) {
    try {
      showMessage('#flightMessage', 'Réservation du vol dans Prométhée…');
      operation = unwrap(await call('/api/v1/flights/' + encodeURIComponent(operation.id) + '/reserve', {}));
      showMessage('#flightMessage', '');
    } catch (error) {
      showMessage('#flightMessage', 'Réservation impossible : ' + friendlyError(error), true);
      return;
    }
  }

  // Switching operations must be atomic from the UI point of view. Do not
  // render the new flight with the previous flight's terminal Dispatch/OFP
  // snapshot for even one refresh cycle (that used to show "Vol déjà terminé"
  // on a brand-new selection).
  serverDispatch = null;
  flightPlan = null;
  linkedSimBrief = null;
  readiness.operation = false;
  readiness.aircraft = false;
  readiness.ofp = false;
  readiness.pirep = false;

  selectedOperation = operation;
  // Rehydrate an already-prefiled operation after a restart/reselection. The
  // server is authoritative; never keep a stale PIREP id from another flight.
  pirepId = operation.pirep_id || operation.pirep?.id || null;
  selectedAircraft = operation.aircraft?.id ? operation.aircraft : null;
  selectedVariant = operation.simbrief?.variant || null;
  renderOperationLoad(selectedAircraft);
  updateAircraftSelectionStatus();
  lastDatalinkSnapshot = null;
  setTimeout(refreshDatalink, 0);
  setTimeout(refreshNetwork, 0);
  updateWorkflow();
  $$('.operation').forEach(node => node.classList.remove('selected'));
  if (document.activeElement?.classList?.contains('operation')) document.activeElement.classList.add('selected');
  renderNetworkPrefiles(null);
  const flight = normalizeFlight(operation.flight || operation);
  const form = $('#prefileForm');
  const assign = (name, value) => { if (form.elements[name]) form.elements[name].value = value ?? ''; };
  assign('flight_id', flight.id);
  assign('airline_id', flight.airline_id);
  assign('flight_number', flight.flight_number);
  assign('aircraft_id', selectedAircraft?.id || '');
  assign('dpt_airport_id', flight.departure);
  assign('arr_airport_id', flight.arrival);
  assign('alt_airport_id', flight.alternate);
  assign('route', flight.route);
  assign('level', flight.level);
  assign('block_fuel', '');
  assign('notes', '');
  form.dataset.programDraft = JSON.stringify({
    alt_airport_id: flight.alternate || '',
    route: flight.route || '',
    level: flight.level || ''
  });
  setText($('#selectedFlight'), `${displayFlightIdent(flight)} — ${flight.departure || '?'} → ${flight.arrival || '?'}`);
  const simbrief = operation.simbrief || {};
  updateSimBriefAvailability(simbrief);
  setText($('#operationBrief'), simbrief.available
    ? `OFP SimBrief disponible · type ${simbrief.type || 'à confirmer'}`
    : `OFP à préparer · type ${simbrief.type || 'à confirmer'}`);
  $('#selectedOperation').hidden = false;
  form.hidden = false;
  showMessage('#pirepMessage', '');
  serverDispatch = null;

  const typeSelect = $('#aircraftTypeId');
  const aircraftSelect = $('#aircraftId');
  aircraftEligibility = null;

  typeSelect.replaceChildren();
  const loadingType = document.createElement('option');
  loadingType.value = '';
  loadingType.textContent = 'Chargement des types autorisés…';
  typeSelect.append(loadingType);
  typeSelect.disabled = true;

  aircraftSelect.replaceChildren();
  const loadingAircraft = document.createElement('option');
  loadingAircraft.value = '';
  loadingAircraft.textContent = 'Choisissez d’abord un type';
  aircraftSelect.append(loadingAircraft);
  aircraftSelect.disabled = true;

  try {
    const operationRef = operation.operation_id || operation.id || operation.bid_id;
    if (!operationRef) throw new Error('Cette réservation ne possède pas d’identifiant d’opération Prométhée.');

    await loadRouteSuggestions(operationRef, flight.route || '');
    const payload = unwrap(await call(`/api/v1/operations/${encodeURIComponent(operationRef)}/aircraft-eligibility`));
    aircraftEligibility = payload || {};
    renderEligibility(payload);

    const types = Array.isArray(payload?.types) ? payload.types : [];
    typeSelect.replaceChildren();
    const chooseType = document.createElement('option');
    chooseType.value = '';
    chooseType.textContent = types.length ? 'Sélectionnez un type d’appareil' : 'Aucun type disponible pour ce vol';
    typeSelect.append(chooseType);
    types.forEach(item => addAircraftTypeOption(typeSelect, item));
    typeSelect.disabled = types.length === 0;

    if (selectedAircraft?.id) {
      syncAircraftSelectors(selectedAircraft);
      await refreshAircraftVariants();
    } else {
      renderAircraftVariants({ variants: [] });
      populateAircraftInstances('', null);
    }

    if (!types.length) {
      showMessage('#pirepMessage', 'Aucun type d’appareil autorisé et disponible pour ce vol. Vérifiez la flotte, la position et les qualifications.', true);
    }
  } catch (error) {
    typeSelect.replaceChildren(loadingType);
    loadingType.textContent = 'Types indisponibles';
    typeSelect.disabled = true;
    aircraftSelect.replaceChildren(loadingAircraft);
    loadingAircraft.textContent = 'Appareils indisponibles';
    aircraftSelect.disabled = true;
    showMessage('#pirepMessage', friendlyError(error), true);
  }

  try { await refreshDispatch(); }
  catch (error) { showMessage('#pirepMessage', 'Dispatch indisponible : ' + friendlyError(error), true); }
}

async function refreshDispatch() {
  const operationRef = selectedOperation?.operation_id || selectedOperation?.id;
  if (!operationRef) { serverDispatch = null; return null; }

  serverDispatch = unwrap(await call(`/api/v1/operations/${encodeURIComponent(operationRef)}/dispatch`));

  // A dispatch refresh is the canonical recovery path for Hermès. Hydrate all
  // identifiers that may have been lost when the desktop app was restarted.
  if (serverDispatch?.operation) {
    selectedOperation = { ...(selectedOperation || {}), ...serverDispatch.operation };
  }
  const dispatchPirepId = serverDispatch?.pirep?.id
    || serverDispatch?.pirep?.pirep_id
    || serverDispatch?.operation?.pirep_id
    || null;
  if (dispatchPirepId) pirepId = dispatchPirepId;

  if (serverDispatch?.operation?.aircraft?.id) {
    selectedAircraft = serverDispatch.operation.aircraft;
    const form = $('#prefileForm');
    if (form?.elements?.aircraft_id) form.elements.aircraft_id.value = selectedAircraft.id;
    renderOperationLoad(selectedAircraft);
    syncAircraftSelectors(selectedAircraft);
    selectedVariant = serverDispatch.operation?.simbrief?.variant || selectedVariant;
    renderAircraftVariants(serverDispatch.operation?.simbrief || aircraftVariantState || { variants: [] });
  }
  const checks = serverDispatch?.server_checks || {};
  readiness.operation = Boolean(checks.operation);
  readiness.aircraft = Boolean(checks.aircraft);
  readiness.ofp = Boolean(checks.ofp);
  readiness.pirep = Boolean(checks.pirep);

  const labels = { PREPARATION_REQUIRED: 'PRÉPARATION REQUISE', READY: 'PRÊT POUR HERMÈS', IN_PROGRESS: 'VOL EN COURS', AWAITING_FILING: 'ARRIVÉ · PIREP À DÉPOSER', COMPLETED: 'VOL TERMINÉ', CANCELLED: 'OPÉRATION ANNULÉE' };
  setText($('#operationBrief'), `${labels[serverDispatch?.status] || serverDispatch?.status || 'DISPATCH'} · Dispatch Prométhée`);
  updateWorkflow();
  return serverDispatch;
}

async function assertDispatchCanStart() {
  const dispatch = await refreshDispatch();
  const checks = dispatch?.server_checks || {};
  const checksReady = ['operation', 'aircraft', 'ofp', 'pirep'].every(key => checks[key] === true);
  const serverPirepId = dispatch?.pirep?.id || dispatch?.pirep?.pirep_id || dispatch?.operation?.pirep_id || null;
  if (serverPirepId) pirepId = serverPirepId;
  const resumable = dispatch?.status === 'IN_PROGRESS' && checksReady && Boolean(serverPirepId);

  if (!dispatch?.can_start && !resumable) {
    const actions = Array.isArray(dispatch?.actions) ? dispatch.actions.filter(Boolean).join(' ') : '';
    throw new Error(actions || `Prométhée refuse le démarrage : ${dispatch?.status || 'opération non prête'}.`);
  }

  // An operation can already be IN_PROGRESS on Prométhée while the desktop
  // recorder was restarted/reinstalled. Reattach to the authoritative PIREP
  // instead of trapping the pilot behind a disabled START button.
  if (resumable) pirepId = serverPirepId;
  return { ...dispatch, resumable };
}

function simbriefPath(suffix) {
  const operationRef = selectedOperation?.operation_id || selectedOperation?.id;
  if (!operationRef) throw new Error('Sélectionnez une opération Prométhée avant de préparer SimBrief.');
  return `/api/v1/operations/${encodeURIComponent(operationRef)}/simbrief/${suffix}`;
}

function normalizeFlightLevel(value) {
  if (value === undefined || value === null || value === '') return undefined;
  let raw = String(value).trim().toUpperCase();
  raw = raw.replace(/^FL\s*/, '').replace(/^F(?=\d)/, '');
  raw = raw.replace(/\s*(?:FT|FEET|PIEDS?)$/, '').replace(/\s+/g, '').replace(',', '.');
  const altitude = Number(raw);
  if (!Number.isFinite(altitude) || altitude <= 0) return undefined;

  // Accept both flight levels (350) and altitudes in feet (35000).
  // A few imported schedules use hundreds of feet with an extra zero;
  // repeatedly collapse only while the value is clearly outside FL range.
  let level = altitude;
  while (level > 60000) level /= 10;
  if (level > 600) level /= 100;
  level = Math.round(level);

  return level >= 10 && level <= 600 ? level : undefined;
}

function simBriefPlanningPayload(form) {
  const payload = {};
  const aircraftId = form.elements.aircraft_id.value;
  if (aircraftId) payload.aircraft_id = aircraftId;

  const alternate = form.elements.alt_airport_id.value.trim().toUpperCase();
  if (alternate) payload.alternate = alternate;

  const route = form.elements.route.value.trim();
  if (route) payload.route = route;

  const rawLevel = form.elements.level.value.trim();
  if (rawLevel) {
    const level = normalizeFlightLevel(rawLevel);
    if (level) {
      payload.level = level;
      form.elements.level.value = String(level);
    } else {
      // Never block OFP generation because of a malformed level copied from
      // schedule data. Omitting the override lets Prométhée resolve and
      // normalize the canonical flight level server-side.
      form.elements.level.value = '';
      console.warn('Hermès ignored an invalid flight-level override:', rawLevel);
    }
  }

  // Advanced controls remain ephemeral. Empty fields mean "use Prométhée /
  // SimBrief defaults"; only explicit pilot choices cross the API boundary.
  const advanced = [
    'callsign', 'units', 'planformat', 'maps', 'navlog',
    'tlr', 'notams', 'firnot', 'stepclimbs', 'etops', 'find_sidstar',
    'cruise', 'civalue', 'contpct', 'resvrule', 'selcal', 'deprwy',
    'arrrwy', 'taxiout', 'taxiin', 'pax', 'manualrmk'
  ];
  advanced.forEach(name => {
    const field = form.elements[name];
    if (!field) return;
    const raw = String(field.value ?? '').trim();
    if (raw === '') return;

    if (['taxiout', 'taxiin', 'pax'].includes(name)) {
      const number = Number(raw);
      if (Number.isFinite(number)) payload[name] = Math.round(number);
      return;
    }

    payload[name] = raw;
  });

  return payload;
}

async function assertSimBriefReady(form, mode = 'company') {
  const resolved = unwrap(await call(simbriefPath('readiness'), simBriefPlanningPayload(form)));
  const ready = mode === 'account'
    ? Boolean(resolved?.ready_account)
    : Boolean(resolved?.ready_company_api ?? resolved?.ready);

  if (!ready) {
    const failed = (resolved?.checks || [])
      .filter(check => check?.ready === false && (mode !== 'account' || check?.code !== 'COMPANY_API'))
      .map(check => check?.label)
      .filter(Boolean);
    throw new Error('SimBrief non prêt' + (failed.length ? ' : ' + failed.join(' · ') : '.'));
  }

  const flight = resolved?.flight?.ident || resolved?.flight?.number || 'vol';
  const origin = resolved?.origin?.icao || '—';
  const destination = resolved?.destination?.icao || '—';
  const registration = resolved?.aircraft?.registration || 'appareil';
  const type = resolved?.aircraft?.simbrief_display_type || resolved?.aircraft?.icao || 'type inconnu';
  const strategy = resolved?.aircraft?.simbrief_strategy;
  const planningLabel = strategy === 'proxy' ? ' · compatible via profil Air Inter' : '';
  const pax = resolved?.demand?.passengers;
  const cabin = resolved?.demand?.cabin_profile?.label || resolved?.demand?.cabinProfile?.label || '';
  showMessage(
    '#simbriefState',
    `Résolution BDD OK · ${flight} · ${origin} → ${destination} · ${registration} · ${type}${planningLabel}${cabin ? ' · ' + cabin : ''}${Number.isFinite(Number(pax)) ? ' · ' + pax + ' pax' : ''}.`
  );
  renderSimBriefPreparationSummary(resolved);
  return resolved;
}

function renderNetworkPrefiles(prefiles) {
  const panel = $('#networkPrefilePanel');
  const preview = $('#icaoFlightPlanPreview');
  if (!panel) return;
  const available = Boolean(prefiles?.vatsim?.url || prefiles?.ivao?.url);
  panel.hidden = !available;
  if (preview) {
    preview.hidden = !prefiles?.icao_flightplan;
    preview.textContent = prefiles?.icao_flightplan || '';
  }
  const vatsimButton = $('#vatsimPrefileBtn');
  const ivaoButton = $('#ivaoPrefileBtn');
  if (vatsimButton) vatsimButton.disabled = !prefiles?.vatsim?.url;
  if (ivaoButton) ivaoButton.disabled = !prefiles?.ivao?.url;
  showMessage('#networkPrefileMessage', available
    ? 'Plan ICAO prêt. Vérifiez-le avant l’envoi sur le réseau.'
    : '');
}

async function applyBriefing(briefing, sourceLabel) {
  const form = $('#prefileForm');
  const flightLevel = normalizeFlightLevel(briefing.initial_altitude);
  const importedPax = briefing.passengers
    ?? briefing.pax
    ?? briefing.weights?.passengers
    ?? briefing.general?.passengers
    ?? undefined;
  flightPlan = {
    source: briefing.source || sourceLabel,
    simbrief_id: briefing.id,
    route: briefing.route,
    level: flightLevel,
    block_fuel: briefing.block_fuel || undefined,
    passengers: Number.isFinite(Number(importedPax)) ? Number(importedPax) : undefined,
    estimated_time_enroute: Number(briefing.estimated_time_enroute || 0) || null,
    route_points: Array.isArray(briefing.route_points) ? briefing.route_points : [],
    network_prefiles: briefing.network_prefiles || null
  };
  if (briefing.block_fuel) form.elements.block_fuel.value = Math.round(briefing.block_fuel);
  if (briefing.route) form.elements.route.value = briefing.route;
  if (flightLevel) form.elements.level.value = flightLevel;
  if (briefing.alternate) form.elements.alt_airport_id.value = briefing.alternate;
  $('#planBox').textContent = JSON.stringify(briefing, null, 2);
  renderNetworkPrefiles(flightPlan.network_prefiles);
  renderOperationLoad(selectedAircraft, flightPlan);
  renderSimBriefPreparationSummary(briefing.resolved || null);

  const commercialPax = Number(selectedAircraft?.passengers);
  const actualPax = Number(flightPlan.passengers);
  if (Number.isFinite(actualPax) && Number.isFinite(commercialPax) && actualPax !== commercialPax) {
    showMessage(
      '#pirepMessage',
      `SimBrief a retenu ${actualPax} pax contre ${commercialPax} prévus par Air Inter. Le PIREP utilisera les ${actualPax} pax réellement présents dans l’OFP.`,
      false
    );
  }

  // The imported OFP is authoritative for the client immediately. Refresh the
  // dispatch so step 03 cannot remain stuck on a stale server snapshot.
  try { await refreshDispatch(); } catch {}
  updateWorkflow();

  showMessage('#simbriefState', 'OFP importé depuis ' + sourceLabel + '. Enregistrement automatique de la préparation opérationnelle…');
  if (!pirepId) await prefilePreparedOperation({ navigate: true, automatic: true });
  drawMap(flightMapState.lastTrack, lastStatus?.latest || {});
}

function recommendedPlanMode() {
  const hasIdentity = Boolean($('#simbriefUsername')?.value.trim() || $('#simbriefPilotId')?.value.trim());
  if (hasIdentity) return 'account';
  if (selectedOperation?.simbrief?.company_api_available === true) return 'api';
  return 'file';
}
function updateRecommendedPlanSource() {
  const labels = { account:'Compte SimBrief · alias pilote détecté', api:'API SimBrief compagnie · source automatique', file:'Plan local · aucun compte/API disponible' };
  setText($('#ofpSourceHint'), 'Source recommandée : ' + labels[recommendedPlanMode()]);
}

function updateSimBriefAvailability(simbrief = selectedOperation?.simbrief || {}) {
  const available = simbrief.company_api_available === true;
  const apiButton = document.querySelector('[data-plan-mode="api"]');
  const apiHint = document.querySelector('[data-plan-panel="api"] .hint');
  if (apiButton) {
    apiButton.disabled = !available;
    apiButton.title = available
      ? 'Clé API compagnie configurée dans Prométhée'
      : 'La clé API compagnie doit être configurée dans Prométhée.';
  }
  if (apiHint) {
    apiHint.textContent = available
      ? 'Mode compagnie disponible : la clé API SimBrief reste sur Prométhée et n’est jamais transmise à Hermès.'
      : 'Mode compagnie indisponible : configurez la clé API SimBrief dans l’administration Prométhée.';
  }
  if (!available && localSettings.flightPlanMode === 'api') setPlanMode('account');
  updateRecommendedPlanSource();
}

function setPlanMode(mode) {
  if (!['account', 'api', 'file'].includes(mode)) mode = 'account';
  if (mode === 'api' && selectedOperation?.simbrief?.company_api_available === false) {
    mode = 'account';
  }

  localSettings.flightPlanMode = mode;
  localStorage.prometheeAcarsSettings = JSON.stringify(localSettings);

  $$('[data-plan-mode]').forEach(button => {
    const active = button.dataset.planMode === mode;
    button.classList.toggle('active', active);
    button.setAttribute('aria-selected', String(active));
    button.tabIndex = active ? 0 : -1;
  });

  $$('[data-plan-panel]').forEach(panel => {
    const active = panel.dataset.planPanel === mode;
    panel.classList.toggle('active', active);
    panel.hidden = !active;
    panel.setAttribute('aria-hidden', String(!active));
  });

  const labels = {
    account: 'Compte SimBrief : envoyez la préparation, générez l’OFP sur SimBrief puis récupérez-le dans Hermès.',
    api: 'API SimBrief : Prométhée prépare la génération et Hermès récupère automatiquement l’OFP terminé.',
    file: 'Plan local : importez un fichier PLN/XML depuis votre ordinateur.'
  };
  if (!flightPlan) showMessage('#simbriefState', labels[mode] || '');
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

async function prefilePreparedOperation({ navigate = true, automatic = false } = {}) {
  const form = $('#prefileForm');
  const body = Object.fromEntries([...new FormData(form)].filter(([, value]) => value !== ''));
  if (!body.aircraft_id) {
    showMessage('#pirepMessage', 'Sélectionnez un appareil.', true);
    return false;
  }
  if (body.block_fuel) body.block_fuel = Number(body.block_fuel);
  if (body.level) {
    const normalizedLevel = normalizeFlightLevel(body.level);
    if (normalizedLevel) body.level = normalizedLevel;
    else delete body.level;
  }
  if (body.alt_airport_id) body.alt_airport_id = body.alt_airport_id.toUpperCase();
  Object.assign(body, flightPlan || {}, { source_name: 'Hermes ACARS' });

  try {
    const operationRef = selectedOperation?.operation_id || selectedOperation?.id;
    const operationPirepBody = operationRef ? {
      route: body.route || flightPlan?.route || undefined,
      level: normalizeFlightLevel(body.level || flightPlan?.level),
      block_fuel: body.block_fuel || flightPlan?.block_fuel || undefined,
      simbrief_source: flightPlan?.source === 'simbrief_account'
        ? 'simbrief_account'
        : (String(flightPlan?.source || '').toLowerCase().includes('simbrief') ? 'simbrief' : undefined)
    } : body;
    if (!operationRef) throw new Error('Impossible de préparer le brouillon PIREP sans operation_id.');

    const result = unwrap(await call(`/api/v1/operations/${encodeURIComponent(operationRef)}/pirep`, operationPirepBody));
    pirepId = result.id || result.pirep_id || result.pirep?.id;
    if (!pirepId) throw new Error('Prométhée n’a pas retourné l’identifiant du PIREP.');

    await refreshDispatch();
    updateWorkflow();
    showMessage(
      '#pirepMessage',
      automatic
        ? `OFP importé · brouillon PIREP ${pirepId} préparé. Aucun rapport de vol n’est encore déposé : il le sera après le vol.`
        : `Brouillon PIREP ${pirepId} prêt. Le rapport restera IN_PROGRESS jusqu’à la fin du vol.`
    );
    showMessage('#simbriefState', automatic
      ? 'OFP importé et brouillon PIREP ACARS préparé.'
      : 'OFP prêt.');
    if (navigate) document.querySelector('[data-tab="record"]')?.click();
    return true;
  } catch (error) {
    showMessage('#pirepMessage', friendlyError(error), true);
    if (automatic) showMessage('#simbriefState', 'OFP importé. Préparation du brouillon PIREP impossible : ' + friendlyError(error), true);
    return false;
  }
}

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

function currentDatalinkOperation() {
  const activeFlight = lastStatus?.flight || lastStatus?.Flight;
  return selectedOperation?.operation_id || selectedOperation?.operationId || selectedOperation?.id
    || activeFlight?.operationId || activeFlight?.OperationId || null;
}

function datalinkRead(object, camel, pascal = camel) {
  return object?.[camel] ?? object?.[pascal] ?? null;
}

function renderDatalink(snapshot) {
  lastDatalinkSnapshot = snapshot || null;
  const operationId = snapshot ? datalinkRead(snapshot, 'operationId', 'OperationId') : currentDatalinkOperation();
  const messages = snapshot ? (datalinkRead(snapshot, 'messages', 'Messages') || []) : [];
  const syncState = String(snapshot ? datalinkRead(snapshot, 'syncState', 'SyncState') || 'LOCAL' : 'STANDBY').toUpperCase();
  const pendingOutbound = Number(snapshot ? datalinkRead(snapshot, 'pendingOutbound', 'PendingOutbound') || 0 : 0);
  const pendingReads = Number(snapshot ? datalinkRead(snapshot, 'pendingReads', 'PendingReads') || 0 : 0);
  const pendingAcks = Number(snapshot ? datalinkRead(snapshot, 'pendingAcks', 'PendingAcks') || 0 : 0);
  const unreadCount = Number(snapshot ? datalinkRead(snapshot, 'unreadCount', 'UnreadCount') || 0 : 0);
  const requiredAcks = Number(snapshot ? datalinkRead(snapshot, 'pendingRequiredAcks', 'PendingRequiredAcks') || 0 : 0);
  const lastSync = snapshot ? datalinkRead(snapshot, 'lastSuccessfulSyncAt', 'LastSuccessfulSyncAt') : null;
  const error = snapshot ? datalinkRead(snapshot, 'error', 'Error') : null;

  setText($('#datalinkOperation'), operationId || '—');
  setText($('#datalinkLastSync'), lastSync ? new Date(lastSync).toLocaleTimeString('fr-FR', { timeZone: localSettings.timeFormat === 'utc' ? 'UTC' : undefined }) : '—');
  setText($('#datalinkPending'), String(pendingOutbound + pendingReads + pendingAcks));
  const state = $('#datalinkState');
  if (state) {
    state.textContent = syncState;
    state.classList.toggle('ready', syncState === 'SYNCED');
  }
  const badge = $('#datalinkBadge');
  if (badge) {
    const attention = Math.max(unreadCount, requiredAcks);
    badge.hidden = attention <= 0;
    badge.textContent = String(attention);
  }

  const list = $('#datalinkMessages');
  list.replaceChildren();
  if (!operationId) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = 'Sélectionnez une opération Air Inter.';
    list.append(empty);
  } else if (!messages.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = syncState === 'OFFLINE' ? 'Aucun message local. Prométhée est hors ligne.' : 'Aucun message datalink pour cette opération.';
    list.append(empty);
  } else {
    messages.forEach(message => {
      const id = datalinkRead(message, 'id', 'Id');
      const direction = String(datalinkRead(message, 'direction', 'Direction') || '');
      const priority = String(datalinkRead(message, 'priority', 'Priority') || 'ROUTINE');
      const category = String(datalinkRead(message, 'category', 'Category') || 'OPS');
      const sender = datalinkRead(message, 'senderLabel', 'SenderLabel') || (direction === 'OPS_TO_COCKPIT' ? 'AIR INTER OPS' : 'COCKPIT');
      const body = datalinkRead(message, 'body', 'Body') || '';
      const createdAt = datalinkRead(message, 'createdAt', 'CreatedAt');
      const requiresAck = Boolean(datalinkRead(message, 'requiresAck', 'RequiresAck'));
      const readAt = datalinkRead(message, 'readAt', 'ReadAt');
      const acknowledgedAt = datalinkRead(message, 'acknowledgedAt', 'AcknowledgedAt');
      const status = String(datalinkRead(message, 'status', 'Status') || 'SENT');
      const localPending = Boolean(datalinkRead(message, 'localPending', 'LocalPending'));

      const item = document.createElement('article');
      item.className = 'datalink-message ' + (direction === 'OPS_TO_COCKPIT' ? 'incoming' : 'outgoing') + ' priority-' + priority.toLowerCase();
      const header = document.createElement('header');
      const meta = document.createElement('div');
      const source = document.createElement('strong');
      source.textContent = sender;
      const tags = document.createElement('span');
      const time = createdAt ? new Date(createdAt).toLocaleTimeString('fr-FR', { timeZone: localSettings.timeFormat === 'utc' ? 'UTC' : undefined }) : '—';
      tags.textContent = category + ' · ' + priority + ' · ' + time;
      meta.append(source, tags);
      const statusNode = document.createElement('em');
      statusNode.textContent = localPending ? 'QUEUED' : (acknowledgedAt ? 'ACK' : status);
      header.append(meta, statusNode);
      const text = document.createElement('p');
      text.textContent = body;
      const actions = document.createElement('div');
      actions.className = 'datalink-message-actions';

      if (direction === 'OPS_TO_COCKPIT' && !readAt && !acknowledgedAt) {
        const read = document.createElement('button');
        read.type = 'button';
        read.textContent = status === 'READ_QUEUED' ? 'Lecture en file' : 'Marquer lu';
        read.disabled = status === 'READ_QUEUED';
        read.onclick = () => readDatalink(id);
        actions.append(read);
      }
      if (direction === 'OPS_TO_COCKPIT' && requiresAck && !acknowledgedAt) {
        const ack = document.createElement('button');
        ack.type = 'button';
        ack.textContent = status === 'ACK_QUEUED' ? 'ACK en file' : 'ACK';
        ack.disabled = status === 'ACK_QUEUED';
        ack.onclick = () => acknowledgeDatalink(id);
        actions.append(ack);
      }
      if (direction === 'OPS_TO_COCKPIT' && !localPending) {
        const reply = document.createElement('button');
        reply.type = 'button';
        reply.textContent = 'Répondre';
        reply.onclick = () => prepareDatalinkReply(id, sender);
        actions.append(reply);
      }

      item.append(header, text);
      if (actions.childElementCount) item.append(actions);
      list.append(item);
    });
  }

  const form = $('#datalinkForm');
  if (form) form.querySelector('button[type="submit"]').disabled = !operationId;
  showMessage('#datalinkMessage', error || '', Boolean(error && syncState !== 'OFFLINE'));
}

async function refreshDatalink() {
  const operationId = currentDatalinkOperation();
  if (!connected || !operationId || datalinkRefreshing) {
    if (!operationId) renderDatalink(null);
    return;
  }
  datalinkRefreshing = true;
  try {
    const snapshot = await call('/api/datalink?operation=' + encodeURIComponent(operationId));
    renderDatalink(snapshot);
  } catch (error) {
    showMessage('#datalinkMessage', friendlyError(error), true);
  } finally {
    datalinkRefreshing = false;
  }
}

async function readDatalink(messageId) {
  const operationId = currentDatalinkOperation();
  if (!operationId) return;
  try {
    const snapshot = await call('/api/datalink/read?operation=' + encodeURIComponent(operationId), { message_id: messageId });
    renderDatalink(snapshot);
  } catch (error) {
    showMessage('#datalinkMessage', friendlyError(error), true);
  }
}

async function acknowledgeDatalink(messageId) {
  const operationId = currentDatalinkOperation();
  if (!operationId) return;
  try {
    const snapshot = await call('/api/datalink/ack?operation=' + encodeURIComponent(operationId), { message_id: messageId });
    renderDatalink(snapshot);
  } catch (error) {
    showMessage('#datalinkMessage', friendlyError(error), true);
  }
}

function prepareDatalinkReply(messageId, sender) {
  const form = $('#datalinkForm');
  form.elements.reply_to.value = messageId || '';
  $('#datalinkCancelReplyBtn').hidden = false;
  form.elements.body.placeholder = 'Réponse à ' + (sender || 'OPS') + '…';
  form.elements.body.focus();
}

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


function networkRead(object, snake, camel = snake) {
  return object?.[snake] ?? object?.[camel] ?? null;
}

function renderNetwork(payload) {
  const data = unwrap(payload) || {};
  const crews = Array.isArray(data.crews) ? data.crews : [];
  const operationId = currentDatalinkOperation();
  const generatedAt = data.generated_at || data.generatedAt;
  const count = Number(data.online_count ?? data.onlineCount ?? crews.length);

  setText($('#networkCount'), String(count));
  setText($('#networkOperation'), operationId || '—');
  setText($('#networkUpdated'), generatedAt
    ? new Date(generatedAt).toLocaleTimeString('fr-FR', { timeZone: localSettings.timeFormat === 'utc' ? 'UTC' : undefined })
    : '—');

  const state = $('#networkState');
  if (state) {
    state.textContent = count > 0 ? 'ONLINE' : 'STANDBY';
    state.classList.toggle('ready', count > 0);
  }

  const badge = $('#networkBadge');
  if (badge) {
    badge.hidden = count <= 0;
    badge.textContent = String(count);
  }

  const list = $('#networkCrews');
  if (!list) return;
  list.replaceChildren();

  if (!crews.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = 'Aucun équipage Hermès connecté actuellement.';
    list.append(empty);
    return;
  }

  crews.forEach(crew => {
    const pilot = crew.pilot || {};
    const flight = crew.flight || {};
    const aircraft = crew.aircraft || {};
    const item = document.createElement('article');
    item.className = 'network-crew';

    const heading = document.createElement('div');
    heading.className = 'network-crew-heading';
    const title = document.createElement('strong');
    title.textContent = [pilot.ident || 'PILOT', flight.ident].filter(Boolean).join(' · ');
    const route = document.createElement('span');
    route.textContent = [flight.departure, flight.arrival].filter(Boolean).join(' → ') || 'Opération Air Inter';
    heading.append(title, route);

    const details = document.createElement('p');
    const phase = crew.phase || 'STANDBY';
    const simulator = String(crew.simulator || 'unknown').toUpperCase();
    const plane = [aircraft.registration, aircraft.icao].filter(Boolean).join(' · ') || 'Appareil non affecté';
    const version = crew.hermes_version || crew.hermesVersion || 'version inconnue';
    const age = Number(crew.age_seconds ?? crew.ageSeconds ?? 0);
    details.textContent = `${plane} · ${phase} · ${simulator} · Hermès ${version} · signal ${age}s`;

    item.append(heading, details);
    list.append(item);
  });
}

let networkRefreshing = false;
async function refreshNetwork() {
  if (!connected || networkRefreshing) return;
  networkRefreshing = true;
  try {
    const operationId = currentDatalinkOperation();
    const path = '/api/network' + (operationId ? '?operation=' + encodeURIComponent(operationId) : '');
    const network = await call(path);
    renderNetwork(network);
    showMessage('#networkMessage', '');
  } catch (error) {
    showMessage('#networkMessage', friendlyError(error), true);
  } finally {
    networkRefreshing = false;
  }
}

const flightMapState = {
  centerLat: 46.5,
  centerLon: 2.5,
  zoom: 5,
  autoFit: true,
  dragging: false,
  pointerId: null,
  dragStart: null,
  dragCenterWorld: null,
  lastTrack: [],
  hadTrack: false
};

function clampMapLatitude(value) {
  return Math.max(-85.05112878, Math.min(85.05112878, Number(value)));
}

function mapWorldPoint(lat, lon, zoom = flightMapState.zoom) {
  const scale = 256 * Math.pow(2, zoom);
  const x = (Number(lon) + 180) / 360 * scale;
  const latitude = clampMapLatitude(lat) * Math.PI / 180;
  const y = (1 - Math.log(Math.tan(latitude) + 1 / Math.cos(latitude)) / Math.PI) / 2 * scale;
  return { x, y };
}

function mapGeoPoint(x, y, zoom = flightMapState.zoom) {
  const scale = 256 * Math.pow(2, zoom);
  const lon = x / scale * 360 - 180;
  const n = Math.PI - 2 * Math.PI * y / scale;
  const lat = 180 / Math.PI * Math.atan(Math.sinh(n));
  return { lat: clampMapLatitude(lat), lon };
}

function normalizedTrackPoint(point) {
  const lat = Number(point?.lat ?? point?.Lat);
  const lon = Number(point?.lon ?? point?.Lon);
  const altitude = Number(point?.altitude ?? point?.Altitude);
  if (!Number.isFinite(lat) || !Number.isFinite(lon) || Math.abs(lat) > 90 || Math.abs(lon) > 180) return null;
  return { lat, lon, altitude: Number.isFinite(altitude) ? altitude : null };
}

function normalizedPlanPoint(point) {
  const lat = Number(point?.lat ?? point?.Lat), lon = Number(point?.lon ?? point?.Lon);
  if (!Number.isFinite(lat) || !Number.isFinite(lon) || Math.abs(lat) > 90 || Math.abs(lon) > 180) return null;
  return { lat, lon, ident: String(point?.ident ?? point?.Ident ?? '').trim() };
}
function currentPlannedRoute() {
  return (Array.isArray(flightPlan?.route_points) ? flightPlan.route_points : []).map(normalizedPlanPoint).filter(Boolean);
}
function currentMapFitPoints() {
  return [...currentPlannedRoute(), ...(Array.isArray(flightMapState.lastTrack) ? flightMapState.lastTrack.map(normalizedTrackPoint).filter(Boolean) : [])];
}
function plannedProgress(planned, current) {
  if (!current || planned.length < 2) return null;
  let best=0, bestDistance=Infinity;
  planned.forEach((point,index)=>{ const latScale=Math.cos(current.lat*Math.PI/180), dx=(point.lon-current.lon)*latScale, dy=point.lat-current.lat, d=dx*dx+dy*dy; if(d<bestDistance){bestDistance=d;best=index;} });
  return Math.max(0,Math.min(100,best/(planned.length-1)*100));
}

function mapScreenPoint(point, width, height) {
  const center = mapWorldPoint(flightMapState.centerLat, flightMapState.centerLon);
  const world = mapWorldPoint(point.lat, point.lon);
  return {
    x: width / 2 + world.x - center.x,
    y: height / 2 + world.y - center.y
  };
}

function fitFlightMap(points) {
  const map = $('#flightMap');
  if (!map || !points.length) return;
  const width = Math.max(320, map.clientWidth);
  const height = Math.max(300, map.clientHeight);
  if (points.length === 1) {
    flightMapState.centerLat = points[0].lat;
    flightMapState.centerLon = points[0].lon;
    flightMapState.zoom = 12;
    return;
  }

  const minLat = Math.min(...points.map(point => point.lat));
  const maxLat = Math.max(...points.map(point => point.lat));
  const minLon = Math.min(...points.map(point => point.lon));
  const maxLon = Math.max(...points.map(point => point.lon));
  const padding = 84;

  for (let zoom = 13; zoom >= 3; zoom -= 1) {
    const northWest = mapWorldPoint(maxLat, minLon, zoom);
    const southEast = mapWorldPoint(minLat, maxLon, zoom);
    if (Math.abs(southEast.x - northWest.x) <= width - padding * 2
      && Math.abs(southEast.y - northWest.y) <= height - padding * 2) {
      flightMapState.zoom = zoom;
      const center = mapGeoPoint((northWest.x + southEast.x) / 2, (northWest.y + southEast.y) / 2, zoom);
      flightMapState.centerLat = center.lat;
      flightMapState.centerLon = center.lon;
      return;
    }
  }

  flightMapState.zoom = 3;
  flightMapState.centerLat = (minLat + maxLat) / 2;
  flightMapState.centerLon = (minLon + maxLon) / 2;
}

function renderFlightMapTiles(width, height) {
  const layer = $('#flightMapTiles');
  if (!layer) return;
  const zoom = flightMapState.zoom;
  const center = mapWorldPoint(flightMapState.centerLat, flightMapState.centerLon, zoom);
  const tileSize = 256;
  const tilesAcross = Math.pow(2, zoom);
  const left = center.x - width / 2;
  const top = center.y - height / 2;
  const firstX = Math.floor(left / tileSize) - 1;
  const lastX = Math.floor((left + width) / tileSize) + 1;
  const firstY = Math.floor(top / tileSize) - 1;
  const lastY = Math.floor((top + height) / tileSize) + 1;
  const wanted = new Set();

  for (let tileY = firstY; tileY <= lastY; tileY += 1) {
    if (tileY < 0 || tileY >= tilesAcross) continue;
    for (let tileX = firstX; tileX <= lastX; tileX += 1) {
      const wrappedX = ((tileX % tilesAcross) + tilesAcross) % tilesAcross;
      const key = zoom + '/' + wrappedX + '/' + tileY + '@' + tileX;
      wanted.add(key);
      let tile = layer.querySelector('[data-map-tile="' + key + '"]');
      if (!tile) {
        tile = document.createElement('img');
        tile.className = 'flight-map-tile';
        tile.alt = '';
        tile.draggable = false;
        tile.dataset.mapTile = key;
        tile.src = 'https://tile.openstreetmap.org/' + zoom + '/' + wrappedX + '/' + tileY + '.png';
        layer.append(tile);
      }
      tile.style.left = (tileX * tileSize - left) + 'px';
      tile.style.top = (tileY * tileSize - top) + 'px';
    }
  }

  layer.querySelectorAll('[data-map-tile]').forEach(tile => {
    if (!wanted.has(tile.dataset.mapTile)) tile.remove();
  });
}

function resizeFlightMapCanvas(canvas, width, height) {
  const ratio = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
  const pixelWidth = Math.round(width * ratio);
  const pixelHeight = Math.round(height * ratio);
  if (canvas.width !== pixelWidth || canvas.height !== pixelHeight) {
    canvas.width = pixelWidth;
    canvas.height = pixelHeight;
  }
  canvas.style.width = width + 'px';
  canvas.style.height = height + 'px';
  const context = canvas.getContext('2d');
  context.setTransform(ratio, 0, 0, ratio, 0, 0);
  return context;
}

function approximateTrackHeading(points, latest) {
  const direct = Number(latest?.headingDegrees ?? latest?.HeadingDegrees ?? latest?.heading ?? latest?.Heading);
  if (Number.isFinite(direct)) return direct;
  if (points.length < 2) return 0;
  const a = points[points.length - 2];
  const b = points[points.length - 1];
  const lat1 = a.lat * Math.PI / 180;
  const lat2 = b.lat * Math.PI / 180;
  const deltaLon = (b.lon - a.lon) * Math.PI / 180;
  const y = Math.sin(deltaLon) * Math.cos(lat2);
  const x = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(deltaLon);
  return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
}

function updateFlightMapControls() {
  const follow = $('#mapFollowBtn');
  if (follow) {
    follow.classList.toggle('active', flightMapState.autoFit);
    follow.setAttribute('aria-pressed', flightMapState.autoFit ? 'true' : 'false');
    follow.textContent = flightMapState.autoFit ? 'Suivi auto ✓' : 'Suivi auto';
  }
  setText($('#flightMapZoom'), 'Z' + flightMapState.zoom);
}

function drawMap(track, latest = {}) {
  const map=$('#flightMap'), canvas=$('#flightMapOverlay'), empty=$('#flightMapEmpty'), aircraft=$('#flightMapAircraft');
  if(!map||!canvas)return;
  const width=Math.max(1,map.clientWidth), height=Math.max(1,map.clientHeight), context=resizeFlightMapCanvas(canvas,width,height);
  const points=(Array.isArray(track)?track:[]).map(normalizedTrackPoint).filter(Boolean), planned=currentPlannedRoute();
  flightMapState.lastTrack=track||[];
  if(!points.length&&!planned.length){
    if(flightMapState.hadTrack){flightMapState.centerLat=46.5;flightMapState.centerLon=2.5;flightMapState.zoom=5;flightMapState.autoFit=true;}
    flightMapState.hadTrack=false;renderFlightMapTiles(width,height);context.clearRect(0,0,width,height);
    if(aircraft)aircraft.hidden=true;if(empty){empty.hidden=false;empty.textContent='En attente de la télémétrie…';}
    setText($('#flightMapState'),'STANDBY');setText($('#flightMapFlight'),selectedOperation?displayFlightIdent(normalizeFlight(selectedOperation.flight||selectedOperation)):'—');
    ['#flightMapPosition','#flightMapAltitude','#flightMapSpeed','#flightMapProgress','#flightMapEta'].forEach(id=>setText($(id),'—'));
    const flight=selectedOperation?normalizeFlight(selectedOperation.flight||selectedOperation):null;
    setText($('#flightMapRoute'),flight?(flight.departure||'—')+' → '+(flight.arrival||'—'):'La carte se mettra à jour dès le premier point ACARS.');updateFlightMapControls();return;
  }
  flightMapState.hadTrack=points.length>0;const fitPoints=[...planned,...points];if(flightMapState.autoFit&&!flightMapState.dragging&&fitPoints.length)fitFlightMap(fitPoints);
  renderFlightMapTiles(width,height);context.clearRect(0,0,width,height);
  const bodyStyle=getComputedStyle(document.body), plannedColor=bodyStyle.getPropertyValue('--red').trim()||'#c43a42', actualColor=bodyStyle.getPropertyValue('--blue').trim()||'#2f8fd8';
  if(planned.length>1){
    const screen=planned.map(p=>mapScreenPoint(p,width,height));context.save();context.lineJoin='round';context.lineCap='round';context.strokeStyle=plannedColor;context.globalAlpha=.78;context.lineWidth=2.4;context.setLineDash([8,7]);context.beginPath();
    screen.forEach((p,i)=>i?context.lineTo(p.x,p.y):context.moveTo(p.x,p.y));context.stroke();context.setLineDash([]);context.globalAlpha=.95;
    const every=Math.max(1,Math.ceil(planned.length/8));screen.forEach((p,i)=>{const label=i===0||i===planned.length-1||i%every===0;context.beginPath();context.fillStyle='#fff';context.strokeStyle=plannedColor;context.lineWidth=2;context.arc(p.x,p.y,label?4:2.5,0,Math.PI*2);context.fill();context.stroke();if(label&&planned[i].ident){context.fillStyle=bodyStyle.getPropertyValue('--navy').trim()||'#173b59';context.font='700 10px system-ui, sans-serif';context.fillText(planned[i].ident,p.x+6,p.y-6);}});context.restore();
  }
  const screenPoints=points.map(p=>mapScreenPoint(p,width,height));
  if(screenPoints.length>1){context.save();context.lineJoin='round';context.lineCap='round';context.strokeStyle='rgba(5, 29, 52, .45)';context.lineWidth=7;context.beginPath();screenPoints.forEach((p,i)=>i?context.lineTo(p.x,p.y):context.moveTo(p.x,p.y));context.stroke();context.strokeStyle=actualColor;context.lineWidth=3.5;context.beginPath();screenPoints.forEach((p,i)=>i?context.lineTo(p.x,p.y):context.moveTo(p.x,p.y));context.stroke();context.restore();}
  if(screenPoints.length){const first=screenPoints[0];context.save();context.fillStyle='#fff';context.strokeStyle=actualColor;context.lineWidth=3;context.beginPath();context.arc(first.x,first.y,7,0,Math.PI*2);context.fill();context.stroke();context.restore();}
  const currentPoint=points.length?points[points.length-1]:null,current=screenPoints.length?screenPoints[screenPoints.length-1]:null;
  if(aircraft){aircraft.hidden=!current;if(current){aircraft.style.left=current.x+'px';aircraft.style.top=current.y+'px';aircraft.style.setProperty('--aircraft-heading',(approximateTrackHeading(points,latest)-45)+'deg');}}
  if(empty){empty.hidden=points.length>0;if(!points.length)empty.textContent='Route prévue chargée · en attente de la télémétrie…';}
  const flight=selectedOperation?normalizeFlight(selectedOperation.flight||selectedOperation):null, altitude=Number(latest?.altitudeMslFeet??latest?.AltitudeMslFeet??latest?.altitude??latest?.Altitude??currentPoint?.altitude), speed=Number(latest?.groundSpeedKnots??latest?.GroundSpeedKnots??latest?.gs??latest?.Gs);
  const recording=Boolean(lastStatus?.flight?.recording??lastStatus?.flight?.Recording);setText($('#flightMapState'),recording?'LIVE':(points.length?'TRACK':'PLANNED'));setText($('#flightMapFlight'),flight?displayFlightIdent(flight):'Vol en cours');setText($('#flightMapRoute'),flight?(flight.departure||'—')+' → '+(flight.arrival||'—'):'Trajet ACARS enregistré');
  setText($('#flightMapPosition'),currentPoint?currentPoint.lat.toFixed(4)+', '+currentPoint.lon.toFixed(4):'—');setText($('#flightMapAltitude'),Number.isFinite(altitude)?Math.round(altitude).toLocaleString('fr-FR')+' ft':'—');setText($('#flightMapSpeed'),Number.isFinite(speed)?Math.round(speed)+' kt':'—');
  const progress=plannedProgress(planned,currentPoint);setText($('#flightMapProgress'),progress==null?(planned.length?'0 %':'—'):Math.round(progress)+' %');
  const estimated=Number(flightPlan?.estimated_time_enroute||0), activeFlight=lastStatus?.flight||lastStatus?.Flight||{}, elapsed=Number(activeFlight.airborneSeconds??activeFlight.AirborneSeconds??0), remaining=estimated>0?Math.max(0,estimated-elapsed):null;setText($('#flightMapEta'),remaining==null?'—':Math.ceil(remaining/60)+' min');updateFlightMapControls();
}

const flightMap = $('#flightMap');
if (flightMap) {
  flightMap.addEventListener('pointerdown', event => {
    if (event.button !== 0 || event.target.closest('.flight-map-controls')) return;
    flightMapState.dragging = true;
    flightMapState.pointerId = event.pointerId;
    flightMapState.dragStart = { x: event.clientX, y: event.clientY };
    flightMapState.dragCenterWorld = mapWorldPoint(flightMapState.centerLat, flightMapState.centerLon);
    flightMapState.autoFit = false;
    flightMap.setPointerCapture?.(event.pointerId);
    flightMap.classList.add('dragging');
    updateFlightMapControls();
  });

  flightMap.addEventListener('pointermove', event => {
    if (!flightMapState.dragging || event.pointerId !== flightMapState.pointerId) return;
    const deltaX = event.clientX - flightMapState.dragStart.x;
    const deltaY = event.clientY - flightMapState.dragStart.y;
    const center = mapGeoPoint(
      flightMapState.dragCenterWorld.x - deltaX,
      flightMapState.dragCenterWorld.y - deltaY
    );
    flightMapState.centerLat = center.lat;
    flightMapState.centerLon = center.lon;
    drawMap(flightMapState.lastTrack, lastStatus?.latest || {});
  });

  const stopDragging = event => {
    if (!flightMapState.dragging || (event.pointerId != null && event.pointerId !== flightMapState.pointerId)) return;
    flightMapState.dragging = false;
    flightMap.classList.remove('dragging');
    if (flightMapState.pointerId != null) {
      try { flightMap.releasePointerCapture?.(flightMapState.pointerId); } catch {}
    }
    flightMapState.pointerId = null;
  };
  flightMap.addEventListener('pointerup', stopDragging);
  flightMap.addEventListener('pointercancel', stopDragging);

  flightMap.addEventListener('wheel', event => {
    event.preventDefault();
    flightMapState.autoFit = false;
    flightMapState.zoom = Math.max(3, Math.min(15, flightMapState.zoom + (event.deltaY < 0 ? 1 : -1)));
    updateFlightMapControls();
    drawMap(flightMapState.lastTrack, lastStatus?.latest || {});
  }, { passive: false });
}

$('#mapZoomInBtn')?.addEventListener('click', () => {
  flightMapState.autoFit = false;
  flightMapState.zoom = Math.min(15, flightMapState.zoom + 1);
  drawMap(flightMapState.lastTrack, lastStatus?.latest || {});
});

$('#mapZoomOutBtn')?.addEventListener('click', () => {
  flightMapState.autoFit = false;
  flightMapState.zoom = Math.max(3, flightMapState.zoom - 1);
  drawMap(flightMapState.lastTrack, lastStatus?.latest || {});
});

$('#mapFollowBtn')?.addEventListener('click', () => {
  flightMapState.autoFit = !flightMapState.autoFit;
  if (flightMapState.autoFit) {
    const points = currentMapFitPoints();
    if (points.length) fitFlightMap(points);
  }
  drawMap(flightMapState.lastTrack, lastStatus?.latest || {});
});

$('#mapFitBtn')?.addEventListener('click', () => {
  const points = currentMapFitPoints();
  if (!points.length) return;
  flightMapState.autoFit = true;
  fitFlightMap(points);
  drawMap(flightMapState.lastTrack, lastStatus?.latest || {});
});

window.addEventListener('resize', () => drawMap(flightMapState.lastTrack, lastStatus?.latest || {}));

function renderTimeline(selector, entries) {
  const node = $(selector);
  node.replaceChildren();
  if (!entries?.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = 'En attente d’un vol.';
    node.append(empty);
    return;
  }
  entries.forEach(entry => {
    const item = document.createElement('span');
    const occurredAt = entry.occurredAt || entry.OccurredAt;
    const value = entry.value ?? entry.Value;
    const time = new Date(occurredAt).toLocaleTimeString('fr-FR', { timeZone: localSettings.timeFormat === 'utc' ? 'UTC' : undefined });
    item.textContent = `${time} — ${entry.name || entry.Name}${value == null ? '' : ' · ' + Number(value).toFixed(0)}`;
    node.append(item);
  });
}

function reviewValue(review, camel, pascal = camel) {
  return review?.[camel] ?? review?.[pascal] ?? null;
}

function renderObservations(selector, entries, emptyText) {
  const node = $(selector);
  if (!node) return;
  node.replaceChildren();
  if (!entries?.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = emptyText;
    node.append(empty);
    return;
  }
  entries.forEach(entry => {
    const item = document.createElement('article');
    const severity = String(entry.severity ?? entry.Severity ?? 'info').toLowerCase();
    item.className = 'observation ' + severity;
    const code = entry.code ?? entry.Code ?? 'OBS';
    const message = entry.message ?? entry.Message ?? '';
    const occurredAt = entry.occurredAt ?? entry.OccurredAt;
    const phase = entry.phase ?? entry.Phase;
    const value = entry.value ?? entry.Value;
    const unit = entry.unit ?? entry.Unit ?? '';
    const meta = document.createElement('span');
    const time = occurredAt ? new Date(occurredAt).toLocaleTimeString('fr-FR', { timeZone: localSettings.timeFormat === 'utc' ? 'UTC' : undefined }) : null;
    meta.textContent = [time, phase, code].filter(Boolean).join(' · ');
    const title = document.createElement('strong');
    title.textContent = message || code;
    const detail = document.createElement('small');
    detail.textContent = value == null ? severity.toUpperCase() : (Number(value).toFixed(Number.isInteger(Number(value)) ? 0 : 1) + ' ' + unit).trim();
    item.append(meta, title, detail);
    node.append(item);
  });
}

function renderReview(review) {
  const current = review || lastFiledReview;
  const state = $('#reviewState');
  if (!current) {
    if (state) { state.textContent = 'AUCUN VOL'; state.classList.remove('ready'); }
    ['#reviewDistance','#reviewAirborne','#reviewBlock','#reviewFuel','#reviewLandingRate','#reviewMaxBank','#reviewFuelAdded','#reviewSimRate'].forEach(id => setText($(id), '—'));
    setText($('#review1000'), 'NON OBSERVÉ');
    setText($('#review500'), 'NON OBSERVÉ');
    setText($('#reviewGoAround'), '0 remise de gaz');
    setText($('#reviewBounce'), '0 rebond');
    renderObservations('#fdmObservations', [], 'Aucune observation pour le moment.');
    renderObservations('#reviewIssues', [], 'Aucune anomalie détectée.');
    if ($('#submitReviewBtn')) $('#submitReviewBtn').disabled = true;
    setText($('#reviewHint'), 'Le dépôt du PIREP devient disponible après l’événement IN.');
    return;
  }

  const phase = String(reviewValue(current, 'phase', 'Phase') || '—');
  const ready = Boolean(reviewValue(current, 'readyToFile', 'ReadyToFile'));
  const filed = Boolean(lastFiledReview && !lastStatus?.review && !lastStatus?.Review);
  if (state) {
    state.textContent = filed ? 'PIREP DÉPOSÉ' : (ready ? 'READY TO FILE' : phase);
    state.classList.toggle('ready', ready || filed);
  }

  setText($('#reviewDistance'), Number(reviewValue(current,'distance','Distance') || 0).toFixed(1) + ' NM');
  setText($('#reviewAirborne'), Number(reviewValue(current,'airborneMinutes','AirborneMinutes') || 0) + ' min');
  setText($('#reviewBlock'), Number(reviewValue(current,'blockMinutes','BlockMinutes') || 0) + ' min');
  setText($('#reviewFuel'), Math.round(Number(reviewValue(current,'fuelUsed','FuelUsed') || 0)) + ' lb');
  const landing = reviewValue(current,'landingRate','LandingRate');
  setText($('#reviewLandingRate'), landing == null ? '—' : Math.round(Number(landing)) + ' ft/min');
  const maxBank = reviewValue(current,'maxBankDegrees','MaxBankDegrees');
  setText($('#reviewMaxBank'), maxBank == null ? '—' : Number(maxBank).toFixed(1) + '°');
  setText($('#reviewFuelAdded'), Math.round(Number(reviewValue(current,'fuelAdded','FuelAdded') || 0)) + ' lb');
  const simRate = reviewValue(current,'maxSimulationRate','MaxSimulationRate');
  setText($('#reviewSimRate'), simRate == null ? 'x1' : 'x' + Number(simRate).toFixed(2).replace(/\.00$/,''));

  setText($('#review1000'), reviewValue(current,'approach1000Status','Approach1000Status') || 'NON OBSERVÉ');
  setText($('#review500'), reviewValue(current,'approach500Status','Approach500Status') || 'NON OBSERVÉ');
  const goArounds = Number(reviewValue(current,'goAroundCount','GoAroundCount') || 0);
  const bounces = Number(reviewValue(current,'bounceCount','BounceCount') || 0);
  setText($('#reviewGoAround'), goArounds + ' remise' + (goArounds > 1 ? 's' : '') + ' de gaz');
  setText($('#reviewBounce'), bounces + ' rebond' + (bounces > 1 ? 's' : ''));

  renderObservations('#fdmObservations', reviewValue(current,'observations','Observations') || [], 'Aucune observation FDM.');
  renderObservations('#reviewIssues', reviewValue(current,'issues','Issues') || [], 'Aucune anomalie détectée.');

  const button = $('#submitReviewBtn');
  if (button) button.disabled = !ready || filed;
  setText($('#reviewHint'), ready
    ? 'Vol arrivé au parking. Vérifiez la synthèse puis déposez le PIREP.'
    : 'Flight Review en cours · phase ' + phase + '. Le dépôt sera disponible après IN.');
}

$('#submitReviewBtn').onclick = async () => {
  try {
    const result = await call('/api/file', {});
    lastFiledReview = result.review || result.Review || lastStatus?.review || lastStatus?.Review || null;
    showMessage('#reviewMessage', 'PIREP déposé. Flight Review archivé localement.');
    renderReview(lastFiledReview);
    await refreshStatus();
  } catch (error) {
    showMessage('#reviewMessage', friendlyError(error), true);
  }
};

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

function renderAircraftCapabilities(report) {
  const panel = $('#aircraftCapabilities');
  if (!panel) return;
  if (!report) { panel.hidden = true; return; }
  panel.hidden = false;
  const read = (camel, pascal) => report?.[camel] ?? report?.[pascal];
  setText($('#capabilityAircraft'), read('aircraftLabel','AircraftLabel') || 'Appareil non identifié');
  const adapterName = read('adapterName','AdapterName') || 'Generic aircraft';
  const connector = read('connectorId','ConnectorId') || 'connector';
  setText($('#capabilityAdapter'), adapterName + ' · ' + connector);
  setText($('#capabilitySupported'), String(read('supportedCount','SupportedCount') ?? 0));
  setText($('#capabilityUnknown'), String(read('unknownCount','UnknownCount') ?? 0));
  setText($('#capabilityUnsupported'), String(read('unsupportedCount','UnsupportedCount') ?? 0));

  const matrix = $('#capabilityMatrix');
  matrix.replaceChildren();
  const entries = read('capabilities','Capabilities') || [];
  entries.forEach(entry => {
    const capability = entry.capability ?? entry.Capability;
    const availability = String(entry.availability ?? entry.Availability ?? 'Unknown');
    const source = entry.source ?? entry.Source ?? '';
    const item = document.createElement('span');
    item.className = 'capability-item ' + availability.toLowerCase();
    const label = document.createElement('strong');
    label.textContent = capabilityLabels[capability] || capability;
    const state = document.createElement('small');
    state.textContent = availability === 'Supported' ? 'DISPONIBLE'
      : availability === 'Unsupported' ? 'NON PRIS EN CHARGE'
      : 'À COMPLÉTER';
    item.title = source;
    item.append(label, state);
    matrix.append(item);
  });
}

function renderRecovery(status) {
  const center = $('#recoveryCenter');
  if (!center) return;
  const available = Boolean(status?.recoveryAvailable);
  center.hidden = !available;
  if (!available) {
    recoveryWasVisible = false;
    return;
  }

  if (!recoveryWasVisible) {
    recoveryWasVisible = true;
    setTimeout(() => center.scrollIntoView({ behavior: 'smooth', block: 'start' }), 0);
  }

  const info = status.recovery || {};
  const read = (camel, pascal) => info[camel] ?? info[pascal];
  const phase = read('phase', 'Phase') || 'INTERROMPU';
  const pending = Number(read('pendingMessages', 'PendingMessages') || status.pending || 0);
  const distance = Number(read('distance', 'Distance') || 0);
  const airborne = Number(read('airborneMinutes', 'AirborneMinutes') || 0);
  const started = read('started', 'Started');
  const pirep = read('pirepId', 'PirepId') || '—';

  setText($('#recoveryPirep'), pirep);
  setText($('#recoveryPhase'), 'RECOVERY · ' + phase);
  setText($('#recoveryPhaseValue'), phase);
  setText($('#recoveryDistance'), distance.toFixed(1) + ' NM');
  setText($('#recoveryAirborne'), airborne + ' min');
  setText($('#recoveryPending'), String(pending));
  setText($('#recoverySummary'), started
    ? `Hermès a retrouvé le vol ${pirep} commencé le ${new Date(started).toLocaleString('fr-FR')}.`
    : `Hermès a retrouvé le vol ${pirep} enregistré localement.`);

  const resume = $('#recoveryResumeBtn');
  const simReady = Boolean(status.latest);
  resume.disabled = !connected || !simReady;
  if (!connected) setText($('#recoveryHint'), 'Connectez-vous à votre compte Air Inter pour reprendre ce vol.');
  else if (!simReady) setText($('#recoveryHint'), status.simLinkState === 'RECONNECTING'
    ? 'Le simulateur a été perdu. Hermès attend sa reconnexion avant de reprendre.'
    : 'Reconnectez le simulateur avant de reprendre ce vol.');
  else {
    const warning = String(status.warning || '');
    if (warning.includes('autre serveur Prométhée')) {
      resume.disabled = true;
      setText($('#recoveryHint'), 'Cet ancien état local ne correspond pas au serveur Prométhée actuel. Cliquez « Abandonner ce vol » pour l’archiver localement puis démarrez la nouvelle réservation.');
    } else {
      setText($('#recoveryHint'), 'Compte et simulateur disponibles. La reprise peut continuer sans recréer le vol.');
    }
  }
}

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
    await call('/api/recovery/resume', {});
    showMessage('#recoveryMessage', 'Vol repris. Hermès continue à partir de l’état local sauvegardé.');
    $('#recoveryCenter').hidden = true;
    document.querySelector('[data-tab="record"]')?.click();
    await refreshStatus();
  } catch (error) {
    showMessage('#recoveryMessage', friendlyError(error), true);
  }
};

$('#recoveryAbandonBtn').onclick = async () => {
  const pirep = lastStatus?.recovery?.pirepId ?? lastStatus?.recovery?.PirepId ?? 'ce vol';
  if (!confirm(`Abandonner ${pirep} ? L’état sera archivé localement avant nettoyage.`)) return;
  try {
    await call('/api/recovery/abandon', {});
    $('#recoveryCenter').hidden = true;
    showMessage(connected ? '#recordMessage' : '#loginMessage', 'Vol interrompu abandonné. Une copie de récupération a été archivée localement.');
    await refreshStatus();
  } catch (error) {
    showMessage('#recoveryMessage', friendlyError(error), true);
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
    renderReview(status.review || status.Review || lastFiledReview);
  } catch {}
}

updateWorkflow();
drawMap([]);
refreshStatus();
setInterval(() => {
  if (!document.hidden) refreshStatus();
}, 1000);
setInterval(() => {
  if (!document.hidden && $('#datalink')?.classList.contains('active')) refreshDatalink();
}, 5000);
setInterval(() => {
  if (!document.hidden && $('#network')?.classList.contains('active')) refreshNetwork();
}, 15000);
document.addEventListener('visibilitychange', () => {
  if (document.hidden) return;
  refreshStatus();
  if ($('#datalink')?.classList.contains('active')) refreshDatalink();
  if ($('#network')?.classList.contains('active')) refreshNetwork();
  drawMap(flightMapState.lastTrack, lastStatus?.latest || {});
});
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
