(() => {
  const i18n = window.prometheeI18n || {};
  const dateLocale = i18n.dateLocale || i18n.locale || 'fr-FR';
  const allowed = ['modern','2000','minitel'];
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

  const setEra = (era) => {
    if (!allowed.includes(era)) era = 'modern';
    document.documentElement.dataset.era = era;
    try { localStorage.setItem('promethee-era',era); } catch {}
    const control = document.getElementById('era'); if (control) control.value = era;
    document.querySelectorAll('[data-era-choice]').forEach(button => button.setAttribute('aria-pressed',String(button.dataset.eraChoice === era)));
    bootMinitel();
    revealMinitelLines();
  };
  let initial = 'modern'; try { initial = localStorage.getItem('promethee-era') || 'modern'; } catch {}
  setEra(initial);
  document.getElementById('era')?.addEventListener('change',e => setEra(e.target.value));
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
    board.querySelectorAll('.board-cell').forEach((field) => renderFlapField(field, true));
    // External live updates can set data-flap-value; only affected palettes flip.
    new MutationObserver((changes) => changes.forEach((change) => {
      if (change.type === 'attributes' && change.attributeName === 'data-flap-value') renderFlapField(change.target);
    })).observe(board, {subtree: true, attributes: true, attributeFilter: ['data-flap-value']});
  });
  bootSplitFlapBoards();
})();
