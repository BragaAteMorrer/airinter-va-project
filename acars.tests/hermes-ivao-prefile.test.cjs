const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const root = path.resolve(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'promethee/modules/Promethee/Http/Api/AcarsSimBriefController.php'), 'utf8');
const app = fs.readFileSync(path.join(root, 'acars/wwwroot/app.js'), 'utf8');

// Both new OFPs (server URL) and already imported OFPs (client fallback)
// must take the pilot to IVAO's actual flight plan creation form.
const target = 'https://fpl.ivao.aero/flight-plans/create';
assert.ok(php.includes("'url' => '" + target + "'"), 'Prométhée must return the IVAO create form');
assert.doesNotMatch(php, /'url'\s*=>\s*'https:\/\/fpl\.ivao\.aero\/home'/);

const handler = app.match(/\$\('#ivaoPrefileBtn'\)\.onclick\s*=\s*async \(\) => \{([\s\S]*?)\n\};/);
assert.ok(handler, 'IVAO handler must exist');
assert.ok(handler[1].includes("const ivaoUrl = '" + target + "'"), 'Legacy URLs must use the create form');
assert.match(app, /function ivaoIcaoPlan\(\)/, 'Dedicated ICAO FPL validation helper');
assert.match(app, /icao_flightplan/, 'Retain SimBrief ICAO FPL export');
assert.match(app, /Le texte ICAO FPL de cet OFP est absent ou incomplet/, 'Reject incomplete OFP');
assert.match(app, /FPL-\[A-Z0-9\]/, 'Validate ICAO header');
assert.match(app, /clipboard\.writeText\(icaoPlan\)/, 'Retain modern clipboard support');
assert.match(app, /ivaoCopyFplBtn/, 'Allow clipboard retry after IVAO login');
assert.match(handler[1], /copyIvaoIcaoPlan\(\)/, 'Open action must copy actual FPL');
assert.match(handler[1], /open-external/, 'Open IVAO via the existing secure native bridge');
assert.doesNotMatch(handler[1], /fpl\.ivao\.aero\/home/);
console.log('Hermès IVAO prefile routing regression passed.');
