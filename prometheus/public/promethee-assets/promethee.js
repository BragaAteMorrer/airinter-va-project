(() => {
  const i18n = window.prometheeI18n || {};
  const dateLocale = i18n.dateLocale || i18n.locale || 'fr-FR';
  const allowed = ['modern','2000','minitel'];
  const appearances = ['light','dark'];
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let revealTimeout;
  let bootTimeout;

  const clearMinitelBoot = () => {
    window.clearTimeout(bootTimeout);
    document.getElementById('minitel-screen')?.remove();
  };

  const bootMinitel = () => {
    clearMinitelBoot();
    if (reduceMotion || document.documentElement.dataset.era !== 'minitel') return;

    const screen = document.createElement('pre');
    screen.id = 'minitel-screen';
    screen.setAttribute('aria-hidden', 'true');
    document.body.append(screen);

    const lines = i18n.minitelBoot || [];
    let index = 0;
    const writeLine = () => {
      if (document.documentElement.dataset.era !== 'minitel' || !screen.isConnected) return;
      screen.textContent += `${lines[index]}\n`;
      index += 1;
      if (index < lines.length) {
        bootTimeout = window.setTimeout(writeLine, 80);
        return;
      }
      bootTimeout = window.setTimeout(() => {
        screen.classList.add('is-complete');
        bootTimeout = window.setTimeout(() => screen.remove(), 100);
      }, 220);
    };
    writeLine();
  };

  const revealMinitelLines = () => {
    document.body.classList.remove('minitel-enter');
    if (reduceMotion || document.documentElement.dataset.era !== 'minitel') return;

    const lines = document.querySelectorAll([
      '.page-heading', '.hero', '.stats-grid > *', '.control-strip > *',
      '.panel', '.signal', '.dispatch-flight', '.route-board article'
    ].join(','));

    lines.forEach((line, index) => line.style.setProperty('--minitel-line', String(Math.min(index, 14))));
    // One frame without the class resets the CSS animation before the rapid redraw.
    requestAnimationFrame(() => requestAnimationFrame(() => document.body.classList.add('minitel-enter')));
    window.clearTimeout(revealTimeout);
    revealTimeout = window.setTimeout(() => document.body.classList.remove('minitel-enter'), 760);
  };

  const setEra = (era, persist = true) => {
    if (!allowed.includes(era)) era = 'modern';
    document.documentElement.dataset.era = era;
    if (persist) try { localStorage.setItem('promethee-era',era); } catch {}
    const control = document.getElementById('era'); if (control) control.value = era;
    document.querySelectorAll('[data-era-choice]').forEach(button => button.setAttribute('aria-pressed',String(button.dataset.eraChoice === era)));
    bootMinitel();
    revealMinitelLines();
  };
  const setAppearance = (appearance, persist = true) => {
    if (!appearances.includes(appearance)) appearance = 'light';
    if (persist) {
      document.documentElement.dataset.appearanceTransition = 'true';
      window.setTimeout(() => delete document.documentElement.dataset.appearanceTransition, 230);
    }
    document.documentElement.dataset.appearance = appearance;
    if (persist) try { localStorage.setItem('promethee-appearance', appearance); } catch {}
    const control = document.getElementById('appearance'); if (control) control.value = appearance;
  };
  let initial = 'modern';
  let minitelSessionFallback = false;
  try {
    initial = localStorage.getItem('promethee-era') || 'modern';
    minitelSessionFallback = document.documentElement.dataset.minitelRuntime === 'm2'
      && initial === 'minitel'
      && sessionStorage.getItem('promethee-minitel-session-disabled') === '1';
    if (minitelSessionFallback) initial = 'modern';
  } catch {}
  setEra(initial, !minitelSessionFallback);
  let initialAppearance; try { initialAppearance = localStorage.getItem('promethee-appearance'); } catch {}
  setAppearance(appearances.includes(initialAppearance) ? initialAppearance : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'), false);
  document.getElementById('era')?.addEventListener('change',e => {
    const nextEra = e.target.value;
    setEra(nextEra);
    if (document.documentElement.dataset.minitelRuntime === 'm2' && nextEra === 'minitel') window.location.reload();
  });
  document.getElementById('appearance')?.addEventListener('change',e => setAppearance(e.target.value));
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => { try { if (!localStorage.getItem('promethee-appearance')) setAppearance(event.matches ? 'dark' : 'light', false); } catch {} });
  document.querySelectorAll('[data-era-choice]').forEach(button => button.addEventListener('click',() => setEra(button.dataset.eraChoice)));
  document.querySelectorAll('[data-print]').forEach(button => button.addEventListener('click',() => window.print()));
  const tick = () => {
    const clock = document.getElementById('utc-clock');
    if (clock) clock.textContent = new Date().toISOString().slice(11,19)+' UTC';
    const paris = document.getElementById('paris-clock');
    if (paris) paris.textContent = new Intl.DateTimeFormat(dateLocale,{timeZone:'Europe/Paris',hour:'2-digit',minute:'2-digit'}).format(new Date());
    document.querySelectorAll('[data-world-clock]').forEach((node) => {
      const zone = node.dataset.worldClock;
      try {
        const parts = new Intl.DateTimeFormat(dateLocale,{timeZone:zone,hour:'2-digit',minute:'2-digit',timeZoneName:'shortOffset'}).formatToParts(new Date());
        node.querySelector('strong').textContent = parts.filter(p => p.type === 'hour' || p.type === 'minute').map(p => p.value).join(':');
        const offset = parts.find(p => p.type === 'timeZoneName')?.value; if (offset) node.querySelector('small').textContent = offset;
      } catch { node.querySelector('strong').textContent = '—'; }
    });
  };
  tick(); setInterval(tick,1000);

  // A small, framework-free split-flap renderer. Fields retain their semantic
  // Blade elements, while each visible glyph becomes an independent palette.
  const flapAlphabet = " ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-.:/'%";
  const flapReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const flapNormalise = (value) => String(value || ' ')
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase()
    .split('').map((char) => flapAlphabet.includes(char) ? char : ' ').join('');
  const canAnimateFlap = (value) => {
    const normalised = String(value || ' ').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toUpperCase();
    return [...normalised].every((char) => flapAlphabet.includes(char));
  };
  const flapFit = (value, width) => flapNormalise(value).slice(0, width).padEnd(width, ' ');
  const flapMarkup = (char, className = '') => `<span class="flap-half ${className}"><span class="flap-face">${char === ' ' ? '&nbsp;' : char}</span></span>`;

  const setFlapCharacter = (palette, next, animate, delay = 0) => {
    const current = palette.dataset.value || ' ';
    if (current === next) return;
    const finish = () => {
      palette.dataset.value = next;
      palette.innerHTML = flapMarkup(next, 'flap-top') + flapMarkup(next, 'flap-bottom');
      palette.classList.remove('is-flipping');
    };
    if (!animate || flapReduced) return finish();
    const from = flapAlphabet.indexOf(current);
    const to = flapAlphabet.indexOf(next);
    const steps = (to - from + flapAlphabet.length) % flapAlphabet.length || flapAlphabet.length;
    let step = 0;
    const flip = () => {
      const visible = flapAlphabet[(from + step) % flapAlphabet.length];
      const following = flapAlphabet[(from + step + 1) % flapAlphabet.length];
      palette.innerHTML = flapMarkup(visible, 'flap-top') + flapMarkup(visible, 'flap-bottom') + flapMarkup(visible, 'flap-flip flap-flip-top') + flapMarkup(following, 'flap-bottom flap-flip flap-flip-bottom');
      palette.classList.remove('is-flipping');
      void palette.offsetWidth;
      palette.classList.add('is-flipping');
      step += 1;
      if (step < steps) window.setTimeout(flip, 116);
      else window.setTimeout(finish, 116);
    };
    window.setTimeout(flip, delay);
  };

  const renderFlapField = (field, initial = false) => {
    const source = field.dataset.flapValue || field.textContent;
    // The physical character wheel is Latin-only. Do not transliterate
    // unsupported scripts: render the original Unicode text as a readable
    // static field instead (for example Japanese destination labels).
    if (!canAnimateFlap(source)) {
      field.classList.add('split-flap-plain');
      field.textContent = source;
      field.setAttribute('aria-label', String(source).trim());
      return;
    }
    field.classList.remove('split-flap-plain');
    const width = Number(field.dataset.flapWidth) || (field.classList.contains('destination') ? 18 : field.classList.contains('flight-ident') ? 8 : field.classList.contains('duration') ? 5 : field.classList.contains('load') ? 4 : 5);
    const target = flapFit(source, width);
    const palettes = [...field.querySelectorAll('.split-flap-char')];
    field.setAttribute('aria-label', source.trim());
    if (!palettes.length) {
      field.textContent = '';
      [...target].forEach((char, index) => {
        const palette = document.createElement('span');
        palette.className = 'split-flap-char';
        // Start a handful of notches before the target: a short believable boot,
        // rather than a long alphabetic sweep on every cell.
        const targetIndex = flapAlphabet.indexOf(char);
        const lead = initial && !flapReduced ? 2 + ((index * 5 + width) % 7) : 0;
        const start = flapAlphabet[(targetIndex - lead + flapAlphabet.length) % flapAlphabet.length];
        palette.dataset.value = start;
        palette.innerHTML = flapMarkup(start, 'flap-top') + flapMarkup(start, 'flap-bottom');
        field.append(palette);
        setFlapCharacter(palette, char, initial, (index * 17) + ((Number(field.closest('.dispatch-flight')?.style.getPropertyValue('--board-row')) || 0) * 31));
      });
      return;
    }
    [...target].forEach((char, index) => setFlapCharacter(palettes[index], char, true, index * 13));
  };

  const bootSplitFlapBoards = () => document.querySelectorAll('[data-split-flap-board]').forEach((board) => {
    board.querySelectorAll('.board-cell:not(.split-flap-logo), .airline-logo-fallback').forEach((field) => renderFlapField(field, true));
    // External live updates can set data-flap-value; only affected palettes flip.
    new MutationObserver((changes) => changes.forEach((change) => {
      if (change.type === 'attributes' && change.attributeName === 'data-flap-value') renderFlapField(change.target);
    })).observe(board, {subtree: true, attributes: true, attributeFilter: ['data-flap-value']});

    if (!board.dataset.boardUrl) return;
    const makeCell = (tag, className, value, width) => {
      const cell = document.createElement(tag);
      cell.className = `board-cell ${className || ''}`.trim();
      cell.dataset.flapWidth = width;
      cell.textContent = value;
      return cell;
    };
    const setLogo = (cell, flight) => {
      cell.replaceChildren();
      cell.setAttribute('aria-label', flight.airline_code);
      if (flight.logo_url) {
        const logo = document.createElement('img');
        logo.src = flight.logo_url; logo.alt = flight.airline_code;
        cell.append(logo);
      } else {
        const fallback = document.createElement('span');
        fallback.className = 'airline-logo-fallback'; fallback.dataset.flapWidth = 4; fallback.textContent = flight.airline_code;
        cell.append(fallback);
        renderFlapField(fallback, true);
      }
    };
    const makeRow = (flight, index) => {
      const row = document.createElement('article');
      row.className = 'split-flap-grid dispatch-flight'; row.dataset.boardFlight = flight.id;
      row.style.setProperty('--board-row', index);
      const logo = document.createElement('span');
      logo.className = 'board-cell split-flap-logo airline-logo-cell'; setLogo(logo, flight);
      const ident = makeCell('a', 'flight-cell flight-ident', flight.flight, 7); ident.href = flight.url;
      row.append(logo, ident, makeCell('span', 'departure-cell', flight.departure, 18), makeCell('time', 'departure-time-cell', flight.departure_time, 5), makeCell('span', 'destination-cell destination', flight.destination, 18), makeCell('time', 'arrival-time-cell', flight.arrival_time, 5), makeCell('span', 'status-cell status', flight.status_label, 11));
      row.querySelectorAll('.board-cell:not(.split-flap-logo)').forEach((field) => renderFlapField(field, true));
      return row;
    };
    const updateRows = (flights) => {
      const current = new Map([...board.querySelectorAll('[data-board-flight]')].map((row) => [row.dataset.boardFlight, row]));
      const empty = board.querySelector('[data-board-empty]'); if (empty) empty.remove();
      flights.forEach((flight, index) => {
        let row = current.get(flight.id);
        if (!row) { row = makeRow(flight, index); board.append(row); return; }
        row.style.setProperty('--board-row', index); current.delete(flight.id);
        const cells = row.querySelectorAll('.board-cell');
        const values = [flight.flight, flight.departure, flight.departure_time, flight.destination, flight.arrival_time, flight.status_label];
        setLogo(cells[0], flight); cells[1].href = flight.url;
        values.forEach((value, cellIndex) => cells[cellIndex + 1].dataset.flapValue = value);
        board.append(row);
      });
      current.forEach((row) => row.remove());
      if (!flights.length) {
        const message = document.createElement('p'); message.className = 'empty'; message.dataset.boardEmpty = '';
        message.textContent = board.dataset.emptyText || '—'; board.append(message);
      }
    };
    let refreshTimer = null;
    let refreshing = false;
    let lastRevision = null;
    const scheduleRefresh = (seconds) => {
      window.clearTimeout(refreshTimer);
      const delay = Math.max(10, Number(seconds) || Number(board.dataset.boardRefresh) || 30);
      refreshTimer = window.setTimeout(refresh, delay * 1000);
    };
    const flashBoardUpdate = () => {
      board.classList.remove('board-updated');
      void board.offsetWidth;
      board.classList.add('board-updated');
      window.setTimeout(() => board.classList.remove('board-updated'), 1200);
    };
    const refresh = async () => {
      if (refreshing) return;
      if (document.hidden) { scheduleRefresh(60); return; }
      refreshing = true;
      let next = Number(board.dataset.boardRefresh) || 30;
      try {
        const response = await fetch(board.dataset.boardUrl, {
          headers: {Accept: 'application/json'},
          cache: 'no-store'
        });
        if (response.ok) {
          const payload = await response.json();
          next = Number(payload.refresh_after_seconds) || next;
          const revision = payload.revision || JSON.stringify(payload.flights || []);
          if (revision !== lastRevision) {
            // No continuous mechanical redraw: palettes move only when data,
            // time-derived status or ordering really changed.
            updateRows(payload.flights || []);
            if (lastRevision !== null) flashBoardUpdate();
            lastRevision = revision;
          }
        }
      } catch { /* Leave the last valid mechanical display in place. */ }
      finally {
        refreshing = false;
        scheduleRefresh(next);
      }
    };
    const onVisibility = () => {
      if (!document.hidden) refresh();
    };
    document.addEventListener('visibilitychange', onVisibility);
    window.addEventListener('focus', refresh);
    // Server-rendered rows are already current. First conditional check is
    // deferred instead of starting an immediate polling loop.
    scheduleRefresh(Number(board.dataset.boardRefresh) || 30);
  });
  bootSplitFlapBoards();
})();
