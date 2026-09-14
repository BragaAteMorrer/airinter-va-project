const $ = (s, root = document) => root.querySelector(s);
const $$ = (s, root = document) => [...root.querySelectorAll(s)];
const nativeCall = (path, body) => new Promise((resolve, reject) => {
  const id = crypto.randomUUID();
  const onMessage = event => { const message = event.data; if (message.id !== id) return; window.chrome.webview.removeEventListener('message', onMessage); message.ok ? resolve(message.data) : reject(new Error(message.data)); };
  window.chrome.webview.addEventListener('message', onMessage); window.chrome.webview.postMessage({id, path, body});
});
const call = async (path, body) => {
  if (window.chrome?.webview) return nativeCall(path, body);
  const res = await fetch(path, {
    method: body === undefined ? 'GET' : 'POST',
    headers: body === undefined ? {} : {'content-type':'application/json'},
    body: body === undefined ? undefined : JSON.stringify(body)
  });
  if (!res.ok) throw new Error(await res.text());
  return res.headers.get('content-type')?.includes('json') ? res.json() : {};
};
const show = (node, value) => node.textContent = typeof value === 'string' ? value : JSON.stringify(value, null, 2);
let flightPlan = null;
let latestStatus = null;
const download = (name, value) => {
  const url = URL.createObjectURL(new Blob([JSON.stringify(value, null, 2)], {type:'application/json'}));
  const a = document.createElement('a'); a.href = url; a.download = name; a.click();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
};

const era = $('#era');
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
let minitelRevealTimer;
let minitelBootTimer;
const clearMinitelBoot = () => {
  clearTimeout(minitelBootTimer);
  $('#minitel-screen')?.remove();
};
const bootMinitel = () => {
  clearMinitelBoot();
  if (reduceMotion || document.body.dataset.era !== 'minitel') return;
  const screen = document.createElement('pre');
  screen.id = 'minitel-screen';
  screen.setAttribute('aria-hidden', 'true');
  document.body.append(screen);
  const lines = [
    '3615 AIR INTER',
    '----------------------------------------',
    'PROMETHEE ACARS',
    '',
    'LIAISON SIMULATEUR .......... PRET',
    'LIAISON PHPVMS .............. PRET',
    '',
    'CHARGEMENT DU TERMINAL',
    'PATIENTEZ _'
  ];
  let index = 0;
  const writeLine = () => {
    if (document.body.dataset.era !== 'minitel' || !screen.isConnected) return;
    screen.textContent += `${lines[index]}\n`;
    index += 1;
    if (index < lines.length) {
      minitelBootTimer = setTimeout(writeLine, 80);
      return;
    }
    minitelBootTimer = setTimeout(() => {
      screen.classList.add('is-complete');
      minitelBootTimer = setTimeout(() => screen.remove(), 100);
    }, 220);
  };
  writeLine();
};
const revealMinitel = () => {
  document.body.classList.remove('minitel-enter');
  if (reduceMotion || document.body.dataset.era !== 'minitel') return;
  [...document.querySelectorAll('header > *, .panel.active > *')]
    .forEach((line, index) => line.style.setProperty('--minitel-line', String(Math.min(index, 14))));
  requestAnimationFrame(() => requestAnimationFrame(() => document.body.classList.add('minitel-enter')));
  clearTimeout(minitelRevealTimer);
  minitelRevealTimer = setTimeout(() => document.body.classList.remove('minitel-enter'), 760);
};
document.body.dataset.era = localStorage.prometheeEra || 'modern';
era.value = document.body.dataset.era;
bootMinitel();
revealMinitel();
era.onchange = () => { document.body.dataset.era = era.value; localStorage.prometheeEra = era.value; bootMinitel(); revealMinitel(); };

$$('.tab').forEach(btn => btn.onclick = () => {
  $$('.tab,.panel').forEach(x => x.classList.remove('active'));
  btn.classList.add('active');
  $('#' + btn.dataset.tab).classList.add('active');
  revealMinitel();
});

$('#configForm').onsubmit = async e => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.currentTarget));
  try { show($('#userBox'), await call('/api/config', data)); }
  catch (err) { show($('#userBox'), String(err.message || err)); }
};

$('#loginForm').onsubmit = async e => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.currentTarget));
  try { show($('#userBox'), await call('/api/login', data)); }
  catch (err) { show($('#userBox'), String(err.message || err)); }
};

$('#searchBtn').onclick = async () => {
  try { renderFlights(await call('/api/flights?search=' + encodeURIComponent($('#flightSearch').value))); }
  catch (err) { show($('#flightList'), String(err.message || err)); }
};
$('#bidsBtn').onclick = async () => {
  try { show($('#flightList'), await call('/api/bids')); }
  catch (err) { show($('#flightList'), String(err.message || err)); }
};

function renderFlights(data) {
  const rows = Array.isArray(data) ? data : (data.data || data.flights || []);
  $('#flightList').innerHTML = '';
  rows.slice(0, 30).forEach(f => {
    const card = document.createElement('button');
    card.className = 'flight';
    card.type = 'button';
    card.innerHTML = `<strong>${f.ident || `${f.airline_id || ''}${f.flight_number || ''}`}</strong><span>${f.dpt_airport_id || '?'} -> ${f.arr_airport_id || '?'}</span>`;
    card.onclick = () => {
      const form = $('#prefileForm');
      form.flight_id.value = f.id || '';
      form.airline_id.value = f.airline_id || '';
      form.flight_number.value = f.flight_number || '';
      form.dpt_airport_id.value = f.dpt_airport_id || '';
      form.arr_airport_id.value = f.arr_airport_id || '';
      loadAircraft(f.id);
    };
    $('#flightList').append(card);
  });
  if (!rows.length) show($('#flightList'), data);
}

async function loadAircraft(flightId) {
  const select = $('#aircraftId');
  select.innerHTML = '<option>Chargement des avions…</option>';
  try {
    const data = await call(`/api/flights/${encodeURIComponent(flightId)}/aircraft`);
    const aircraft = Array.isArray(data) ? data : (data.data || data.aircraft || []);
    select.innerHTML = '<option value="">Choisir un avion</option>';
    aircraft.forEach(a => {
      const option = document.createElement('option'); option.value = a.id;
      option.textContent = a.registration ? `${a.registration} — ${a.name || a.subfleet || 'Avion'}` : (a.name || a.ident || a.id);
      select.append(option);
    });
    if (aircraft.length === 1) select.value = aircraft[0].id;
  } catch (err) { select.innerHTML = '<option value="">Avions indisponibles</option>'; show($('#pirepBox'), String(err.message || err)); }
}

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

$('#planFile').onchange = async e => {
  const file = e.target.files[0]; if (!file) return;
  try { flightPlan = parseFlightPlan(await file.text());
    const form = $('#prefileForm'); const p = flightPlan.prefile;
    if (p.dpt_airport_id) form.dpt_airport_id.value = p.dpt_airport_id;
    if (p.arr_airport_id) form.arr_airport_id.value = p.arr_airport_id;
    show($('#planBox'), flightPlan); }
  catch (err) { flightPlan = null; show($('#planBox'), `Plan non reconnu : ${err.message || err}`); }
};
$('#clearPlanBtn').onclick = () => { flightPlan = null; $('#planFile').value = ''; show($('#planBox'), 'Aucun plan chargé.'); };
function parseFlightPlan(xml) {
  const doc = new DOMParser().parseFromString(xml, 'application/xml');
  if (doc.querySelector('parsererror')) throw new Error('XML invalide');
  const read = (...selectors) => selectors.map(s => doc.querySelector(s)?.textContent?.trim()).find(Boolean) || '';
  const attr = (...selectors) => selectors.map(s => doc.querySelector(s)?.getAttribute('id') || doc.querySelector(s)?.getAttribute('icao')).find(Boolean) || '';
  const origin = read('origin icao_code', 'origin') || attr('ATCWaypoint[id]');
  const destination = read('destination icao_code', 'destination') || '';
  const alternate = read('alternate icao_code', 'alternate');
  const route = read('general route', 'route') || [...doc.querySelectorAll('ATCWaypoint')].map(x => x.getAttribute('id')).filter(Boolean).join(' ');
  const cruise = read('general initial_altitude', 'CruisingAlt', 'altitude');
  return { name: 'Plan chargé', origin, destination, alternate, route, cruise, prefile: {
    ...(origin && {dpt_airport_id: origin}), ...(destination && {arr_airport_id: destination}),
    ...(alternate && {alt_airport_id: alternate}), ...(route && {route}), ...(cruise && {level: Number(cruise) || undefined})
  }};
}

$('#historyBtn').onclick = async () => { try { show($('#historyBox'), await call('/api/history')); } catch (err) { show($('#historyBox'), String(err)); } };
call('/api/rules').then(r => { $('#rulesForm').taxiSpeed.value = r.taxiSpeed; $('#rulesForm').hardLandingRate.value = r.hardLandingRate; }).catch(() => {});
$('#rulesForm').onsubmit = async e => { e.preventDefault(); const v = Object.fromEntries(new FormData(e.currentTarget)); try { show($('#historyBox'), await call('/api/rules', {taxiSpeed:Number(v.taxiSpeed), hardLandingRate:Number(v.hardLandingRate)})); } catch (err) { show($('#historyBox'), String(err)); } };
$('#diagnosticBtn').onclick = async () => { try { download(`promethee-acars-diagnostic-${Date.now()}.json`, await call('/api/diagnostics')); } catch (err) { show($('#historyBox'), String(err)); } };
$('#exportProfileBtn').onclick = () => download('promethee-acars-profile.json', {server: latestStatus?.server || $('#loginForm [name=server]').value, exportedAt: new Date().toISOString()});
$('#profileFile').onchange = async e => {
  try { const profile = JSON.parse(await e.target.files[0].text()); if (!profile.server) throw new Error('Serveur absent');
    $$('#loginForm [name=server], #configForm [name=server]').forEach(x => x.value = profile.server); show($('#historyBox'), 'Profil importé.');
  } catch (err) { show($('#historyBox'), `Profil invalide : ${err.message || err}`); }
};

function drawMap(track) {
  const canvas = $('#flightMap'); const ctx = canvas.getContext('2d'); const w = canvas.width, h = canvas.height;
  ctx.fillStyle = '#071321'; ctx.fillRect(0,0,w,h);
  if (!track?.length) { ctx.fillStyle = '#9fb0c3'; ctx.font = '20px sans-serif'; ctx.fillText('En attente de la télémétrie…', 28, 48); return; }
  const lat = track.map(p => p.lat), lon = track.map(p => p.lon); const pad = .03;
  const minLat = Math.min(...lat)-pad, maxLat = Math.max(...lat)+pad, minLon = Math.min(...lon)-pad, maxLon = Math.max(...lon)+pad;
  const point = p => [30 + (p.lon-minLon)/(maxLon-minLon || 1)*(w-60), h-30-(p.lat-minLat)/(maxLat-minLat || 1)*(h-60)];
  ctx.strokeStyle = '#59a3ff'; ctx.lineWidth=3; ctx.beginPath(); track.forEach((p,i) => { const [x,y]=point(p); i ? ctx.lineTo(x,y) : ctx.moveTo(x,y); }); ctx.stroke();
  const [x,y] = point(track.at(-1)); ctx.fillStyle='#ff4d55'; ctx.beginPath(); ctx.arc(x,y,7,0,Math.PI*2); ctx.fill();
}
function renderTimeline(flight) {
  const el = $('#timeline'); const phases = flight?.timeline || []; const issues = flight?.issues || [];
  el.innerHTML = `<strong>${flight?.phase || 'Aucune phase active'}</strong>` +
    phases.map(x => `<span>${new Date(x.occurredAt).toLocaleTimeString('fr-FR')} — ${x.name}</span>`).join('') +
    issues.map(x => `<span>${new Date(x.occurredAt).toLocaleTimeString('fr-FR')} — ⚠ ${x.message}</span>`).join('');
}

setInterval(async () => {
  try {
    const s = await call('/api/status');
    latestStatus = s;
    $('#serverState').textContent = s.connected ? s.server : 'Déconnecté';
    $('#simState').textContent = s.sim;
    $('#pending').textContent = s.pending ?? 0;
    $('#phase').textContent = s.flight?.phase || '-';
    $('#warning').textContent = s.warning || '';
    if (s.latest) {
      $('#pos').textContent = `${s.latest.lat.toFixed(4)}, ${s.latest.lon.toFixed(4)}`;
      $('#ias').textContent = `${Math.round(s.latest.ias)} kt`;
      $('#gs').textContent = `${Math.round(s.latest.gs)} kt`;
      $('#agl').textContent = `${Math.round(s.latest.agl)} ft`;
      $('#fuel').textContent = `${Math.round(s.latest.fuel).toLocaleString('fr-FR')} lb`;
    }
    $('#distance').textContent = s.flight ? `${s.flight.distance.toFixed(1)} NM` : '-';
    $('#airborne').textContent = s.flight ? `${Math.floor(s.flight.airborneSeconds / 60)} min` : '-';
    if (s.flight?.pirepId) $('#pirepId').value = s.flight.pirepId;
    drawMap(s.track); renderTimeline(s.flight);
  } catch {}
}, 1000);
