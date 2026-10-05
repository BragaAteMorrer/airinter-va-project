(() => {
  'use strict';

  const mt = window.AirInterMinitel;
  if (!mt) return;

  const SESSION_DISABLE_KEY = 'promethee-minitel-session-disabled';
  const ERA_KEY = 'promethee-era';
  const MINITEL_SPEED_KEY = 'airinter-minitel-speed';
  const MINITEL_DISPLAY_KEY = 'airinter-minitel-display';

  const readMinitelPreferences = () => {
    let speed = 'fast';
    let displayMode = 'monochrome';
    try {
      const storedSpeed = localStorage.getItem(MINITEL_SPEED_KEY);
      const storedDisplay = localStorage.getItem(MINITEL_DISPLAY_KEY);
      if (storedSpeed && mt.SPEEDS[storedSpeed]) speed = storedSpeed;
      if (storedDisplay && mt.DISPLAY_MODES[storedDisplay]) displayMode = storedDisplay;
    } catch (_) {}
    return { speed, displayMode };
  };

  const persistMinitelPreferences = (preferences) => {
    try {
      if (mt.SPEEDS[preferences.speed]) localStorage.setItem(MINITEL_SPEED_KEY, preferences.speed);
      if (mt.DISPLAY_MODES[preferences.displayMode]) localStorage.setItem(MINITEL_DISPLAY_KEY, preferences.displayMode);
    } catch (_) {}
  };

  const state = {
    bootstrap: null,
    query: '',
    page: 1,
    collection: null,
    loading: false,
    error: null,
    operation: null,
    aircraft: null,
    briefing: null,
    dispatch: null,
    result: null,
    simbriefApiState: null,
    operationPage: 1
  };

  let shell;
  let session;
  let renderer;
  let keyboard;
  let host;

  const normalise = (value) => String(value ?? '')
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^\x20-\x7E]/g, ' ')
    .toUpperCase();

  const fit = (value, width) => normalise(value).slice(0, width).padEnd(width, ' ');
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

  const fillRow = (screen, row, background = 'black', foreground = 'white') => {
    screen.fill(row, 0, 39, ' ', { background, foreground });
  };

  const writeBand = (screen, row, text, background = 'blue', foreground = 'white') => {
    fillRow(screen, row, background, foreground);
    screen.write(row, 1, fit(text, 38), { background, foreground });
  };

  const titleBand = (screen, title, subtitle = '') => {
    // Télétel composition: semigraphic identity + sparse text, not a web-style full-width header.
    mt.writeAirInterMosaic(screen, 2, 1, { foreground: 'blue', separatedMosaic: true });
    screen.write(2, 12, fit('AIR INTER', 27), { foreground: 'yellow' });
    screen.write(3, 12, fit(title, 27), { foreground: 'cyan' });
    screen.write(4, 12, fit(subtitle || 'SERVICE TELEMATIQUE', 27), { foreground: 'white' });
  };

  const noticeBand = (screen, row, text, background = 'red', foreground = 'white') => {
    writeBand(screen, row, text, background, foreground);
  };

  const menuLine = (screen, row, number, label, accent = false) => {
    screen.write(row, 2, String(number), { foreground: accent ? 'yellow' : 'cyan' });
    screen.write(row, 4, '- ' + fit(label, 33), { foreground: accent ? 'yellow' : 'white' });
  };

  const writeStatus = (screen, identity, service = '3615 AIRINTER') => {
    screen.write(0, 0, mt.buildServiceLine({
      service,
      identity: identity || state.bootstrap?.pilot?.pilot_id || '',
      state: 'C'
    }), { foreground: 'cyan', background: 'black' });
  };

  const writeFooter = (screen, pagination = false) => {
    // Historical commands belong to the Minitel keyboard. The raster only
    // exposes context that a Télétel service would have needed to repeat.
    if (pagination) {
      screen.write(24, 1, 'RETOUR', { foreground: 'cyan' });
      screen.write(24, 32, 'SUITE', { foreground: 'cyan' });
    }
  };

  const promptLine = (screen, row, value = '', label = 'VOTRE CHOIX') => {
    screen.write(row, 4, fit(label, 14) + ' : ' + String(value ?? ''), { foreground: 'yellow' });
  };

  const pageLine = (screen, row, page, pages, total = null) => {
    const left = 'PAGE ' + page + '/' + pages;
    const right = total === null ? '' : 'TOTAL ' + total;
    screen.write(row, 2, fit(left, 16), { foreground: 'cyan' });
    if (right) screen.write(row, 27, fit(right, 11), { foreground: 'cyan' });
  };

  const action = (type, payload = {}) => {
    if (!session) return;
    session.context.__prometheeMinitelAction = { type, ...payload };
  };

  const requestJson = async (url, options = {}) => {
    const method = String(options.method || 'GET').toUpperCase();
    const headers = {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(options.headers || {})
    };
    if (method !== 'GET' && method !== 'HEAD') {
      headers['Content-Type'] = 'application/json';
      headers['X-CSRF-TOKEN'] = csrf();
    }

    const response = await fetch(url, {
      credentials: 'same-origin',
      method,
      headers,
      body: options.body === undefined ? undefined : JSON.stringify(options.body)
    });

    let payload = null;
    try { payload = await response.json(); } catch (_) {}

    if (!response.ok) {
      const error = new Error(payload?.message || ('HTTP ' + response.status));
      error.status = response.status;
      error.payload = payload;
      throw error;
    }
    return payload;
  };

  const pageUrl = (base, params = {}) => {
    const url = new URL(base, window.location.origin);
    Object.entries(params).forEach(([key, value]) => {
      if (value !== '' && value !== null && value !== undefined) url.searchParams.set(key, value);
    });
    return url.toString();
  };

  const operationUrl = (operation, suffix = '') =>
    state.bootstrap.endpoints.operation_base + '/' + encodeURIComponent(operation) + suffix;

  const updateCursor = () => {
    if (!renderer || !session) return;
    const page = session.currentPageId;
    if (page === 'home') return renderer.showCursor(19, Math.min(39, 20 + session.input.value.length), true);
    if (['flight-search', 'fleet-search', 'pilot-search'].includes(page)) {
      return renderer.showCursor(9, Math.min(39, 4 + session.input.value.length), true);
    }
    if (page === 'flights') return renderer.showCursor(21, Math.min(39, 9 + session.input.value.length), true);
    if (page === 'operations') return renderer.showCursor(20, Math.min(39, 9 + session.input.value.length), true);
    if (page === 'aircraft-select') return renderer.showCursor(20, Math.min(39, 9 + session.input.value.length), true);
    if (page === 'operation') return renderer.showCursor(19, Math.min(39, 9 + session.input.value.length), true);
    if (page === 'simbrief') return renderer.showCursor(19, Math.min(39, 9 + session.input.value.length), true);
    if (page === 'simbrief-account') return renderer.showCursor(10, Math.min(39, 4 + session.input.value.length), true);
    renderer.showCursor(0, 0, false);
  };

  const showSnapshot = async (snapshot, replay = true) => {
    await renderer.render(snapshot, { replay });
    updateCursor();
  };

  const renderLoadingOrError = (screen) => {
    if (state.loading) {
      screen.write(8, 7, 'CHARGEMENT EN COURS...', { foreground: 'cyan' });
      return true;
    }
    if (state.error) {
      renderError(screen);
      return true;
    }
    return false;
  };

  const renderError = (screen) => {
    screen.write(7, 8, 'SERVICE INDISPONIBLE', { foreground: 'red', blink: true });
    screen.write(10, 2, fit(state.error || 'ERREUR DE TRANSMISSION', 36), { foreground: 'yellow' });
    screen.write(14, 3, 'RETOUR POUR PAGE PRECEDENTE', { foreground: 'cyan' });
    screen.write(16, 3, 'REPETITION POUR RETRANSMETTRE', { foreground: 'cyan' });
    writeFooter(screen);
  };

  const setResult = (title, message, detail = '') => {
    state.result = { title, message, detail };
    state.error = null;
  };

  const registerPages = () => {
    session.register(new mt.MinitelPage('home', {
      onRender: (_context, screen, current) => {
        writeStatus(screen);
        titleBand(screen, 'PROMETHEE', 'CENTRE DES OPERATIONS');
        menuLine(screen, 7, 1, 'TABLEAU DE BORD');
        menuLine(screen, 8, 2, 'DEPARTS');
        menuLine(screen, 9, 3, 'ARRIVEES');
        menuLine(screen, 10, 4, 'MES OPERATIONS', true);
        menuLine(screen, 11, 5, 'RAPPORTS DE VOL');
        menuLine(screen, 12, 6, 'MISSIONS');
        menuLine(screen, 13, 7, 'FLOTTE');
        menuLine(screen, 14, 8, 'DOSSIER PILOTE');
        menuLine(screen, 15, 9, 'FINANCES');
        menuLine(screen, 16, 0, 'AUTRES SERVICES');
        screen.write(19, 7, 'VOTRE CHOIX : ' + current.input.value, { foreground: 'yellow' });
        screen.write(21, 4, 'GUIDE POUR PLUS D INFORMATIONS', { foreground: 'cyan' });
        writeFooter(screen);
      },
      acceptInput: (key) => /^[0-9]$/.test(key),
      send: (value) => {
        const targets = {
          '1': 'dashboard', '2': 'departures', '3': 'arrivals',
          '4': 'operations', '5': 'pireps', '6': 'missions',
          '7': 'fleet-search', '8': 'passport', '9': 'finances',
          '0': 'services'
        };
        if (targets[value]) action('open', { target: targets[value], page: 1 });
      }
    }));

    for (const [id, title, hint, target] of [
      ['flight-search', 'RECHERCHE VOL', 'ITF749 OU LFPO>LIRF', 'flights'],
      ['fleet-search', 'RECHERCHE FLOTTE', 'IMMATRICULATION / TYPE', 'fleet'],
      ['pilot-search', 'RECHERCHE PILOTE', 'MATRICULE / NOM', 'pilots']
    ]) {
      session.register(new mt.MinitelPage(id, {
        onRender: (_context, screen, current) => {
          writeStatus(screen);
          titleBand(screen, title, hint);
          screen.write(8, 2, 'CRITERE :', { foreground: 'white' });
          screen.write(10, 4, '> ' + current.input.value, { foreground: 'cyan' });
          screen.write(14, 4, 'ENVOI POUR LANCER LA RECHERCHE', { foreground: 'yellow' });
          screen.write(16, 4, 'ANNULATION EFFACE LA SAISIE', { foreground: 'cyan' });
          writeFooter(screen);
        },
        acceptInput: (key) => /^[A-Za-z0-9 ._/-]$/.test(key),
        send: (value) => action('search', { target, query: value.trim(), page: 1 })
      }));
    }

    session.register(new mt.MinitelPage('departures', {
      onRender: (_context, screen) => {
        writeStatus(screen);
        titleBand(screen, 'DEPARTS', 'MOUVEMENTS AIR INTER');
        if (renderLoadingOrError(screen)) return;
        screen.write(4, 1, 'VOL      DEP   H.    DEST   H.   ETAT', { foreground: 'cyan' });
        (state.collection?.items || []).slice(0, 7).forEach((flight, index) => {
          const row = 6 + index * 2;
          screen.write(row, 1, fit(flight.flight, 8) + ' ' + fit(flight.departure, 4) + ' ' + fit(flight.departure_time, 5) + ' ' + fit(flight.destination, 6) + ' ' + fit(flight.arrival_time, 5));
          screen.write(row + 1, 10, fit(flight.status_label, 28), { foreground: statusColour(flight.status) });
        });
        if (!(state.collection?.items || []).length) screen.write(8, 5, 'AUCUN MOUVEMENT PREVU');
        screen.write(21, 1, 'SHIFT+F2 : RAFRAICHIR DONNEES');
        writeFooter(screen);
      },
      repeat: (_context, refresh) => {
        if (refresh) action('refresh', { target: 'departures' });
      }
    }));

    session.register(new mt.MinitelPage('flights', {
      onRender: (_context, screen, current) => {
        writeStatus(screen);
        titleBand(screen, 'PROGRAMME DES VOLS', 'SERVICE RESERVATIONS');
        if (renderLoadingOrError(screen)) return;
        screen.write(4, 1, 'N VOL      DEP   H.    ARR   H.', { foreground: 'cyan' });
        const items = state.collection?.items || [];
        items.slice(0, 7).forEach((item, index) => {
          const row = 6 + index * 2;
          screen.write(row, 1, String(index + 1) + ' ' + fit(item.ident, 8) + ' ' + fit(item.departure, 4) + ' > ' + fit(item.arrival, 4));
          screen.write(row + 1, 3, fit((item.airline?.icao || 'ITF') + ' / ' + (item.route || 'ROUTE PROGRAMMEE'), 35), { foreground: 'cyan' });
        });
        if (!items.length) screen.write(8, 7, 'AUCUN VOL RESERVABLE');
        const pagination = state.collection?.pagination || { page: 1, last_page: 1, total: items.length };
        screen.write(20, 1, 'RESULTATS ' + fit(pagination.total ?? items.length, 5));
        promptLine(screen, 21, current.input.value, 'VOL A RESERVER');
        writeFooter(screen, true);
      },
      acceptInput: (key) => /^[1-7]$/.test(key),
      send: (value) => {
        const item = (state.collection?.items || [])[Number(value) - 1];
        if (item) action('reserve-flight', { flight: item });
      },
      previous: () => 'flight-search'
    }));

    session.register(new mt.MinitelPage('operations', {
      onRender: (_context, screen, current) => {
        writeStatus(screen);
        titleBand(screen, 'MES OPERATIONS', 'RESERVATIONS ACTIVES');
        if (renderLoadingOrError(screen)) return;
        screen.write(4, 1, 'N VOL      TRAJET       ETAT', { foreground: 'cyan' });
        const allItems = state.collection?.operations || [];
        const operationPages = Math.max(1, Math.ceil(allItems.length / 7));
        state.operationPage = Math.max(1, Math.min(operationPages, state.operationPage));
        const items = allItems.slice((state.operationPage - 1) * 7, state.operationPage * 7);
        items.forEach((item, index) => {
          const row = 6 + index * 2;
          const flight = item.flight || {};
          screen.write(row, 1, String(index + 1) + ' ' + fit(flight.ident, 8) + ' ' + fit(flight.departure + '>' + flight.arrival, 11) + ' ' + fit(item.status, 13));
          screen.write(row + 1, 3, fit(item.aircraft?.registration || 'APPAREIL A SELECTIONNER', 35), { foreground: item.aircraft ? 'green' : 'yellow' });
        });
        if (!allItems.length) screen.write(8, 4, 'AUCUNE OPERATION RESERVEE');
        pageLine(screen, 19, state.operationPage, operationPages, allItems.length);
        promptLine(screen, 21, current.input.value);
        writeFooter(screen, operationPages > 1);
      },
      acceptInput: (key) => /^[1-7]$/.test(key),
      send: (value) => {
        const allItems = state.collection?.operations || [];
        const item = allItems[((state.operationPage - 1) * 7) + Number(value) - 1];
        if (item) action('select-operation', { operation: item });
      },
      next: () => {
        const pages = Math.max(1, Math.ceil((state.collection?.operations || []).length / 7));
        state.operationPage = Math.min(pages, state.operationPage + 1);
      },
      previous: () => {
        if (state.operationPage > 1) { state.operationPage -= 1; return null; }
        return 'home';
      }
    }));

    session.register(new mt.MinitelPage('operation', {
      onRender: (_context, screen, current) => {
        writeStatus(screen);
        const op = state.operation || {};
        const flight = op.flight || {};
        screen.write(2, 1, 'OPERATION ' + fit(op.operation_id, 18), { foreground: 'yellow' });
        if (renderLoadingOrError(screen)) return;
        screen.write(4, 2, fit(flight.ident, 9) + ' ' + fit(flight.departure, 4) + ' > ' + fit(flight.arrival, 4));
        screen.write(5, 2, 'APPAREIL..... ' + fit(op.aircraft?.registration || 'A CHOISIR', 20));
        screen.write(6, 2, 'ETAT......... ' + fit(op.status || 'RESERVED', 20));
        screen.write(8, 2, '1 CHOISIR APPAREIL', { foreground: 'cyan' });
        screen.write(9, 2, '2 CONSULTER BRIEFING', { foreground: 'cyan' });
        screen.write(10, 2, '3 PREPARER / IMPORTER SIMBRIEF', { foreground: 'cyan' });
        screen.write(11, 2, '4 PREPARER LE PIREP', { foreground: 'cyan' });
        screen.write(12, 2, '5 ETAT DISPATCH', { foreground: 'cyan' });
        screen.write(15, 2, 'OFP........... ' + (op.simbrief?.available ? 'PRET' : 'A PREPARER'), { foreground: op.simbrief?.available ? 'green' : 'yellow' });
        screen.write(16, 2, 'PIREP......... ' + (op.pirep_id ? 'PRET' : 'A PREPARER'), { foreground: op.pirep_id ? 'green' : 'yellow' });
        promptLine(screen, 19, current.input.value);
        writeFooter(screen);
      },
      acceptInput: (key) => /^[1-5]$/.test(key),
      send: (value) => {
        const targets = {
          '1': 'load-aircraft',
          '2': 'load-briefing',
          '3': 'open-simbrief',
          '4': 'prefile-pirep',
          '5': 'load-dispatch'
        };
        if (targets[value]) action(targets[value]);
      }
    }));

    session.register(new mt.MinitelPage('aircraft-select', {
      onRender: (_context, screen, current) => {
        writeStatus(screen);
        screen.write(2, 1, 'SELECTION APPAREIL', { foreground: 'yellow' });
        if (renderLoadingOrError(screen)) return;
        screen.write(4, 1, 'N IMMATR.    TYPE    BASE', { foreground: 'cyan' });
        const items = state.aircraft?.available || [];
        items.slice(0, 7).forEach((item, index) => {
          const row = 6 + index * 2;
          screen.write(row, 1, String(index + 1) + ' ' + fit(item.registration, 10) + ' ' + fit(item.icao || item.type_key, 7) + ' ' + fit(item.airport || '---', 5));
          screen.write(row + 1, 3, fit((item.type_label || item.subfleet || '') + ' PAX ' + (item.passengers ?? '-'), 35), { foreground: 'cyan' });
        });
        if (!items.length) screen.write(8, 3, 'AUCUN APPAREIL DISPONIBLE');
        promptLine(screen, 20, current.input.value);
        writeFooter(screen);
      },
      acceptInput: (key) => /^[1-7]$/.test(key),
      send: (value) => {
        const item = (state.aircraft?.available || [])[Number(value) - 1];
        if (item) action('select-aircraft', { aircraft: item });
      }
    }));

    session.register(new mt.MinitelPage('briefing', {
      onRender: (_context, screen) => {
        writeStatus(screen);
        screen.write(2, 1, 'BRIEFING OPERATIONNEL', { foreground: 'yellow' });
        if (renderLoadingOrError(screen)) return;
        const data = state.briefing || {};
        const flight = data.operation?.flight || state.operation?.flight || {};
        screen.write(4, 2, 'VOL.......... ' + fit(flight.ident, 20));
        screen.write(5, 2, 'TRAJET....... ' + fit(flight.departure + ' > ' + flight.arrival, 20));
        screen.write(6, 2, 'DEGAGEMENT... ' + fit(data.alternate || flight.alternate || 'AUTO', 20));
        screen.write(7, 2, 'NIVEAU....... ' + fit(data.level ? 'FL' + data.level : 'AUTO', 20));
        screen.write(9, 2, 'ROUTE');
        const route = normalise(data.route || flight.route || 'NON RENSEIGNEE');
        screen.write(10, 2, fit(route.slice(0, 36), 36), { foreground: 'cyan' });
        screen.write(11, 2, fit(route.slice(36, 72), 36), { foreground: 'cyan' });
        screen.write(13, 2, 'OFP SIMBRIEF. ' + (data.ofp?.available ? 'DISPONIBLE' : 'ABSENT'), { foreground: data.ofp?.available ? 'green' : 'yellow' });
        writeFooter(screen);
      }
    }));

    session.register(new mt.MinitelPage('simbrief', {
      onRender: (_context, screen, current) => {
        writeStatus(screen);
        screen.write(2, 1, 'SIMBRIEF / PREPARATION OFP', { foreground: 'yellow' });
        if (renderLoadingOrError(screen)) return;
        const op = state.operation || {};
        screen.write(4, 2, 'VOL.......... ' + fit(op.flight?.ident, 20));
        screen.write(5, 2, 'APPAREIL..... ' + fit(op.aircraft?.registration || 'A CHOISIR', 20));
        screen.write(7, 2, '1 OUVRIR SIMBRIEF (COMPTE)', { foreground: 'cyan' });
        screen.write(8, 2, '2 IMPORTER VIA PILOT ID', { foreground: 'cyan' });
        screen.write(9, 2, '3 GENERATION CLE COMPAGNIE', { foreground: 'cyan' });
        screen.write(10, 2, '4 IMPORTER GENERATION COMPAGNIE', { foreground: 'cyan' });
        screen.write(13, 2, 'CLE COMPAGNIE ' + (op.simbrief?.company_api_available ? 'DISPONIBLE' : 'NON CONFIGUREE'), { foreground: op.simbrief?.company_api_available ? 'green' : 'yellow' });
        screen.write(14, 2, 'OFP ACTUEL.... ' + (op.simbrief?.available ? 'DISPONIBLE' : 'ABSENT'), { foreground: op.simbrief?.available ? 'green' : 'yellow' });
        screen.write(19, 2, 'CHOIX : ' + current.input.value, { foreground: 'yellow' });
        writeFooter(screen);
      },
      acceptInput: (key) => /^[1-4]$/.test(key),
      send: (value) => {
        const actions = {
          '1': 'simbrief-redirect',
          '2': 'simbrief-account-prompt',
          '3': 'simbrief-company-session',
          '4': 'simbrief-company-import'
        };
        if (actions[value]) action(actions[value]);
      }
    }));

    session.register(new mt.MinitelPage('simbrief-account', {
      onRender: (_context, screen, current) => {
        writeStatus(screen);
        screen.write(3, 2, 'IMPORT SIMBRIEF / PILOT ID', { foreground: 'yellow' });
        screen.write(6, 2, 'SAISISSEZ VOTRE PILOT ID NUMERIQUE');
        screen.write(10, 2, '> ' + current.input.value, { foreground: 'cyan' });
        screen.write(14, 2, 'ENVOI : IMPORTER LE DERNIER OFP');
        writeFooter(screen);
      },
      acceptInput: (key) => /^\d$/.test(key),
      send: (value) => {
        if (/^\d{1,7}$/.test(value)) action('simbrief-account-import', { pilotId: value });
      }
    }));

    session.register(new mt.MinitelPage('dispatch', {
      onRender: (_context, screen) => {
        writeStatus(screen);
        screen.write(2, 1, 'ETAT DISPATCH', { foreground: 'yellow' });
        if (renderLoadingOrError(screen)) return;
        const data = state.dispatch || {};
        screen.write(4, 2, 'STATUT....... ' + fit(data.status || 'INCONNU', 20), { foreground: data.ready ? 'green' : 'yellow' });
        (data.checks || []).slice(0, 4).forEach((check, index) => {
          const row = 7 + index * 3;
          screen.write(row, 2, (check.ready ? '[OK] ' : '[--] ') + fit(check.code, 10), { foreground: check.ready ? 'green' : 'yellow' });
          screen.write(row + 1, 4, fit(check.label, 34));
        });
        screen.write(20, 2, data.ready ? 'OPERATION PRETE COTE SERVEUR' : 'PREPARATION A COMPLETER', { foreground: data.ready ? 'green' : 'yellow' });
        writeFooter(screen);
      }
    }));

    session.register(new mt.MinitelPage('action-result', {
      onRender: (_context, screen) => {
        writeStatus(screen);
        const result = state.result || {};
        screen.write(4, 2, fit(result.title || 'OPERATION', 36), { foreground: 'yellow' });
        screen.write(8, 2, fit(result.message || 'TERMINE', 36), { foreground: 'green' });
        if (result.detail) {
          screen.write(11, 2, fit(result.detail.slice(0, 36), 36), { foreground: 'cyan' });
          screen.write(12, 2, fit(result.detail.slice(36, 72), 36), { foreground: 'cyan' });
        }
        screen.write(17, 2, 'RETOUR : OPERATION');
        screen.write(18, 2, 'SOMMAIRE : ACCUEIL');
        writeFooter(screen);
      },
      previous: () => state.operation ? 'operation' : 'home'
    }));

    session.register(new mt.MinitelPage('routes', {
      onRender: (_context, screen) => renderListHeader(screen, 'ROUTES AIR INTER', 'DEPART  ARRIVEE  VOLS  PLAGE HORAIRE', (item, row) => {
        screen.write(row, 1, fit(item.departure, 7) + ' ' + fit(item.arrival, 8) + ' ' + fit(item.flights, 5) + ' ' + fit((item.first_departure || '--:--') + '-' + (item.last_departure || '--:--'), 14));
      }),
      next: () => paginate('routes', 1),
      previous: () => pageBack('routes', 'home')
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

    session.register(new mt.MinitelPage('calendar', {
      onRender: (_context, screen) => renderListHeader(screen, 'CALENDRIER', 'DATE        EVENEMENT', (item, row) => {
        screen.write(row, 1, fit(item.starts_at || '--/-- --:--', 11) + ' ' + fit(item.title, 26));
        if (item.location) screen.write(row + 1, 3, fit(item.location, 35), { foreground: 'cyan' });
      }),
      next: () => paginate('calendar', 1),
      previous: () => pageBack('calendar', 'home')
    }));

    session.register(new mt.MinitelPage('profile', {
      onRender: (_context, screen) => {
        writeStatus(screen);
        screen.write(2, 2, 'MON DOSSIER PILOTE', { foreground: 'yellow' });
        if (renderLoadingOrError(screen)) return;
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


    session.register(new mt.MinitelPage('services', {
      onRender: (_context, screen, current) => {
        writeStatus(screen);
        titleBand(screen, 'AUTRES SERVICES', 'PROMETHEE');
        menuLine(screen, 6, 1, 'PROGRAMME DES VOLS');
        menuLine(screen, 7, 2, 'ROUTES');
        menuLine(screen, 8, 3, 'PILOTES');
        menuLine(screen, 9, 4, 'CALENDRIER');
        menuLine(screen, 10, 5, 'MON DOSSIER');
        menuLine(screen, 11, 6, 'ADMINISTRATION');
        promptLine(screen, 18, current.input.value);
        writeFooter(screen);
      },
      acceptInput: (key) => /^[1-6]$/.test(key),
      send: (value) => {
        const targets = { '1':'flight-search','2':'routes','3':'pilot-search','4':'calendar','5':'profile','6':'admin' };
        if (targets[value]) action('open', { target: targets[value], page: 1 });
      }
    }));

    session.register(new mt.MinitelPage('dashboard', {
      onRender: (_context, screen) => {
        writeStatus(screen);
        titleBand(screen, 'TABLEAU DE BORD', 'EXPLOITATION AIR INTER');
        if (renderLoadingOrError(screen)) return;
        const s = state.collection?.stats || {};
        screen.write(6, 2, 'VOLS EN COURS..... ' + fit(s.active_flights, 8), { foreground:'green' });
        screen.write(8, 2, 'ARRIVEES JOUR..... ' + fit(s.today_arrivals, 8));
        screen.write(10,2, 'MES PIREP......... ' + fit(s.my_reports, 8));
        screen.write(12,2, 'MISSIONS ACTIVES.. ' + fit(s.missions, 8), { foreground:'cyan' });
        screen.write(14,2, 'FLOTTE DISP....... ' + fit(s.fleet_available, 8), { foreground:'green' });
        screen.write(16,2, 'EVENEMENTS........ ' + fit(s.events, 8));
        writeFooter(screen);
      },
      previous: () => 'home'
    }));

    session.register(new mt.MinitelPage('arrivals', {
      onRender: (_context, screen) => {
        writeStatus(screen);
        titleBand(screen, 'ARRIVEES', 'MOUVEMENTS AIR INTER');
        if (renderLoadingOrError(screen)) return;
        screen.write(4, 1, 'VOL      ORIG  H.    ARR   H.   ETAT', { foreground: 'cyan' });
        (state.collection?.items || []).slice(0, 7).forEach((flight, index) => {
          const row = 6 + index * 2;
          screen.write(row, 1, fit(flight.flight, 8) + ' ' + fit(flight.departure, 4) + ' ' + fit(flight.departure_time, 5) + ' ' + fit(flight.destination || flight.arrival, 5) + ' ' + fit(flight.arrival_time, 5));
          screen.write(row + 1, 10, fit(flight.status_label, 28), { foreground: statusColour(flight.status) });
        });
        if (!(state.collection?.items || []).length) screen.write(8, 5, 'AUCUNE ARRIVEE PREVUE');
        screen.write(21, 1, 'SHIFT+F2 : RAFRAICHIR DONNEES');
        writeFooter(screen);
      },
      repeat: (_context, refresh) => { if (refresh) action('refresh', { target:'arrivals' }); },
      previous: () => 'home'
    }));

    session.register(new mt.MinitelPage('pireps', {
      onRender: (_context, screen) => renderListHeader(screen, 'MES RAPPORTS DE VOL', 'DATE  VOL     DEP ARR  ETAT', (item, row) => {
        screen.write(row, 1, fit(item.date,5)+' '+fit(item.flight,8)+' '+fit(item.departure,3)+' '+fit(item.arrival,3)+'  '+fit(item.status,5),
          { foreground: item.status === 'OK' ? 'green' : 'yellow' });
        screen.write(row + 1, 3, fit((item.aircraft || '---') + '  ' + Math.floor((Number(item.block_minutes)||0)/60) + 'H' + String((Number(item.block_minutes)||0)%60).padStart(2,'0'), 35), { foreground:'cyan' });
      }),
      next: () => paginate('pireps', 1),
      previous: () => pageBack('pireps', 'home')
    }));

    session.register(new mt.MinitelPage('missions', {
      onRender: (_context, screen) => renderListHeader(screen, 'MISSIONS DISPONIBLES', 'TYPE / TRAJET / APPAREIL', (item, row) => {
        const kind = normalise(item.type || 'MISSION').slice(0,12);
        screen.write(row, 1, fit(kind,12)+' '+fit((item.departure||'---')+'>'+ (item.arrival||'---'),11)+' '+fit(item.aircraft||'',10),
          { foreground:item.reserved?'green':'white' });
        screen.write(row + 1, 3, fit(item.title,35), { foreground:'cyan' });
      }),
      next: () => paginate('missions', 1),
      previous: () => pageBack('missions', 'home')
    }));

    session.register(new mt.MinitelPage('passport', {
      onRender: (_context, screen) => {
        writeStatus(screen);
        titleBand(screen, 'AIR INTER PASSPORT', 'SERVICE PILOTES');
        if (renderLoadingOrError(screen)) return;
        const p=state.collection?.pilot||{};
        const h=Math.floor((Number(p.flight_time)||0)/60), m=String((Number(p.flight_time)||0)%60).padStart(2,'0');
        screen.write(6,2,'PILOTE : '+fit(p.pilot_id,12),{foreground:'yellow'});
        screen.write(8,2,'VOLS......... '+fit(p.flights,8));
        screen.write(9,2,'HEURES....... '+fit(h+'H'+m,8));
        screen.write(10,2,'PAYS......... '+fit(p.countries,8));
        screen.write(11,2,'AEROPORTS.... '+fit(p.airports,8));
        screen.write(14,2,'DERNIERES DESTINATIONS',{foreground:'cyan'});
        screen.write(16,2,fit((p.destinations||[]).join('  '),36));
        writeFooter(screen);
      },
      previous: () => 'home'
    }));

    session.register(new mt.MinitelPage('finances', {
      onRender: (_context, screen) => {
        writeStatus(screen);
        titleBand(screen, 'AIR INTER - FINANCES', 'PERIODE : MOIS EN COURS');
        if (renderLoadingOrError(screen)) return;
        const rows=state.collection?.items||[];
        let credits=0,debits=0,balance=0;
        rows.forEach(x=>{credits+=Number(x.credits)||0; debits+=Number(x.debits)||0; balance+=Number(x.balance)||0;});
        screen.write(7,2,'RECETTES..... '+fit(credits,16),{foreground:'green'});
        screen.write(9,2,'DEPENSES..... '+fit(debits,16),{foreground:'red'});
        screen.write(11,2,'RESULTAT..... '+fit(credits-debits,16),{foreground:(credits-debits)>=0?'green':'red'});
        screen.write(13,2,'SOLDE........ '+fit(balance,16),{foreground:'yellow'});
        screen.write(17,2,fit(rows.map(x=>x.code).join(' / '),36),{foreground:'cyan'});
        writeFooter(screen);
      },
      previous: () => 'home'
    }));

    session.register(new mt.MinitelPage('admin', {
      onRender: (_context, screen) => {
        writeStatus(screen);
        titleBand(screen, 'ADMINISTRATION', 'PROMETHEE');
        if (renderLoadingOrError(screen)) return;
        const s=state.collection?.stats||{};
        screen.write(6,2,'1 OPERATIONS / DISPATCH');
        screen.write(7,2,'2 FLOTTE / MAINTENANCE');
        screen.write(8,2,'3 MISSIONS');
        screen.write(9,2,'4 ECONOMIE');
        screen.write(10,2,'5 CRM........... '+fit(s.crm_campaigns,5));
        screen.write(11,2,'6 CALENDRIER.... '+fit(s.events,5));
        screen.write(12,2,'7 RESEAU REGIONAL');
        screen.write(13,2,'8 AUTOMATISATION');
        screen.write(15,2,'BASES ACTIVES... '+fit(s.bases,5),{foreground:'cyan'});
        screen.write(16,2,'APPAREILS....... '+fit(s.aircraft,5),{foreground:'cyan'});
        screen.write(19,2,'ENVOI 7 : RESEAU REGIONAL',{foreground:'yellow'});
        writeFooter(screen);
      },
      acceptInput: (key) => /^[7]$/.test(key),
      send: (value) => { if(value==='7') action('open',{target:'regional',page:1}); },
      previous: () => 'services'
    }));

    session.register(new mt.MinitelPage('regional', {
      onRender: (_context, screen) => renderListHeader(screen, 'RESEAU REGIONAL', 'BASE   ROLE       FLOTTE CHECKS', (item, row) => {
        const role=item.is_hub?'HUB':(item.is_regional_platform?'REGIONAL':'TECH');
        const checks=(item.check_a?'A':'')+(item.check_b?'B':'')+(item.check_c?'C':'');
        screen.write(row,1,fit(item.airport_id,6)+' '+fit(role,10)+' '+fit(item.aircraft_count,6)+' '+fit(checks,5),
          {foreground:item.is_hub?'yellow':'white'});
        screen.write(row+1,3,fit(item.name||'',35),{foreground:'cyan'});
      }),
      previous: () => 'admin'
    }));

  const renderListHeader = (screen, title, columns, renderItem) => {
    writeStatus(screen);
    screen.write(2, 1, title, { foreground: 'yellow' });
    if (renderLoadingOrError(screen)) return;
    screen.write(4, 1, columns, { foreground: 'cyan' });
    const items = state.collection?.items || [];
    items.slice(0, 7).forEach((item, index) => renderItem(item, 6 + index * 2));
    if (!items.length) screen.write(8, 7, 'AUCUN RESULTAT');
    const pagination = state.collection?.pagination || {};
    pageLine(screen, 20, pagination.page || 1, pagination.last_page || 1, pagination.total || 0);
    writeFooter(screen, true);
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

    if (target === 'flight-search' || target === 'fleet-search' || target === 'pilot-search') {
      state.loading = false;
      state.query = '';
      state.collection = null;
      return showSnapshot(session.go(target));
    }

    await showSnapshot(session.go(target), false);

    try {
      if (target === 'departures' || target === 'arrivals') {
        const data = await requestJson(state.bootstrap.endpoints.departures);
        state.collection = { items: target === 'arrivals' ? (data.arrivals || []) : (data.departures || data.flights || []) };
      } else if (target === 'profile') {
        state.collection = await requestJson(state.bootstrap.endpoints.profile);
      } else if (target === 'operations') {
        const data = await requestJson(state.bootstrap.endpoints.operations);
        state.collection = data.data || { operations: [] };
        state.operationPage = 1;
      } else if (target === 'flights') {
        const raw = String(state.query || '').trim().toUpperCase();
        const params = {};
        const routeMatch = raw.match(/^([A-Z0-9]{3,8})\s*(?:>|-|\s)\s*([A-Z0-9]{3,8})$/);
        if (routeMatch) {
          params.dep_icao = routeMatch[1];
          params.arr_icao = routeMatch[2];
        } else if (raw) {
          params.flight_number = raw;
        }
        const data = await requestJson(pageUrl(state.bootstrap.endpoints.operation_search, params));
        const items = data.data || [];
        state.collection = {
          items,
          pagination: { page: 1, last_page: 1, total: items.length, has_more: false }
        };
      } else if (['routes', 'fleet', 'pilots', 'calendar'].includes(target)) {
        state.collection = await requestJson(pageUrl(state.bootstrap.endpoints[target], {
          q: state.query,
          page: state.page
        }));
      } else if (target === 'dashboard') {
        state.collection = await requestJson(state.bootstrap.endpoints.dashboard);
      } else if (target === 'pireps') {
        state.collection = await requestJson(pageUrl(state.bootstrap.endpoints.pireps, { mine: 1, page: state.page }));
      } else if (target === 'missions') {
        state.collection = await requestJson(pageUrl(state.bootstrap.endpoints.missions, { page: state.page }));
      } else if (target === 'passport') {
        state.collection = await requestJson(state.bootstrap.endpoints.passport);
      } else if (target === 'finances') {
        state.collection = await requestJson(pageUrl(state.bootstrap.endpoints.finances, { period: 'month' }));
      } else if (target === 'regional') {
        state.collection = await requestJson(state.bootstrap.endpoints.regional);
      } else if (target === 'admin') {
        state.collection = await requestJson(state.bootstrap.endpoints.admin);
      } else {
        throw new Error('PAGE INCONNUE');
      }
      state.loading = false;
      await showSnapshot(session.render(), true);
    } catch (error) {
      await fail(error);
    }
  };

  const reloadOperation = async (operationId, destination = 'operation') => {
    state.loading = true;
    state.error = null;
    if (session.currentPageId !== destination) await showSnapshot(session.go(destination), false);
    try {
      const data = await requestJson(operationUrl(operationId));
      state.operation = data.data;
      state.loading = false;
      await showSnapshot(session.render(), true);
      return state.operation;
    } catch (error) {
      await fail(error);
      return null;
    }
  };

  const fail = async (error) => {
    state.loading = false;
    state.error = error?.message || (error?.status ? 'SERVICE HTTP ' + error.status : 'LIAISON PROMETHEE INTERROMPUE');
    await showSnapshot(session.render(), true);
  };

  const submitSimBriefForm = (payload) => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = payload.worker_url;
    form.target = '_blank';
    form.style.display = 'none';
    Object.entries(payload.parameters || {}).forEach(([name, value]) => {
      if (value === null || value === undefined) return;
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = String(value);
      form.appendChild(input);
    });
    document.body.appendChild(form);
    form.submit();
    form.remove();
  };

  const runM3Action = async (pending) => {
    const opId = state.operation?.operation_id;

    try {
      if (pending.type === 'reserve-flight') {
        state.loading = true;
        state.error = null;
        await showSnapshot(session.render(), false);
        const data = await requestJson(
          state.bootstrap.endpoints.reserve_base + '/' + encodeURIComponent(pending.flight.id) + '/reserve',
          { method: 'POST', body: {} }
        );
        state.operation = data.data;
        setResult('RESERVATION CONFIRMEE', 'VOL ' + (state.operation?.flight?.ident || pending.flight.ident), 'OPERATION ' + (state.operation?.operation_id || 'CREEE'));
        state.loading = false;
        return showSnapshot(session.go('action-result'), true);
      }

      if (pending.type === 'select-operation') {
        state.operation = pending.operation;
        return reloadOperation(pending.operation.operation_id);
      }

      if (!opId) throw new Error('AUCUNE OPERATION SELECTIONNEE');

      if (pending.type === 'load-aircraft') {
        state.loading = true; state.error = null;
        await showSnapshot(session.go('aircraft-select'), false);
        const data = await requestJson(operationUrl(opId, '/aircraft'));
        state.aircraft = data.data || { available: [] };
        state.loading = false;
        return showSnapshot(session.render(), true);
      }

      if (pending.type === 'select-aircraft') {
        const data = await requestJson(operationUrl(opId, '/aircraft'), {
          method: 'PUT',
          body: { aircraft_id: pending.aircraft.id }
        });
        state.operation.aircraft = data.data?.aircraft || pending.aircraft;
        setResult('APPAREIL SELECTIONNE', pending.aircraft.registration, data.data?.status || '');
        return showSnapshot(session.go('action-result'), true);
      }

      if (pending.type === 'load-briefing') {
        state.loading = true; state.error = null;
        await showSnapshot(session.go('briefing'), false);
        const data = await requestJson(operationUrl(opId, '/briefing'));
        state.briefing = data.data || {};
        state.loading = false;
        return showSnapshot(session.render(), true);
      }

      if (pending.type === 'open-simbrief') {
        state.error = null;
        return showSnapshot(session.go('simbrief'), true);
      }

      if (pending.type === 'simbrief-account-prompt') {
        return showSnapshot(session.go('simbrief-account'), true);
      }

      if (pending.type === 'simbrief-redirect') {
        const payload = await requestJson(operationUrl(opId, '/simbrief/redirect'), { method: 'POST', body: {} });
        window.open(payload.url, '_blank', 'noopener,noreferrer');
        setResult('SIMBRIEF OUVERT', 'PREPARATION DANS UN NOUVEL ONGLET', 'REVENEZ ICI PUIS IMPORTEZ VIA PILOT ID');
        return showSnapshot(session.go('action-result'), true);
      }

      if (pending.type === 'simbrief-account-import') {
        const payload = await requestJson(operationUrl(opId, '/simbrief/account/import'), {
          method: 'POST',
          body: { pilot_id: pending.pilotId }
        });
        await reloadOperation(opId, 'operation');
        setResult('OFP IMPORTE', 'SIMBRIEF #' + (payload.id || ''), (payload.origin || '') + ' > ' + (payload.destination || ''));
        return showSnapshot(session.go('action-result'), true);
      }

      if (pending.type === 'simbrief-company-session') {
        const payload = await requestJson(operationUrl(opId, '/simbrief/session'), { method: 'POST', body: {} });
        state.simbriefApiState = payload.state;
        try { sessionStorage.setItem('promethee-minitel-simbrief-state-' + opId, payload.state); } catch (_) {}
        submitSimBriefForm(payload);
        setResult('SIMBRIEF COMPAGNIE', 'GENERATION OUVERTE DANS UN ONGLET', 'TERMINEZ LA GENERATION PUIS CHOISISSEZ IMPORTER');
        return showSnapshot(session.go('action-result'), true);
      }

      if (pending.type === 'simbrief-company-import') {
        let apiState = state.simbriefApiState;
        try { apiState ||= sessionStorage.getItem('promethee-minitel-simbrief-state-' + opId); } catch (_) {}
        if (!apiState) throw new Error('AUCUNE SESSION SIMBRIEF COMPAGNIE ACTIVE');
        const payload = await requestJson(operationUrl(opId, '/simbrief/import'), {
          method: 'POST',
          body: { state: apiState }
        });
        try { sessionStorage.removeItem('promethee-minitel-simbrief-state-' + opId); } catch (_) {}
        state.simbriefApiState = null;
        await reloadOperation(opId, 'operation');
        setResult('OFP IMPORTE', 'SIMBRIEF #' + (payload.id || ''), (payload.origin || '') + ' > ' + (payload.destination || ''));
        return showSnapshot(session.go('action-result'), true);
      }

      if (pending.type === 'prefile-pirep') {
        const data = await requestJson(operationUrl(opId, '/pirep'), { method: 'POST', body: {} });
        await reloadOperation(opId, 'operation');
        setResult('PIREP PREPARE', 'PIREP #' + (data.data?.pirep_id || ''), data.data?.already_prefiled ? 'DEJA PREPARE' : 'PRE-DEPOT EFFECTUE');
        return showSnapshot(session.go('action-result'), true);
      }

      if (pending.type === 'load-dispatch') {
        state.loading = true; state.error = null;
        await showSnapshot(session.go('dispatch'), false);
        const data = await requestJson(operationUrl(opId, '/dispatch'));
        state.dispatch = data.data || {};
        state.loading = false;
        return showSnapshot(session.render(), true);
      }
    } catch (error) {
      await fail(error);
    }
  };

  const handleAction = async () => {
    const pending = session.context.__prometheeMinitelAction;
    if (!pending) return;
    session.context.__prometheeMinitelAction = null;

    if (pending.type === 'open') return loadTarget(pending.target, pending);
    if (pending.type === 'search') return loadTarget(pending.target, pending);
    if (pending.type === 'refresh') return loadTarget(pending.target, { query: state.query, page: state.page });
    return runM3Action(pending);
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

    const terminalPreferences = readMinitelPreferences();
    shell = new mt.MinitelShell({
      host,
      service: '3615 AIRINTER',
      product: 'PROMETHEE',
      identity: '',
      speed: terminalPreferences.speed,
      displayMode: terminalPreferences.displayMode,
      onPreferencesChange: persistMinitelPreferences,
      onExit: exitMinitel,
      bootFrameDelay: terminalPreferences.speed === 'authentic' ? 260 : 70
    });

    const capability = shell.capability();
    if (!capability.allowed) {
      shell.showFallback(capability.reason || 'unsupported');
      return;
    }

    shell.mount();
    renderer = new mt.MinitelDomRenderer(shell.terminalNode, {
      speed: terminalPreferences.speed,
      displayMode: terminalPreferences.displayMode,
      maxProgressiveDurationMs: terminalPreferences.speed === 'authentic' ? 0 : 2200,
      transmissionTickMs: 16
    });

    try {
      const bootstrapUrl = document.documentElement.dataset.minitelBootstrap;
      state.bootstrap = await requestJson(bootstrapUrl);
      session = new mt.MinitelSession({ homePageId: 'home', speed: terminalPreferences.speed, context: {} });
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

  if (document.readyState === 'loading') {
    window.addEventListener('DOMContentLoaded', start, { once: true });
  } else {
    start();
  }
})();
