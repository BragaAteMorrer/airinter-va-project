(() => {
  'use strict';

  const mt = window.AirInterMinitel;
  if (!mt) return;

  const SESSION_DISABLE_KEY = 'promethee-minitel-session-disabled';
  const ERA_KEY = 'promethee-era';

  const state = {
    bootstrap: null,
    query: '',
    page: 1,
    lastPage: 1,
    collection: null,
    loading: false,
    error: null
  };

  let shell;
  let session;
  let renderer;
  let keyboard;
  let host;

  const normalise = (value) => String(value ?? '')
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^ -~]/g, ' ')
    .toUpperCase();

  const fit = (value, width) => normalise(value).slice(0, width).padEnd(width, ' ');

  const writeStatus = (screen, identity, service = '3615 AIRINTER') => {
    screen.write(0, 0, mt.buildServiceLine({
      service,
      identity: identity || state.bootstrap?.pilot?.pilot_id || '',
      state: 'C'
    }), { foreground: 'cyan' });
  };

  const writeFooter = (screen, pagination = false) => {
    if (pagination) screen.write(22, 1, 'RETOUR/↑ PAGE-    SUITE/↓ PAGE+', { foreground: 'cyan' });
    screen.write(23, 0, 'GUIDE SOMMAIRE RETOUR SUITE      ENVOI', { foreground: 'cyan' });
  };

  const action = (type, payload = {}) => {
    if (!session) return;
    session.context.__prometheeMinitelAction = { type, ...payload };
  };

  const json = async (url) => {
    const response = await fetch(url, {
      credentials: 'same-origin',
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (!response.ok) {
      const error = new Error('HTTP ' + response.status);
      error.status = response.status;
      throw error;
    }
    return response.json();
  };

  const pageUrl = (base, params = {}) => {
    const url = new URL(base, window.location.origin);
    Object.entries(params).forEach(([key, value]) => {
      if (value !== '' && value !== null && value !== undefined) url.searchParams.set(key, value);
    });
    return url.toString();
  };

  const updateCursor = () => {
    if (!renderer || !session) return;
    const page = session.currentPageId;
    if (page === 'home') return renderer.showCursor(18, Math.min(39, 16 + session.input.value.length), true);
    if (['flight-search', 'fleet-search', 'pilot-search'].includes(page)) {
      return renderer.showCursor(9, Math.min(39, 4 + session.input.value.length), true);
    }
    renderer.showCursor(0, 0, false);
  };

  const showSnapshot = async (snapshot, replay = true) => {
    await renderer.render(snapshot, { replay });
    updateCursor();
  };

  const registerPages = () => {
    session.register(new mt.MinitelPage('home', {
      onRender: (_context, screen, current) => {
        writeStatus(screen);
        screen.write(2, 11, 'AIR INTER', { foreground: 'yellow' });
        screen.write(4, 7, 'CENTRE DES OPERATIONS');
        screen.write(6, 2, '1 DEPARTS / MOUVEMENTS', { foreground: 'cyan' });
        screen.write(7, 2, '2 RECHERCHER UN VOL', { foreground: 'cyan' });
        screen.write(8, 2, '3 FLOTTE', { foreground: 'cyan' });
        screen.write(9, 2, '4 PILOTES', { foreground: 'cyan' });
        screen.write(10, 2, '5 MON DOSSIER', { foreground: 'cyan' });
        screen.write(12, 2, 'VOLS   ' + fit(state.bootstrap?.stats?.flights, 6));
        screen.write(13, 2, 'PILOTES' + fit(state.bootstrap?.stats?.pilots, 6));
        screen.write(14, 2, 'EN VOL ' + fit(state.bootstrap?.stats?.active, 6));
        screen.write(15, 2, 'AUJ.   ' + fit(state.bootstrap?.stats?.today, 6));
        screen.write(18, 2, 'VOTRE CHOIX : ' + current.input.value, { foreground: 'yellow' });
        writeFooter(screen);
      },
      acceptInput: (key) => /^[1-5]$/.test(key),
      send: (value) => {
        const targets = { '1': 'departures', '2': 'flight-search', '3': 'fleet-search', '4': 'pilot-search', '5': 'profile' };
        if (targets[value]) action('open', { target: targets[value], page: 1 });
      }
    }));

    for (const [id, title, hint, target] of [
      ['flight-search', 'RECHERCHE VOL', 'VOL / DEPART / ARRIVEE', 'flights'],
      ['fleet-search', 'RECHERCHE FLOTTE', 'IMMATRICULATION / TYPE', 'fleet'],
      ['pilot-search', 'RECHERCHE PILOTE', 'MATRICULE / NOM', 'pilots']
    ]) {
      session.register(new mt.MinitelPage(id, {
        onRender: (_context, screen, current) => {
          writeStatus(screen);
          screen.write(3, 2, title, { foreground: 'yellow' });
          screen.write(6, 2, hint);
          screen.write(9, 2, '> ' + current.input.value, { foreground: 'cyan' });
          screen.write(13, 2, 'ENVOI : RECHERCHER');
          screen.write(14, 2, 'ANNULATION : EFFACER');
          writeFooter(screen);
        },
        acceptInput: (key) => /^[A-Za-z0-9 ._/-]$/.test(key),
        send: (value) => action('search', { target, query: value.trim(), page: 1 })
      }));
    }

    session.register(new mt.MinitelPage('departures', {
      onRender: (_context, screen) => {
        writeStatus(screen);
        screen.write(2, 1, 'DEPARTS / MOUVEMENTS', { foreground: 'yellow' });
        if (state.loading) return screen.write(8, 7, 'CHARGEMENT EN COURS...', { foreground: 'cyan' });
        if (state.error) return renderError(screen);
        screen.write(4, 1, 'VOL      DEP   H.    DEST   H.   ETAT', { foreground: 'cyan' });
        (state.collection?.items || []).slice(0, 7).forEach((flight, index) => {
          const row = 6 + index * 2;
          screen.write(row, 1, fit(flight.flight, 8) + ' ' + fit(flight.departure, 4) + ' ' + fit(flight.departure_time, 5) + ' ' + fit(flight.destination, 6) + ' ' + fit(flight.arrival_time, 5));
          screen.write(row + 1, 10, fit(flight.status_label, 28), { foreground: statusColour(flight.status) });
        });
        if (!(state.collection?.items || []).length) screen.write(8, 5, 'AUCUN MOUVEMENT PREVU');
        screen.write(21, 1, 'F2 REPETITION = RAFRAICHIR');
        writeFooter(screen);
      },
      repeat: (_context, refresh) => {
        if (refresh) action('refresh', { target: 'departures' });
      }
    }));

    session.register(new mt.MinitelPage('flights', {
      onRender: (_context, screen) => renderListHeader(screen, 'PROGRAMME DES VOLS', 'VOL      DEP   H.    ARR   H.', (item, row) => {
        screen.write(row, 1, fit(item.ident, 8) + ' ' + fit(item.departure, 4) + ' ' + fit(item.departure_time || '--:--', 5) + ' ' + fit(item.arrival, 4) + ' ' + fit(item.arrival_time || '--:--', 5));
        screen.write(row + 1, 3, fit((item.departure_name || '') + ' > ' + (item.arrival_name || ''), 35), { foreground: 'cyan' });
      }),
      next: () => paginate('flights', 1),
      previous: () => pageBack('flights', 'flight-search')
    }));

    session.register(new mt.MinitelPage('fleet', {
      onRender: (_context, screen) => renderListHeader(screen, 'FLOTTE AIR INTER', 'IMMATR.   TYPE   BASE   ETAT', (item, row) => {
        screen.write(row, 1, fit(item.registration, 10) + ' ' + fit(item.icao, 6) + ' ' + fit(item.airport || '---', 5) + ' ' + fit(item.status, 14));
        screen.write(row + 1, 3, fit(item.subfleet || item.airline || '', 35), { foreground: 'cyan' });
      }),
      next: () => paginate('fleet', 1),
      previous: () => pageBack('fleet', 'fleet-search')
    }));

    session.register(new mt.MinitelPage('pilots', {
      onRender: (_context, screen) => renderListHeader(screen, 'ANNUAIRE PILOTES', 'MATRICULE  NOM', (item, row) => {
        screen.write(row, 1, fit(item.pilot_id, 10) + ' ' + fit(item.name, 27));
        screen.write(row + 1, 3, fit((item.rank || 'PILOTE') + (item.home_airport ? ' / ' + item.home_airport : ''), 35), { foreground: 'cyan' });
      }),
      next: () => paginate('pilots', 1),
      previous: () => pageBack('pilots', 'pilot-search')
    }));

    session.register(new mt.MinitelPage('profile', {
      onRender: (_context, screen) => {
        writeStatus(screen);
        screen.write(2, 2, 'MON DOSSIER PILOTE', { foreground: 'yellow' });
        if (state.loading) return screen.write(8, 7, 'CHARGEMENT EN COURS...', { foreground: 'cyan' });
        if (state.error) return renderError(screen);
        const pilot = state.collection?.pilot || {};
        const hours = Math.floor((Number(pilot.flight_time) || 0) / 60);
        const minutes = String((Number(pilot.flight_time) || 0) % 60).padStart(2, '0');
        screen.write(5, 2, 'MATRICULE...... ' + fit(pilot.pilot_id, 20));
        screen.write(6, 2, 'NOM............ ' + fit(pilot.name, 20));
        screen.write(7, 2, 'GRADE.......... ' + fit(pilot.rank || 'PILOTE', 20));
        screen.write(8, 2, 'COMPAGNIE...... ' + fit(pilot.airline || 'AIR INTER', 20));
        screen.write(9, 2, 'BASE........... ' + fit(pilot.home_airport || '---', 20));
        screen.write(10, 2, 'POSITION....... ' + fit(pilot.current_airport || '---', 20));
        screen.write(12, 2, 'VOLS VALIDES... ' + fit(pilot.accepted_pireps, 8));
        screen.write(13, 2, 'RESERVATIONS... ' + fit(pilot.bookings, 8));
        screen.write(14, 2, 'TEMPS DE VOL... ' + fit(hours + 'H' + minutes, 8));
        if (pilot.vatsim_id) screen.write(16, 2, 'VATSIM......... ' + fit(pilot.vatsim_id, 16));
        if (pilot.ivao_id) screen.write(17, 2, 'IVAO........... ' + fit(pilot.ivao_id, 16));
        writeFooter(screen);
      }
    }));
  };

  const renderListHeader = (screen, title, columns, renderItem) => {
    writeStatus(screen);
    screen.write(2, 1, title, { foreground: 'yellow' });
    if (state.loading) {
      screen.write(8, 7, 'CHARGEMENT EN COURS...', { foreground: 'cyan' });
      return;
    }
    if (state.error) return renderError(screen);
    screen.write(4, 1, columns, { foreground: 'cyan' });
    const items = state.collection?.items || [];
    items.slice(0, 7).forEach((item, index) => renderItem(item, 6 + index * 2));
    if (!items.length) screen.write(8, 7, 'AUCUN RESULTAT');
    const pagination = state.collection?.pagination || {};
    screen.write(20, 1, 'PAGE ' + fit(pagination.page || 1, 3) + '/' + fit(pagination.last_page || 1, 3) + '  TOTAL ' + fit(pagination.total || 0, 6), { foreground: 'yellow' });
    writeFooter(screen, true);
  };

  const renderError = (screen) => {
    screen.write(7, 10, '*** ERREUR ***', { foreground: 'red', blink: true });
    screen.write(10, 2, fit(state.error || 'SERVICE INDISPONIBLE', 36));
    screen.write(13, 2, 'F2 : REESSAYER');
    writeFooter(screen);
  };

  const statusColour = (status) => {
    const key = String(status || '').toLowerCase();
    if (key.includes('delay') || key.includes('retard')) return 'red';
    if (key.includes('board') || key.includes('embar')) return 'yellow';
    if (key.includes('cancel') || key.includes('annul')) return 'red';
    return 'green';
  };

  const paginate = (target, delta) => {
    const pagination = state.collection?.pagination || {};
    const next = Math.max(1, Math.min(Number(pagination.last_page || 1), Number(pagination.page || 1) + delta));
    if (next !== Number(pagination.page || 1)) action('search', { target, query: state.query, page: next });
  };

  const pageBack = (target, searchPage) => {
    const pagination = state.collection?.pagination || {};
    if (Number(pagination.page || 1) > 1) return paginate(target, -1);
    return searchPage;
  };

  const loadTarget = async (target, options = {}) => {
    state.loading = true;
    state.error = null;
    state.query = options.query ?? state.query ?? '';
    state.page = options.page || 1;

    let pageId = target;
    if (target === 'flight-search' || target === 'fleet-search' || target === 'pilot-search') {
      state.loading = false;
      state.query = '';
      state.collection = null;
      return showSnapshot(session.go(target));
    }

    showSnapshot(session.go(pageId), false);

    try {
      if (target === 'departures') {
        const data = await json(state.bootstrap.endpoints.departures);
        state.collection = { items: data.flights || [] };
      } else if (target === 'profile') {
        state.collection = await json(state.bootstrap.endpoints.profile);
      } else if (['flights', 'fleet', 'pilots'].includes(target)) {
        state.collection = await json(pageUrl(state.bootstrap.endpoints[target], {
          q: state.query,
          page: state.page
        }));
      } else {
        throw new Error('PAGE INCONNUE');
      }
      state.loading = false;
      await showSnapshot(session.render(), true);
    } catch (error) {
      state.loading = false;
      state.error = error.status ? 'SERVICE HTTP ' + error.status : 'LIAISON PROMETHEE INTERROMPUE';
      await showSnapshot(session.render(), true);
    }
  };

  const handleAction = async () => {
    const pending = session.context.__prometheeMinitelAction;
    if (!pending) return;
    session.context.__prometheeMinitelAction = null;

    if (pending.type === 'open') return loadTarget(pending.target, pending);
    if (pending.type === 'search') return loadTarget(pending.target, pending);
    if (pending.type === 'refresh') return loadTarget(pending.target, { query: state.query, page: state.page });
  };

  const exitMinitel = (reason) => {
    try {
      if (reason === mt.EXIT_REASONS.MOBILE) {
        sessionStorage.setItem(SESSION_DISABLE_KEY, '1');
      } else {
        localStorage.setItem(ERA_KEY, 'modern');
        sessionStorage.removeItem(SESSION_DISABLE_KEY);
      }
    } catch (_) {}
    window.location.reload();
  };

  const shouldStart = () => {
    if (document.documentElement.dataset.era !== 'minitel') return false;
    if (!document.documentElement.dataset.minitelBootstrap) return false;
    try {
      if (sessionStorage.getItem(SESSION_DISABLE_KEY) === '1') return false;
    } catch (_) {}
    return true;
  };

  const start = async () => {
    if (!shouldStart()) return;

    host = document.createElement('div');
    host.id = 'promethee-minitel-root';
    host.className = 'promethee-minitel-overlay';
    document.body.appendChild(host);

    shell = new mt.MinitelShell({
      host,
      service: '3615 AIRINTER',
      product: 'PROMETHEE',
      identity: '',
      onExit: exitMinitel
    });

    const capability = shell.capability();
    if (!capability.allowed) {
      shell.showFallback(capability.reason || 'unsupported');
      return;
    }

    shell.mount();
    renderer = new mt.MinitelDomRenderer(shell.terminalNode, { speed: 'fast' });

    try {
      const bootstrapUrl = document.documentElement.dataset.minitelBootstrap;
      state.bootstrap = await json(bootstrapUrl);
      session = new mt.MinitelSession({
        homePageId: 'home',
        speed: 'fast',
        context: {}
      });
      registerPages();
      keyboard = new mt.MinitelKeyboardController(session, renderer, document, {
        afterDispatch: () => { handleAction(); }
      });
      shell.session = session;
      shell.renderer = renderer;
      shell.keyboard = keyboard;
      shell.identity = state.bootstrap.pilot?.pilot_id || '';
      await shell.start();
      updateCursor();
    } catch (error) {
      shell.ensureEscapeVisible();
      shell.terminalNode.innerHTML = '<div class="ai-minitel-fatal">PROMETHEE / MINITEL<br>ERREUR DE CONNEXION<br><br>UTILISEZ « QUITTER LE MODE MINITEL »</div>';
      console.error('[Promethee Minitel]', error);
    }
  };

  window.addEventListener('DOMContentLoaded', start, { once: true });
})();
