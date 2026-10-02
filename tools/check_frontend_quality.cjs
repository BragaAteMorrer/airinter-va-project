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
const prometheeV2 = read('prometheus/public/promethee-assets/promethee-v2.css');
const dispatch = read('prometheus/modules/Promethee/Resources/views/admin/dispatch.blade.php');
const crm = read('prometheus/modules/Promethee/Resources/views/admin/crm.blade.php');
const adminDashboard = read('prometheus/modules/Promethee/Resources/views/admin/dashboard.blade.php');

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
expect(prometheeV2.includes('prefers-reduced-motion'),
  'Prométhée v2 CSS must preserve prefers-reduced-motion handling.');
expect(prometheeV2.includes('focus-visible'),
  'Prométhée v2 CSS must preserve visible keyboard focus handling.');

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
