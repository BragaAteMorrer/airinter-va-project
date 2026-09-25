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
for (const contract of ['departures', 'flights', 'routes', 'fleet', 'pilots', 'calendar', 'profile']) {
  if (!client.includes(contract)) {
    failures += 1;
    console.error('Missing Prométhée M2 client surface:', contract);
  }
}

if (failures) process.exitCode = 1;
else console.log('\nMinitel shared/public assets are synchronized.');
