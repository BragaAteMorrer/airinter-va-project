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
document.body.dataset.era = localStorage.prometheeEra || 'modern';
era.value = document.body.dataset.era;
era.onchange = () => { document.body.dataset.era = era.value; localStorage.prometheeEra = era.value; };

$$('.tab').forEach(btn => btn.onclick = () => {
  $$('.tab,.panel').forEach(x => x.classList.remove('active'));
  btn.classList.add('active');
  $('#' + btn.dataset.tab).classList.add('active');
});

$('#configForm').onsubmit = async e => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.currentTarget));
  try { show($('#userBox'), await call('/api/config', data)); }
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
$('#maxIas').onchange = async () => action('/api/max-ias', {maxIas: Number($('#maxIas').value) || null});
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
    if (s.latest) {
      $('#pos').textContent = `${s.latest.lat.toFixed(4)}, ${s.latest.lon.toFixed(4)}`;
      $('#ias').textContent = `${Math.round(s.latest.ias)} kt`;
      $('#agl').textContent = `${Math.round(s.latest.agl)} ft`;
    }
    if (s.flight?.pirepId) $('#pirepId').value = s.flight.pirepId;
  } catch {}
}, 1000);
