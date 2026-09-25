(() => {
  'use strict';

  const mt = window.AirInterMinitel;
  const core = window.HermesMinitelCore;
  if (!mt || !core) return;

  const hm = {
    active: false,
    authenticated: false,
    pilot: null,
    login: '',
    operation: null,
    operations: [],
    searchResults: [],
    aircraft: [],
    dispatch: null,
    briefing: null,
    status: null,
    error: null,
    result: null,
    operationPage: 1,
    searchPage: 1,
    simbriefState: null,
    timer: null
  };

  let host;
  let shell;
  let renderer;
  let keyboard;
  let terminalSession;

  const unwrapHm = value => value?.data ?? value;
  const fit = core.fit;

  const serviceLine = screen => {
    const identity = hm.pilot?.ident || hm.pilot?.pilot_id || hm.pilot?.pilotId || 'CREW';
    screen.write(0, 0, mt.buildServiceLine({ service: '3615 HERMES', identity, state: 'C' }), { foreground: 'cyan' });
  };

  const footer = (screen, paging = false) => {
    if (paging) screen.write(22, 1, 'RETOUR/↑ PAGE-    SUITE/↓ PAGE+', { foreground: 'cyan' });
    screen.write(23, 0, 'GUIDE SOMMAIRE RETOUR SUITE      ENVOI', { foreground: 'cyan' });
  };

  const setAction = (type, payload = {}) => {
    if (!terminalSession) return;
    terminalSession.context.__hermesMinitelAction = { type, ...payload };
  };

  const opRef = () => core.operationId(hm.operation);

  const path = suffix => {
    const id = opRef();
    if (!id) throw new Error('AUCUNE OPERATION SELECTIONNEE');
    return '/api/v1/operations/' + encodeURIComponent(id) + suffix;
  };

  const currentPirepId = () =>
    hm.dispatch?.pirep?.id
    || hm.dispatch?.pirep_id
    || hm.operation?.pirep_id
    || hm.operation?.pirepId
    || null;

  const planningPayload = () => {
    const aircraftId = hm.operation?.aircraft?.id;
    if (!aircraftId) throw new Error('SELECTIONNEZ UN APPAREIL');
    const flight = core.operationFlight(hm.operation);
    const payload = { aircraft_id: aircraftId };
    if (flight.alternate) payload.alternate = flight.alternate;
    if (flight.route) payload.route = flight.route;
    if (flight.level) payload.level = Number(flight.level);
    return payload;
  };

  const runCall = async (route, body) => unwrapHm(await call(route, body));

  const show = async (snapshot, replay = true) => {
    await renderer.render(snapshot, { replay });
    updateCursor();
  };

  const updateCursor = () => {
    if (!renderer || !terminalSession) return;
    const page = terminalSession.currentPageId;
    const value = terminalSession.input.value.length;
    if (page === 'login-user') return renderer.showCursor(10, Math.min(39, 4 + value), true);
    if (page === 'login-password') return renderer.showCursor(10, Math.min(39, 4 + value), true);
    if (page === 'home') return renderer.showCursor(18, Math.min(39, 16 + value), true);
    if (page === 'search') return renderer.showCursor(9, Math.min(39, 4 + value), true);
    if (['operations', 'search-results', 'aircraft'].includes(page)) return renderer.showCursor(20, Math.min(39, 9 + value), true);
    if (['preparation', 'simbrief'].includes(page)) return renderer.showCursor(19, Math.min(39, 9 + value), true);
    if (page === 'simbrief-pilot') return renderer.showCursor(10, Math.min(39, 4 + value), true);
    renderer.showCursor(0, 0, false);
  };

  const showError = screen => {
    screen.write(7, 10, '*** ERREUR ***', { foreground: 'red', blink: true });
    screen.write(10, 2, fit(hm.error || 'SERVICE INDISPONIBLE', 36));
    screen.write(13, 2, 'RETOUR : PAGE PRECEDENTE');
    footer(screen);
  };

  const openExternal = async url => {
    await runCall('/api/open-external', { url });
  };

  const submitWorker = payload => {
    const popup = window.open('about:blank', 'HermesMinitelSimBrief', 'width=900,height=720');
    if (!popup) throw new Error('AUTORISEZ LA FENETRE SIMBRIEF');
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = payload.worker_url;
    form.target = 'HermesMinitelSimBrief';
    form.style.display = 'none';
    Object.entries(payload.parameters || {}).forEach(([name, value]) => {
      if (value === null || value === undefined || value === '') return;
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

  const registerPages = () => {
    terminalSession.register(new mt.MinitelPage('login-user', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        screen.write(3, 9, 'H E R M E S', { foreground: 'yellow' });
        screen.write(5, 5, 'SYSTEME ACARS AIR INTER');
        screen.write(8, 2, 'IDENTIFIANT PILOTE');
        screen.write(10, 2, '> ' + current.input.value, { foreground: 'cyan' });
        screen.write(14, 2, 'ENVOI : CONTINUER');
        screen.write(18, 2, 'CONNEXION SECURISEE PROMETHEE');
        footer(screen);
      },
      acceptInput: key => /^[A-Za-z0-9@._+\-]$/.test(key),
      send: value => {
        if (!value.trim()) return;
        hm.login = value.trim();
        return 'login-password';
      }
    }));

    terminalSession.register(new mt.MinitelPage('login-password', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        screen.write(3, 9, 'H E R M E S', { foreground: 'yellow' });
        screen.write(7, 2, 'PILOTE : ' + fit(hm.login, 26));
        screen.write(9, 2, 'MOT DE PASSE');
        screen.write(10, 2, '> ' + '*'.repeat(current.input.value.length), { foreground: 'cyan' });
        screen.write(14, 2, 'ENVOI : SE CONNECTER');
        screen.write(18, 2, 'MOT DE PASSE NON CONSERVE');
        footer(screen);
      },
      acceptInput: key => key.length === 1,
      send: value => {
        if (value) setAction('login', { password: value });
      },
      previous: () => 'login-user'
    }));

    terminalSession.register(new mt.MinitelPage('home', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        screen.write(2, 10, 'H E R M E S', { foreground: 'yellow' });
        screen.write(4, 5, 'SYSTEME ACARS AIR INTER');
        screen.write(7, 2, '1 MES OPERATIONS', { foreground: 'cyan' });
        screen.write(8, 2, '2 RECHERCHER / RESERVER VOL', { foreground: 'cyan' });
        screen.write(9, 2, '3 PREPARATION OPERATIONNELLE', { foreground: 'cyan' });
        screen.write(10, 2, '4 ETAT SIMULATEUR', { foreground: 'cyan' });
        screen.write(12, 2, 'PROMETHEE...... ' + (hm.status?.connected ? 'CONNECTE' : 'HORS LIGNE'), { foreground: hm.status?.connected ? 'green' : 'red' });
        screen.write(13, 2, 'SIMULATEUR..... ' + (hm.status?.latest ? 'CONNECTE' : 'EN ATTENTE'), { foreground: hm.status?.latest ? 'green' : 'yellow' });
        screen.write(14, 2, 'OPERATION...... ' + fit(opRef() || 'AUCUNE', 18));
        screen.write(18, 2, 'VOTRE CHOIX : ' + current.input.value, { foreground: 'yellow' });
        footer(screen);
      },
      acceptInput: key => /^[1-4]$/.test(key),
      send: value => {
        if (value === '1') setAction('load-operations');
        if (value === '2') return 'search';
        if (value === '3') setAction('open-preparation');
        if (value === '4') setAction('load-status');
      }
    }));

    terminalSession.register(new mt.MinitelPage('operations', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        screen.write(2, 1, 'MES OPERATIONS HERMES', { foreground: 'yellow' });
        if (hm.error) return showError(screen);
        const pages = Math.max(1, Math.ceil(hm.operations.length / 7));
        hm.operationPage = Math.max(1, Math.min(pages, hm.operationPage));
        const items = hm.operations.slice((hm.operationPage - 1) * 7, hm.operationPage * 7);
        screen.write(4, 1, 'N VOL      TRAJET       ETAT', { foreground: 'cyan' });
        items.forEach((op, index) => {
          const row = 6 + index * 2;
          const flight = core.operationFlight(op);
          screen.write(row, 1, String(index + 1) + ' ' + fit(flight.ident, 8) + ' ' + fit(flight.departure + '>' + flight.arrival, 11) + ' ' + fit(op.status || 'RESERVED', 13));
          screen.write(row + 1, 3, fit(op.aircraft?.registration || 'APPAREIL A SELECTIONNER', 35), { foreground: op.aircraft ? 'green' : 'yellow' });
        });
        if (!hm.operations.length) screen.write(8, 4, 'AUCUNE OPERATION RESERVEE');
        screen.write(20, 1, 'CHOIX : ' + current.input.value + '  PAGE ' + hm.operationPage + '/' + pages, { foreground: 'yellow' });
        footer(screen, pages > 1);
      },
      acceptInput: key => /^[1-7]$/.test(key),
      send: value => {
        const index = ((hm.operationPage - 1) * 7) + Number(value) - 1;
        if (hm.operations[index]) setAction('select-operation', { operation: hm.operations[index] });
      },
      next: () => {
        const pages = Math.max(1, Math.ceil(hm.operations.length / 7));
        hm.operationPage = Math.min(pages, hm.operationPage + 1);
      },
      previous: () => {
        if (hm.operationPage > 1) { hm.operationPage -= 1; return null; }
        return 'home';
      }
    }));

    terminalSession.register(new mt.MinitelPage('search', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        screen.write(3, 2, 'RECHERCHER UN VOL', { foreground: 'yellow' });
        screen.write(6, 2, 'ITF749 OU LFPO>LIRF');
        screen.write(9, 2, '> ' + current.input.value, { foreground: 'cyan' });
        screen.write(13, 2, 'ENVOI : RECHERCHER');
        screen.write(14, 2, 'RESULTATS RESPECTENT QUALIFICATIONS');
        footer(screen);
      },
      acceptInput: key => /^[A-Za-z0-9 >\-]$/.test(key),
      send: value => setAction('search', { query: value.trim() })
    }));

    terminalSession.register(new mt.MinitelPage('search-results', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        screen.write(2, 1, 'VOLS RESERVABLES', { foreground: 'yellow' });
        if (hm.error) return showError(screen);
        const pages = Math.max(1, Math.ceil(hm.searchResults.length / 7));
        hm.searchPage = Math.max(1, Math.min(pages, hm.searchPage));
        const items = hm.searchResults.slice((hm.searchPage - 1) * 7, hm.searchPage * 7);
        screen.write(4, 1, 'N VOL      DEPART  ARRIVEE', { foreground: 'cyan' });
        items.forEach((flight, index) => {
          const row = 6 + index * 2;
          const normalized = core.operationFlight(flight);
          screen.write(row, 1, String(index + 1) + ' ' + fit(normalized.ident, 8) + ' ' + fit(normalized.departure, 7) + ' ' + fit(normalized.arrival, 7));
          screen.write(row + 1, 3, fit(normalized.route || 'ROUTE PROGRAMMEE', 35), { foreground: 'cyan' });
        });
        if (!hm.searchResults.length) screen.write(8, 5, 'AUCUN VOL RESERVABLE');
        screen.write(20, 1, 'CHOIX : ' + current.input.value + ' + ENVOI', { foreground: 'yellow' });
        footer(screen, pages > 1);
      },
      acceptInput: key => /^[1-7]$/.test(key),
      send: value => {
        const index = ((hm.searchPage - 1) * 7) + Number(value) - 1;
        if (hm.searchResults[index]) setAction('reserve', { flight: hm.searchResults[index] });
      },
      next: () => {
        const pages = Math.max(1, Math.ceil(hm.searchResults.length / 7));
        hm.searchPage = Math.min(pages, hm.searchPage + 1);
      },
      previous: () => {
        if (hm.searchPage > 1) { hm.searchPage -= 1; return null; }
        return 'search';
      }
    }));

    terminalSession.register(new mt.MinitelPage('preparation', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        screen.write(2, 1, 'PREPARATION DU VOL', { foreground: 'yellow' });
        if (hm.error) return showError(screen);
        const op = hm.operation;
        if (!op) {
          screen.write(8, 4, 'AUCUNE OPERATION SELECTIONNEE');
          screen.write(11, 4, 'SOMMAIRE > MES OPERATIONS');
          return footer(screen);
        }
        const flight = core.operationFlight(op);
        const checks = core.operationChecks(op, hm.dispatch, hm.status);
        screen.write(4, 2, fit(flight.ident, 9) + ' ' + fit(flight.departure, 4) + ' > ' + fit(flight.arrival, 4));
        checks.forEach((check, index) => {
          screen.write(6 + index * 2, 2, (check[1] ? '[OK] ' : '[--] ') + fit(check[0], 10) + ' ' + fit(check[2], 16), { foreground: check[1] ? 'green' : 'yellow' });
        });
        screen.write(16, 2, '1 CHOISIR APPAREIL', { foreground: 'cyan' });
        screen.write(17, 2, '2 PREPARER SIMBRIEF', { foreground: 'cyan' });
        screen.write(18, 2, '3 PREPARER PIREP', { foreground: 'cyan' });
        screen.write(19, 2, '4 ETAT DISPATCH', { foreground: 'cyan' });
        screen.write(20, 2, '5 DEMARRER ENREGISTREMENT', { foreground: 'cyan' });
        screen.write(21, 2, 'CHOIX : ' + current.input.value, { foreground: 'yellow' });
        footer(screen);
      },
      acceptInput: key => /^[1-5]$/.test(key),
      send: value => {
        if (value === '1') setAction('load-aircraft');
        if (value === '2') return 'simbrief';
        if (value === '3') setAction('prefile');
        if (value === '4') setAction('load-dispatch', { page: 'dispatch' });
        if (value === '5') setAction('start-flight');
      }
    }));

    terminalSession.register(new mt.MinitelPage('aircraft', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        screen.write(2, 1, 'APPAREILS ELIGIBLES', { foreground: 'yellow' });
        if (hm.error) return showError(screen);
        screen.write(4, 1, 'N IMMATR.    TYPE    BASE', { foreground: 'cyan' });
        hm.aircraft.slice(0, 7).forEach((plane, index) => {
          const row = 6 + index * 2;
          screen.write(row, 1, String(index + 1) + ' ' + fit(plane.registration, 10) + ' ' + fit(plane.icao || plane.type_key, 7) + ' ' + fit(plane.airport || '---', 5));
          screen.write(row + 1, 3, fit((plane.type_label || plane.subfleet || '') + ' PAX ' + (plane.passengers ?? '-'), 35), { foreground: 'cyan' });
        });
        if (!hm.aircraft.length) screen.write(8, 4, 'AUCUN APPAREIL DISPONIBLE');
        screen.write(20, 1, 'CHOIX : ' + current.input.value + ' + ENVOI', { foreground: 'yellow' });
        footer(screen);
      },
      acceptInput: key => /^[1-7]$/.test(key),
      send: value => {
        const plane = hm.aircraft[Number(value) - 1];
        if (plane) setAction('select-aircraft', { plane });
      }
    }));

    terminalSession.register(new mt.MinitelPage('simbrief', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        screen.write(2, 1, 'SIMBRIEF / HERMES', { foreground: 'yellow' });
        if (!hm.operation?.aircraft?.id) {
          screen.write(7, 3, 'SELECTIONNEZ D ABORD UN APPAREIL', { foreground: 'yellow' });
          return footer(screen);
        }
        screen.write(5, 2, '1 OUVRIR SIMBRIEF COMPTE', { foreground: 'cyan' });
        screen.write(6, 2, '2 IMPORTER VIA PILOT ID', { foreground: 'cyan' });
        screen.write(7, 2, '3 GENERATION CLE COMPAGNIE', { foreground: 'cyan' });
        screen.write(8, 2, '4 IMPORTER GENERATION COMPAGNIE', { foreground: 'cyan' });
        screen.write(11, 2, 'OFP........... ' + (hm.operation?.simbrief?.available ? 'DISPONIBLE' : 'ABSENT'), { foreground: hm.operation?.simbrief?.available ? 'green' : 'yellow' });
        screen.write(12, 2, 'API COMPAGNIE. ' + (hm.operation?.simbrief?.company_api_available ? 'DISPONIBLE' : 'NON CONFIGUREE'), { foreground: hm.operation?.simbrief?.company_api_available ? 'green' : 'yellow' });
        screen.write(19, 2, 'CHOIX : ' + current.input.value, { foreground: 'yellow' });
        footer(screen);
      },
      acceptInput: key => /^[1-4]$/.test(key),
      send: value => {
        if (value === '1') setAction('simbrief-redirect');
        if (value === '2') return 'simbrief-pilot';
        if (value === '3') setAction('simbrief-session');
        if (value === '4') setAction('simbrief-import');
      }
    }));

    terminalSession.register(new mt.MinitelPage('simbrief-pilot', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        screen.write(3, 2, 'IMPORT SIMBRIEF / PILOT ID', { foreground: 'yellow' });
        screen.write(6, 2, 'PILOT ID NUMERIQUE');
        screen.write(10, 2, '> ' + current.input.value, { foreground: 'cyan' });
        screen.write(14, 2, 'ENVOI : IMPORTER DERNIER OFP');
        footer(screen);
      },
      acceptInput: key => /^\d$/.test(key),
      send: value => {
        if (/^\d{1,7}$/.test(value)) setAction('simbrief-account-import', { pilotId: value });
      }
    }));

    terminalSession.register(new mt.MinitelPage('dispatch', {
      onRender: (_ctx, screen) => {
        serviceLine(screen);
        screen.write(2, 1, 'ETAT DISPATCH / READY', { foreground: 'yellow' });
        if (hm.error) return showError(screen);
        const dispatch = hm.dispatch || {};
        screen.write(4, 2, 'STATUT....... ' + fit(dispatch.status || 'INCONNU', 20), { foreground: dispatch.ready ? 'green' : 'yellow' });
        (dispatch.checks || []).slice(0, 4).forEach((check, index) => {
          const row = 7 + index * 3;
          screen.write(row, 2, (check.ready ? '[OK] ' : '[--] ') + fit(check.code, 10), { foreground: check.ready ? 'green' : 'yellow' });
          screen.write(row + 1, 4, fit(check.label, 34));
        });
        const preflight = core.preflightState(hm.status, dispatch);
        screen.write(20, 2, 'LOCAL READY... ' + (preflight.ready ? 'OUI' : 'NON'), { foreground: preflight.ready ? 'green' : 'yellow' });
        footer(screen);
      }
    }));

    terminalSession.register(new mt.MinitelPage('simulator', {
      onRender: (_ctx, screen) => {
        serviceLine(screen);
        screen.write(2, 1, 'ETAT SIMULATEUR', { foreground: 'yellow' });
        const status = hm.status || {};
        const latest = status.latest || status.Latest || {};
        const value = (camel, pascal = camel) => core.snapshotValue(latest, camel, pascal);
        screen.write(5, 2, 'LIAISON........ ' + (status.latest ? 'ACTIVE' : 'EN ATTENTE'), { foreground: status.latest ? 'green' : 'yellow' });
        screen.write(6, 2, 'SIMULATEUR..... ' + fit(status.sim || 'NON DETECTE', 20));
        screen.write(8, 2, 'ALTITUDE....... ' + fit(value('altitude', 'Altitude') == null ? '---' : Math.round(value('altitude', 'Altitude')) + ' FT', 18));
        screen.write(9, 2, 'VITESSE SOL.... ' + fit(value('gs', 'Gs') == null ? '---' : Math.round(value('gs', 'Gs')) + ' KT', 18));
        screen.write(10, 2, 'CARBURANT...... ' + fit(value('fuel', 'Fuel') == null ? '---' : Math.round(value('fuel', 'Fuel')) + ' LB', 18));
        screen.write(12, 2, 'PHASE.......... ' + fit(status.flight?.phase || status.flight?.Phase || 'STANDBY', 18));
        screen.write(13, 2, 'TRACKING....... ' + ((status.flight?.recording ?? status.flight?.Recording) ? 'ACTIF' : 'ARRETE'), { foreground: (status.flight?.recording ?? status.flight?.Recording) ? 'green' : 'yellow' });
        screen.write(17, 2, 'F2 : RAFRAICHIR');
        footer(screen);
      },
      repeat: (_ctx, refresh) => {
        if (refresh) setAction('load-status', { stay: true });
      }
    }));

    terminalSession.register(new mt.MinitelPage('result', {
      onRender: (_ctx, screen) => {
        serviceLine(screen);
        screen.write(4, 2, fit(hm.result?.title || 'HERMES', 36), { foreground: 'yellow' });
        screen.write(8, 2, fit(hm.result?.message || 'TERMINE', 36), { foreground: 'green' });
        if (hm.result?.detail) screen.write(11, 2, fit(hm.result.detail, 36), { foreground: 'cyan' });
        screen.write(17, 2, 'RETOUR : PREPARATION');
        screen.write(18, 2, 'SOMMAIRE : ACCUEIL');
        footer(screen);
      },
      previous: () => hm.operation ? 'preparation' : 'home'
    }));
  };

  const setResult = (title, message, detail = '') => {
    hm.result = { title, message, detail };
    hm.error = null;
  };

  const refreshStatus = async () => {
    try {
      hm.status = await runCall('/api/status');
      hm.authenticated = Boolean(hm.status?.connected);
      return hm.status;
    } catch {
      return hm.status;
    }
  };

  const loadMe = async () => {
    try {
      hm.pilot = await runCall('/api/v1/me');
    } catch {
      hm.pilot = hm.pilot || {};
    }
  };

  const loadOperation = async operation => {
    const id = core.operationId(operation);
    if (!id) throw new Error('OPERATION INVALIDE');
    hm.operation = await runCall('/api/v1/operations/' + encodeURIComponent(id));
    hm.dispatch = await runCall('/api/v1/operations/' + encodeURIComponent(id) + '/dispatch');
    if (hm.dispatch?.operation) hm.operation = hm.dispatch.operation;
    await refreshStatus();
    return hm.operation;
  };

  const fail = async error => {
    hm.error = error?.message || 'ERREUR HERMES';
    await show(terminalSession.render(), true);
  };

  const runAction = async pending => {
    try {
      if (pending.type === 'login') {
        const password = pending.password;
        const response = await runCall('/api/login', { login: hm.login, password });
        pending.password = null;
        hm.pilot = response?.user || response || {};
        hm.authenticated = true;
        if (typeof setAuthenticated === 'function') setAuthenticated(true);
        terminalSession.homePageId = 'home';
        await refreshStatus();
        await loadMe();
        return show(terminalSession.go('home'), true);
      }

      if (!hm.authenticated) throw new Error('CONNECTEZ-VOUS A AIR INTER');

      if (pending.type === 'load-operations') {
        hm.error = null;
        const data = await runCall('/api/v1/operations');
        hm.operations = data?.operations || [];
        hm.operationPage = 1;
        return show(terminalSession.go('operations'), true);
      }

      if (pending.type === 'search') {
        hm.error = null;
        const params = new URLSearchParams(core.parseFlightSearch(pending.query));
        const data = await runCall('/api/v1/flights' + (params.size ? '?' + params.toString() : ''));
        hm.searchResults = Array.isArray(data) ? data : [];
        hm.searchPage = 1;
        return show(terminalSession.go('search-results'), true);
      }

      if (pending.type === 'reserve') {
        const reserved = await runCall('/api/v1/flights/' + encodeURIComponent(pending.flight.id) + '/reserve', {});
        await loadOperation(reserved);
        setResult('RESERVATION CONFIRMEE', core.operationFlight(hm.operation).ident || 'VOL AIR INTER', opRef() || '');
        return show(terminalSession.go('result'), true);
      }

      if (pending.type === 'select-operation') {
        await loadOperation(pending.operation);
        return show(terminalSession.go('preparation'), true);
      }

      if (pending.type === 'open-preparation') {
        if (!hm.operation) {
          const data = await runCall('/api/v1/operations');
          hm.operations = data?.operations || [];
          if (hm.operations.length === 1) await loadOperation(hm.operations[0]);
        } else {
          await loadOperation(hm.operation);
        }
        return show(terminalSession.go(hm.operation ? 'preparation' : 'operations'), true);
      }

      if (pending.type === 'load-aircraft') {
        const data = await runCall(path('/aircraft-eligibility'));
        hm.aircraft = data?.available || [];
        hm.error = null;
        return show(terminalSession.go('aircraft'), true);
      }

      if (pending.type === 'select-aircraft') {
        const assignment = await runCall(path('/aircraft'), { _method: 'PUT', aircraft_id: pending.plane.id });
        hm.operation.aircraft = assignment?.aircraft || pending.plane;
        await loadOperation(hm.operation);
        setResult('APPAREIL AFFECTE', hm.operation.aircraft?.registration || pending.plane.registration, assignment?.status || '');
        return show(terminalSession.go('result'), true);
      }

      if (pending.type === 'load-dispatch') {
        hm.dispatch = await runCall(path('/dispatch'));
        if (hm.dispatch?.operation) hm.operation = hm.dispatch.operation;
        await refreshStatus();
        return show(terminalSession.go(pending.page || 'dispatch'), true);
      }

      if (pending.type === 'load-status') {
        await refreshStatus();
        return show(terminalSession.go(pending.stay ? terminalSession.currentPageId : 'simulator'), true);
      }

      if (pending.type === 'simbrief-redirect') {
        const ready = await runCall(path('/simbrief/readiness'), planningPayload());
        if (!(ready?.ready_account ?? ready?.ready)) throw new Error('SIMBRIEF COMPTE NON PRET');
        const payload = await runCall(path('/simbrief/redirect'), planningPayload());
        await openExternal(payload.url);
        setResult('SIMBRIEF OUVERT', 'GENEREZ L OFP PUIS REVENEZ', 'UTILISEZ IMPORT VIA PILOT ID');
        return show(terminalSession.go('result'), true);
      }

      if (pending.type === 'simbrief-account-import') {
        const briefing = await runCall(path('/simbrief/account/import'), {
          aircraft_id: hm.operation.aircraft.id,
          pilot_id: pending.pilotId
        });
        await loadOperation(hm.operation);
        setResult('OFP IMPORTE', 'SIMBRIEF #' + (briefing?.id || ''), (briefing?.origin || '') + ' > ' + (briefing?.destination || ''));
        return show(terminalSession.go('result'), true);
      }

      if (pending.type === 'simbrief-session') {
        const ready = await runCall(path('/simbrief/readiness'), planningPayload());
        if (!(ready?.ready_company_api ?? ready?.ready)) throw new Error('API SIMBRIEF NON PRETE');
        const payload = await runCall(path('/simbrief/session'), planningPayload());
        hm.simbriefState = payload.state;
        try { sessionStorage.setItem('hermes-minitel-simbrief-' + opRef(), payload.state); } catch {}
        submitWorker(payload);
        setResult('SIMBRIEF COMPAGNIE', 'GENERATION OUVERTE', 'TERMINEZ PUIS CHOISISSEZ IMPORTER');
        return show(terminalSession.go('result'), true);
      }

      if (pending.type === 'simbrief-import') {
        let stateValue = hm.simbriefState;
        try { stateValue ||= sessionStorage.getItem('hermes-minitel-simbrief-' + opRef()); } catch {}
        if (!stateValue) throw new Error('AUCUNE SESSION SIMBRIEF ACTIVE');
        const briefing = await runCall(path('/simbrief/import'), {
          aircraft_id: hm.operation.aircraft.id,
          state: stateValue
        });
        try { sessionStorage.removeItem('hermes-minitel-simbrief-' + opRef()); } catch {}
        hm.simbriefState = null;
        await loadOperation(hm.operation);
        setResult('OFP IMPORTE', 'SIMBRIEF #' + (briefing?.id || ''), (briefing?.origin || '') + ' > ' + (briefing?.destination || ''));
        return show(terminalSession.go('result'), true);
      }

      if (pending.type === 'prefile') {
        const result = await runCall(path('/pirep'), {});
        await loadOperation(hm.operation);
        setResult('PIREP PREPARE', 'PIREP #' + (result?.pirep_id || result?.pirep?.id || ''), result?.already_prefiled ? 'DEJA PREPARE' : 'PRE-DEPOT EFFECTUE');
        return show(terminalSession.go('result'), true);
      }

      if (pending.type === 'start-flight') {
        hm.dispatch = await runCall(path('/dispatch'));
        await refreshStatus();
        const preflight = core.preflightState(hm.status, hm.dispatch);
        if (!preflight.ready) throw new Error('CONTROLES AVANT DEPART NON SATISFAITS');
        const pirepId = currentPirepId();
        if (!pirepId) throw new Error('PIREP PRE-DEPOSE INTROUVABLE');
        await runCall('/api/start', { pirepId: String(pirepId), operationId: opRef() });
        await refreshStatus();
        setResult('ENREGISTREMENT DEMARRE', 'HERMES TRACKING ACTIF', 'LE SUIVI DETAILLE ARRIVE AU LOT M5');
        return show(terminalSession.go('result'), true);
      }
    } catch (error) {
      return fail(error);
    }
  };

  const handleAction = async () => {
    const pending = terminalSession?.context?.__hermesMinitelAction;
    if (!pending) return;
    terminalSession.context.__hermesMinitelAction = null;
    await runAction(pending);
  };

  const tick = async () => {
    if (!hm.active) return;
    const previous = hm.status;
    await refreshStatus();
    if (!hm.active || previous === hm.status) return;
    if (['home', 'preparation', 'dispatch', 'simulator'].includes(terminalSession.currentPageId)) {
      await show(terminalSession.render(), false);
    }
  };

  async function start() {
    if (hm.active || document.body.dataset.era !== 'minitel') return;
    hm.active = true;
    host = document.createElement('div');
    host.id = 'hermes-minitel-root';
    host.className = 'hermes-minitel-overlay';
    document.body.appendChild(host);

    shell = new mt.MinitelShell({
      host,
      service: '3615 HERMES',
      product: 'HERMES',
      identity: '',
      onExit: () => {
        try { localStorage.hermesEra = 'modern'; } catch {}
        window.location.reload();
      }
    });

    const capability = shell.capability();
    if (!capability.allowed) {
      shell.showFallback(capability.reason || 'unsupported');
      return;
    }

    shell.mount();
    renderer = new mt.MinitelDomRenderer(shell.terminalNode, { speed: 'fast' });
    await refreshStatus();
    hm.authenticated = Boolean(hm.status?.connected);
    if (hm.authenticated) await loadMe();

    terminalSession = new mt.MinitelSession({
      homePageId: hm.authenticated ? 'home' : 'login-user',
      speed: 'fast',
      context: {}
    });
    registerPages();

    keyboard = new mt.MinitelKeyboardController(terminalSession, renderer, document, {
      afterDispatch: () => { handleAction(); }
    });
    shell.session = terminalSession;
    shell.renderer = renderer;
    shell.keyboard = keyboard;
    shell.identity = hm.pilot?.ident || '';
    await shell.start();
    hm.timer = window.setInterval(tick, 2000);
    updateCursor();
  }

  function stop() {
    hm.active = false;
    if (hm.timer) window.clearInterval(hm.timer);
    hm.timer = null;
    keyboard?.detach?.();
    shell?.destroy?.();
    document.getElementById('hermes-minitel-root')?.remove();
  }

  window.HermesMinitel = Object.freeze({ start, stop, state: hm });

  window.addEventListener('DOMContentLoaded', () => {
    if (document.body.dataset.era === 'minitel') start();
  }, { once: true });

  window.addEventListener('hermes:auth-changed', event => {
    hm.authenticated = Boolean(event.detail?.authenticated);
    if (document.body.dataset.era === 'minitel' && !hm.active) start();
  });
})();
