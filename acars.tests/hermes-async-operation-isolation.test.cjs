const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.resolve(__dirname, '../acars/wwwroot');
const planning = fs.readFileSync(path.join(root, 'hermes-planning.js'), 'utf8');
const workflow = fs.readFileSync(path.join(root, 'hermes-workflow.js'), 'utf8');
const app = fs.readFileSync(path.join(root, 'app.js'), 'utf8');
const deferred = () => {
  let resolve;
  const promise = new Promise(r => { resolve = r; });
  return { promise, resolve };
};
const form = { elements: {
  aircraft_id: { value: 'A1' }, route: { value: 'LGL DCT SOMAX' },
  alt_airport_id: { value: '' }, level: { value: '340' }
} };
const calls = [], pending = new Map();
const context = {
  selectedOperation: { operation_id: 'op_A' },
  selectedAircraft: { id: 'A1' },
  serverDispatch: null,
  pirepId: null,
  selectedVariant: null,
  readiness: {},
  flightPlan: null,
  aircraftVariantState: null,
  $: selector => selector === '#prefileForm' ? form : { textContent: '' },
  clearOperationalWeather() {},
  renderOperationLoad() {},
  syncAircraftSelectors() {},
  renderAircraftVariants() {},
  updateWorkflow() {},
  scheduleEfbContextSync() {},
  setText() {},
  showMessage() {},
  friendlyError: error => error.message,
  unwrap: data => data,
  call: uri => {
    calls.push(uri);
    const request = deferred();
    pending.set(uri, request);
    return request.promise;
  }
};
vm.createContext(context);
vm.runInContext(workflow, context);
vm.runInContext(planning, context);
const switchTo = (operation, aircraft) => {
  context.selectedOperation = { operation_id: operation };
  context.selectedAircraft = { id: aircraft };
  form.elements.aircraft_id.value = aircraft;
};
const dispatch = (operation, pirep, status = 'READY') => ({
  operation: { operation_id: operation },
  pirep: { id: pirep },
  status,
  server_checks: { operation: true, aircraft: true, ofp: true, pirep: true }
});

(async () => {
  const ctxA = vm.runInContext('captureFlightContext()', context);
  assert.equal(ctxA.operation, 'op_A');
  assert.equal(ctxA.aircraft, 'A1');

  const stale = vm.runInContext('refreshDispatch()', context);
  switchTo('op_B', 'B1');
  const fresh = vm.runInContext('refreshDispatch()', context);
  pending.get('/api/v1/operations/op_B/dispatch').resolve(dispatch('op_B', 'pirep_B'));
  const current = await fresh;
  assert.equal(current.status, 'READY');
  assert.equal(context.pirepId, 'pirep_B');

  pending.get('/api/v1/operations/op_A/dispatch').resolve(dispatch('op_A', 'pirep_A', 'COMPLETED'));
  assert.equal(await stale, null);
  assert.equal(context.serverDispatch.status, 'READY');
  assert.equal(context.selectedOperation.operation_id, 'op_B');
  assert.equal(context.pirepId, 'pirep_B');
  assert.throws(() => vm.runInContext('assertFlightContext({ operation: "op_A", aircraft: "A1" })', context), /changé/);

  // Two Dispatch calls for the same operation must not let the older reply
  // overwrite a newer result. Calls are kept as an ordered queue here.
  const replies = [];
  context.call = uri => {
    const request = deferred();
    replies.push({ uri, ...request });
    return request.promise;
  };
  const first = vm.runInContext('refreshDispatch()', context);
  const second = vm.runInContext('refreshDispatch()', context);
  replies[1].resolve(dispatch('op_B', 'pirep_B_latest', 'IN_PROGRESS'));
  assert.equal((await second).status, 'IN_PROGRESS');
  replies[0].resolve(dispatch('op_B', 'pirep_B_older', 'COMPLETED'));
  assert.equal(await first, null);
  assert.equal(context.pirepId, 'pirep_B_latest');
  assert.equal(context.serverDispatch.status, 'IN_PROGRESS');

  // Changing the assigned aircraft in the middle of a Dispatch request must
  // also discard that request.
  const aircraftRequest = vm.runInContext('refreshDispatch()', context);
  switchTo('op_B', 'B2');
  replies[2].resolve(dispatch('op_B', 'pirep_B_wrong_aircraft'));
  assert.equal(await aircraftRequest, null);
  assert.equal(context.pirepId, 'pirep_B_latest');

  // Guard all three asynchronous SimBrief code paths, not only Dispatch.
  assert.match(app, /applyBriefing\(briefing, 'l’API SimBrief', expected\)/);
  assert.match(app, /applyBriefing\(briefing, 'le vol SimBrief lié à cette opération', expected\)/);
  assert.match(app, /if \(!sameFlightContext\(expected\)\) \{ clearInterval\(waitForClose\); return; \}/);
  assert.match(planning, /assertFlightContext\(expected\);\s+pirepId = result\.id/);
  console.log('Hermès async isolation: changed flights, aircraft, out-of-order Dispatch and SimBrief guards OK');
})().catch(error => { console.error(error); process.exitCode = 1; });
