(() => {
  'use strict';
  const $ = selector => document.querySelector(selector);
  const rawId = (value, max) => {
    const input = String(value ?? '').trim();
    return new RegExp('^[0-9]{1,' + max + '}$').test(input) ? input : '';
  };
  let network = null;
  let pilotId = null;
  const imported = new Map();

  function status(message) {
    const node = $('#networkIdsInfo');
    if (node) node.textContent = message;
  }

  function clearImported() {
    for (const [selector, value] of imported) {
      const input = $(selector);
      if (input && input.value === value) input.value = '';
    }
    imported.clear();
  }

  function copyFromProfile(overwrite = false) {
    if (!network) return;
    let changed = 0;
    for (const [selector, value] of [
      ['#vatsimCid', network.vatsim],
      ['#ivaoVid', network.ivao]
    ]) {
      const node = $(selector);
      if (!node || !value || (!overwrite && node.value.trim())) continue;
      node.value = value;
      imported.set(selector, value);
      changed++;
    }
    status(changed
      ? 'CID/VID récupérés depuis Prométhée. Enregistrez vos réglages pour les conserver.'
      : network.vatsim || network.ivao
        ? 'Identifiants disponibles depuis Prométhée. Les valeurs locales sont conservées.'
        : 'Aucun CID/VID renseigné dans votre profil Prométhée.');
  }

  function apply(user) {
    if (!user || typeof user !== 'object') return;
    const nextPilot = String(user.id ?? '');
    if (pilotId !== null && nextPilot !== pilotId) clearImported();
    pilotId = nextPilot;
    const ids = user.network_ids || user.networkIds || {};
    network = {
      vatsim: rawId(ids.vatsim_cid ?? ids.vatsimCid, 10),
      ivao: rawId(ids.ivao_vid ?? ids.ivaoVid, 12)
    };
    const button = $('#importNetworkIdsBtn');
    if (button) button.disabled = !(network.vatsim || network.ivao);
    copyFromProfile();
  }

  function clear() {
    clearImported();
    pilotId = null;
    network = null;
    const button = $('#importNetworkIdsBtn');
    if (button) button.disabled = true;
    status('Connectez-vous pour récupérer vos identifiants réseau.');
  }

  function initialize(call, errorHandler) {
    $('#importNetworkIdsBtn')?.addEventListener('click', () => copyFromProfile(true));
    $('#simbriefPilotHelpBtn')?.addEventListener('click', async () => {
      try {
        await call('/api/open-external', {
          url: 'https://www.simbrief.com/system/profile.php#settings'
        });
      } catch (error) {
        errorHandler?.(error, 'Impossible d’ouvrir vos paramètres SimBrief.', 'SimBrief');
      }
    });
  }

  window.HermesAccountSync = { apply, clear, initialize };
})();
