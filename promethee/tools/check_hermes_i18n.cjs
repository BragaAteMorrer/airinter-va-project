const fs = require('fs');
const path = require('path');

global.window = {};
require(path.resolve(__dirname, '../acars/wwwroot/i18n.js'));

const i18n = global.window.HermesI18n;
const expected = ['fr', 'en', 'pt', 'es', 'it', 'ja', 'tr', 'de'];

if (!i18n) throw new Error('HermesI18n was not initialized');
if (JSON.stringify(i18n.supportedLanguages) !== JSON.stringify(expected)) {
  throw new Error('Unexpected Hermès language list: ' + JSON.stringify(i18n.supportedLanguages));
}

const referenceKeys = Object.keys(i18n.messages.fr).sort();
for (const locale of expected) {
  const catalogue = i18n.messages[locale];
  if (!catalogue) throw new Error(`Missing catalogue: ${locale}`);
  const keys = Object.keys(catalogue).sort();
  const missing = referenceKeys.filter(key => !keys.includes(key));
  const empty = referenceKeys.filter(key => String(catalogue[key] ?? '').trim() === '');
  if (missing.length || empty.length) {
    throw new Error(`${locale}: missing=[${missing.join(', ')}] empty=[${empty.join(', ')}]`);
  }
}

const index = fs.readFileSync(path.resolve(__dirname, '../acars/wwwroot/index.html'), 'utf8');
for (const locale of expected) {
  if (!index.includes(`value="${locale}"`)) {
    throw new Error(`Hermès language selector is missing ${locale}`);
  }
}

console.log(`Hermès i18n OK: ${expected.join(', ')} · ${referenceKeys.length} shared keys`);
