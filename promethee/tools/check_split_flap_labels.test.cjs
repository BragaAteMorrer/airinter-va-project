#!/usr/bin/env node
'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

// Exercise the real production formatter without requiring a browser DOM.
const runtime = fs.readFileSync(path.join(__dirname, '../public/promethee-assets/promethee.js'), 'utf8');
const start = runtime.indexOf('  const flapAlphabet = ');
const end = runtime.indexOf('  const flapMarkup = ', start);
assert.ok(start !== -1 && end > start, 'Split-flap formatter not found in production script');
const script = runtime.slice(start, end);
const formatting = vm.runInNewContext(script + '\n({ flapNormalise, canAnimateFlap, flapFit });', {
  window: { matchMedia: () => ({ matches: false }) },
});

test('a French destination with parentheses stays in mechanical split-flap mode', () => {
  const original = 'PAU/PYRENEES (UZEIN)';
  assert.equal(formatting.canAnimateFlap(original), true);
  assert.equal(formatting.flapFit(original, 18), 'PAU/PYRENEES UZEIN');
});

test('French diacritics and typographic separators fit wheel alphabet', () => {
  const text = 'SAINT-ÉTIENNE (LOIRE)';
  assert.equal(formatting.canAnimateFlap(text), true);
  assert.equal(formatting.flapFit(text, 18), 'SAINT-ETIENNE LOIR');
});

test('non-Latin scripts still use the Unicode accessible fallback', () => {
  assert.equal(formatting.canAnimateFlap('東京'), false);
  assert.equal(formatting.canAnimateFlap('ATHÈNES'), true);
});

test('fitted text always has exact wheel width and safe characters', () => {
  const fitted = formatting.flapFit('Tarbes–Lourdes (FR)', 18);
  assert.equal(fitted.length, 18);
  assert.match(fitted, /^[ A-Z0-9.:'%\/-]+$/);
});
