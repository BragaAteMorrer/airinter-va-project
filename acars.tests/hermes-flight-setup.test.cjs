const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.resolve(__dirname, '..');
const html = fs.readFileSync(path.join(root, 'wwwroot/index.html'), 'utf8');
const source = fs.readFileSync(path.join(root, 'wwwroot/hermes-flight-setup.js'), 'utf8');

function fakeSelect(value) {
  return {
    value,
    handlers: {},
    addEventListener(name, listener) { this.handlers[name] = listener; },
    change(value) { this.value = value; this.handlers.change?.(); }
  };
}
const preference = fakeSelect('STANDARD');
const quick = fakeSelect('AIRINTER');
const storage = { prometheeAcarsSettings: JSON.stringify({ simbriefUsername: 'pilot', radioCallsign: 'STANDARD' }) };
const context = {
  document: {
    querySelector(selector) {
      return selector === '#flightRadioCallsign' ? quick
        : selector === '#settingsForm [name="radioCallsign"]' ? preference : null;
    }
  },
  localStorage: storage,
  window: {}
};
vm.runInNewContext(source, context);
const flow = context.window.HermesFlightSetup;
assert.ok(flow);
const settings = { radioCallsign: 'STANDARD', simbriefUsername: 'pilot' };
flow.initialize(settings);
assert.equal(quick.value, 'STANDARD', 'quick preference should reflect the saved settings');

quick.change('AIRINTER');
assert.equal(preference.value, 'AIRINTER', 'quick choice should update Settings');
assert.equal(settings.radioCallsign, 'AIRINTER');
assert.deepEqual(JSON.parse(storage.prometheeAcarsSettings), {
  simbriefUsername: 'pilot', radioCallsign: 'AIRINTER'
});
assert.equal(flow.radioCallsign({ flight: { airline: { name: 'Air Inter' } } }), 'AIRINTER');
assert.equal(flow.radioCallsign({ flight: { airline: { name: 'Inter Cargo' } } }), 'INTERCARGO');

preference.change('STANDARD');
assert.equal(quick.value, 'STANDARD', 'settings choice should update quick selection');
assert.match(flow.radioCallsign({ flight: { ident: 'ITF4410' } }), /ITF4410/);

// The actual SimBrief callsign input is outside the prefile form,
// but its HTML form owner must remain prefileForm.
assert.equal((html.match(/name="callsign"/g) || []).length, 1);
assert.match(html, /id="flightAtcCallsign"[^>]*form="prefileForm"/);
assert.equal((html.match(/id="aircraftVariantId"/g) || []).length, 1);
assert.ok(html.indexOf('class="operations-browser"') < html.indexOf('class="operations-current"'));
assert.ok(html.indexOf('class="operations-current"') < html.indexOf('id="quickAircraftSelection"'));
assert.ok(html.indexOf('id="quickAircraftSelection"') < html.indexOf('class="flightplan-source operations-planning"'));
console.log('Hermès flight setup: radio preference, callsign ownership and layout assertions OK');
