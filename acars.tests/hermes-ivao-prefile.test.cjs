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
assert.match(handler[1], /icao_flightplan/, 'Retain SimBrief ICAO FPL export');
assert.match(handler[1], /clipboard\.writeText\(icaoPlan\)/, 'Retain clipboard import path');
assert.match(handler[1], /open-external/, 'Open IVAO via the existing secure native bridge');
assert.doesNotMatch(handler[1], /fpl\.ivao\.aero\/home/);
console.log('Hermès IVAO prefile routing regression passed.');
