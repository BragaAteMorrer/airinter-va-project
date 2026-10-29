const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.resolve(__dirname, '../acars/wwwroot');
const html = fs.readFileSync(path.join(root, 'index.html'), 'utf8');
const source = fs.readFileSync(path.join(root, 'hermes-account-sync.js'), 'utf8');
function node(value = '') {
  return {
    value, disabled: false, textContent: '', handlers: {},
    addEventListener(name, cb) { this.handlers[name] = cb; },
    click() { return this.handlers.click?.(); }
  };
}
const elements = {
  '#vatsimCid': node(),
  '#ivaoVid': node(),
  '#networkIdsInfo': node(),
  '#importNetworkIdsBtn': node(),
  '#simbriefPilotHelpBtn': node()
};
const window = {};
const document = { querySelector: name => elements[name] || null };
vm.runInNewContext(source, { window, document });
const sync = window.HermesAccountSync;
assert.ok(sync);
let opened = null;
sync.initialize(async (route, payload) => { opened = { route, payload }; });
sync.apply({ id: 17, network_ids: { vatsim_cid: '123456', ivao_vid: '456789' } });
assert.equal(elements['#vatsimCid'].value, '123456');
assert.equal(elements['#ivaoVid'].value, '456789');
assert.equal(elements['#importNetworkIdsBtn'].disabled, false);

// No silent overwrite of a user edit, but allow an explicit refresh.
elements['#vatsimCid'].value = '111111';
sync.apply({ id: 17, network_ids: { vatsim_cid: '123456', ivao_vid: '456789' } });
assert.equal(elements['#vatsimCid'].value, '111111');
elements['#importNetworkIdsBtn'].click();
assert.equal(elements['#vatsimCid'].value, '123456');

// A different authenticated pilot must not inherit auto-filled values.
sync.apply({ id: 18, network_ids: { vatsim_cid: '222222', ivao_vid: null } });
assert.equal(elements['#vatsimCid'].value, '222222');
assert.equal(elements['#ivaoVid'].value, '');
sync.clear();
assert.equal(elements['#vatsimCid'].value, '');
assert.equal(elements['#importNetworkIdsBtn'].disabled, true);

// Invalid IDs must never be populated.
sync.apply({ id: 19, network_ids: { vatsim_cid: 'abc', ivao_vid: '9999999999999' } });
assert.equal(elements['#vatsimCid'].value, '');
assert.equal(elements['#ivaoVid'].value, '');
assert.equal(elements['#importNetworkIdsBtn'].disabled, true);

(async () => {
  await elements['#simbriefPilotHelpBtn'].click();
  assert.equal(opened.route, '/api/open-external');
  assert.equal(opened.payload.url, 'https://www.simbrief.com/system/profile.php#settings');
  assert.ok(html.indexOf('hermes-account-sync.js') < html.indexOf('hermes-identity.js'));
  assert.match(html, /id="simbriefPilotHelpBtn" type="button"/);
  assert.match(html, /id="importNetworkIdsBtn" type="button"/);
  assert.match(html, /Pilot ID SimBrief \\(facultatif\\)/);
  console.log('Hermès account sync: pilots scoped, local overrides preserved and SimBrief help verified');
})().catch(error => { console.error(error); process.exitCode = 1; });
