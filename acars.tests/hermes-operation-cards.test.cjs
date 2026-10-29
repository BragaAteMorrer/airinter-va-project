const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const source = fs.readFileSync(path.resolve(__dirname, '../acars/wwwroot/hermes-operations.js'), 'utf8');

// Operation cards are a collection. The single-node $ helper must not be
// passed to forEach; $$ is the project's querySelectorAll helper.
const match = source.match(/function updateOperationCardStates\(\)\s*\{([\s\S]*?)\n\}/);
assert.ok(match, 'Operation card status updater must exist');
assert.match(match[1], /\$\$\('\.operation\[data-flight-id\]'\)\.forEach\(/);
assert.doesNotMatch(match[1], /(?<!\$)\$\('\.operation\[data-flight-id\]'\)\.forEach\(/);
console.log('Hermes operation card selector regression passed.');
