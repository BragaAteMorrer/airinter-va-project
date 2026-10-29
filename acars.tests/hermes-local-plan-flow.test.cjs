const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '../acars/wwwroot');
const html = fs.readFileSync(path.join(root, 'index.html'), 'utf8');
const importer = fs.readFileSync(path.join(root, 'hermes-local-plan-ui.js'), 'utf8');
const planning = fs.readFileSync(path.join(root, 'hermes-planning.js'), 'utf8');

function element(value = '') {
  return {
    value, hidden: false, disabled: false, textContent: '', files: [],
    listeners: {},
    addEventListener(event, callback) { this.listeners[event] = callback; },
    change(value) { this.value = value; this.listeners.input?.(); }
  };
}
const fileInput = element(), clear = element(), confirm = element(), preview = element();
confirm.hidden = true;
const route = element('DCT FROM PROGRAMME'), level = element('');
const form = { elements: {
  aircraft_id: element('F-GABC'), alt_airport_id: element(''), route, level
} };
const reset = element();
const selectors = {
  '#planFile': fileInput,
  '#clearPlanBtn': clear,
  '#confirmLocalPlanBtn': confirm,
  '#planBox': preview,
  '#prefileForm': form,
  '#resetDraftBtn': reset
};
const messages = [];
let currentPlanMode = 'file';
const context = {
  window: {},
  document: { querySelector: selector => selectors[selector] || null },
  selectedOperation: {
    operation_id: 'op_1',
    flight: { departure: 'LFPO', arrival: 'EIDW' },
    simbrief: { available: false }
  },
  flightPlan: null,
  normalizeFlight: flight => flight,
  updateWorkflow() {},
  renderNetworkPrefiles() {},
  scheduleEfbContextSync() {},
  showMessage(where, message, error) { messages.push({ where, message, error }); },
  recommendedPlanMode() { return 'account'; },
  setPlanMode(mode) { currentPlanMode = mode; }
};
context.window.HermesLocalPlan = {
  parse(raw) { return { departure: 'LFPO', arrival: 'EIDW', route: raw, waypointCount: 3, format: 'FMS' }; },
  verifyAirports(plan, flight) {
    if (plan.departure !== flight.departure || plan.arrival !== flight.arrival) throw Error('Wrong flight');
    return plan;
  }
};
context.form = form;
vm.runInNewContext(importer, context);
const planningContext = { window: context.window, form };
vm.runInNewContext(planning, planningContext);
const ui = context.window.HermesLocalPlanUi;
assert.ok(ui?.assertReady);

async function test() {
  assert.equal(confirm.hidden, true); // initial UI controlled by HTML "hidden" in real DOM
  fileInput.files = [{
    name: 'LFPOEIDW.fms', size: 200,
    async text() { return 'LGL DCT DUB'; }
  }];
  await fileInput.onchange({ target: fileInput });
  assert.equal(route.value, 'LGL DCT DUB');
  assert.equal(preview.hidden, false);
  assert.equal(confirm.hidden, false);
  assert.equal(confirm.disabled, false);
  assert.throws(() => ui.assertReady(), /Valider cette route/);
  assert.throws(() => vm.runInNewContext('simBriefPlanningPayload(form)', planningContext), /Valider cette route/);
  confirm.onclick();
  assert.equal(currentPlanMode, 'account');
  assert.equal(ui.assertReady(), true);
  const payload = vm.runInNewContext('simBriefPlanningPayload(form)', planningContext);
  assert.equal(payload.route, 'LGL DCT DUB');
  assert.equal(confirm.disabled, true);

  route.change('LGL DCT SOMAX');
  assert.throws(() => ui.assertReady(), /Valider cette route/);
  assert.equal(confirm.disabled, false);
  confirm.onclick();
  assert.equal(vm.runInNewContext('simBriefPlanningPayload(form)', planningContext).route, 'LGL DCT SOMAX');

  // Importing the actual OFP dismisses temporary FPL state without erasing
  // the route now belonging to the authoritative SimBrief briefing.
  ui.complete();
  assert.equal(route.value, 'LGL DCT SOMAX');
  assert.equal(preview.hidden, true);
  assert.equal(confirm.hidden, true);

  // A delayed file read from op_1 must not apply to the next operation.
  let resolveRead;
  const delayed = new Promise(resolve => { resolveRead = resolve; });
  fileInput.files = [{ name: 'slow.fms', size: 200, text() { return delayed; } }];
  const pending = fileInput.onchange({ target: fileInput });
  context.selectedOperation = {
    operation_id: 'op_2',
    flight: { departure: 'LFPO', arrival: 'EIDW' },
    simbrief: { available: false }
  };
  route.value = 'SECOND OPERATION ROUTE';
  ui.reset();
  resolveRead('LGL DCT STALE');
  await pending;
  assert.equal(route.value, 'SECOND OPERATION ROUTE');
  assert.equal(confirm.hidden, true);

  assert.match(html, /id="confirmLocalPlanBtn"[^>]+hidden/);
  assert.ok(planning.includes('verifiedDispatch?.server_checks?.ofp !== true'));
  assert.ok(messages.some(message => message.message.includes('Route importée')));
  console.log('Hermès local FPL flow: review, payload, OFP handoff and stale read checks OK');
}
test().catch(error => { console.error(error); process.exitCode = 1; });
