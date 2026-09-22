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
const call = (path, body) => new Promise((resolve, reject) => {
  if (!globalThis.chrome?.webview) {
    reject(new Error('Pont Hermès/WebView2 indisponible. Redémarrez Hermès après reconstruction.'));
    return;
  }
  const id = crypto.randomUUID();
  const timer = setTimeout(() => {
    chrome.webview.removeEventListener('message', onMessage);
    reject(new Error('Hermès n’a reçu aucune réponse du backend local après 15 secondes.'));
  }, 15000);
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
let pirepId = null;
let flightPlan = null;
let linkedSimBrief = null;
let serverDispatch = null;
let connected = false;
let readiness = { operation: false, aircraft: false, ofp: false, pirep: false, simulator: false };

function setAuthenticated(value) {
  connected = Boolean(value);
  document.body.classList.toggle('auth-locked', !connected);
  $$('.protected-tab').forEach(tab => { tab.disabled = !connected; });
}
setAuthenticated(false);

const settingsForm = $('#settingsForm');
const defaultSettings = { autoDetection: 'true', forcedSimulator: '', timeFormat: 'local', notifications: 'true', simbriefUsername: '', simbriefPilotId: '', flightPlanMode: 'account' };
let savedSettings = {};
try { savedSettings = JSON.parse(localStorage.prometheeAcarsSettings || '{}'); } catch {}
let localSettings = { ...defaultSettings, ...savedSettings };

const era = $('#era');
document.body.dataset.era = localStorage.prometheeEra || 'modern';
era.value = document.body.dataset.era;
era.onchange = () => {
  document.body.dataset.era = era.value;
  localStorage.prometheeEra = era.value;
};
Object.entries(localSettings).forEach(([key, value]) => {
  if (settingsForm.elements[key]) settingsForm.elements[key].value = value;
});
settingsForm.onsubmit = event => {
  event.preventDefault();
  localSettings = { ...defaultSettings, ...Object.fromEntries(new FormData(settingsForm)) };
  localStorage.prometheeAcarsSettings = JSON.stringify(localSettings);
  showMessage('#settingsMessage', 'Réglages locaux enregistrés.');
};

$$('.tab').forEach(button => {
  button.onclick = () => {
    if (button.classList.contains('protected-tab') && !connected) return;
    $$('.tab,.panel').forEach(node => node.classList.remove('active'));
    button.classList.add('active');
    $('#' + button.dataset.tab).classList.add('active');
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

async function login(form, advanced = false) {
  const body = Object.fromEntries(new FormData(form));
  try {
    const response = await call(advanced ? '/api/config' : '/api/login', body);
    pilotIdentity(response);
    setAuthenticated(true);
    showMessage('#loginMessage', 'Connexion réussie. Chargement de vos opérations…');
    await refreshOperations();
    document.querySelector('[data-tab="flight"]').click();
  } catch (error) {
    showMessage('#loginMessage', error.message || 'Impossible de se connecter à Prométhée.', true);
  }
}
$('#loginForm').onsubmit = event => { event.preventDefault(); login(event.currentTarget); };
$('#configForm').onsubmit = event => { event.preventDefault(); login(event.currentTarget, true); };

function updateWorkflow() {
  const server = serverDispatch?.server_checks;
  const state = server ? {
    operation: Boolean(server.operation),
    aircraft: Boolean(server.aircraft),
    ofp: Boolean(server.ofp),
    pirep: Boolean(server.pirep)
  } : {
    operation: Boolean(selectedOperation),
    aircraft: Boolean(selectedAircraft?.id),
    ofp: Boolean(flightPlan || selectedOperation?.simbrief?.available),
    pirep: Boolean(pirepId)
  };
  $$('#workflow [data-step]').forEach(node => {
    const key = node.dataset.step;
    const passed = key === 'ready'
      ? state.operation && state.aircraft && state.ofp && state.pirep && readiness.simulator
      : Boolean(state[key]);
    node.classList.toggle('done', passed);
    node.classList.toggle('current', !passed && (
      (key === 'operation' && !state.operation) ||
      (key === 'aircraft' && state.operation && !state.aircraft) ||
      (key === 'ofp' && state.aircraft && !state.ofp) ||
      (key === 'pirep' && state.ofp && !state.pirep) ||
      (key === 'ready' && state.pirep)
    ));
  });
  const ready = (serverDispatch ? serverDispatch.status === 'READY' : (state.operation && state.aircraft && state.ofp && state.pirep)) && readiness.simulator;
  const node = $('#readyState');
  if (node) {
    node.textContent = ready ? 'READY FOR DEPARTURE' : 'NOT READY';
    node.classList.toggle('ready', ready);
  }
}

function renderEligibility(payload) {
  const box = $('#aircraftEligibility');
  if (!box) return;
  const available = payload?.available || [];
  const unavailable = payload?.unavailable || [];
  box.replaceChildren();
  box.hidden = false;

  const heading = document.createElement('div');
  heading.className = 'eligibility-heading';
  heading.innerHTML = '<div><span class="kicker">DISPATCH</span><h3>Éligibilité des appareils</h3></div>';
  const count = document.createElement('strong');
  count.textContent = available.length + ' disponible' + (available.length > 1 ? 's' : '');
  heading.append(count);
  box.append(heading);

  [...available, ...unavailable].slice(0, 12).forEach(aircraft => {
    const card = document.createElement('article');
    card.className = 'aircraft-card ' + (aircraft.eligible ? 'eligible' : 'blocked');
    const title = document.createElement('div');
    const name = document.createElement('strong');
    name.textContent = [aircraft.registration, aircraft.name, aircraft.icao || aircraft.subfleet].filter(Boolean).join(' · ');
    const status = document.createElement('em');
    status.textContent = aircraft.eligible ? 'DISPONIBLE' : 'INDISPONIBLE';
    title.append(name, status);
    card.append(title);
    const checks = document.createElement('ul');
    (aircraft.eligible ? aircraft.checks : aircraft.reasons).forEach(item => {
      const li = document.createElement('li');
      const passed = item.passed !== false && aircraft.eligible;
      li.textContent = (passed ? '✓ ' : '✕ ') + (item.label || item.message || item.code);
      if (item.code && !aircraft.eligible) li.dataset.code = item.code;
      checks.append(li);
    });
    card.append(checks);
    box.append(card);
  });
}

function simulatorCode() {
  const forced = localSettings.forcedSimulator;
  return ({ xplane: 'xplane', fs2004: 'fs2004', fsx: 'fsx', p3d: 'p3d', msfs: 'msfs2024' })[forced] || '';
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
  setText(detail, [aircraft.registration, aircraft.name].filter(Boolean).join(' · ') || aircraft.subfleet || 'Appareil à sélectionner');
  const badge = document.createElement('em');
  badge.textContent = operation.bid_id ? 'RÉSERVÉ' : 'PROGRAMME';
  button.append(badge, title, route, detail);
  button.onclick = () => selectOperation({ ...operation, flight });
  return button;
}

function renderOperations(value) {
  const payload = unwrap(value);
  const operations = Array.isArray(payload) ? payload : (payload?.operations || payload?.data || []);
  const list = $('#flightList');
  list.replaceChildren();
  if (!operations.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    setText(empty, 'Aucune opération trouvée.');
    list.append(empty);
    return;
  }
  operations.forEach(operation => list.append(operationCard(operation)));
}

async function refreshOperations() {
  if (!connected) {
    renderOperations([]);
    showMessage('#flightMessage', 'Connectez-vous d’abord à votre compte pilote.', true);
    return;
  }
  try {
    showMessage('#flightMessage', 'Chargement de vos réservations…');
    const simulator = simulatorCode();
    renderOperations(await call('/api/v1/operations' + (simulator ? '?simulator=' + encodeURIComponent(simulator) : '')));
    showMessage('#flightMessage', '');
  } catch (error) {
    showMessage('#flightMessage', error.message, true);
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
  number = number.replace(/^ITF[ -]?/, '');
  if (number) params.set('flight_number', number);
  if (departure) params.set('dep_icao', departure);
  if (arrival) params.set('arr_icao', arrival);
  if (aircraftType) params.set('icao_type', aircraftType);
  try {
    showMessage('#flightMessage', 'Recherche dans le programme…');
    renderOperations(await call('/api/flights' + (params.size ? '?' + params.toString() : '')));
    showMessage('#flightMessage', '');
  } catch (error) {
    showMessage('#flightMessage', error.message, true);
  }
}
$('#bidsBtn').onclick = refreshOperations;
$('#flightSearchForm').onsubmit = event => {
  event.preventDefault();
  searchFlights();
};

function addAircraftOption(select, aircraft) {
  const option = document.createElement('option');
  option.value = aircraft.id || '';
  option.textContent = [aircraft.registration, aircraft.name].filter(Boolean).join(' · ') || aircraft.icao || aircraft.subfleet || 'Appareil sans immatriculation';
  option.dataset.aircraft = JSON.stringify(aircraft);
  select.append(option);
}

async function selectOperation(operation) {
  selectedOperation = operation;
  selectedAircraft = operation.aircraft?.id ? operation.aircraft : null;
  updateWorkflow();
  $$('.operation').forEach(node => node.classList.remove('selected'));
  if (document.activeElement?.classList?.contains('operation')) document.activeElement.classList.add('selected');
  flightPlan = null;
  const flight = normalizeFlight(operation.flight || operation);
  const form = $('#prefileForm');
  const assign = (name, value) => { if (form.elements[name]) form.elements[name].value = value ?? ''; };
  assign('flight_id', flight.id);
  assign('airline_id', flight.airline_id);
  assign('flight_number', flight.flight_number);
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
  setText($('#operationBrief'), simbrief.available
    ? `OFP SimBrief disponible · type ${simbrief.type || 'à confirmer'}`
    : `OFP à préparer · type ${simbrief.type || 'à confirmer'}`);
  $('#selectedOperation').hidden = false;
  form.hidden = false;
  showMessage('#pirepMessage', '');
  serverDispatch = null;

  const select = $('#aircraftId');
  select.replaceChildren();
  const loading = document.createElement('option');
  loading.value = '';
  loading.textContent = 'Chargement des appareils autorisés…';
  select.append(loading);

  if (selectedAircraft) {
    select.replaceChildren();
    addAircraftOption(select, selectedAircraft);
    select.value = selectedAircraft.id;
    try { await refreshDispatch(); }
    catch (error) { showMessage('#pirepMessage', 'Dispatch indisponible : ' + error.message, true); }
    return;
  }

  try {
    const operationRef = operation.operation_id || operation.id || operation.bid_id;
    if (!operationRef) throw new Error('Cette réservation ne possède pas d’identifiant d’opération Prométhée.');
    const payload = unwrap(await call(`/api/v1/operations/${encodeURIComponent(operationRef)}/aircraft-eligibility`));
    if (payload?.available || payload?.unavailable) renderEligibility(payload);
    const aircraft = Array.isArray(payload) ? payload : (payload?.available || payload?.aircraft || payload?.data || []);
    select.replaceChildren();
    const choose = document.createElement('option');
    choose.value = '';
    choose.textContent = aircraft.length ? 'Sélectionnez un appareil' : 'Aucun appareil disponible pour ce vol';
    select.append(choose);
    aircraft.forEach(item => addAircraftOption(select, item));
    if (!aircraft.length) {
      showMessage('#pirepMessage', 'Aucun appareil autorisé et disponible pour ce vol. Vérifiez la flotte, la position et les qualifications.', true);
    }
  } catch (error) {
    select.replaceChildren(loading);
    loading.textContent = 'Appareils indisponibles';
    showMessage('#pirepMessage', error.message, true);
  }

  try { await refreshDispatch(); }
  catch (error) { showMessage('#pirepMessage', 'Dispatch indisponible : ' + error.message, true); }
}

async function refreshDispatch() {
  const operationRef = selectedOperation?.operation_id || selectedOperation?.id;
  if (!operationRef) { serverDispatch = null; return null; }

  serverDispatch = unwrap(await call(`/api/v1/operations/${encodeURIComponent(operationRef)}/dispatch`));
  const checks = serverDispatch?.server_checks || {};
  readiness.operation = Boolean(checks.operation);
  readiness.aircraft = Boolean(checks.aircraft);
  readiness.ofp = Boolean(checks.ofp);
  readiness.pirep = Boolean(checks.pirep);

  const labels = { PREPARATION_REQUIRED: 'PRÉPARATION REQUISE', READY: 'PRÊT POUR HERMÈS', IN_PROGRESS: 'VOL EN COURS', COMPLETED: 'VOL TERMINÉ', CANCELLED: 'OPÉRATION ANNULÉE' };
  setText($('#operationBrief'), `${labels[serverDispatch?.status] || serverDispatch?.status || 'DISPATCH'} · Dispatch Prométhée`);
  updateReadiness();
  return serverDispatch;
}

async function assertDispatchCanStart() {
  const dispatch = await refreshDispatch();
  if (!dispatch?.can_start) {
    const actions = Array.isArray(dispatch?.actions) ? dispatch.actions.filter(Boolean).join(' ') : '';
    throw new Error(actions || `Prométhée refuse le démarrage : ${dispatch?.status || 'opération non prête'}.`);
  }
  return dispatch;
}

function simbriefPath(suffix) {
  const operationRef = selectedOperation?.operation_id || selectedOperation?.id;
  if (!operationRef) throw new Error('Sélectionnez une opération Prométhée avant de préparer SimBrief.');
  return `/api/v1/operations/${encodeURIComponent(operationRef)}/simbrief/${suffix}`;
}

function normalizeFlightLevel(value) {
  const altitude = Number(value);
  if (!Number.isFinite(altitude) || altitude <= 0) return undefined;
  // SimBrief returns general.initial_altitude in feet (e.g. 37000),
  // while phpVMS/Hermès stores the flight level (e.g. 370).
  return altitude > 600 ? Math.round(altitude / 100) : Math.round(altitude);
}

function applyBriefing(briefing, sourceLabel) {
  const form = $('#prefileForm');
  const flightLevel = normalizeFlightLevel(briefing.initial_altitude);
  flightPlan = {
    source: briefing.source || sourceLabel,
    simbrief_id: briefing.id,
    route: briefing.route,
    level: flightLevel,
    block_fuel: briefing.block_fuel || undefined
  };
  if (briefing.block_fuel) form.elements.block_fuel.value = Math.round(briefing.block_fuel);
  if (briefing.route) form.elements.route.value = briefing.route;
  if (flightLevel) form.elements.level.value = flightLevel;
  if (briefing.alternate) form.elements.alt_airport_id.value = briefing.alternate;
  $('#planBox').textContent = JSON.stringify(briefing, null, 2);
  showMessage('#simbriefState', 'OFP importé depuis ' + sourceLabel + ' et prêt pour le pré-PIREP.');
  updateWorkflow();
}

function setPlanMode(mode) {
  localSettings.flightPlanMode = mode;
  localStorage.prometheeAcarsSettings = JSON.stringify(localSettings);
  $$('[data-plan-mode]').forEach(button => button.classList.toggle('active', button.dataset.planMode === mode));
  $$('[data-plan-panel]').forEach(panel => {
    const active = panel.dataset.planPanel === mode;
    panel.classList.toggle('active', active);
    panel.hidden = !active;
  });
}
$$('[data-plan-mode]').forEach(button => button.onclick = () => setPlanMode(button.dataset.planMode));
$('#simbriefUsername').value = localSettings.simbriefUsername || '';
$('#simbriefPilotId').value = localSettings.simbriefPilotId || '';
['simbriefUsername', 'simbriefPilotId'].forEach(key => {
  const node = $('#' + key);
  node.onchange = () => {
    localSettings[key] = node.value.trim();
    localStorage.prometheeAcarsSettings = JSON.stringify(localSettings);
  };
});
setPlanMode(localSettings.flightPlanMode || 'account');

$('#simbriefAccountOpenBtn').onclick = async () => {
  const form = $('#prefileForm');
  const flightId = form.elements.flight_id.value;
  const aircraftId = form.elements.aircraft_id.value;
  if (!flightId || !aircraftId) return showMessage('#simbriefState', 'Sélectionnez un vol et un appareil.', true);
  try {
    showMessage('#simbriefState', 'Préparation du dispatch SimBrief…');
    const payload = unwrap(await call(simbriefPath('redirect'), { aircraft_id: aircraftId }));
    linkedSimBrief = payload;
    const editButton = $('#simbriefAccountEditBtn');
    if (editButton) editButton.hidden = !payload.edit_url;
    await call('/api/open-external', { url: payload.url });
    showMessage('#simbriefState', 'SimBrief est ouvert avec les données Air Inter. Personnalisez puis générez l’OFP, revenez ensuite dans Hermès pour l’importer.');
  } catch (error) {
    showMessage('#simbriefState', error.message, true);
  }
};

$('#simbriefAccountEditBtn').onclick = async () => {
  if (!linkedSimBrief?.edit_url) return showMessage('#simbriefState', 'Préparez d’abord ce vol dans SimBrief.', true);
  try { await call('/api/open-external', { url: linkedSimBrief.edit_url }); }
  catch (error) { showMessage('#simbriefState', error.message, true); }
};

$('#simbriefAccountImportBtn').onclick = async () => {
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
      pilot_id: username ? null : pilotId
    }));
    linkedSimBrief = { ...(linkedSimBrief || {}), static_id: briefing.static_id, edit_url: briefing.edit_url };
    const editButton = $('#simbriefAccountEditBtn');
    if (editButton) editButton.hidden = !briefing.edit_url;
    applyBriefing(briefing, 'le vol SimBrief lié à cette opération');
  } catch (error) {
    showMessage('#simbriefState', error.message, true);
  }
};

$('#aircraftId').onchange = event => {
  const option = event.target.selectedOptions[0];
  try { selectedAircraft = option?.dataset.aircraft ? JSON.parse(option.dataset.aircraft) : null; } catch { selectedAircraft = null; }
  updateWorkflow();
};

$('#resetDraftBtn').onclick = () => {
  const form = $('#prefileForm');
  let draft = {};
  try { draft = JSON.parse(form.dataset.programDraft || '{}'); } catch {}
  ['alt_airport_id', 'route', 'level'].forEach(name => {
    if (form.elements[name]) form.elements[name].value = draft[name] || '';
  });
  form.elements.block_fuel.value = '';
  form.elements.notes.value = '';
  flightPlan = null;
  $('#planFile').value = '';
  $('#planBox').textContent = 'Aucun plan chargé.';
  showMessage('#simbriefState', 'Brouillon réinitialisé aux données du programme.');
};

$('#simbriefBtn').onclick = async () => {
  const form = $('#prefileForm');
  const flightId = form.elements.flight_id.value;
  const aircraftId = form.elements.aircraft_id.value;
  if (!flightId || !aircraftId) return showMessage('#simbriefState', 'Sélectionnez un vol et un appareil.', true);
  try {
    showMessage('#simbriefState', 'Préparation de la demande SimBrief…');
    const session = unwrap(await call(simbriefPath('session'), {
      aircraft_id: aircraftId,
      alternate: form.elements.alt_airport_id.value.trim().toUpperCase(),
      route: form.elements.route.value.trim(),
      level: form.elements.level.value ? Number(form.elements.level.value) : null
    }));
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
            ofp_id: session.ofp_id
          }));
          applyBriefing(briefing, 'l’API SimBrief');
          return;
        } catch (error) {
          if (attempt === 5) return showMessage('#simbriefState', error.message, true);
          await new Promise(resolve => setTimeout(resolve, 1500));
        }
      }
    }, 500);
  } catch (error) {
    showMessage('#simbriefState', error.message, true);
  }
};

$('#planFile').onchange = async event => {
  const file = event.target.files[0];
  if (!file) return;
  await file.text();
  // The local plan is displayed for the pilot only. phpVMS receives the
  // normalized route/OFP fields, never an arbitrary local file payload.
  flightPlan = {};
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
  const body = Object.fromEntries([...new FormData(event.currentTarget)].filter(([, value]) => value !== ''));
  if (!body.aircraft_id) return showMessage('#pirepMessage', 'Sélectionnez un appareil.', true);
  if (body.block_fuel) body.block_fuel = Number(body.block_fuel);
  if (body.level) body.level = Number(body.level);
  if (body.alt_airport_id) body.alt_airport_id = body.alt_airport_id.toUpperCase();
  Object.assign(body, flightPlan || {}, { source_name: 'Hermes ACARS' });
  try {
    const operationRef = selectedOperation?.operation_id || selectedOperation?.id;
    const operationPirepBody = operationRef ? {
      route: body.route || flightPlan?.route || undefined,
      level: body.level ? Number(body.level) : (flightPlan?.level || undefined),
      block_fuel: body.block_fuel || flightPlan?.block_fuel || undefined,
      simbrief_source: flightPlan?.source === 'simbrief_account' ? 'simbrief_account' : (String(flightPlan?.source || '').toLowerCase().includes('simbrief') ? 'simbrief' : undefined)
    } : body;
    if (!operationRef) throw new Error('Impossible de pré-déposer le PIREP sans operation_id.');
    const result = unwrap(await call(`/api/v1/operations/${encodeURIComponent(operationRef)}/pirep`, operationPirepBody));
    pirepId = result.id || result.pirep_id || result.pirep?.id;
    if (!pirepId) throw new Error('Prométhée n’a pas retourné l’identifiant du PIREP.');
    showMessage('#pirepMessage', `PIREP ${pirepId} prêt. Vérification finale du Dispatch Prométhée…`);
    await refreshDispatch();
    updateWorkflow();
    document.querySelector('[data-tab="record"]').click();
  } catch (error) {
    showMessage('#pirepMessage', error.message, true);
  }
};

async function action(path, success) {
  try {
    await call(path, path === '/api/start' ? { pirepId, operationId: selectedOperation?.operation_id || selectedOperation?.id || null } : {});
    showMessage('#recordMessage', success);
  } catch (error) {
    showMessage('#recordMessage', error.message, true);
  }
}
$('#startBtn').onclick = async () => {
  try {
    await assertDispatchCanStart();
    await action('/api/start', 'Enregistrement démarré.');
  } catch (error) {
    showMessage('#pirepMessage', error.message, true);
  }
};
$('#pauseBtn').onclick = () => action('/api/pause', 'Enregistrement en pause.');
$('#resumeBtn').onclick = () => action('/api/resume', 'Enregistrement repris.');
$('#syncBtn').onclick = () => action('/api/sync', 'Données synchronisées.');
$('#fileBtn').onclick = () => action('/api/file', 'PIREP déposé.');

function drawMap(track) {
  const canvas = $('#flightMap');
  const context = canvas.getContext('2d');
  const width = canvas.width;
  const height = canvas.height;
  context.fillStyle = '#0d2740';
  context.fillRect(0, 0, width, height);
  if (!track?.length) {
    context.fillStyle = '#a9bfd2';
    context.font = '20px sans-serif';
    context.fillText('En attente de la télémétrie…', 28, 48);
    return;
  }
  const latitudes = track.map(point => point.lat ?? point.Lat);
  const longitudes = track.map(point => point.lon ?? point.Lon);
  const minLat = Math.min(...latitudes) - 0.03;
  const maxLat = Math.max(...latitudes) + 0.03;
  const minLon = Math.min(...longitudes) - 0.03;
  const maxLon = Math.max(...longitudes) + 0.03;
  const position = point => {
    const lat = point.lat ?? point.Lat;
    const lon = point.lon ?? point.Lon;
    return [30 + (lon - minLon) / (maxLon - minLon || 1) * (width - 60), height - 30 - (lat - minLat) / (maxLat - minLat || 1) * (height - 60)];
  };
  context.strokeStyle = '#54a4ed';
  context.lineWidth = 3;
  context.beginPath();
  track.forEach((point, index) => {
    const [x, y] = position(point);
    index ? context.lineTo(x, y) : context.moveTo(x, y);
  });
  context.stroke();
}

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
    const flight = status.flight;
    const latest = status.latest || {};
    const value = (camel, pascal) => latest[camel] ?? latest[pascal];
    if (status.connected && !connected) setAuthenticated(true);
    readiness.simulator = Boolean(status.latest);
    updateWorkflow();
    const simulators = (status.detectedSimulators || []).map(item => item.displayName || item.DisplayName).filter(Boolean);
    setText($('#simState'), simulators.length ? `${simulators.join(' · ')} — ${status.sim || 'connexion en attente'}` : status.sim || 'Simulateur non détecté');
    setText($('#pending'), String(status.pending ?? 0));
    setText($('#phase'), flight?.phase || flight?.Phase || '—');
    setText($('#distance'), flight ? `${Number(flight.distance ?? flight.Distance ?? 0).toFixed(1)} NM` : '—');
    setText($('#airborne'), flight ? `${Math.floor(Number(flight.airborneSeconds ?? flight.AirborneSeconds ?? 0) / 60)} min` : '—');
    setText($('#warning'), status.warning || '');
    setText($('#altitude'), value('altitude', 'Altitude') == null ? '—' : `${Math.round(value('altitude', 'Altitude'))} ft`);
    setText($('#groundSpeed'), value('gs', 'Gs') == null ? '—' : `${Math.round(value('gs', 'Gs'))} kt`);
    setText($('#fuel'), value('fuel', 'Fuel') == null ? '—' : `${Math.round(value('fuel', 'Fuel'))} lb`);
    updateRemotePolicy(status.remoteConfiguration || status.RemoteConfiguration);
    if (flight?.pirepId || flight?.PirepId) pirepId = flight.pirepId || flight.PirepId;
    drawMap(status.track || []);
    renderTimeline('#timeline', flight?.timeline || flight?.Timeline || []);
    renderTimeline('#journalEntries', flight?.journal || flight?.Journal || flight?.timeline || flight?.Timeline || []);
  } catch {}
}

updateWorkflow();
drawMap([]);
refreshStatus();
setInterval(refreshStatus, 1000);
call('/api/about').then(info => {
  setText($('#build'), 'Version ' + info.version);
  setText($('#loginServer'), info.server || 'Serveur inconnu');
  setText($('#loginEndpoint'), info.loginEndpoint ? 'POST ' + info.loginEndpoint : '');
  setText($('#loginServerSource'), info.serverSource ? 'Source : ' + info.serverSource : '');
}).catch(error => {
  setText($('#loginServer'), 'Impossible de lire la configuration Hermès');
  setText($('#loginEndpoint'), error.message || '');
});


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
    setText($('#updateMessage'), `Échec de la vérification : ${error.message}`);
  }
};
