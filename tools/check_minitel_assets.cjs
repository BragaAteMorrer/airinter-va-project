'use strict';

const fs = require('node:fs');
const path = require('node:path');

const root = path.resolve(__dirname, '..');
const names = ['runtime.js', 'renderer.js', 'shell.js', 'minitel-runtime.css', 'minitel-shell.css'];
let failures = 0;

for (const name of names) {
  const canonical = fs.readFileSync(path.join(root, 'shared', 'minitel', name), 'utf8').replace(/\r\n/g, '\n');
  for (const [label, target] of [
    ['Prométhée', path.join(root, 'prometheus', 'public', 'promethee-assets', 'minitel', name)],
    ['Hermès', path.join(root, 'acars', 'wwwroot', 'minitel', name)]
  ]) {
    const publicCopy = fs.readFileSync(target, 'utf8').replace(/\r\n/g, '\n');
    if (canonical !== publicCopy) {
      failures += 1;
      console.error('Minitel asset drift:', label, name);
    } else {
      console.log('✓ synced', label, name);
    }
  }
}

const client = fs.readFileSync(path.join(root, 'prometheus', 'public', 'promethee-assets', 'promethee-minitel.js'), 'utf8');
for (const contract of ['departures', 'flights', 'routes', 'fleet', 'pilots', 'calendar', 'profile', 'operations', 'operation_search']) {
  if (!client.includes(contract)) {
    failures += 1;
    console.error('Missing Prométhée M2 client surface:', contract);
  }
}

const m3Contracts = [
  'reserve-flight',
  'select-aircraft',
  'load-briefing',
  'prefile-pirep',
  'load-dispatch',
  'simbrief-redirect',
  'simbrief-account-import',
  'simbrief-company-session',
  'simbrief-company-import'
];
for (const contract of m3Contracts) {
  if (!client.includes(contract)) {
    failures += 1;
    console.error('Missing Prométhée M3 action:', contract);
  }
}
if (!client.includes('endpoints.operation_search')) {
  failures += 1;
  console.error('M3 reservable flight search must use Operations V1 projection.');
}

const hermesClient = fs.readFileSync(path.join(root, 'acars', 'wwwroot', 'hermes-minitel.js'), 'utf8');
const hermesApp = fs.readFileSync(path.join(root, 'acars', 'wwwroot', 'app.js'), 'utf8');
const hermesIndex = fs.readFileSync(path.join(root, 'acars', 'wwwroot', 'index.html'), 'utf8');

for (const contract of [
  '/api/v1/operations',
  '/api/v1/flights/',
  '/aircraft-eligibility',
  '/simbrief/readiness',
  '/simbrief/redirect',
  '/simbrief/account/import',
  '/simbrief/session',
  '/simbrief/import',
  '/pirep',
  '/dispatch',
  '/api/start',
  '/api/pause',
  '/api/resume',
  '/api/sync',
  '/api/datalink?operation=',
  '/api/datalink/read?operation=',
  '/api/datalink/ack?operation=',
  '/api/datalink/send?operation=',
  '/api/network',
  '/api/review',
  '/api/file',
  '/api/recovery/resume'
]) {
  if (!hermesClient.includes(contract)) {
    failures += 1;
    console.error('Missing Hermès M4/M5 action:', contract);
  }
}

for (const [label, source] of [['Prométhée', client], ['Hermès', hermesClient]]) {
  for (const preferenceKey of ['airinter-minitel-speed', 'airinter-minitel-display']) {
    if (!source.includes(preferenceKey)) {
      failures += 1;
      console.error(label + ' does not persist shared M6 preference:', preferenceKey);
    }
  }
}

const sharedShell = fs.readFileSync(path.join(root, 'shared', 'minitel', 'shell.js'), 'utf8');
const sharedRuntime = fs.readFileSync(path.join(root, 'shared', 'minitel', 'runtime.js'), 'utf8');
const sharedRuntimeCss = fs.readFileSync(path.join(root, 'shared', 'minitel', 'minitel-runtime.css'), 'utf8');
const sharedShellCss = fs.readFileSync(path.join(root, 'shared', 'minitel', 'minitel-shell.css'), 'utf8');

for (const contract of [
  'aspect-ratio:32/25',
  'container-type:size',
  'font-size:clamp(10px,3.15cqh,30px)',
  'grid-template-columns:repeat(40,minmax(0,1fr))'
]) {
  if (!sharedRuntimeCss.includes(contract)) {
    failures += 1;
    console.error('Missing responsive Minitel CRT contract:', contract);
  }
}
for (const contract of [
  '--mt-chassis-size:min(94vw,calc(100dvh - 92px),1040px)',
  'aspect-ratio:1/1',
  'max-width:100%',
  'max-height:100%'
]) {
  if (!sharedShellCss.includes(contract)) {
    failures += 1;
    console.error('Missing responsive Minitel chassis contract:', contract);
  }
}
for (const forbidden of ['aspect-ratio:40/25', 'width:min(100%,720px)', 'font-size:clamp(12px,2.1vw,24px)']) {
  if (sharedRuntimeCss.includes(forbidden) || sharedShellCss.includes(forbidden)) {
    failures += 1;
    console.error('Legacy squashed Minitel layout returned:', forbidden);
  }
}
for (const contract of ['__system_settings', 'AUTHENTIQUE  1200/75', 'MONOCHROME / LUMINANCE', 'writeAirInterMosaic']) {
  if (!sharedShell.includes(contract)) {
    failures += 1;
    console.error('Missing M6 shell fidelity contract:', contract);
  }
}
for (const contract of ['mosaicMask', 'separatedMosaic', 'DISPLAY_MODES', 'MAX_INPUT_LENGTH']) {
  if (!sharedRuntime.includes(contract)) {
    failures += 1;
    console.error('Missing M6 runtime fidelity contract:', contract);
  }
}

const sharedRenderer = fs.readFileSync(path.join(root, 'shared', 'minitel', 'renderer.js'), 'utf8');
const loginCss = fs.readFileSync(path.join(root, 'prometheus', 'public', 'promethee-assets', 'login.css'), 'utf8');
const loginJs = fs.readFileSync(path.join(root, 'prometheus', 'public', 'promethee-assets', 'login.js'), 'utf8');

for (const contract of [
  "setAttribute('tabindex', '0')",
  "addEventListener('keydown', this.onKeyDown, true)",
  "addEventListener('pointerdown'"
]) {
  if (!sharedRenderer.includes(contract)) {
    failures += 1;
    console.error('Missing Minitel keyboard/focus contract:', contract);
  }
}

for (const contract of ['#001cff', '#ff2028', '#39ff4a']) {
  if (!sharedRuntimeCss.includes(contract) || !loginCss.includes(contract)) {
    failures += 1;
    console.error('Missing colorful Videotex palette contract:', contract);
  }
}

for (const contract of ["event.code==='NumpadEnter'", "event.key==='F10'||event.key==='End'", "TAPEZ 3615 AIR INTER PUIS ENVOI"]) {
  if (!loginJs.includes(contract)) {
    failures += 1;
    console.error('Missing Minitel login keyboard contract:', contract);
  }
}

for (const [label, source] of [['Prométhée', client], ['Hermès', hermesClient]]) {
  for (const contract of ['titleBand', 'noticeBand', "background: 'green'"]) {
    if (!source.includes(contract)) {
      failures += 1;
      console.error(label + ' is missing directory-style Videotex UI contract:', contract);
    }
  }
}

if (!hermesApp.includes("['modern', '2000', 'minitel']")) {
  failures += 1;
  console.error('Hermès Minitel era is not enabled in app.js.');
}
if (!hermesIndex.includes('value="minitel"') || !hermesIndex.includes('/hermes-minitel.js')) {
  failures += 1;
  console.error('Hermès Minitel selector/assets are not mounted in index.html.');
}

if (failures) process.exitCode = 1;
for (const page of ['flight-live', 'datalink', 'journal', 'network', 'review', 'review-list', 'recovery']) {
  if (!hermesClient.includes("MinitelPage('" + page + "'")) {
    failures += 1;
    console.error('Missing Hermès M5 page:', page);
  }
}

if (failures) process.exitCode = 1;
else console.log('\nMinitel M0-M6 shared/public, operational and fidelity contracts are synchronized.');
