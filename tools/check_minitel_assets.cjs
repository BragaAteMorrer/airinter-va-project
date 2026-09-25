'use strict';

const fs = require('node:fs');
const path = require('node:path');

const root = path.resolve(__dirname, '..');
const names = ['runtime.js', 'renderer.js', 'shell.js', 'minitel-runtime.css', 'minitel-shell.css'];
let failures = 0;

for (const name of names) {
  const canonical = fs.readFileSync(path.join(root, 'shared', 'minitel', name), 'utf8').replace(/\r\n/g, '\n');
  const publicCopy = fs.readFileSync(path.join(root, 'prometheus', 'public', 'promethee-assets', 'minitel', name), 'utf8').replace(/\r\n/g, '\n');
  if (canonical !== publicCopy) {
    failures += 1;
    console.error('Minitel asset drift:', name);
  } else {
    console.log('✓ synced', name);
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

if (failures) process.exitCode = 1;
else console.log('\nMinitel M0-M3 shared/public and operational contracts are synchronized.');
