const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.resolve(__dirname, '../acars/wwwroot');
const app = fs.readFileSync(path.join(root, 'app.js'), 'utf8');
const importer = fs.readFileSync(path.join(root, 'hermes-local-plan-ui.js'), 'utf8');
const html = fs.readFileSync(path.join(root, 'index.html'), 'utf8');

assert.ok(!app.includes("$('#planFile').onchange"), 'no duplicate legacy input handler');
assert.ok(!app.includes("$('#clearPlanBtn').onclick"), 'no duplicate clear handler');
assert.ok(!app.includes('flightPlan = {};'), 'no fake OFP object when import fails');
assert.ok(importer.includes('fileInput.onchange = async event =>'));
assert.ok(importer.includes('clearButton.onclick = () =>'));
assert.ok(html.indexOf('/app.js') < html.indexOf('/hermes-local-plan-ui.js'));
assert.ok(html.indexOf('/hermes-local-plan.js') < html.indexOf('/hermes-local-plan-ui.js'));

const head = '// The local plan module is the sole owner of file import and removal.';
const start = app.indexOf(head), end = app.indexOf('\n});', start);
assert.ok(start >= 0 && end > start, 'fail-closed guard present');
const guardSource = app.slice(start, end + 4);

function runGuard(hasModule) {
  const controls = {
    '#planFile': { disabled: false },
    '#clearPlanBtn': { disabled: false }
  };
  const messages = [];
  let onLoad;
  const window = {
    HermesLocalPlanUi: hasModule ? {} : null,
    addEventListener(name, callback) {
      assert.equal(name, 'load');
      onLoad = callback;
    }
  };
  vm.runInNewContext(guardSource, {
    window,
    $: selector => controls[selector],
    showMessage: (...args) => messages.push(args)
  });
  onLoad();
  return { controls, messages };
}

const failed = runGuard(false);
assert.equal(failed.controls['#planFile'].disabled, true);
assert.equal(failed.controls['#clearPlanBtn'].disabled, true);
assert.equal(failed.messages.length, 1);
assert.match(failed.messages[0][1], /indisponible/);
assert.equal(failed.messages[0][2], true);

const ready = runGuard(true);
assert.equal(ready.controls['#planFile'].disabled, false);
assert.equal(ready.controls['#clearPlanBtn'].disabled, false);
assert.equal(ready.messages.length, 0);

console.log('Hermès local FPL: unique handler ownership and fail-closed guard OK');
