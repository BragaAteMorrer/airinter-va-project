#!/usr/bin/env node
'use strict';

const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const failures = [];

const read = relative => fs.readFileSync(path.join(root, relative), 'utf8');
const size = relative => fs.statSync(path.join(root, relative)).size;
const fail = message => failures.push(message);
const expect = (condition, message) => { if (!condition) fail(message); };

const budgets = {
  'acars/wwwroot/app.js': 180000,
  'acars/wwwroot/index.html': 50000,
  'acars/wwwroot/styles.css': 60000,
  'acars/wwwroot/hermes-themes.css': 35000,
  'prometheus/public/promethee-assets/promethee-v2.css': 90000,
  'prometheus/public/promethee-assets/promethee-appearance.css': 40000,
  'prometheus/modules/Promethee/Resources/views/admin/dispatch.blade.php': 45000,
};

console.log('Frontend performance budgets');
for (const [file, budget] of Object.entries(budgets)) {
  const bytes = size(file);
  const ratio = Math.round((bytes / budget) * 100);
  console.log(`- ${file}: ${bytes} / ${budget} bytes (${ratio}%)`);
  expect(bytes <= budget, `${file} exceeds its performance budget (${bytes} > ${budget} bytes)`);
}

const hermesIndex = read('acars/wwwroot/index.html');
const hermesApp = read('acars/wwwroot/app.js');
const hermesDesktop = read('acars/WebDesktop.cs');
const hermesTheme = read('acars/wwwroot/hermes-themes.css');
const prometheeBase = read('prometheus/public/promethee-assets/promethee.css');
const prometheeV2 = read('prometheus/public/promethee-assets/promethee-v2.css');
const prometheeAdmin = read('prometheus/public/promethee-assets/admin-promethee.css');
const dispatch = read('prometheus/modules/Promethee/Resources/views/admin/dispatch.blade.php');
const crm = read('prometheus/modules/Promethee/Resources/views/admin/crm.blade.php');
const adminDashboard = read('prometheus/modules/Promethee/Resources/views/admin/dashboard.blade.php');
const prometheeLayout = read('prometheus/modules/Promethee/Resources/views/layout.blade.php');
const prometheeFlights = read('prometheus/modules/Promethee/Resources/views/flights.blade.php');

const stylesheetHrefs = [...hermesIndex.matchAll(/<link\b[^>]*rel=["']stylesheet["'][^>]*href=["']([^"']+)["']/gi)]
  .map(match => match[1]);
const duplicates = stylesheetHrefs.filter((href, index) => stylesheetHrefs.indexOf(href) !== index);
expect(duplicates.length === 0, 'Hermès contains duplicate stylesheet links: ' + [...new Set(duplicates)].join(', '));

expect(!/<script\b[^>]*src=["']\/minitel\//i.test(hermesIndex),
  'Hermès must lazy-load Minitel scripts instead of loading them in the default shell.');
expect(!/<link\b[^>]*href=["']\/minitel\//i.test(hermesIndex),
  'Hermès must lazy-load Minitel styles instead of loading them in the default shell.');

expect(hermesIndex.includes('reviewAltitudeChart') && hermesIndex.includes('reviewFuelChart'),
  'Hermès Flight Review must preserve altitude and fuel chart surfaces.');
expect(hermesApp.includes('normalizeReviewProfile') && hermesApp.includes('renderReviewCharts'),
  'Hermès Flight Review must preserve profile-series rendering.');
expect(hermesDesktop.includes('"/api/file" => await File(body)')
    && hermesDesktop.includes('report["notes"] = notes'),
  'Hermès desktop must forward the optional pilot Flight Review comment when filing.');

expect(hermesTheme.includes('prefers-reduced-motion'),
  'Hermès theme CSS must preserve prefers-reduced-motion handling.');
expect(hermesTheme.includes('focus-visible'),
  'Hermès theme CSS must preserve visible keyboard focus handling.');

const hermesOverrides = read('acars/wwwroot/layout-overrides.css');
expect(hermesIndex.includes('class="rail-nav-label"')
    && hermesIndex.includes('class="display-settings"')
    && hermesIndex.includes('20261003-ops-ui-phase2'),
  'Hermès must preserve the grouped operational navigation and refreshed asset revision.');
expect(hermesApp.includes('data-tab') || hermesIndex.includes('data-tab="flight"'),
  'Hermès navigation must keep data-tab based workspace routing.');
expect(read('acars/wwwroot/styles.css').includes('Audit UX phase 2 — operational visual consolidation.')
    && read('acars/wwwroot/styles.css').includes('--radius:8px;')
    && read('acars/wwwroot/styles.css').includes('.rail-nav-label'),
  'Hermès must preserve the compact operational visual system.');
expect(!hermesOverrides.includes('.rail')
    && !hermesOverrides.includes('#selectedOperation.prefile'),
  'Hermès layout hotfixes must stay consolidated in the canonical stylesheet.');
expect(prometheeV2.includes('prefers-reduced-motion'),
  'Prométhée v2 CSS must preserve prefers-reduced-motion handling.');
expect(prometheeV2.includes('focus-visible'),
  'Prométhée v2 CSS must preserve visible keyboard focus handling.');

expect(prometheeV2.includes('Audit UX phase 10 — modern operational visual system')
    && prometheeV2.includes('--radius: 8px;')
    && prometheeV2.includes('backdrop-filter: none'),
  'Prométhée Modern must preserve the restrained operational visual system from audit phase 10.');
expect(!prometheeV2.includes('box-shadow: 0 9px 18px rgb(21 94 239 / 28%)'),
  'Prométhée Modern must not reintroduce SaaS-style floating button shadows.');
expect(prometheeBase.includes('justify-content:center;gap:8px;'),
  'Prométhée shared buttons must keep compact icon/text spacing.');
expect(prometheeAdmin.includes('--admin-radius:7px;')
    && prometheeAdmin.includes('--admin-shadow:0 2px 9px #162f460a;'),
  'Prométhée admin must keep the compact visual tokens aligned with the portal.');

expect(prometheeLayout.includes('class="topbar-controls"')
    && prometheeLayout.includes('theme-control-label'),
  'Prométhée shell must keep compact grouped display controls.');
expect(prometheeFlights.includes('class="flight-filter-advanced"')
    && prometheeFlights.includes('<table class="flight-results">')
    && !prometheeFlights.includes('<section class="flight-cards">'),
  'Prométhée flight programme must preserve progressive filters and scan-friendly tabular results.');
expect(prometheeV2.includes('Audit UX phase 11 — flight programme progressive disclosure')
    && prometheeV2.includes('Audit UX phase 11 — compact shell controls'),
  'Prométhée must preserve phase 11 shell and flight-programme primitives.');

const prometheeDashboard = read('prometheus/modules/Promethee/Resources/views/dashboard.blade.php');
const prometheeFleet = read('prometheus/modules/Promethee/Resources/views/fleet.blade.php');
const prometheeMaintenance = read('prometheus/modules/Promethee/Resources/views/maintenance.blade.php');
expect(prometheeDashboard.includes('<progress class="operation-progress next-operation-progress"'),
  'Prométhée dashboard must use the shared semantic operation progress primitive.');
expect(prometheeFleet.includes('class="airline-mark"') && prometheeFleet.includes('PARC ACTIF') && prometheeFleet.includes('table-sort-link'),
  'Prométhée fleet must keep shared table, logo and sort primitives.');
expect(prometheeMaintenance.includes('CELLULE · SUIVI ACTIF') && prometheeMaintenance.includes('Potentiel moteurs') && prometheeMaintenance.includes('<div class="table-wrap">'),
  'Prométhée maintenance must preserve the structured technical workspace.');
expect(prometheeV2.includes('Audit UX phase 12 — fleet/maintenance shared details.'),
  'Prométhée must preserve phase 12 fleet/maintenance primitives.');

expect(dispatch.includes("if (!document.hidden) refreshBoard()"),
  'Dispatch Desk polling must remain suspended while the page is hidden.');
expect(dispatch.includes('promethee-dispatch-filter') && dispatch.includes('promethee-dispatch-selected'),
  'Dispatch Desk must preserve dispatcher context across refresh/navigation.');

expect(crm.includes('data-crm-campaign-workspace')
    && crm.includes('promethee-crm-selected-campaign')
    && crm.includes('admin-master-detail'),
  'CRM must preserve the reusable master/detail campaign workspace and selected campaign context.');
expect(prometheeV2.includes('.admin-master-detail')
    && prometheeV2.includes('.admin-detail-pane')
    && prometheeV2.includes('.admin-master-row'),
  'Prométhée must preserve shared staff master/detail primitives.');

expect(adminDashboard.includes('Inbox d’exceptions')
    && adminDashboard.includes('attentionItems')
    && adminDashboard.includes('À TRAITER MAINTENANT'),
  'Prométhée admin dashboard must preserve its exception-first attention inbox.');
expect(prometheeV2.includes('.attention-inbox')
    && prometheeV2.includes('.attention-item'),
  'Prométhée must preserve shared OCC attention-inbox styling.');

const inlineStyleFreeViews = [
  'prometheus/modules/Promethee/Resources/views/bookings.blade.php',
  'prometheus/modules/Promethee/Resources/views/flight.blade.php',
  'prometheus/modules/Promethee/Resources/views/fleet.blade.php',
  'prometheus/modules/Promethee/Resources/views/maintenance.blade.php',
];
for (const file of inlineStyleFreeViews) {
  expect(!/<[^>]+\sstyle\s*=/i.test(read(file)),
    `${file} must use shared CSS primitives instead of inline style attributes.`);
}

if (failures.length) {
  console.error('\nFrontend quality gate failed:');
  failures.forEach(item => console.error('- ' + item));
  process.exit(1);
}

console.log('\nFrontend quality gate passed.');
