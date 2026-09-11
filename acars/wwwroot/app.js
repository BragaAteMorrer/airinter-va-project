const $ = (s, root = document) => root.querySelector(s);
const $$ = (s, root = document) => [...root.querySelectorAll(s)];
const call = async (path, body) => {
  const res = await fetch(path, {
    method: body === undefined ? 'GET' : 'POST',
    headers: body === undefined ? {} : {'content-type':'application/json'},
    body: body === undefined ? undefined : JSON.stringify(body)
  });
  if (!res.ok) throw new Error(await res.text());
  return res.headers.get('content-type')?.includes('json') ? res.json() : {};
};
const show = (node, value) => node.textContent = typeof value === 'string' ? value : JSON.stringify(value, null, 2);

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
    };
    $('#flightList').append(card);
  });
  if (!rows.length) show($('#flightList'), data);
}

$('#prefileForm').onsubmit = async e => {
  e.preventDefault();
  const raw = Object.fromEntries(new FormData(e.currentTarget));
  const body = Object.fromEntries(Object.entries(raw).filter(([,v]) => v !== ''));
  if (body.block_fuel) body.block_fuel = Number(body.block_fuel);
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
async function action(path, body) {
  try { show($('#recordBox'), await call(path, body)); }
  catch (err) { show($('#recordBox'), String(err.message || err)); }
}

setInterval(async () => {
  try {
    const s = await call('/api/status');
    $('#serverState').textContent = s.connected ? s.server : 'Déconnecté';
    $('#simState').textContent = s.sim;
    $('#pending').textContent = s.pending ?? 0;
    $('#phase').textContent = s.flight?.phase || '-';
    if (s.latest) {
      $('#pos').textContent = `${s.latest.lat.toFixed(4)}, ${s.latest.lon.toFixed(4)}`;
      $('#ias').textContent = `${Math.round(s.latest.ias)} kt`;
      $('#agl').textContent = `${Math.round(s.latest.agl)} ft`;
    }
    if (s.flight?.pirepId) $('#pirepId').value = s.flight.pirepId;
  } catch {}
}, 1000);
