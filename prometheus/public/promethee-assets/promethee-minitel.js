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
    collection: null,
    loading: false,
    error: null,
    operation: null,
    aircraft: null,
    briefing: null,
    dispatch: null,
    result: null,
    simbriefApiState: null
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
    if (page === 'home') return renderer.showCursor(20, Math.min(39, 16 + session.input.value.length), true);
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
    screen.write(7, 10, '*** ERREUR ***', { foreground: 'red', blink: true });
    screen.write(10, 2, fit(state.error || 'SERVICE INDISPONIBLE', 36));
    screen.write(13, 2, 'RETOUR : PAGE PRECEDENTE');
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
        screen.write(2, 11, 'AIR INTER', { foreground: 'yellow' });
        screen.write(4, 7, 'CENTRE DES OPERATIONS');
        screen.write(6, 2, '1 DEPARTS / MOUVEMENTS', { foreground: 'cyan' });
        screen.write(7, 2, '2 RECHERCHER / RESERVER VOL', { foreground: 'cyan' });
        screen.write(8, 2, '3 MES OPERATIONS', { foreground: 'yellow' });
        screen.write(9, 2, '4 ROUTES', { foreground: 'cyan' });
        screen.write(10, 2, '5 FLOTTE', { foreground: 'cyan' });
        screen.write(11, 2, '6 PILOTES', { foreground: 'cyan' });
        screen.write(12, 2, '7 CALENDRIER', { foreground: 'cyan' });
        screen.write(13, 2, '8 MON DOSSIER', { foreground: 'cyan' });
        screen.write(15, 2, 'VOLS   ' + fit(state.bootstrap?.stats?.flights, 6));
        screen.write(16, 2, 'PILOTES' + fit(state.bootstrap?.stats?.pilots, 6));
        screen.write(17, 2, 'EN VOL ' + fit(state.bootstrap?.stats?.active, 6));
        screen.write(18, 2, 'AUJ.   ' + fit(state.bootstrap?.stats?.today, 6));
        screen.write(20, 2, 'VOTRE CHOIX : ' + current.input.value, { foreground: 'yellow' });
        writeFooter(screen);
      },
      acceptInput: (key) => /^[1-8]$/.test(key),
      send: (value) => {
        const targets = {
          '1': 'departures', '2': 'flight-search', '3': 'operations',
          '4': 'routes', '5': 'fleet-search', '6': 'pilot-search',
          '7': 'calendar', '8': 'profile'
        };
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
        screen.write(2, 1, 'PROGRAMME DES VOLS', { foreground: 'yellow' });
        if (renderLoadingOrError(screen)) return;
        screen.write(4, 1, 'N VOL      DEP   H.    ARR   H.', { foreground: 'cyan' });
        const items = state.collection?.items || [];
        items.slice(0, 7).forEach((item, index) => {
          const row = 6 + index * 2;
          screen.write(row, 1, String(index + 1) + ' ' + fit(item.ident, 8) + ' ' + fit(item.departure, 4) + ' ' + fit(item.departure_time || '--:--', 5) + ' ' + fit(item.arrival, 4) + ' ' + fit(item.arrival_time || '--:--', 5));
          screen.write(row + 1, 3, fit((item.departure_name || '') + ' > ' + (item.arrival_name || ''), 35), { foreground: 'cyan' });
        });
        if (!items.length) screen.write(8, 7, 'AUCUN RESULTAT');
        const pagination = state.collection?.pagination || {};
        screen.write(20, 1, 'PAGE ' + fit(pagination.page || 1, 3) + '/' + fit(pagination.last_page || 1, 3) + ' TOTAL ' + fit(pagination.total || 0, 5));
        screen.write(21, 1, 'CHOIX : ' + current.input.value + ' + ENVOI = RESERVER', { foreground: 'yellow' });
        writeFooter(screen, true);
      },
      acceptInput: (key) => /^[1-7]$/.test(key),
      send: (value) => {
        const item = (state.collection?.items || [])[Number(value) - 1];
        if (item) action('reserve-flight', { flight: item });
      },
      next: () => paginate('flights', 1),
      previous: () => pageBack('flights', 'flight-search')
    }));

    session.register(new mt.MinitelPage('operations', {
      onRender: (_context, screen, current) => {
        writeStatus(screen);
        screen.write(2, 1, 'MES OPERATIONS', { foreground: 'yellow' });
        if (renderLoadingOrError(screen)) return;
        screen.write(4, 1, 'N VOL      TRAJET       ETAT', { foreground: 'cyan' });
        const items = state.collection?.operations || [];
        items.slice(0, 7).forEach((item, index) => {
          const row = 6 + index * 2;
          const flight = item.flight || {};
          screen.write(row, 1, String(index + 1) + ' ' + fit(flight.ident, 8) + ' ' + fit(flight.departure + '>' + flight.arrival, 11) + ' ' + fit(item.status, 13));
          screen.write(row + 1, 3, fit(item.aircraft?.registration || 'APPAREIL A SELECTIONNER', 35), { foreground: item.aircraft ? 'green' : 'yellow' });
        });
        if (!items.length) screen.write(8, 4, 'AUCUNE OPERATION RESERVEE');
        screen.write(20, 1, 'CHOIX : ' + current.input.value + ' + ENVOI', { foreground: 'yellow' });
        writeFooter(screen);
      },
      acceptInput: (key) => /^[1-7]$/.test(key),
      send: (value) => {
        const item = (state.collection?.operations || [])[Number(value) - 1];
        if (item) action('select-operation', { operation: item });
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
        screen.write(19, 2, 'CHOIX : ' + current.input.value, { foreground: 'yellow' });
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
        screen.write(20, 1, 'CHOIX : ' + current.input.value + ' + ENVOI', { foreground: 'yellow' });
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

  const renderListHeader = (screen, title, columns, renderItem) => {
    writeStatus(screen);
    screen.write(2, 1, title, { foreground: 'yellow' });
    if (renderLoadingOrError(screen)) return;
    screen.write(4, 1, columns, { foreground: 'cyan' });
    const items = state.collection?.items || [];
    items.slice(0, 7).forEach((item, index) => renderItem(item, 6 + index * 2));
    if (!items.length) screen.write(8, 7, 'AUCUN RESULTAT');
    const pagination = state.collection?.pagination || {};
    screen.write(20, 1, 'PAGE ' + fit(pagination.page || 1, 3) + '/' + fit(pagination.last_page || 1, 3) + ' TOTAL ' + fit(pagination.total || 0, 6), { foreground: 'yellow' });
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
      if (target === 'departures') {
        const data = await requestJson(state.bootstrap.endpoints.departures);
        state.collection = { items: data.flights || [] };
      } else if (target === 'profile') {
        state.collection = await requestJson(state.bootstrap.endpoints.profile);
      } else if (target === 'operations') {
        const data = await requestJson(state.bootstrap.endpoints.operations);
        state.collection = data.data || { operations: [] };
      } else if (['flights', 'routes', 'fleet', 'pilots', 'calendar'].includes(target)) {
        state.collection = await requestJson(pageUrl(state.bootstrap.endpoints[target], {
          q: state.query,
          page: state.page
        }));
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
      state.bootstrap = await requestJson(bootstrapUrl);
      session = new mt.MinitelSession({ homePageId: 'home', speed: 'fast', context: {} });
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
