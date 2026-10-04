(() => {
  'use strict';

  const $ = selector => document.querySelector(selector);
  const unwrap = value => value?.data ?? value;
  let accountUrl = null;

  function clear() {
    accountUrl = null;
    const provider = $('#identityProvider');
    const account = $('#argosAccountBtn');
    const logout = $('#logoutBtn');
    if (provider) {
      provider.hidden = true;
      provider.classList.remove('sso');
      provider.textContent = '';
    }
    if (account) account.hidden = true;
    if (logout) logout.hidden = true;
  }

  function apply(value) {
    const root = unwrap(value) ?? {};
    const user = root?.user ?? root ?? {};
    const first = user.first_name || user.firstname || user.firstName || '';
    const last = user.last_name || user.lastname || user.lastName || '';
    const name = [first, last].filter(Boolean).join(' ') || user.name || user.name_private || '';
    const callsign = user.ident || user.pilot_id || user.pilotId || '';
    const serverState = $('#serverState');
    if (serverState) {
      serverState.textContent = name && callsign
        ? `${name} · ${callsign}`
        : name || callsign || 'Pilote connecté';
    }

    const auth = user.auth || root.auth || {};
    const providerName = String(auth.provider || root.provider || '').toLowerCase();
    const provider = $('#identityProvider');
    if (provider) {
      provider.textContent = providerName === 'argos'
        ? 'ARGOS · SSO'
        : (providerName === 'promethee' ? 'PROMÉTHÉE · SECOURS' : 'AIR INTER');
      provider.classList.toggle('sso', providerName === 'argos');
      provider.hidden = false;
    }

    const rawAccountUrl = auth.account_url || auth.accountUrl || null;
    accountUrl = typeof rawAccountUrl === 'string'
      && /^https:\/\/argos\.airinter-va\.org\/account(?:[/?#]|$)/i.test(rawAccountUrl)
        ? rawAccountUrl
        : null;

    const account = $('#argosAccountBtn');
    const logout = $('#logoutBtn');
    if (account) account.hidden = !accountUrl;
    if (logout) logout.hidden = false;
  }

  function initialize(call, registerError) {
    const account = $('#argosAccountBtn');
    if (!account) return;
    account.onclick = async () => {
      if (!accountUrl) return;
      try {
        await call('/api/open-external', { url: accountUrl });
      } catch (error) {
        registerError?.(error, 'Impossible d’ouvrir votre compte Air Inter.', 'Compte Argos');
      }
    };
  }

  window.HermesIdentity = { apply, clear, initialize };
})();
