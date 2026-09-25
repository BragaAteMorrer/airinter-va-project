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
  '/api/start'
]) {
  if (!hermesClient.includes(contract)) {
    failures += 1;
    console.error('Missing Hermès M4 action:', contract);
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
else console.log('\nMinitel M0-M4 shared/public and operational contracts are synchronized.');
