(() => {
  'use strict';

  const mt = window.AirInterMinitel;
  const core = window.HermesMinitelCore;
  if (!mt || !core) return;

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
    } catch {}
    return { speed, displayMode };
  };

  const persistMinitelPreferences = (preferences) => {
    try {
      if (mt.SPEEDS[preferences.speed]) localStorage.setItem(MINITEL_SPEED_KEY, preferences.speed);
      if (mt.DISPLAY_MODES[preferences.displayMode]) localStorage.setItem(MINITEL_DISPLAY_KEY, preferences.displayMode);
    } catch {}
  };

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
    timer: null,
    datalink: null,
    datalinkPage: 1,
    selectedMessage: null,
    messagePage: 1,
    replyTo: null,
    journalPage: 1,
    network: null,
    networkPage: 1,
    review: null,
    filedReview: null,
    reviewListMode: 'observations',
    reviewListPage: 1,
    lastDatalinkRefreshAt: 0,
    lastNetworkRefreshAt: 0,
    localPlan: null
  };

  let host;
  let shell;
  let renderer;
  let keyboard;
  let terminalSession;

  const unwrapHm = value => value?.data ?? value;
  const fit = core.fit;

  const fillRow = (screen, row, background = 'black', foreground = 'white') => {
    screen.fill(row, 0, 39, ' ', { background, foreground });
  };

  const writeBand = (screen, row, text, background = 'blue', foreground = 'white') => {
    fillRow(screen, row, background, foreground);
    screen.write(row, 1, fit(text, 38), { background, foreground });
  };

  const titleBand = (screen, title, subtitle = '') => {
    mt.writeAirInterMosaic(screen, 2, 1, { foreground: 'blue', separatedMosaic: true });
    screen.write(2, 12, fit('AIR INTER', 27), { foreground: 'yellow' });
    screen.write(3, 12, fit(title, 27), { foreground: 'cyan' });
    screen.write(4, 12, fit(subtitle || 'TERMINAL DE VOL', 27), { foreground: 'white' });
  };

  const videotexHeader = (screen, title, subtitle = 'SERVICE PILOTES') => {
    mt.writeAirInterMosaic(screen, 2, 1, { foreground: 'blue', separatedMosaic: true });
    screen.write(2, 12, fit('AIR INTER', 27), { foreground: 'yellow' });
    screen.write(3, 12, fit(title, 27), { foreground: 'cyan' });
    screen.write(4, 12, fit(subtitle, 27), { foreground: 'white' });
  };

  const noticeBand = (screen, row, text, background = 'green', foreground = 'black') => {
    writeBand(screen, row, text, background, foreground);
  };

  const sectionBand = (screen, row, text, background = 'blue', foreground = 'white') => {
    writeBand(screen, row, text, background, foreground);
  };

  const menuLine = (screen, row, number, label, accent = false) => {
    screen.write(row, 2, String(number), { foreground: accent ? 'yellow' : 'cyan' });
    screen.write(row, 4, '- ' + fit(label, 33), { foreground: accent ? 'yellow' : 'white' });
  };

  const statusLine = (screen, row, label, value, tone = 'cyan') => {
    screen.write(row, 2, fit(label, 13), { foreground: 'cyan' });
    screen.write(row, 15, fit(value, 23), { foreground: tone });
  };

  const serviceLine = screen => {
    const identity = hm.pilot?.ident || hm.pilot?.pilot_id || hm.pilot?.pilotId || 'CREW';
    screen.write(0, 0, mt.buildServiceLine({ service: '3615 HERMES', identity, state: 'C' }), { foreground: 'cyan', background: 'black' });
  };

  const footer = (screen, paging = false) => {
    if (paging) {
      screen.write(24, 1, 'RETOUR', { foreground: 'cyan' });
      screen.write(24, 32, 'SUITE', { foreground: 'cyan' });
    }
  };

  const promptLine = (screen, row, value = '', label = 'VOTRE CHOIX') => {
    screen.write(row, 4, fit(label, 14) + ' : ' + String(value ?? ''), { foreground: 'yellow' });
  };

  const pageLine = (screen, row, page, pages) => {
    screen.write(row, 2, 'PAGE ' + page + '/' + pages, { foreground: 'cyan' });
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
    if (page === 'home') return renderer.showCursor(21, Math.min(39, 21 + value), true);
    if (page === 'search') return renderer.showCursor(10, Math.min(39, 6 + value), true);
    if (['operations', 'search-results'].includes(page)) return renderer.showCursor(21, Math.min(39, 21 + value), true);
    if (page === 'aircraft') return renderer.showCursor(21, Math.min(39, 21 + value), true);
    if (page === 'preparation') return renderer.showCursor(21, Math.min(39, 21 + value), true);
    if (['simbrief', 'simbrief-account', 'simbrief-api', 'simbrief-local', 'parameters'].includes(page)) return renderer.showCursor(20, Math.min(39, 10 + value), true);
    if (page === 'simbrief-pilot') return renderer.showCursor(10, Math.min(39, 4 + value), true);
    if (page === 'flight-live') return renderer.showCursor(21, Math.min(39, 21 + value), true);
    if (page === 'datalink') return renderer.showCursor(21, Math.min(39, 21 + value), true);
    if (page === 'datalink-message') return renderer.showCursor(20, Math.min(39, 9 + value), true);
    if (page === 'datalink-compose') {
      const row = 12 + Math.min(3, Math.floor(value / 36));
      const column = 2 + (value % 36);
      return renderer.showCursor(row, Math.min(39, column), true);
    }
    if (page === 'journal') return renderer.showCursor(21, Math.min(39, 21 + value), true);
    if (page === 'network') return renderer.showCursor(20, Math.min(39, 9 + value), true);
    if (page === 'review') return renderer.showCursor(20, Math.min(39, 9 + value), true);
    if (page === 'review-list') return renderer.showCursor(20, Math.min(39, 9 + value), true);
    if (page === 'recovery') return renderer.showCursor(20, Math.min(39, 9 + value), true);
    renderer.showCursor(0, 0, false);
  };

  const showError = screen => {
    screen.write(7, 10, 'LIAISON INTERROMPUE', { foreground: 'red', blink: true });
    screen.write(10, 2, fit(hm.error || 'SERVICE HERMES INDISPONIBLE', 36), { foreground: 'yellow' });
    screen.write(14, 3, 'RETOUR POUR PAGE PRECEDENTE', { foreground: 'cyan' });
    screen.write(16, 3, 'REPETITION POUR RETRANSMETTRE', { foreground: 'cyan' });
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
        videotexHeader(screen, 'HERMES', 'SERVICE PILOTES');
        noticeBand(screen, 6, 'IDENTIFICATION AIR INTER', 'red', 'white');
        screen.write(8, 2, 'IDENTIFIANT / E-MAIL', { foreground: 'yellow' });
        screen.write(10, 2, '> ' + fit(current.input.value, 35), { foreground: 'cyan' });
        screen.write(13, 2, 'ENVOI POUR CONTINUER', { foreground: 'green' });
        screen.write(16, 2, 'ACCES RESERVE AUX PILOTES', { foreground: 'white' });
        screen.write(17, 2, 'HERMES / PROMETHEE', { foreground: 'cyan' });
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
        videotexHeader(screen, 'HERMES', 'SERVICE PILOTES');
        sectionBand(screen, 6, 'PILOTE ' + fit(hm.login, 31), 'magenta', 'white');
        screen.write(8, 2, 'MOT DE PASSE', { foreground: 'yellow' });
        screen.write(10, 2, '> ' + fit('*'.repeat(current.input.value.length), 35), { foreground: 'cyan' });
        screen.write(13, 2, 'ENVOI POUR OUVRIR LA SESSION', { foreground: 'green' });
        screen.write(17, 2, 'MOT DE PASSE NON CONSERVE', { foreground: 'cyan' });
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
        titleBand(screen, 'HERMES', 'TERMINAL DE VOL');
        menuLine(screen, 7, 1, 'MES OPERATIONS');
        menuLine(screen, 8, 2, 'PREPARATION / ENREGISTREMENT');
        menuLine(screen, 9, 3, 'VOL EN COURS');
        menuLine(screen, 10, 4, 'JOURNAL DE BORD');
        menuLine(screen, 11, 5, 'MESSAGERIE DATALINK');
        menuLine(screen, 12, 6, 'RESEAU EQUIPAGES');
        menuLine(screen, 13, 7, 'PARAMETRES TERMINAL');
        if (hm.status?.recoveryAvailable) menuLine(screen, 14, 8, 'VOL INTERROMPU / REPRISE', true);
        statusLine(screen, 16, 'PROMETHEE', hm.status?.connected ? 'CONNECTE' : 'HORS LIGNE', hm.status?.connected ? 'green' : 'red');
        statusLine(screen, 17, 'SIMULATEUR', hm.status?.latest ? 'CONNECTE' : 'EN ATTENTE', hm.status?.latest ? 'green' : 'yellow');
        statusLine(screen, 18, 'OPERATION', opRef() || hm.status?.flight?.operationId || hm.status?.flight?.OperationId || 'AUCUNE', opRef() ? 'green' : 'cyan');
        promptLine(screen, 21, current.input.value);
        footer(screen);
      },
      acceptInput: key => /^[1-8]$/.test(key),
      send: value => {
        if (value === '1') setAction('load-operations');
        if (value === '2') setAction('open-preparation');
        if (value === '3') setAction('open-flight-live');
        if (value === '4') return 'journal';
        if (value === '5') setAction('load-datalink');
        if (value === '6') setAction('load-network');
        if (value === '7') return 'parameters';
        if (value === '8' && hm.status?.recoveryAvailable) return 'recovery';
      }
    }));

    terminalSession.register(new mt.MinitelPage('operations', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        titleBand(screen, 'MES OPERATIONS', 'RESERVATIONS ACTIVES');
        if (hm.error) return showError(screen);
        const pages = Math.max(1, Math.ceil(hm.operations.length / 6));
        hm.operationPage = Math.max(1, Math.min(pages, hm.operationPage));
        const items = hm.operations.slice((hm.operationPage - 1) * 6, hm.operationPage * 6);
        screen.write(5, 1, 'N VOL      TRAJET      APPAREIL', { foreground: 'yellow' });
        items.forEach((op, index) => {
          const row = 7 + index * 2;
          const flight = core.operationFlight(op);
          screen.write(row, 1, String(index + 1) + ' ' + fit(flight.ident, 8) + ' ' + fit(flight.departure + '>' + flight.arrival, 10) + ' ' + fit(op.aircraft?.registration || 'A CHOISIR', 10), { foreground: 'white' });
          screen.write(row + 1, 3, fit(op.status || 'RESERVEE', 15) + fit(op.aircraft?.type_label || op.aircraft?.subfleet || '', 20), { foreground: op.aircraft ? 'green' : 'cyan' });
        });
        if (!hm.operations.length) {
          screen.write(9, 4, 'AUCUNE RESERVATION ACTIVE', { foreground: 'yellow' });
          screen.write(11, 4, '8 POUR RECHERCHER UN VOL', { foreground: 'cyan' });
        }
        screen.write(19, 1, '8 RECHERCHER / RESERVER', { foreground: 'cyan' });
        pageLine(screen, 19, hm.operationPage, pages);
        promptLine(screen, 21, current.input.value);
        footer(screen, pages > 1);
      },
      acceptInput: key => /^[1-8]$/.test(key),
      send: value => {
        if (value === '8') return 'search';
        const index = ((hm.operationPage - 1) * 6) + Number(value) - 1;
        if (hm.operations[index]) setAction('select-operation', { operation: hm.operations[index] });
      },
      next: () => {
        const pages = Math.max(1, Math.ceil(hm.operations.length / 6));
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
        titleBand(screen, 'RECHERCHER UN VOL', 'ITF749 OU LFPO>LIRF');
        screen.write(8, 2, 'VOL / TRAJET :', { foreground: 'white' });
        screen.write(10, 4, '> ' + current.input.value, { foreground: 'cyan' });
        screen.write(14, 4, 'ENVOI POUR RECHERCHER', { foreground: 'yellow' });
        screen.write(16, 4, 'QUALIFICATIONS PILOTE APPLIQUEES', { foreground: 'cyan' });
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
        promptLine(screen, 21, current.input.value, 'VOL A RESERVER');
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
        titleBand(screen, 'PREPARATION', 'OPERATION ACTIVE');
        if (hm.error) return showError(screen);
        const op = hm.operation;
        if (!op) {
          noticeBand(screen, 8, 'AUCUNE OPERATION SELECTIONNEE', 'red', 'white');
          screen.write(12, 3, 'SOMMAIRE > MES OPERATIONS', { foreground: 'cyan' });
          return footer(screen);
        }
        const flight = core.operationFlight(op);
        const checks = core.operationChecks(op, hm.dispatch, hm.status);
        screen.write(5, 2, fit(flight.ident, 9) + fit(flight.departure + '>' + flight.arrival, 12) + fit(op.aircraft?.registration || '---', 12), { foreground: 'yellow' });
        checks.forEach((check, index) => {
          const row = 7 + index;
          screen.write(row, 2, (check[1] ? '[OK] ' : '[--] ') + fit(check[0], 10) + fit(check[2], 18), { foreground: check[1] ? 'green' : 'yellow' });
        });
        sectionBand(screen, 13, 'ACTIONS DE PREPARATION', 'blue', 'white');
        menuLine(screen, 14, 1, 'CHOISIR APPAREIL');
        menuLine(screen, 15, 2, 'PREPARER PLAN / SIMBRIEF', true);
        menuLine(screen, 16, 3, 'PRE-DEPOSER PIREP');
        menuLine(screen, 17, 4, 'ETAT DISPATCH');
        menuLine(screen, 18, 5, 'DEMARRER ENREGISTREMENT');
        promptLine(screen, 21, current.input.value);
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
        titleBand(screen, 'APPAREILS', 'ELIGIBLES POUR LE VOL');
        if (hm.error) return showError(screen);
        screen.write(6, 1, 'N IMMATR.    TYPE    BASE', { foreground: 'cyan' });
        hm.aircraft.slice(0, 7).forEach((plane, index) => {
          const row = 7 + index * 2;
          screen.write(row, 1, String(index + 1) + ' ' + fit(plane.registration, 10) + ' ' + fit(plane.icao || plane.type_key, 7) + ' ' + fit(plane.airport || '---', 5));
          screen.write(row + 1, 3, fit((plane.type_label || plane.subfleet || '') + ' PAX ' + (plane.passengers ?? '-'), 35), { foreground: 'cyan' });
        });
        if (!hm.aircraft.length) screen.write(8, 4, 'AUCUN APPAREIL DISPONIBLE');
        promptLine(screen, 21, current.input.value, 'APPAREIL');
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
        titleBand(screen, 'PLAN DE VOL', 'CHOIX DE LA SOURCE');
        if (!hm.operation?.aircraft?.id) {
          noticeBand(screen, 7, 'APPAREIL REQUIS AVANT SIMBRIEF', 'red', 'white');
          screen.write(11, 3, 'RETOUR > CHOISIR APPAREIL', { foreground: 'cyan' });
          return footer(screen);
        }
        menuLine(screen, 7, 1, 'COMPTE SIMBRIEF', true);
        menuLine(screen, 9, 2, 'API SIMBRIEF / COMPAGNIE');
        menuLine(screen, 11, 3, 'PLAN LOCAL PLN / XML');
        statusLine(screen, 15, 'OFP', hm.operation?.simbrief?.available ? 'DISPONIBLE' : 'ABSENT', hm.operation?.simbrief?.available ? 'green' : 'yellow');
        statusLine(screen, 16, 'API COMPAGNIE', hm.operation?.simbrief?.company_api_available ? 'DISPONIBLE' : 'NON CONFIGUREE', hm.operation?.simbrief?.company_api_available ? 'green' : 'yellow');
        promptLine(screen, 21, current.input.value);
        footer(screen);
      },
      acceptInput: key => /^[1-3]$/.test(key),
      send: value => {
        if (value === '1') return 'simbrief-account';
        if (value === '2') return 'simbrief-api';
        if (value === '3') return 'simbrief-local';
      },
      previous: () => 'preparation'
    }));

    terminalSession.register(new mt.MinitelPage('simbrief-account', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        titleBand(screen, 'COMPTE SIMBRIEF', 'WORKFLOW PILOTE');
        screen.write(6, 2, '1 ENVOYER VOL VERS SIMBRIEF', { foreground: 'cyan' });
        screen.write(8, 2, '2 RECUPERER OFP GENERE', { foreground: 'cyan' });
        screen.write(10, 2, '3 RETOUR SOURCES', { foreground: 'white' });
        screen.write(13, 2, 'ETAPES :', { foreground: 'yellow' });
        screen.write(14, 4, 'ENVOI > GENERATION > RECUPERATION');
        statusLine(screen, 17, 'OFP', hm.operation?.simbrief?.available ? 'DISPONIBLE' : 'A GENERER', hm.operation?.simbrief?.available ? 'green' : 'yellow');
        promptLine(screen, 21, current.input.value);
        footer(screen);
      },
      acceptInput: key => /^[1-3]$/.test(key),
      send: value => {
        if (value === '1') setAction('simbrief-redirect');
        if (value === '2') return 'simbrief-pilot';
        if (value === '3') return 'simbrief';
      },
      previous: () => 'simbrief'
    }));

    terminalSession.register(new mt.MinitelPage('simbrief-api', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        titleBand(screen, 'API SIMBRIEF', 'GENERATION COMPAGNIE');
        const available = Boolean(hm.operation?.simbrief?.company_api_available);
        statusLine(screen, 6, 'API', available ? 'DISPONIBLE' : 'NON CONFIGUREE', available ? 'green' : 'red');
        screen.write(9, 2, '1 GENERER OFP SUR SIMBRIEF', { foreground: available ? 'cyan' : 'yellow' });
        screen.write(11, 2, '2 RECUPERER GENERATION', { foreground: 'cyan' });
        screen.write(13, 2, '3 RETOUR SOURCES', { foreground: 'white' });
        screen.write(16, 2, 'RECUPERATION LIEE A LA SESSION', { foreground: 'yellow' });
        screen.write(20, 2, 'CHOIX : ' + current.input.value, { foreground: 'yellow' });
        footer(screen);
      },
      acceptInput: key => /^[1-3]$/.test(key),
      send: value => {
        if (value === '1') setAction('simbrief-session');
        if (value === '2') setAction('simbrief-import');
        if (value === '3') return 'simbrief';
      },
      previous: () => 'simbrief'
    }));

    terminalSession.register(new mt.MinitelPage('simbrief-local', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        titleBand(screen, 'PLAN LOCAL', 'FICHIER PLN / XML');
        statusLine(screen, 6, 'FICHIER', hm.localPlan?.name || 'AUCUN', hm.localPlan ? 'green' : 'yellow');
        if (hm.localPlan) statusLine(screen, 7, 'TAILLE', Math.max(1, Math.round((hm.localPlan.size || 0) / 1024)) + ' KO');
        screen.write(10, 2, '1 IMPORTER FICHIER', { foreground: 'cyan' });
        screen.write(12, 2, '2 EFFACER PLAN LOCAL', { foreground: 'cyan' });
        screen.write(14, 2, '3 RETOUR SOURCES', { foreground: 'white' });
        screen.write(17, 2, 'LE FICHIER RESTE LOCAL AU PC', { foreground: 'yellow' });
        screen.write(20, 2, 'CHOIX : ' + current.input.value, { foreground: 'yellow' });
        footer(screen);
      },
      acceptInput: key => /^[1-3]$/.test(key),
      send: value => {
        if (value === '1') setAction('local-plan-import');
        if (value === '2') setAction('local-plan-clear');
        if (value === '3') return 'simbrief';
      },
      previous: () => 'simbrief'
    }));

    terminalSession.register(new mt.MinitelPage('parameters', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        videotexHeader(screen, 'HERMES', 'PARAMETRES');
        menuLine(screen, 7, 1, 'AFFICHAGE VIDEOTEX', true);
        menuLine(screen, 9, 2, 'SIMULATEUR / DIAGNOSTIC');
        menuLine(screen, 11, 3, 'SIMBRIEF / PLANIFICATION');
        menuLine(screen, 13, 4, 'RESEAUX / EQUIPAGES');
        menuLine(screen, 15, 5, 'GUIDE CLAVIER');
        screen.write(20, 2, 'CHOIX : ' + current.input.value, { foreground: 'yellow' });
        footer(screen);
      },
      acceptInput: key => /^[1-5]$/.test(key),
      send: (value, context) => {
        if (value === '1') {
          context.__minitelSettingsReturnPage = 'parameters';
          return mt.SYSTEM_PAGES.SETTINGS;
        }
        if (value === '2') setAction('load-status');
        if (value === '3') return 'simbrief';
        if (value === '4') setAction('load-network');
        if (value === '5') return mt.SYSTEM_PAGES.GUIDE;
      },
      previous: () => 'home'
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

    terminalSession.register(new mt.MinitelPage('flight-live', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        const t = core.telemetrySummary(hm.status || {});
        const flight = core.operationFlight(hm.operation || {});
        titleBand(screen, 'VOL EN COURS', t.recording ? 'ENREGISTREMENT ACTIF' : 'ENREGISTREMENT PAUSE');
        screen.write(5, 1, fit(flight.ident || t.pirepId || 'AIR INTER', 9) + ' ' + fit(flight.departure + '>' + flight.arrival, 10) + ' ' + fit(hm.operation?.aircraft?.registration || '', 10), { foreground: 'yellow' });
        statusLine(screen, 7, 'PHASE', t.phase, t.recording ? 'green' : 'yellow');
        statusLine(screen, 8, 'ALTITUDE', t.altitude == null ? '---' : Math.round(t.altitude) + ' FT');
        statusLine(screen, 9, 'IAS / GS', (t.ias == null ? '---' : Math.round(t.ias)) + ' / ' + (t.gs == null ? '---' : Math.round(t.gs)) + ' KT');
        statusLine(screen, 10, 'CARBURANT', t.fuel == null ? '---' : Math.round(t.fuel) + ' LB');
        statusLine(screen, 11, 'TEMPS', t.airborneMinutes + ' MIN');
        statusLine(screen, 12, 'DISTANCE', t.distance.toFixed(1) + ' NM');
        screen.write(14, 2, 'ETAT DES LIAISONS', { foreground: 'yellow' });
        screen.write(15, 1, 'PROMETHEE ' + (hm.status?.connected ? 'OK' : 'HS') + '   SIM ' + (hm.status?.latest ? 'OK' : '--'), { foreground: hm.status?.connected && hm.status?.latest ? 'green' : 'yellow' });
        screen.write(16, 1, 'TRACKING  ' + (t.recording ? 'OK' : '--') + '   SYNC ' + fit(t.syncState, 10), { foreground: t.pending ? 'yellow' : 'green' });
        if (t.warning) screen.write(17, 1, fit(t.warning, 38), { foreground: 'red' });
        screen.write(18, 2, '1 ' + (t.recording ? 'PAUSE' : 'REPRENDRE') + '   2 SYNC   3 MESSAGES', { foreground: 'cyan' });
        screen.write(19, 2, '4 JOURNAL   5 RESEAU   6 COMPTE RENDU', { foreground: 'cyan' });
        promptLine(screen, 21, current.input.value);
        footer(screen);
      },
      acceptInput: key => /^[1-6]$/.test(key),
      send: value => {
        if (value === '1') setAction(core.telemetrySummary(hm.status || {}).recording ? 'pause-flight' : 'resume-flight');
        if (value === '2') setAction('sync-flight');
        if (value === '3') setAction('load-datalink');
        if (value === '4') return 'journal';
        if (value === '5') setAction('load-network');
        if (value === '6') setAction('load-review');
      },
      repeat: (_ctx, refresh) => { if (refresh) setAction('load-status', { stay: true }); }
    }));

    terminalSession.register(new mt.MinitelPage('datalink', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        const dl = core.datalinkSnapshot(hm.datalink || {});
        titleBand(screen, 'MESSAGERIE', 'AIR INTER DATALINK');
        screen.write(6, 1, 'ETAT ' + fit(dl.syncState, 9) + ' NON LUS ' + fit(dl.unreadCount, 3) + ' ACK ' + fit(dl.pendingRequiredAcks, 3), { foreground: dl.error ? 'yellow' : 'cyan' });
        const pages = Math.max(1, Math.ceil(dl.messages.length / 4));
        hm.datalinkPage = Math.max(1, Math.min(pages, hm.datalinkPage));
        const items = dl.messages.slice((hm.datalinkPage - 1) * 4, hm.datalinkPage * 4);
        items.forEach((message, index) => {
          const row = 7 + index * 3;
          const incoming = message.direction === 'OPS_TO_COCKPIT';
          screen.write(row, 1, String(index + 1) + ' ' + fit(incoming ? '< ' + (message.sender || 'OPS') : '> COCKPIT', 15) + ' ' + fit(message.priority, 9) + ' ' + core.timeLabel(message.createdAt), { foreground: incoming ? 'yellow' : 'cyan' });
          screen.write(row + 1, 3, fit(message.body, 36));
          screen.write(row + 2, 3, fit((message.acknowledgedAt ? 'ACK' : message.status) + (message.localPending ? ' / LOCAL' : ''), 32), { foreground: message.localPending ? 'yellow' : 'green' });
        });
        if (!dl.messages.length) screen.write(8, 5, 'AUCUN MESSAGE DATALINK');
        screen.write(19, 2, '8 - NOUVEAU MESSAGE', { foreground: 'cyan' });
        promptLine(screen, 21, current.input.value);
        footer(screen, pages > 1);
      },
      acceptInput: key => /^[1-58]$/.test(key),
      send: value => {
        if (value === '8') { hm.replyTo = null; return 'datalink-compose'; }
        const dl = core.datalinkSnapshot(hm.datalink || {});
        const index = ((hm.datalinkPage - 1) * 4) + Number(value) - 1;
        if (dl.messages[index]) {
          hm.selectedMessage = dl.messages[index];
          hm.messagePage = 1;
          return 'datalink-message';
        }
      },
      next: () => {
        const pages = Math.max(1, Math.ceil(core.datalinkSnapshot(hm.datalink || {}).messages.length / 4));
        hm.datalinkPage = Math.min(pages, hm.datalinkPage + 1);
      },
      previous: () => {
        if (hm.datalinkPage > 1) { hm.datalinkPage -= 1; return null; }
        return 'flight-live';
      },
      repeat: (_ctx, refresh) => { if (refresh) setAction('load-datalink', { stay: true }); }
    }));

    terminalSession.register(new mt.MinitelPage('datalink-message', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        const m = hm.selectedMessage || {};
        const incoming = m.direction === 'OPS_TO_COCKPIT';
        screen.write(2, 1, 'MESSAGE DATALINK', { foreground: 'yellow' });
        screen.write(4, 2, 'DE : ' + fit(incoming ? (m.sender || 'AIR INTER OPS') : 'COCKPIT', 30));
        screen.write(5, 2, 'TYPE ' + fit(m.category, 8) + ' PRIORITE ' + fit(m.priority, 10));
        screen.write(6, 2, 'HEURE ' + core.timeLabel(m.createdAt) + '  ETAT ' + fit(m.status, 12));
        const body = core.normalise(m.body || '');
        const chunkSize = 180;
        const pages = Math.max(1, Math.ceil(body.length / chunkSize));
        hm.messagePage = Math.max(1, Math.min(pages, hm.messagePage));
        const chunk = body.slice((hm.messagePage - 1) * chunkSize, hm.messagePage * chunkSize);
        for (let i = 0; i < 5; i += 1) screen.write(9 + i, 2, fit(chunk.slice(i * 36, (i + 1) * 36), 36));
        screen.write(15, 2, 'PAGE MESSAGE ' + hm.messagePage + '/' + pages, { foreground: 'cyan' });
        if (incoming && !m.readAt && !m.acknowledgedAt) screen.write(16, 2, '1 MARQUER LU', { foreground: 'cyan' });
        if (incoming && m.requiresAck && !m.acknowledgedAt) screen.write(17, 2, '2 ACK', { foreground: 'cyan' });
        if (incoming && !m.localPending) screen.write(18, 2, '3 REPONDRE', { foreground: 'cyan' });
        screen.write(20, 2, 'CHOIX : ' + current.input.value, { foreground: 'yellow' });
        footer(screen, pages > 1);
      },
      acceptInput: key => /^[1-3]$/.test(key),
      send: value => {
        const m = hm.selectedMessage;
        if (!m) return;
        if (value === '1') setAction('datalink-read', { message: m });
        if (value === '2') setAction('datalink-ack', { message: m });
        if (value === '3') { hm.replyTo = m.id; return 'datalink-compose'; }
      },
      next: () => {
        const body = core.normalise(hm.selectedMessage?.body || '');
        hm.messagePage = Math.min(Math.max(1, Math.ceil(body.length / 180)), hm.messagePage + 1);
      },
      previous: () => {
        if (hm.messagePage > 1) { hm.messagePage -= 1; return null; }
        return 'datalink';
      }
    }));

    terminalSession.register(new mt.MinitelPage('datalink-compose', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        screen.write(2, 1, hm.replyTo ? 'REPONSE DATALINK' : 'NOUVEAU MESSAGE DATALINK', { foreground: 'yellow' });
        screen.write(5, 2, 'DEST : AIR INTER OPS');
        screen.write(6, 2, 'TYPE : CREW    PRIORITE : ROUTINE');
        if (hm.replyTo) screen.write(7, 2, 'REPONSE A : ' + fit(hm.replyTo, 24));
        screen.write(10, 2, 'MESSAGE (MAX 160 CAR.)');
        const body = core.normalise(current.input.value);
        for (let i = 0; i < 4; i += 1) screen.write(12 + i, 2, fit(body.slice(i * 36, (i + 1) * 36), 36), { foreground: 'cyan' });
        screen.write(17, 2, 'CARACTERES : ' + body.length + '/160');
        screen.write(19, 2, 'ENVOI : TRANSMETTRE');
        footer(screen);
      },
      acceptInput: key => key.length === 1 && terminalSession.input.value.length < 160,
      send: value => {
        if (value.trim()) setAction('datalink-send', { body: value.trim(), replyTo: hm.replyTo });
      },
      previous: () => 'datalink'
    }));

    terminalSession.register(new mt.MinitelPage('journal', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        titleBand(screen, 'JOURNAL DE BORD', 'EVENEMENTS OPERATIONNELS');
        const flight = hm.status?.flight || hm.status?.Flight || {};
        const entries = flight.journal || flight.Journal || flight.timeline || flight.Timeline || [];
        const ordered = [...entries].sort((a, b) => new Date(a.occurredAt || a.OccurredAt) - new Date(b.occurredAt || b.OccurredAt));
        const pages = Math.max(1, Math.ceil(ordered.length / 7));
        hm.journalPage = Math.max(1, Math.min(pages, hm.journalPage));
        const items = ordered.slice((hm.journalPage - 1) * 7, hm.journalPage * 7);
        items.forEach((entry, index) => {
          const at = entry.occurredAt || entry.OccurredAt;
          const name = entry.name || entry.Name || 'EVENEMENT';
          const value = entry.value ?? entry.Value;
          screen.write(5 + index * 2, 2, core.timeLabel(at) + ' ' + fit(name, 22), { foreground: 'cyan' });
          if (value != null) screen.write(6 + index * 2, 8, fit(Number(value).toFixed(0), 20));
        });
        if (!entries.length) screen.write(8, 5, 'AUCUN EVENEMENT ENREGISTRE');
        pageLine(screen, 19, hm.journalPage, pages);
        promptLine(screen, 21, current.input.value);
        footer(screen, pages > 1);
      },
      acceptInput: key => key === '1',
      send: value => { if (value === '1') setAction('load-status', { stay: true }); },
      next: () => {
        const flight = hm.status?.flight || {};
        const entries = flight.journal || flight.Journal || flight.timeline || flight.Timeline || [];
        hm.journalPage = Math.min(Math.max(1, Math.ceil(entries.length / 7)), hm.journalPage + 1);
      },
      previous: () => {
        if (hm.journalPage > 1) { hm.journalPage -= 1; return null; }
        return 'flight-live';
      }
    }));

    terminalSession.register(new mt.MinitelPage('network', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        titleBand(screen, 'RESEAU EQUIPAGES', 'AIR INTER NETWORK');
        const data = unwrapHm(hm.network) || {};
        const crews = Array.isArray(data.crews) ? data.crews : [];
        const pages = Math.max(1, Math.ceil(crews.length / 6));
        hm.networkPage = Math.max(1, Math.min(pages, hm.networkPage));
        const items = crews.slice((hm.networkPage - 1) * 6, hm.networkPage * 6);
        screen.write(6, 1, 'EQUIPAGES EN LIGNE : ' + (data.online_count ?? data.onlineCount ?? crews.length), { foreground: 'cyan' });
        items.forEach((crew, index) => {
          const row = 7 + index * 2;
          const pilot = crew.pilot || {};
          const flight = crew.flight || {};
          const aircraft = crew.aircraft || {};
          screen.write(row, 1, fit(pilot.ident || 'PILOT', 8) + ' ' + fit(flight.ident || '', 8) + ' ' + fit(crew.phase || 'STANDBY', 12));
          screen.write(row + 1, 3, fit((flight.departure || '---') + '>' + (flight.arrival || '---') + ' ' + (aircraft.registration || '') + ' ' + String(crew.simulator || '').toUpperCase(), 35), { foreground: 'cyan' });
        });
        if (!crews.length) screen.write(8, 5, 'AUCUN EQUIPAGE EN LIGNE');
        pageLine(screen, 20, hm.networkPage, pages);
        screen.write(20, 23, '1 RAFRAICHIR', { foreground: 'cyan' });
        footer(screen, pages > 1);
      },
      acceptInput: key => key === '1',
      send: value => { if (value === '1') setAction('load-network', { stay: true }); },
      next: () => {
        const data = unwrapHm(hm.network) || {};
        const crews = Array.isArray(data.crews) ? data.crews : [];
        hm.networkPage = Math.min(Math.max(1, Math.ceil(crews.length / 6)), hm.networkPage + 1);
      },
      previous: () => {
        if (hm.networkPage > 1) { hm.networkPage -= 1; return null; }
        return 'flight-live';
      }
    }));

    terminalSession.register(new mt.MinitelPage('review', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        titleBand(screen, 'COMPTE RENDU VOL', 'FLIGHT REVIEW');
        const review = core.reviewSummary(hm.review || hm.filedReview || hm.status?.review || hm.status?.Review || {});
        screen.write(6, 2, 'PHASE......... ' + fit(review.phase, 18), { foreground: review.readyToFile ? 'green' : 'yellow' });
        screen.write(7, 2, 'DISTANCE...... ' + fit(review.distance.toFixed(1) + ' NM', 18));
        screen.write(8, 2, 'AIRBORNE...... ' + fit(review.airborneMinutes + ' MIN', 18));
        screen.write(9, 2, 'BLOCK......... ' + fit(review.blockMinutes + ' MIN', 18));
        screen.write(10, 2, 'FUEL USE...... ' + fit(Math.round(review.fuelUsed) + ' LB', 18));
        screen.write(11, 2, 'LANDING....... ' + fit(review.landingRate == null ? '---' : Math.round(Number(review.landingRate)) + ' FPM', 18));
        screen.write(12, 2, 'APP 1000/500.. ' + fit(review.approach1000, 7) + '/' + fit(review.approach500, 7));
        screen.write(13, 2, 'GO AROUND..... ' + fit(review.goAroundCount, 5) + ' BOUNCE ' + fit(review.bounceCount, 4));
        screen.write(14, 2, 'MAX BANK...... ' + fit(review.maxBank == null ? '---' : Number(review.maxBank).toFixed(1) + ' DEG', 18));
        screen.write(15, 2, 'SIM RATE MAX.. ' + fit(review.maxSimulationRate == null ? 'X1' : 'X' + Number(review.maxSimulationRate).toFixed(2), 18));
        screen.write(16, 2, 'OBSERVATIONS.. ' + fit(review.observations.length, 5) + ' ANOM. ' + fit(review.issues.length, 4));
        if (review.readyToFile && !hm.filedReview) screen.write(19, 2, '1 DEPOSER PIREP  2 OBS.  3 ANOM.', { foreground: 'green' });
        else if (hm.filedReview) screen.write(19, 2, '2 OBSERVATIONS   3 ANOMALIES', { foreground: 'green' });
        else screen.write(19, 2, '2 OBSERVATIONS   3 ANOMALIES');
        screen.write(20, 2, 'CHOIX : ' + current.input.value, { foreground: 'yellow' });
        footer(screen);
      },
      acceptInput: key => /^[1-3]$/.test(key),
      send: value => {
        const review = core.reviewSummary(hm.review || hm.status?.review || {});
        if (value === '1' && review.readyToFile && !hm.filedReview) setAction('file-pirep');
        if (value === '2') { hm.reviewListMode = 'observations'; hm.reviewListPage = 1; return 'review-list'; }
        if (value === '3') { hm.reviewListMode = 'issues'; hm.reviewListPage = 1; return 'review-list'; }
      },
      previous: () => hm.status?.recoveryAvailable
        ? 'recovery'
        : ((hm.status?.flight || hm.status?.Flight) ? 'flight-live' : 'home'),
      repeat: (_ctx, refresh) => { if (refresh) setAction('load-review', { stay: true }); }
    }));

    terminalSession.register(new mt.MinitelPage('review-list', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        const review = core.reviewSummary(hm.review || hm.filedReview || hm.status?.review || {});
        const entries = hm.reviewListMode === 'issues' ? review.issues : review.observations;
        const title = hm.reviewListMode === 'issues' ? 'ANOMALIES / REGLES' : 'OBSERVATIONS FDM';
        titleBand(screen, title, 'CONTROLE FDM');
        const pages = Math.max(1, Math.ceil(entries.length / 5));
        hm.reviewListPage = Math.max(1, Math.min(pages, hm.reviewListPage));
        const items = entries.slice((hm.reviewListPage - 1) * 5, hm.reviewListPage * 5);
        items.forEach((entry, index) => {
          const row = 5 + index * 3;
          const at = entry.occurredAt || entry.OccurredAt;
          const code = entry.code || entry.Code || 'OBS';
          const message = entry.message || entry.Message || '';
          const severity = String(entry.severity || entry.Severity || 'INFO').toUpperCase();
          screen.write(row, 1, core.timeLabel(at) + ' ' + fit(code, 16) + ' ' + fit(severity, 8), { foreground: severity === 'ERROR' || severity === 'CRITICAL' ? 'red' : (severity === 'WARNING' ? 'yellow' : 'cyan') });
          screen.write(row + 1, 3, fit(message, 36));
          const value = entry.value ?? entry.Value;
          const unit = entry.unit || entry.Unit || '';
          if (value != null) screen.write(row + 2, 5, fit(String(value) + (unit ? ' ' + unit : ''), 30));
        });
        if (!entries.length) screen.write(8, 5, 'AUCUNE DONNEE A SIGNALER');
        pageLine(screen, 20, hm.reviewListPage, pages);
        screen.write(20, 20, 'RETOUR : COMPTE RENDU', { foreground: 'cyan' });
        footer(screen, pages > 1);
      },
      next: () => {
        const review = core.reviewSummary(hm.review || hm.filedReview || hm.status?.review || {});
        const entries = hm.reviewListMode === 'issues' ? review.issues : review.observations;
        hm.reviewListPage = Math.min(Math.max(1, Math.ceil(entries.length / 5)), hm.reviewListPage + 1);
      },
      previous: () => {
        if (hm.reviewListPage > 1) { hm.reviewListPage -= 1; return null; }
        return 'review';
      }
    }));

    terminalSession.register(new mt.MinitelPage('recovery', {
      onRender: (_ctx, screen, current) => {
        serviceLine(screen);
        const info = hm.status?.recovery || {};
        titleBand(screen, 'VOL INTERROMPU', 'CENTRE DE REPRISE');
        screen.write(6, 7, 'VOL INTERROMPU DETECTE', { foreground: 'red', blink: true });
        statusLine(screen, 8, 'PIREP', info.pirepId || info.PirepId || '---', 'yellow');
        statusLine(screen, 9, 'PHASE', info.phase || info.Phase || 'INTERROMPU');
        statusLine(screen, 10, 'DISTANCE', Number(info.distance || info.Distance || 0).toFixed(1) + ' NM');
        statusLine(screen, 11, 'TEMPS', (info.airborneMinutes || info.AirborneMinutes || 0) + ' MIN');
        statusLine(screen, 12, 'EN ATTENTE', String(info.pendingMessages || info.PendingMessages || hm.status?.pending || 0));
        screen.write(14, 2, 'ACTION DE RECUPERATION', { foreground: 'yellow' });
        menuLine(screen, 15, 1, 'REPRENDRE LE VOL', true);
        menuLine(screen, 16, 2, 'CONSULTER FLIGHT REVIEW');
        menuLine(screen, 17, 3, 'ABANDONNER ET ARCHIVER');
        screen.write(20, 2, 'CHOIX : ' + current.input.value, { foreground: 'yellow' });
        footer(screen);
      },
      acceptInput: key => /^[1-3]$/.test(key),
      send: value => {
        if (value === '1') setAction('resume-recovery');
        if (value === '2') setAction('load-review');
        if (value === '3') setAction('abandon-recovery');
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
      previous: () => (hm.status?.flight || hm.status?.Flight) ? 'flight-live' : (hm.operation ? 'preparation' : 'home')
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
        await refreshStatus();
        await loadMe();
        const loginTarget = hm.status?.recoveryAvailable
          ? 'recovery'
          : ((hm.status?.flight?.recording ?? hm.status?.flight?.Recording) ? 'flight-live' : 'home');
        terminalSession.homePageId = 'home';
        return show(terminalSession.go(loginTarget), true);
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

      if (pending.type === 'local-plan-import') {
        const input = document.getElementById('planFile');
        if (!input) throw new Error('IMPORT DE PLAN LOCAL INDISPONIBLE');
        input.addEventListener('change', async () => {
          const file = input.files?.[0];
          if (!file) return;
          hm.localPlan = { name: file.name, size: file.size };
          if (hm.active && terminalSession?.currentPageId === 'simbrief-local') {
            await show(terminalSession.render(), true);
          }
        }, { once: true });
        input.click();
        return show(terminalSession.go('simbrief-local'), false);
      }

      if (pending.type === 'local-plan-clear') {
        document.getElementById('clearPlanBtn')?.click();
        hm.localPlan = null;
        return show(terminalSession.go('simbrief-local'), true);
      }

      if (pending.type === 'prefile') {
        const result = await runCall(path('/pirep'), {});
        await loadOperation(hm.operation);
        setResult('PIREP PREPARE', 'PIREP #' + (result?.pirep_id || result?.pirep?.id || ''), result?.already_prefiled ? 'DEJA PREPARE' : 'PRE-DEPOT EFFECTUE');
        return show(terminalSession.go('result'), true);
      }

      if (pending.type === 'open-flight-live') {
        await refreshStatus();
        const flight = hm.status?.flight || hm.status?.Flight;
        if (!flight) {
          if (hm.status?.recoveryAvailable) return show(terminalSession.go('recovery'), true);
          throw new Error('AUCUN VOL ACARS EN COURS');
        }
        const operationId = flight.operationId || flight.OperationId;
        if (!hm.operation && operationId) {
          try { await loadOperation({ operation_id: operationId }); } catch {}
        }
        return show(terminalSession.go('flight-live'), true);
      }

      if (pending.type === 'pause-flight') {
        await runCall('/api/pause', {});
        await refreshStatus();
        setResult('ENREGISTREMENT EN PAUSE', 'HERMES CONSERVE LE VOL LOCAL', 'REPRENEZ AVANT DE CONTINUER');
        return show(terminalSession.go('result'), true);
      }

      if (pending.type === 'resume-flight') {
        await runCall('/api/resume', {});
        await refreshStatus();
        return show(terminalSession.go('flight-live'), true);
      }

      if (pending.type === 'sync-flight') {
        const result = await runCall('/api/sync', {});
        await refreshStatus();
        setResult('SYNCHRONISATION TERMINEE', String(result?.sent ?? 0) + ' ELEMENT(S) TRANSMIS', 'PROMETHEE ' + core.telemetrySummary(hm.status || {}).syncState);
        return show(terminalSession.go('result'), true);
      }

      if (pending.type === 'load-datalink') {
        const operationId = opRef() || core.telemetrySummary(hm.status || {}).operationId;
        if (!operationId) throw new Error('OPERATION DATALINK INTROUVABLE');
        hm.datalink = await runCall('/api/datalink?operation=' + encodeURIComponent(operationId));
        hm.lastDatalinkRefreshAt = Date.now();
        if (!pending.stay) hm.datalinkPage = 1;
        return show(terminalSession.go(pending.stay ? terminalSession.currentPageId : 'datalink'), true);
      }

      if (pending.type === 'datalink-read') {
        const operationId = opRef() || core.telemetrySummary(hm.status || {}).operationId;
        hm.datalink = await runCall('/api/datalink/read?operation=' + encodeURIComponent(operationId), { message_id: pending.message.id });
        hm.selectedMessage = core.datalinkSnapshot(hm.datalink).messages.find(item => item.id === pending.message.id) || pending.message;
        hm.lastDatalinkRefreshAt = Date.now();
        return show(terminalSession.go('datalink-message'), true);
      }

      if (pending.type === 'datalink-ack') {
        const operationId = opRef() || core.telemetrySummary(hm.status || {}).operationId;
        hm.datalink = await runCall('/api/datalink/ack?operation=' + encodeURIComponent(operationId), { message_id: pending.message.id });
        hm.selectedMessage = core.datalinkSnapshot(hm.datalink).messages.find(item => item.id === pending.message.id) || pending.message;
        hm.lastDatalinkRefreshAt = Date.now();
        return show(terminalSession.go('datalink-message'), true);
      }

      if (pending.type === 'datalink-send') {
        const operationId = opRef() || core.telemetrySummary(hm.status || {}).operationId;
        hm.datalink = await runCall('/api/datalink/send?operation=' + encodeURIComponent(operationId), {
          body: pending.body,
          category: 'CREW',
          priority: 'ROUTINE',
          requires_ack: false,
          reply_to: pending.replyTo || null
        });
        hm.replyTo = null;
        hm.datalinkPage = 1;
        hm.lastDatalinkRefreshAt = Date.now();
        const queued = core.datalinkSnapshot(hm.datalink).pendingOutbound;
        setResult('MESSAGE DATALINK', queued ? 'CONSERVE EN FILE LOCALE' : 'TRANSMIS A AIR INTER OPS', queued ? 'RENVOI AUTOMATIQUE PREVU' : 'LIAISON SYNCHRONISEE');
        return show(terminalSession.go('result'), true);
      }

      if (pending.type === 'load-network') {
        const operationId = opRef() || core.telemetrySummary(hm.status || {}).operationId;
        const route = '/api/network' + (operationId ? '?operation=' + encodeURIComponent(operationId) : '');
        hm.network = await runCall(route);
        hm.lastNetworkRefreshAt = Date.now();
        if (!pending.stay) hm.networkPage = 1;
        return show(terminalSession.go(pending.stay ? terminalSession.currentPageId : 'network'), true);
      }

      if (pending.type === 'load-review') {
        await refreshStatus();
        hm.review = hm.status?.review || hm.status?.Review || null;
        if (!hm.review && hm.status?.recoveryAvailable) {
          const recovery = await runCall('/api/recovery');
          hm.review = recovery?.review || recovery?.Review || null;
        }
        if (!hm.review && hm.status?.flight) hm.review = await runCall('/api/review');
        return show(terminalSession.go(pending.stay ? terminalSession.currentPageId : 'review'), true);
      }

      if (pending.type === 'file-pirep') {
        const result = await runCall('/api/file', {});
        hm.filedReview = result?.review || result?.Review || hm.review || hm.status?.review || null;
        hm.review = hm.filedReview;
        await refreshStatus();
        return show(terminalSession.go('review'), true);
      }

      if (pending.type === 'abandon-recovery') {
        const recovery = hm.status?.recovery || {};
        const pirep = recovery.pirepId || recovery.PirepId || 'VOL INTERROMPU';
        await runCall('/api/recovery/abandon', {});
        await refreshStatus();
        setResult('VOL INTERROMPU ARCHIVE', String(pirep), 'ETAT LOCAL NETTOYE');
        return show(terminalSession.go('result'), true);
      }

      if (pending.type === 'resume-recovery') {
        const result = await runCall('/api/recovery/resume', {});
        await refreshStatus();
        const flight = result?.flight || hm.status?.flight || {};
        const operationId = flight.operationId || flight.OperationId || hm.status?.recovery?.operationId || hm.status?.recovery?.OperationId;
        if (operationId) {
          try { await loadOperation({ operation_id: operationId }); } catch {}
        }
        return show(terminalSession.go('flight-live'), true);
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
        hm.review = hm.status?.review || hm.status?.Review || null;
        return show(terminalSession.go('flight-live'), true);
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
    if (!hm.active || !terminalSession) return;
    await refreshStatus();
    if (!hm.active) return;

    const page = terminalSession.currentPageId;
    const now = Date.now();

    if (page === 'datalink' && now - hm.lastDatalinkRefreshAt >= 5000) {
      const operationId = opRef() || core.telemetrySummary(hm.status || {}).operationId;
      if (operationId) {
        try {
          hm.datalink = await runCall('/api/datalink?operation=' + encodeURIComponent(operationId));
          hm.lastDatalinkRefreshAt = now;
        } catch {}
      }
    }

    if (page === 'network' && now - hm.lastNetworkRefreshAt >= 15000) {
      const operationId = opRef() || core.telemetrySummary(hm.status || {}).operationId;
      try {
        hm.network = await runCall('/api/network' + (operationId ? '?operation=' + encodeURIComponent(operationId) : ''));
        hm.lastNetworkRefreshAt = now;
      } catch {}
    }

    if (page === 'review') hm.review = hm.status?.review || hm.status?.Review || hm.review;

    if (['home', 'preparation', 'dispatch', 'simulator', 'flight-live', 'journal', 'datalink', 'network', 'review', 'review-list', 'recovery'].includes(page)) {
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

    const terminalPreferences = readMinitelPreferences();
    shell = new mt.MinitelShell({
      host,
      service: '3615 HERMES',
      product: 'HERMES',
      identity: '',
      speed: terminalPreferences.speed,
      displayMode: terminalPreferences.displayMode,
      bootFrameDelay: terminalPreferences.speed === 'authentic' ? 260 : 120,
      onPreferencesChange: persistMinitelPreferences,
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
    renderer = new mt.MinitelDomRenderer(shell.terminalNode, {
      speed: terminalPreferences.speed,
      displayMode: terminalPreferences.displayMode,
      maxProgressiveDurationMs: terminalPreferences.speed === 'authentic' ? 0 : 2600,
      transmissionTickMs: 16
    });
    await refreshStatus();
    hm.authenticated = Boolean(hm.status?.connected);
    if (hm.authenticated) {
      await loadMe();
      const knownOperation = hm.status?.flight?.operationId || hm.status?.flight?.OperationId
        || hm.status?.recovery?.operationId || hm.status?.recovery?.OperationId;
      if (knownOperation) {
        try { await loadOperation({ operation_id: knownOperation }); } catch {}
      }
    }

    const initialPage = !hm.authenticated
      ? 'login-user'
      : (hm.status?.recoveryAvailable
          ? 'recovery'
          : ((hm.status?.flight?.recording ?? hm.status?.flight?.Recording) ? 'flight-live' : 'home'));

    terminalSession = new mt.MinitelSession({
      homePageId: hm.authenticated ? 'home' : 'login-user',
      speed: terminalPreferences.speed,
      inputLength: 160,
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
    if (hm.authenticated && initialPage !== 'home') {
      await show(terminalSession.go(initialPage, { recordHistory: false }), true);
    }
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

  const startForCurrentEra = () => {
    if (document.body.dataset.era === 'minitel') start();
  };
  if (document.readyState === 'loading') {
    window.addEventListener('DOMContentLoaded', startForCurrentEra, { once: true });
  } else {
    startForCurrentEra();
  }

  window.addEventListener('hermes:auth-changed', event => {
    hm.authenticated = Boolean(event.detail?.authenticated);
    if (document.body.dataset.era === 'minitel' && !hm.active) start();
  });
})();
