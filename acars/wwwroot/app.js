const $ = s => document.querySelector(s);
const call = (path, body) => new Promise((resolve, reject) => {
  const id = crypto.randomUUID();
  const onMessage = e => { if (e.data.id !== id) return; chrome.webview.removeEventListener('message', onMessage); e.data.ok ? resolve(e.data.data) : reject(new Error(e.data.data)); };
  chrome.webview.addEventListener('message', onMessage); chrome.webview.postMessage({id, path, body});
});
const text = (node, value) => { node.textContent = value; };
const message = (selector, value, error = false) => { const el = $(selector); el.hidden = !value; el.classList.toggle('error', error); text(el, value || ''); };
const dataOf = value => value?.data ?? value;
let selectedOperation, pirepId;
const defaultSettings = { autoDetection: 'true', forcedSimulator: '', timeFormat: 'local', notifications: 'true' };
let savedSettings = {}; try { savedSettings = JSON.parse(localStorage.prometheeAcarsSettings || '{}'); } catch {}
let localSettings = { ...defaultSettings, ...savedSettings };

const era = $('#era');
document.body.dataset.era = localStorage.prometheeEra || 'modern'; era.value = document.body.dataset.era;
era.onchange = () => { document.body.dataset.era = era.value; localStorage.prometheeEra = era.value; };
const settingsForm = $('#settingsForm');
Object.entries(localSettings).forEach(([key, value]) => { if (settingsForm.elements[key]) settingsForm.elements[key].value = value; });
settingsForm.onsubmit = e => {
  e.preventDefault(); localSettings = { ...defaultSettings, ...Object.fromEntries(new FormData(settingsForm)) };
  localStorage.prometheeAcarsSettings = JSON.stringify(localSettings);
  message('#settingsMessage', localSettings.forcedSimulator && localSettings.forcedSimulator !== 'msfs'
    ? 'Réglage conservé pour diagnostic : ce connecteur n’est pas encore implémenté.' : 'Réglages locaux enregistrés.');
};
document.querySelectorAll('.tab').forEach(button => button.onclick = () => {
  document.querySelectorAll('.tab,.panel').forEach(el => el.classList.remove('active'));
  button.classList.add('active'); $('#' + button.dataset.tab).classList.add('active');
});

function pilotIdentity(value) {
  const user = dataOf(value)?.user ?? dataOf(value) ?? {};
  const first = user.first_name || user.firstname || user.firstName || '';
  const last = user.last_name || user.lastname || user.lastName || '';
  const name = [first, last].filter(Boolean).join(' ') || user.name || user.name_private || '';
  const callsign = user.ident || user.pilot_id || user.pilotId || '';
  text($('#serverState'), name && callsign ? `${name} · ${callsign}` : name || callsign || 'Pilote connecté');
}
async function login(form, advanced = false) {
  const body = Object.fromEntries(new FormData(form));
  try { pilotIdentity(await call(advanced ? '/api/config' : '/api/login', body)); message('#loginMessage', 'Connexion réussie.'); }
  catch (err) { message('#loginMessage', err.message || 'Impossible de se connecter au serveur Prométhée.', true); }
}
$('#loginForm').onsubmit = e => { e.preventDefault(); login(e.currentTarget); };
$('#configForm').onsubmit = e => { e.preventDefault(); login(e.currentTarget, true); };

$('#simbriefBtn').onclick = async () => {
  const form = $('#prefileForm');
  const flightId = form.flight_id.value;
  const aircraftId = form.aircraft_id.value;
  if (!flightId || !aircraftId) {
    show($('#simbriefState'), 'Sélectionnez d’abord un vol et un appareil autorisé.');
    return;
  }

  try {
    show($('#simbriefState'), 'Préparation de la demande SimBrief…');
    const session = await call(`/api/flights/${encodeURIComponent(flightId)}/simbrief/session`, {aircraft_id: aircraftId});
    const popup = window.open('about:blank', 'PrometheeSimBrief', 'width=760,height=640');
    if (!popup) throw new Error('Autorisez les fenêtres contextuelles pour ouvrir SimBrief.');

    const dispatch = document.createElement('form');
    dispatch.method = 'GET';
    dispatch.action = session.worker_url;
    dispatch.target = 'PrometheeSimBrief';
    Object.entries(session.parameters).forEach(([name, value]) => {
      if (value === null || value === undefined || value === '') return;
      const input = document.createElement('input');
      input.type = 'hidden'; input.name = name; input.value = value;
      dispatch.append(input);
    });
    document.body.append(dispatch);
    dispatch.submit();
    dispatch.remove();
    show($('#simbriefState'), 'Connectez-vous à SimBrief, générez l’OFP puis fermez la fenêtre.');

    const waitForClose = setInterval(async () => {
      if (!popup.closed) return;
      clearInterval(waitForClose);
      show($('#simbriefState'), 'Import de l’OFP dans Prométhée…');
      for (let attempt = 0; attempt < 5; attempt += 1) {
        try {
          const briefing = await call(`/api/flights/${encodeURIComponent(flightId)}/simbrief/import`, {
            aircraft_id: aircraftId, ofp_id: session.ofp_id
          });
          flightPlan = {name: 'OFP SimBrief', prefile: {
            simbrief_id: briefing.id,
            route: briefing.route,
            level: Number(briefing.initial_altitude) || undefined,
            block_fuel: briefing.block_fuel || undefined
          }};
          show($('#planBox'), briefing);
          show($('#simbriefState'), 'OFP importé et prêt à être rattaché au PIREP.');
          return;
        } catch (err) {
          if (attempt === 4) {
            show($('#simbriefState'), `OFP introuvable : ${err.message || err}`);
            return;
          }
          await new Promise(resolve => setTimeout(resolve, 1500));
        }
      }
    }, 500);
  } catch (err) {
    show($('#simbriefState'), String(err.message || err));
  }
};

$('#simbriefBtn').onclick = async () => {
  const form = $('#prefileForm');
  const flightId = form.flight_id.value;
  const aircraftId = form.aircraft_id.value;
  if (!flightId || !aircraftId) {
    show($('#simbriefState'), 'Sélectionnez d’abord un vol et un appareil autorisé.');
    return;
  }

  try {
    show($('#simbriefState'), 'Préparation de la demande SimBrief…');
    const session = await call(`/api/flights/${encodeURIComponent(flightId)}/simbrief/session`, {aircraft_id: aircraftId});
    const popup = window.open('about:blank', 'PrometheeSimBrief', 'width=760,height=640');
    if (!popup) throw new Error('Autorisez les fenêtres contextuelles pour ouvrir SimBrief.');

    const dispatch = document.createElement('form');
    dispatch.method = 'GET';
    dispatch.action = session.worker_url;
    dispatch.target = 'PrometheeSimBrief';
    Object.entries(session.parameters).forEach(([name, value]) => {
      if (value === null || value === undefined || value === '') return;
      const input = document.createElement('input');
      input.type = 'hidden'; input.name = name; input.value = value;
      dispatch.append(input);
    });
    document.body.append(dispatch);
    dispatch.submit();
    dispatch.remove();
    show($('#simbriefState'), 'Connectez-vous à SimBrief, générez l’OFP puis fermez la fenêtre.');

    const waitForClose = setInterval(async () => {
      if (!popup.closed) return;
      clearInterval(waitForClose);
      show($('#simbriefState'), 'Import de l’OFP dans Prométhée…');
      for (let attempt = 0; attempt < 5; attempt += 1) {
        try {
          const briefing = await call(`/api/flights/${encodeURIComponent(flightId)}/simbrief/import`, {
            aircraft_id: aircraftId, ofp_id: session.ofp_id
          });
          flightPlan = {name: 'OFP SimBrief', prefile: {
            simbrief_id: briefing.id,
            route: briefing.route,
            level: Number(briefing.initial_altitude) || undefined,
            block_fuel: briefing.block_fuel || undefined
          }};
          show($('#planBox'), briefing);
          show($('#simbriefState'), 'OFP importé et prêt à être rattaché au PIREP.');
          return;
        } catch (err) {
          if (attempt === 4) {
            show($('#simbriefState'), `OFP introuvable : ${err.message || err}`);
            return;
          }
          await new Promise(resolve => setTimeout(resolve, 1500));
        }
      }
    }, 500);
  } catch (err) {
    show($('#simbriefState'), String(err.message || err));
  }
};

$('#prefileForm').onsubmit = async e => {
  e.preventDefault();
  const raw = Object.fromEntries(new FormData(e.currentTarget));
  const body = Object.fromEntries(Object.entries(raw).filter(([,v]) => v !== ''));
  if (body.block_fuel) body.block_fuel = Number(body.block_fuel);
  body.source_name = 'Promethee ACARS';
  if (flightPlan) Object.assign(body, flightPlan.prefile);
  try {
    const res = await call('/api/prefile', body);
    const id = res.id || res.pirep_id || res?.pirep?.id || res?.data?.id;
    if (id) $('#pirepId').value = id;
    show($('#pirepBox'), res);
  } catch (err) { show($('#pirepBox'), String(err.message || err)); }
};

$('#startBtn').onclick = async () => action('/api/start', {pirepId: $('#pirepId').value});
$('#pauseBtn').onclick = async () => action('/api/pause', {});
$('#resumeBtn').onclick = async () => action('/api/resume', {});
$('#syncBtn').onclick = async () => action('/api/sync', {});
$('#fileBtn').onclick = async () => action('/api/file', {});
$('#reportBtn').onclick = async () => action('/api/report', {});
async function action(path, body) {
  try { show($('#recordBox'), await call(path, body)); }
  catch (err) { show($('#recordBox'), String(err.message || err)); }
}
function renderOperations(value) {
  const operations = dataOf(value)?.operations || [];
  const list = $('#flightList'); list.replaceChildren();
  if (!operations.length) { const empty = document.createElement('p'); empty.className = 'empty'; text(empty, 'Aucune réservation active pour ce pilote.'); list.append(empty); return; }
  operations.forEach(op => list.append(operationCard(op)));
}
async function refreshOperations() {
  try { renderOperations(await call('/api/operations?simulator=' + encodeURIComponent($('#simulator').value))); }
  catch (err) { const list = $('#flightList'); list.replaceChildren(); const p = document.createElement('p'); p.className='empty'; text(p, err.message); list.append(p); }
}
$('#operationsBtn').onclick = refreshOperations;
async function selectOperation(op) {
  selectedOperation = op; const f = op.flight || {}, a = op.aircraft || {}, form = $('#prefileForm');
  text($('#pirepMessage'), '');
  field(form, 'flight_id', f.id); field(form, 'airline_id', f.airline_id); field(form, 'flight_number', f.flight_number); field(form, 'dpt_airport_id', f.departure); field(form, 'arr_airport_id', f.arrival);
  text($('#selectedFlight'), `${f.ident || 'Vol réservé'} — ${f.departure || '?'} → ${f.arrival || '?'} · ${a.registration || a.subfleet || 'Avion à confirmer'}`);
  const sb = op.simbrief || {};
  text($('#operationBrief'), sb.available ? `SimBrief : ${sb.type || 'type non renseigné'} · OFP disponible` : `SimBrief : ${sb.type || 'type non renseigné'} · aucun OFP disponible`);
  const picker=$('#aircraftPicker'), pickerLabel=$('#aircraftPickerLabel'); picker.replaceChildren();
  const addOption = aircraft => { const option=document.createElement('option'); option.value=aircraft.id || ''; text(option, aircraft.registration || aircraft.name || aircraft.icao || 'Avion sans immatriculation'); picker.append(option); };
  if (a.id) { addOption(a); pickerLabel.hidden=false; }
  else if (f.id) {
    pickerLabel.hidden=false; const placeholder=document.createElement('option'); placeholder.value=''; text(placeholder,'Chargement des appareils disponibles…'); picker.append(placeholder);
    try {
      const aircraftPath = op.bid_id
        ? '/api/operations/' + encodeURIComponent(op.bid_id) + '/aircraft'
        : '/api/flights/' + encodeURIComponent(f.id) + '/aircraft';
      let response;
      try { response = await call(aircraftPath); }
      catch (primaryError) {
        // Transitional fallback for a Prométhée server not yet exposing the
        // reservation-scoped ACARS route. The phpVMS route enforces the same
        // grade and availability restrictions.
        if (!op.bid_id) throw primaryError;
        response = await call('/api/flights/' + encodeURIComponent(f.id) + '/aircraft');
      }
      const payload=dataOf(response);
      const aircraft=Array.isArray(payload) ? payload : (payload?.data || payload?.aircraft || []); picker.replaceChildren();
      const choose=document.createElement('option'); choose.value=''; text(choose, aircraft.length ? 'Sélectionnez un avion' : 'Aucun avion disponible'); picker.append(choose); aircraft.forEach(addOption);
      if (!aircraft.length) text($('#pirepMessage'),'Aucun avion disponible pour ce vol : contactez les opérations.');
    } catch { text($('#pirepMessage'),'Impossible de récupérer les avions autorisés pour ce vol.'); }
  } else { pickerLabel.hidden=true; }
  form.hidden = false; document.querySelector('[data-tab="flight"]').click();
}
$('#prefileForm').onsubmit = async e => {
  e.preventDefault(); const body = Object.fromEntries(new FormData(e.currentTarget));
  if (!body.aircraft_id) { text($('#pirepMessage'),'Sélectionnez un avion avant de préparer le PIREP.'); return; }
  const f = selectedOperation?.flight || {}; Object.assign(body, f.alternate && {alt_airport_id:f.alternate}, f.route && {route:f.route}, f.level && {level:Number(f.level)}, {source_name:'Promethee ACARS'});
  try { const result = dataOf(await call('/api/prefile', body)); pirepId = result.id || result.pirep_id || result.pirep?.id; text($('#pirepMessage'), `PIREP prêt${pirepId ? ' : ' + pirepId : ''}. Vous pouvez démarrer l’enregistrement.`); }
  catch (err) { text($('#pirepMessage'), err.message); }
};
async function action(path, success) {
  try { await call(path, path === '/api/start' ? {pirepId} : {}); message('#recordMessage', success); } catch (err) { message('#recordMessage', err.message, true); }
}
$('#startBtn').onclick = () => action('/api/start', 'Enregistrement démarré.');
$('#pauseBtn').onclick = () => action('/api/pause', 'Enregistrement en pause.');
$('#resumeBtn').onclick = () => action('/api/resume', 'Enregistrement repris.');
$('#syncBtn').onclick = () => action('/api/sync', 'Données synchronisées.');
$('#fileBtn').onclick = () => action('/api/file', 'PIREP déposé.');

function drawMap(track) {
  const canvas = $('#flightMap'), ctx = canvas.getContext('2d'), w = canvas.width, h = canvas.height;
  ctx.fillStyle = '#0d2740'; ctx.fillRect(0, 0, w, h);
  if (!track?.length) { ctx.fillStyle='#a9bfd2'; ctx.font='20px sans-serif'; ctx.fillText('En attente de la télémétrie…', 28, 48); return; }
  const lat=track.map(p=>p.lat), lon=track.map(p=>p.lon), pad=.03, minLat=Math.min(...lat)-pad, maxLat=Math.max(...lat)+pad, minLon=Math.min(...lon)-pad, maxLon=Math.max(...lon)+pad;
  const point = p => [30+(p.lon-minLon)/(maxLon-minLon||1)*(w-60),h-30-(p.lat-minLat)/(maxLat-minLat||1)*(h-60)];
  ctx.strokeStyle='#54a4ed'; ctx.lineWidth=3; ctx.beginPath(); track.forEach((p,i)=>{const [x,y]=point(p);i?ctx.lineTo(x,y):ctx.moveTo(x,y);});ctx.stroke();
}
function timeline(flight) {
  const el=$('#timeline'); el.replaceChildren(); const phases=flight?.timeline || [];
  if (!phases.length) { const p=document.createElement('p');p.className='empty';text(p,'En attente d’un vol.');el.append(p);return; }
  phases.forEach(item=>{const p=document.createElement('span');text(p,`${new Date(item.occurredAt).toLocaleTimeString('fr-FR',{timeZone:localSettings.timeFormat === 'utc' ? 'UTC' : undefined})} — ${item.name}`);el.append(p);});
}
function journal(flight) {
  const el=$('#journalEntries'); el.replaceChildren(); const entries=flight?.journal || flight?.timeline || [];
  if (!entries.length) { const p=document.createElement('p');p.className='empty';text(p,'En attente d’un vol.');el.append(p);return; }
  entries.forEach(item=>{const p=document.createElement('span');const value=item.value == null ? '' : ` · ${Number(item.value).toFixed(0)}`;text(p,`${new Date(item.occurredAt).toLocaleTimeString('fr-FR',{timeZone:localSettings.timeFormat === 'utc' ? 'UTC' : undefined})} — ${item.name}${value}`);el.append(p);});
}
function updateRemotePolicy(configuration) {
  const el=$('#remotePolicy'); if (!configuration) { el.hidden=true; return; }
  const interval=configuration.positionIntervalSeconds ?? configuration.PositionIntervalSeconds;
  const minimum=configuration.minimumVersion ?? configuration.MinimumVersion;
  text(el, 'Politique Prométhée : position live toutes les ' + interval + ' s' + (minimum ? ' · version minimale ' + minimum : '') + '.');
  el.hidden=false;
}
setInterval(async () => { try {
  const s = await call('/api/status');
  if (!s.connected) text($('#serverState'), 'Identité pilote à venir');
  const detected=(s.detectedSimulators || []).map(x => x.displayName || x.DisplayName).filter(Boolean);
  text($('#simState'), detected.length ? `${detected.join(' · ')} — ${s.sim || 'connexion en attente'}` : (s.sim || 'Simulateur non détecté')); text($('#pending'), String(s.pending ?? 0)); text($('#phase'), s.flight?.phase || '—');
  text($('#distance'), s.flight ? `${s.flight.distance.toFixed(1)} NM` : '—'); text($('#airborne'), s.flight ? `${Math.floor(s.flight.airborneSeconds/60)} min` : '—'); text($('#warning'), s.warning || '');
  const latest=s.latest || {}; const number=(key, fallback) => latest[key] ?? latest[key[0].toUpperCase()+key.slice(1)] ?? fallback;
  text($('#altitude'), number('altitude', null) == null ? '—' : `${Math.round(number('altitude'))} ft`);
  text($('#groundSpeed'), number('gs', null) == null ? '—' : `${Math.round(number('gs'))} kt`);
  text($('#fuel'), number('fuel', null) == null ? '—' : `${Math.round(number('fuel'))} lb`);
  updateRemotePolicy(s.remoteConfiguration || s.RemoteConfiguration);
  if (s.flight?.pirepId) pirepId=s.flight.pirepId; drawMap(s.track); timeline(s.flight); journal(s.flight);
} catch {} }, 1000);
call('/api/about').then(info => text($('#build'), 'Version ' + info.version)).catch(() => {});
