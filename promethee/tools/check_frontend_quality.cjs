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
  '../acars/wwwroot/app.js': 145000,
  '../acars/wwwroot/hermes-map.js': 18000,
  '../acars/wwwroot/hermes-review.js': 20000,
  '../acars/wwwroot/index.html': 50000,
  '../acars/wwwroot/styles.css': 60000,
  '../acars/wwwroot/hermes-themes.css': 35000,
  '../acars/wwwroot/hermes-era-components.css': 12000,
  'public/promethee-assets/promethee-v2.css': 90000,
  'public/promethee-assets/promethee-appearance.css': 40000,
  'public/promethee-assets/promethee-admin-workspaces.css': 12000,
  'public/promethee-assets/promethee-era-components.css': 14000,
  'modules/Promethee/Http/PortalController.php': 245000,
  'modules/Promethee/Http/AutomationController.php': 14000,
  'modules/Promethee/Resources/views/admin/dispatch.blade.php': 45000,
};

console.log('Frontend performance budgets');
for (const [file, budget] of Object.entries(budgets)) {
  const bytes = size(file);
  const ratio = Math.round((bytes / budget) * 100);
  console.log(`- ${file}: ${bytes} / ${budget} bytes (${ratio}%)`);
  expect(bytes <= budget, `${file} exceeds its performance budget (${bytes} > ${budget} bytes)`);
}

const hermesIndex = read('../acars/wwwroot/index.html');
const hermesApp = read('../acars/wwwroot/app.js');
const hermesMap = read('../acars/wwwroot/hermes-map.js');
const hermesReview = read('../acars/wwwroot/hermes-review.js');
const hermesDesktop = read('../acars/WebDesktop.cs');
const hermesTheme = read('../acars/wwwroot/hermes-themes.css');
const hermesEraComponents = read('../acars/wwwroot/hermes-era-components.css');
const prometheeBase = read('public/promethee-assets/promethee.css');
const prometheeV2 = read('public/promethee-assets/promethee-v2.css');
const prometheeAdmin = read('public/promethee-assets/admin-promethee.css');
const dispatch = read('modules/Promethee/Resources/views/admin/dispatch.blade.php');
const crm = read('modules/Promethee/Resources/views/admin/crm.blade.php');
const adminDashboard = read('modules/Promethee/Resources/views/admin/dashboard.blade.php');
const prometheeLayout = read('modules/Promethee/Resources/views/layout.blade.php');
const prometheePilots = read('modules/Promethee/Resources/views/pilots.blade.php');
const prometheeProfile = read('modules/Promethee/Resources/views/profile.blade.php');
const prometheeMissions = read('modules/Promethee/Resources/views/missions.blade.php');
const prometheeMyDocuments = read('modules/Promethee/Resources/views/my-documents.blade.php');
const prometheeCommunity = read('public/promethee-assets/promethee-community.css');
const prometheeEraComponents = read('public/promethee-assets/promethee-era-components.css');
const prometheeFlights = read('modules/Promethee/Resources/views/flights.blade.php');
const portalController = read('modules/Promethee/Http/PortalController.php');
const automationController = read('modules/Promethee/Http/AutomationController.php');
const prometheeRoutes = read('modules/Promethee/routes.php');
expect(!portalController.includes('public function automation(')
    && !portalController.includes('public function saveBadgeRule(')
    && automationController.includes('class AutomationController extends Controller'),
  'Prométhée progression administration must stay extracted from PortalController.');
expect(prometheeRoutes.includes("[AutomationController::class,'automation']")
    && prometheeRoutes.includes("[AutomationController::class,'saveRankRule']"),
  'Prométhée automation routes must remain owned by AutomationController without changing route names.');
const adminWorkspaceCss = read('public/promethee-assets/promethee-admin-workspaces.css');
const automationWorkspace = read('modules/Promethee/Resources/views/admin/automation.blade.php');
const seasonsWorkspace = read('modules/Promethee/Resources/views/seasons.blade.php');
const economyWorkspace = read('modules/Promethee/Resources/views/economy.blade.php');
const regionalWorkspace = read('modules/Promethee/Resources/views/admin-regional-operations.blade.php');
const maintenanceWorkspace = read('modules/Promethee/Resources/views/admin-maintenance.blade.php');
const sopWorkspace = read('modules/Promethee/Resources/views/admin/sop.blade.php');

const stylesheetHrefs = [...hermesIndex.matchAll(/<link\b[^>]*rel=["']stylesheet["'][^>]*href=["']([^"']+)["']/gi)]
  .map(match => match[1]);
const duplicates = stylesheetHrefs.filter((href, index) => stylesheetHrefs.indexOf(href) !== index);
expect(duplicates.length === 0, 'Hermès contains duplicate stylesheet links: ' + [...new Set(duplicates)].join(', '));

expect(!/<script\b[^>]*src=["']\/minitel\//i.test(hermesIndex),
  'Hermès must lazy-load Minitel scripts instead of loading them in the default shell.');
expect(!/<link\b[^>]*href=["']\/minitel\//i.test(hermesIndex),
  'Hermès must lazy-load Minitel styles instead of loading them in the default shell.');

expect(hermesIndex.indexOf('/hermes-map.js') < hermesIndex.indexOf('/app.js')
    && hermesIndex.indexOf('/hermes-review.js') < hermesIndex.indexOf('/app.js'),
  'Hermès architecture modules must load before the orchestration script.');
expect(hermesMap.includes('Object.assign(window')
    && hermesMap.includes('drawMap')
    && hermesMap.includes('flightMapState')
    && hermesMap.includes('initializeFlightMapControls'),
  'Hermès map rendering must remain isolated behind the compatibility global contract.');
expect(hermesReview.includes('Object.assign(window')
    && hermesReview.includes('renderReview')
    && hermesReview.includes('refreshCompanyScore')
    && hermesReview.includes('initializeReviewActions'),
  'Hermès review rendering must remain isolated behind the compatibility global contract.');
expect(!hermesApp.includes('function normalizeReviewProfile')
    && !hermesApp.includes('function drawMap(')
    && hermesApp.includes('initializeFlightMapControls();')
    && hermesApp.includes('initializeReviewActions();'),
  'Hermès app.js must not absorb map/review rendering again.');

expect(hermesIndex.includes('reviewAltitudeChart') && hermesIndex.includes('reviewFuelChart'),
  'Hermès Flight Review must preserve altitude and fuel chart surfaces.');
expect(hermesReview.includes('normalizeReviewProfile') && hermesReview.includes('renderReviewCharts'),
  'Hermès Flight Review module must preserve profile-series rendering.');
expect(hermesDesktop.includes('"/api/file" => await File(body)')
    && hermesDesktop.includes('report["notes"] = notes'),
  'Hermès desktop must forward the optional pilot Flight Review comment when filing.');

expect(hermesTheme.includes('prefers-reduced-motion'),
  'Hermès theme CSS must preserve prefers-reduced-motion handling.');
expect(hermesTheme.includes('focus-visible'),
  'Hermès theme CSS must preserve visible keyboard focus handling.');

const hermesOverrides = read('../acars/wwwroot/layout-overrides.css');
expect(hermesIndex.includes('class="rail-nav-label"')
    && hermesIndex.includes('class="display-settings"')
    && hermesIndex.includes('20261003-era-phase6'),
  'Hermès must preserve the grouped operational navigation and refreshed asset revision.');
expect(hermesApp.includes('data-tab') || hermesIndex.includes('data-tab="flight"'),
  'Hermès navigation must keep data-tab based workspace routing.');
expect(read('../acars/wwwroot/styles.css').includes('Audit UX phase 2 — operational visual consolidation.')
    && read('../acars/wwwroot/styles.css').includes('--radius:8px;')
    && read('../acars/wwwroot/styles.css').includes('.rail-nav-label'),
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

const prometheeNavigation = read('public/promethee-assets/navigation-groups.js');
const prometheeEras = read('public/promethee-assets/airinter-eras.css');
expect(prometheeLayout.includes('data-promethee-shell')
    && prometheeLayout.includes('class="shell-primary"')
    && prometheeLayout.includes('class="shell-branding"')
    && prometheeLayout.includes('class="shell-menu-toggle"')
    && prometheeLayout.includes('id="promethee-navigation"'),
  'Prométhée shell must preserve the structured two-level navbar.');

expect(prometheeLayout.includes("'scope' => 'pilot'")
    && prometheeLayout.includes("'scope' => 'shared'")
    && prometheeLayout.includes("'scope' => 'staff'")
    && prometheeLayout.includes("'title' => 'OCC / EXPLOITATION'")
    && !prometheeLayout.includes("['route' => 'admin.promethee.dispatch', 'label' => 'dispatch_desk', 'active' => 'admin.promethee.dispatch*'],\n            ['route' => 'promethee.missions'"),
  'Prométhée navigation must keep explicit pilot/shared/staff information architecture.');
expect(prometheeMissions.includes('id="my-missions"')
    && prometheeMissions.includes('$myMissions')
    && prometheeMissions.includes('$otherMissions')
    && prometheeMissions.includes('pilot-hub-nav'),
  'Prométhée missions must keep personal missions separate from the available mission catalogue.');

expect(!prometheeProfile.includes('id="my-missions"')
    && prometheeProfile.includes("route('promethee.missions') }}#my-missions")
    && prometheeProfile.includes('profile-mission-link'),
  'Prométhée profile must link to the mission workspace instead of duplicating mission management.');
expect(prometheeLayout.includes("'admin.promethee.network*'")
    && prometheeLayout.includes("'admin.promethee.mailbox*'")
    && prometheeLayout.includes("'admin.promethee.health'"),
  'Prométhée staff navigation must keep OCC routes out of the generic Administration active state.');
expect(prometheeMyDocuments.includes('Documentation Air Inter')
    && prometheeMyDocuments.includes('documentation-hub-nav')
    && !/<[^>]+\sstyle\s*=/i.test(prometheeMyDocuments),
  'Prométhée documentation must remain a single pilot hub without inline styles.');
expect(!prometheePilots.includes('Cette page reste à écrire')
    && prometheePilots.includes('community-empty-state'),
  'Prométhée community directory must use finished empty states instead of placeholder copy.');
expect(prometheeCommunity.includes('Audit UX step 5 — pilot information architecture.')
    && prometheeCommunity.includes('@media(max-width:760px)')
    && prometheeCommunity.includes('.mission-owned-row'),
  'Prométhée pilot information architecture must keep its responsive treatment.');

expect(prometheeLayout.includes('promethee-era-components.css')
    && prometheeLayout.indexOf("@stack('styles')") < prometheeLayout.indexOf('promethee-era-components.css'),
  'Prométhée era component adapters must load after page-specific styles.');
expect(prometheeEraComponents.includes('1999–2005')
    && prometheeEraComponents.includes('Minitel fallback')
    && prometheeEraComponents.includes('.pilot-hub-nav')
    && prometheeEraComponents.includes('.admin-workspace-nav')
    && prometheeEraComponents.includes('.next-operation')
    && prometheeEraComponents.includes('.attention-inbox'),
  'Prométhée historical adapter layer must cover shared pilot, admin and operational components.');
expect(!/html\[data-era="(?:2000|minitel)"\]/.test(prometheeCommunity)
    && !/html\[data-era="(?:2000|minitel)"\]/.test(adminWorkspaceCss),
  'Prométhée feature stylesheets must remain era-neutral; historical presentation belongs to the adapter layer.');
expect(hermesIndex.includes('/hermes-era-components.css')
    && hermesIndex.indexOf('/hermes-themes.css') < hermesIndex.indexOf('/hermes-era-components.css')
    && hermesIndex.indexOf('/hermes-era-components.css') < hermesIndex.indexOf('/layout-overrides.css'),
  'Hermès historical component adapters must load after the base theme grammar.');
expect(hermesEraComponents.includes('1999–2005')
    && hermesEraComponents.includes('Minitel fallback')
    && hermesEraComponents.includes('.workflow')
    && hermesEraComponents.includes('.next-action')
    && hermesEraComponents.includes('.rail-nav-label')
    && hermesEraComponents.includes('.display-settings'),
  'Hermès historical adapter layer must cover navigation and operational workflow components.');
expect(!hermesTheme.includes('body[data-era="2000"] .rail-nav-label'),
  'Hermès recent component-era overrides must not leak back into the base theme stylesheet.');
expect(hermesIndex.includes('/hermes-minitel.css?rev=')
    && hermesIndex.includes('window.loadHermesMinitel'),
  'Hermès must preserve the native lazy-loaded Minitel runtime instead of reducing it to a DOM skin.');
expect(prometheeNavigation.includes('setShellMenu')
    && prometheeNavigation.includes("aria-expanded")
    && prometheeNavigation.includes("event.key === 'Escape'"),
  'Prométhée navbar must preserve responsive menu state and keyboard dismissal.');
expect(prometheeEras.includes('Modern shell: a stable two-level airline navigation.')
    && prometheeEras.includes('Prométhée shell navigation v2: mobile and compatibility adapters.')
    && prometheeEras.includes('.sidebar.is-menu-open nav.is-grouped'),
  'Prométhée era adapters must preserve the navbar refactor and mobile layout.');
expect(prometheeFlights.includes('class="flight-filter-advanced"')
    && prometheeFlights.includes('<table class="flight-results">')
    && !prometheeFlights.includes('<section class="flight-cards">'),
  'Prométhée flight programme must preserve progressive filters and scan-friendly tabular results.');
expect(prometheeV2.includes('Audit UX phase 11 — flight programme progressive disclosure')
    && prometheeV2.includes('Audit UX phase 11 — compact shell controls'),
  'Prométhée must preserve phase 11 shell and flight-programme primitives.');

const prometheeDashboard = read('modules/Promethee/Resources/views/dashboard.blade.php');
const prometheeFleet = read('modules/Promethee/Resources/views/fleet.blade.php');
const prometheeMaintenance = read('modules/Promethee/Resources/views/maintenance.blade.php');
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

expect(adminWorkspaceCss.includes('.admin-workspace-nav')
    && adminWorkspaceCss.includes('@media(max-width:980px)')
    && adminWorkspaceCss.includes('@media(max-width:760px)')
    && adminWorkspaceCss.includes('.admin-table-scroll'),
  'Prométhée staff workspaces must preserve responsive local navigation, grid collapse and table scrolling.');
for (const [name, source] of Object.entries({
  automationWorkspace,seasonsWorkspace,economyWorkspace,regionalWorkspace,maintenanceWorkspace,sopWorkspace,
})) {
  expect(source.includes('class="admin-workspace-page"')
      && source.includes('class="admin-workspace-nav"')
      && source.includes('promethee-admin-workspaces.css'),
    name + ' must use the shared responsive staff workspace shell.');
  expect(!/<[^>]+\sstyle\s*=/i.test(source),
    name + ' must not use inline style attributes.');
}
expect(regionalWorkspace.includes('id="regional-fleet"')
    && maintenanceWorkspace.includes('id="maintenance-engines"')
    && sopWorkspace.includes('id="sop-scoring"'),
  'Dense admin workspaces must preserve stable local navigation anchors.');

expect(regionalWorkspace.includes('table-wrap admin-table-scroll')
    && maintenanceWorkspace.includes('table-wrap admin-table-scroll')
    && economyWorkspace.includes('href="#prix-vols"')
    && economyWorkspace.includes('href="#prix-carburant"'),
  'Responsive admin workspaces must keep dense tables scrollable and preserve economy deep links.');

const inlineStyleFreeViews = [
  'modules/Promethee/Resources/views/bookings.blade.php',
  'modules/Promethee/Resources/views/flight.blade.php',
  'modules/Promethee/Resources/views/fleet.blade.php',
  'modules/Promethee/Resources/views/maintenance.blade.php',
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
