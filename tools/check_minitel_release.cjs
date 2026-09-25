'use strict';

const fs = require('node:fs');
const path = require('node:path');

const root = path.resolve(__dirname, '..');
const manifest = JSON.parse(fs.readFileSync(path.join(root, 'shared', 'minitel', 'acceptance-m7.json'), 'utf8'));
const runtime = require(path.join(root, 'shared', 'minitel', 'runtime.js'));
const hermesCore = require(path.join(root, 'acars', 'wwwroot', 'hermes-minitel-core.js'));

const args = process.argv.slice(2);
const reportIndex = args.indexOf('--report-dir');
const reportDir = reportIndex >= 0 && args[reportIndex + 1]
  ? path.resolve(args[reportIndex + 1])
  : null;

const read = relative => fs.readFileSync(path.join(root, relative), 'utf8').replace(/\r\n/g, '\n');
const sources = {
  runtime: read('shared/minitel/runtime.js'),
  renderer: read('shared/minitel/renderer.js'),
  shell: read('shared/minitel/shell.js'),
  runtimeCss: read('shared/minitel/minitel-runtime.css'),
  shellCss: read('shared/minitel/minitel-shell.css'),
  prometheeClient: read('prometheus/public/promethee-assets/promethee-minitel.js'),
  prometheeBase: read('prometheus/public/promethee-assets/promethee.js'),
  prometheeLayout: read('prometheus/modules/Promethee/Resources/views/layout.blade.php'),
  prometheeCss: read('prometheus/public/promethee-assets/promethee-v2.css'),
  prometheeRoutes: read('prometheus/modules/Promethee/routes.php'),
  minitelOperations: read('prometheus/modules/Promethee/Http/MinitelOperationsController.php'),
  hermesClient: read('acars/wwwroot/hermes-minitel.js'),
  hermesCore: read('acars/wwwroot/hermes-minitel-core.js'),
  hermesApp: read('acars/wwwroot/app.js'),
  hermesIndex: read('acars/wwwroot/index.html'),
  hermesThemes: read('acars/wwwroot/hermes-themes.css'),
  hermesDatalink: read('acars/HermesDatalink.cs'),
  flightRecorder: read('acars/FlightRecorder.cs')
};

const checks = [];
function check(id, condition, detail) {
  const ok = Boolean(condition);
  checks.push({ id, status: ok ? 'PASS' : 'FAIL', detail });
  const mark = ok ? '✓' : '✗';
  console[ok ? 'log' : 'error'](mark, id, '-', detail);
  return ok;
}
function containsAll(source, values) {
  return values.every(value => source.includes(value));
}
function absentAll(source, values) {
  return values.every(value => !source.includes(value));
}

check('M7-001-manifest', manifest.lot === 'M7' && manifest.schema_version === 1,
  'machine-readable M7 acceptance contract is versioned');

let assetsSynced = true;
for (const name of manifest.shared_assets) {
  const canonical = read('shared/minitel/' + name);
  for (const target of [
    'prometheus/public/promethee-assets/minitel/' + name,
    'acars/wwwroot/minitel/' + name
  ]) {
    if (read(target) !== canonical) assetsSynced = false;
  }
}
check('M7-002-shared-assets', assetsSynced,
  'Prométhée and Hermès consume byte-identical shared terminal assets');

const keyExpectations = {
  Enter: runtime.ACTIONS.SEND,
  Backspace: runtime.ACTIONS.CORRECT,
  Escape: runtime.ACTIONS.CANCEL,
  Home: runtime.ACTIONS.SUMMARY,
  F1: runtime.ACTIONS.GUIDE,
  F2: runtime.ACTIONS.REPEAT,
  PageUp: runtime.ACTIONS.BACK,
  ArrowUp: runtime.ACTIONS.BACK,
  PageDown: runtime.ACTIONS.NEXT,
  ArrowDown: runtime.ACTIONS.NEXT,
  F10: runtime.ACTIONS.CONNECT_END
};
check('M7-003-keyboard-map',
  Object.entries(keyExpectations).every(([key, action]) => runtime.mapKeyboardEvent(key)?.action === action),
  'all historical PC key mappings remain executable');

const pointerTokens = ["addEventListener('click'", 'onclick', 'pointerdown', 'mousedown', 'touchstart'];
check('M7-004-keyboard-only-business-ui',
  absentAll(sources.prometheeClient, pointerTokens) && absentAll(sources.hermesClient, pointerTokens),
  'Minitel business clients have no pointer/touch event dependency');

check('M7-005-no-hardcoded-remote-api',
  absentAll(sources.prometheeClient, ['https://', 'http://']) && absentAll(sources.hermesClient, ['https://', 'http://']),
  'terminal clients consume relative/local routes or server-provided external URLs only');

check('M7-006-emergency-exit',
  containsAll(sources.shell, ['ai-minitel-shell-exit', 'CTRL+ALT+M', 'EXIT_REASONS.HOTKEY', 'showFallback']),
  'external escape, emergency hotkey and mobile fallback remain outside business navigation');

check('M7-007-accessibility-motion',
  containsAll(sources.renderer, ['role', 'application', 'aria-live', 'prefers-reduced-motion'])
    && sources.runtimeCss.includes('@media(prefers-reduced-motion:reduce)'),
  'terminal keeps application/live-region semantics and reduced-motion handling');

check('M7-008-promethee-pages',
  manifest.promethee.pages.every(page => sources.prometheeClient.includes("MinitelPage('" + page + "'")),
  'all Prométhée M2/M3 terminal pages required by the acceptance manifest exist');

check('M7-009-promethee-actions',
  manifest.promethee.actions.every(action => sources.prometheeClient.includes("'" + action + "'")),
  'reservation → aircraft → briefing → SimBrief → PIREP → dispatch action chain exists');

const getOnlyNames = [
  'bootstrap', 'flights', 'routes', 'fleet', 'pilots', 'calendar', 'profile'
];
check('M7-010-promethee-m2-readonly',
  getOnlyNames.every(name =>
    sources.prometheeRoutes.includes("Route::get('/" + (name === 'bootstrap' ? 'bootstrap' : name))
  ),
  'M2 projection routes remain GET surfaces');

check('M7-011-promethee-qualified-reservation',
  sources.minitelOperations.includes('return $this->operations->searchFlights($request);')
    && sources.prometheeClient.includes('endpoints.operation_search')
    && !sources.prometheeClient.includes("reserve_base + '/' + encodeURIComponent(state.collection"),
  'reservation search delegates to Operations V1 qualification rather than the broad M2 catalogue');

check('M7-012-hermes-pages',
  manifest.hermes.pages.every(page => sources.hermesClient.includes("MinitelPage('" + page + "'")),
  'all Hermès preparation/live/review/recovery pages exist');

check('M7-013-hermes-actions',
  manifest.hermes.actions.every(action => sources.hermesClient.includes("'" + action + "'")),
  'Hermès supports the complete keyboard operational action chain');

check('M7-014-destructive-recovery-guard',
  manifest.hermes.destructive_actions_forbidden.every(action => !sources.hermesClient.includes("'" + action + "'"))
    && sources.hermesIndex.includes('recoveryAbandonBtn'),
  'destructive recovery abandonment stays outside the terminal while remaining available in graphical UI');

const safePreflight = hermesCore.preflightState({
  latest: { onGround: true, parkingBrake: true, enginesRunning: [false, false] }
}, { can_start: true });
const unsafePreflight = hermesCore.preflightState({
  latest: { onGround: false, parkingBrake: false, enginesRunning: [true, true] }
}, { can_start: true });
check('M7-015-hermes-start-gate',
  safePreflight.ready === true && unsafePreflight.ready === false
    && sources.hermesClient.includes("if (!preflight.ready) throw new Error('CONTROLES AVANT DEPART NON SATISFAITS')"),
  'START requires server readiness plus safe local simulator state');

const stressMessages = Array.from({ length: 250 }, (_, index) => ({
  id: 'msg-' + index,
  direction: index % 2 ? 'COCKPIT_TO_OPS' : 'OPS_TO_COCKPIT',
  category: 'OPS',
  priority: 'ROUTINE',
  body: 'MESSAGE ' + index,
  requiresAck: index % 9 === 0,
  status: 'QUEUED',
  localPending: index % 3 === 0
}));
const datalinkStress = hermesCore.datalinkSnapshot({
  operationId: 'op-stress',
  messages: stressMessages,
  pendingOutbound: stressMessages.filter(message => message.localPending).length,
  syncState: 'OFFLINE'
});
check('M7-016-datalink-offline-stress',
  datalinkStress.messages.length === 250
    && datalinkStress.messages.filter(message => message.localPending).length === stressMessages.filter(message => message.localPending).length
    && sources.hermesDatalink.includes('LocalPending')
    && sources.hermesDatalink.includes('PendingOutbound'),
  '250-message Datalink snapshot preserves local-first queue semantics while offline');

check('M7-017-datalink-receipts',
  containsAll(sources.hermesClient, [
    '/api/datalink/read?operation=',
    '/api/datalink/ack?operation=',
    '/api/datalink/send?operation='
  ]),
  'READ, ACK and SEND/REPLY terminal actions remain wired');

check('M7-018-recovery-resume',
  sources.hermesClient.includes('/api/recovery/resume')
    && sources.hermesClient.includes("MinitelPage('recovery'"),
  'interrupted local flights can be resumed from the keyboard terminal');

check('M7-019-pirep-in-gate',
  sources.flightRecorder.includes('Phase == "IN"')
    && sources.hermesClient.includes('/api/file'),
  'final filing remains gated by recorder IN phase');

const longInput = new runtime.MinitelInputBuffer(160);
for (let i = 0; i < 160; i += 1) longInput.append('A');
check('M7-020-long-input',
  longInput.value.length === 160 && longInput.append('B') === false,
  'logical terminal forms support 160-character Datalink input without breaking the 40-column display');

const sparse = new runtime.MinitelScreenBuffer();
sparse.write(2, 3, 'AIR');
check('M7-021-sparse-authentic-transmission',
  runtime.transmissionOperations(sparse.snapshot(), null, { skipDefaultBlank: true }).length === 3,
  'fresh authentic pages model clear-screen + useful-cell transmission rather than 1,000 blank cells');

const mosaic = new runtime.MinitelScreenBuffer();
mosaic.mosaic(1, 1, 63, { foreground: 'blue', separatedMosaic: true });
const mosaicCell = mosaic.snapshot().cells[1][1];
check('M7-022-alphamosaic',
  mosaicCell.attrs.mosaic === true
    && runtime.mosaicBits(mosaicCell.attrs.mosaicMask).every(Boolean)
    && sources.renderer.includes('ai-minitel-mosaic-bit'),
  '2×3 alphamosaic cells are represented in both logical and DOM layers');

check('M7-023-monochrome-luminance',
  sources.runtimeCss.includes('data-display-mode="monochrome"')
    && sources.shell.includes('MONOCHROME / LUMINANCE')
    && runtime.DISPLAY_MODES.monochrome === 'monochrome',
  'logical colors can be rendered as monochrome luminance without page duplication');

check('M7-024-shared-preferences',
  ['airinter-minitel-speed', 'airinter-minitel-display'].every(key =>
    sources.prometheeClient.includes(key) && sources.hermesClient.includes(key)
  ),
  'Prométhée and Hermès share persisted speed/display preferences');

check('M7-025-modern-regression-guard',
  sources.prometheeLayout.includes('value="modern"')
    && sources.hermesIndex.includes('value="modern"')
    && sources.prometheeBase.includes("'modern'")
    && sources.hermesApp.includes("'modern'"),
  'modern mode remains selectable in both products');

check('M7-026-2000-regression-guard',
  sources.prometheeLayout.includes('value="2000"')
    && sources.hermesIndex.includes('value="2000"')
    && sources.prometheeCss.includes('data-era="2000"')
    && sources.hermesThemes.includes('data-era="2000"'),
  'années 2000 mode remains selectable and styled in both products');

check('M7-027-mobile-desktop-boundary',
  runtime.minitelCapability({
    innerWidth: 390,
    matchMedia: query => ({ matches: query.includes('coarse') }),
    navigator: { userAgentData: { mobile: true } }
  }).allowed === false
    && runtime.minitelCapability({
      innerWidth: 1440,
      matchMedia: query => ({ matches: query.includes('fine') || query.includes('hover: hover') }),
      navigator: { userAgentData: { mobile: false } }
    }).allowed === true,
  'terminal remains desktop-only and rejects coarse/mobile capability');

const prometheeChain = [
  'flight-search', 'flights', 'reserve-flight', 'operations', 'select-operation',
  'load-aircraft', 'select-aircraft', 'load-briefing', 'open-simbrief',
  'prefile-pirep', 'load-dispatch'
];
const hermesChain = [
  'login-user', 'login-password', 'search', 'search-results', 'reserve',
  'preparation', 'load-aircraft', 'select-aircraft', 'simbrief',
  'prefile', 'start-flight', 'flight-live', 'datalink', 'review', 'file-pirep'
];
check('M7-028-end-to-end-source-chain',
  prometheeChain.every(token => sources.prometheeClient.includes(token))
    && hermesChain.every(token => sources.hermesClient.includes(token)),
  'source-level acceptance chain covers reservation → preparation → flight → review → final PIREP');

const passed = checks.filter(item => item.status === 'PASS').length;
const failed = checks.length - passed;
const report = {
  generated_at: new Date().toISOString(),
  lot: manifest.lot,
  product: manifest.product,
  summary: {
    total: checks.length,
    passed,
    failed,
    release_ready: failed === 0
  },
  checks,
  manual_acceptance: [
    'Run one real simulator flight from reservation to PIREP in Minitel mode.',
    'Disconnect network during cruise, queue Datalink/telemetry, reconnect and verify flush.',
    'Force-close Hermès during a recorded flight, reopen and verify Recovery Center resume.',
    'Exercise SimBrief account and company-key flows against live service.',
    'Open the same account on a phone with saved Minitel preference and verify touch-safe modern fallback.',
    'Smoke-test Modern and Années 2000 themes after Minitel use.'
  ]
};

if (reportDir) {
  fs.mkdirSync(reportDir, { recursive: true });
  fs.writeFileSync(path.join(reportDir, 'minitel-m7-release.json'), JSON.stringify(report, null, 2) + '\n');
  const markdown = [
    '# Minitel M7 release report',
    '',
    '- Generated: ' + report.generated_at,
    '- Automated checks: ' + passed + '/' + checks.length + ' passed',
    '- Release ready (automated): ' + (failed === 0 ? 'YES' : 'NO'),
    '',
    '## Automated gates',
    '',
    ...checks.map(item => '- [' + (item.status === 'PASS' ? 'x' : ' ') + '] ' + item.id + ' — ' + item.detail),
    '',
    '## Manual acceptance before production',
    '',
    ...report.manual_acceptance.map(item => '- [ ] ' + item),
    ''
  ].join('\n');
  fs.writeFileSync(path.join(reportDir, 'minitel-m7-release.md'), markdown);
}

console.log('\nM7 release gate:', passed + '/' + checks.length, 'checks passed.');
if (failed) {
  console.error(failed + ' release gate(s) failed.');
  process.exitCode = 1;
} else {
  console.log('Automated M7 release gate is GREEN.');
}
