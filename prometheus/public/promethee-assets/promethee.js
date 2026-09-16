(() => {
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

    const lines = [
      '3615 AIR INTER',
      '----------------------------------------',
      'CENTRE D EXPLOITATION',
      '',
      'LIAISON RESEAU ............... OK',
      'TERMINAL VIDEOTEX ............ PRET',
      '',
      'CHARGEMENT DU SERVICE',
      'PATIENTEZ _'
    ];
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
    if (paris) paris.textContent = new Intl.DateTimeFormat('fr-FR',{timeZone:'Europe/Paris',hour:'2-digit',minute:'2-digit'}).format(new Date());
    document.querySelectorAll('[data-world-clock]').forEach((node) => {
      const zone = node.dataset.worldClock;
      try {
        const parts = new Intl.DateTimeFormat('fr-FR',{timeZone:zone,hour:'2-digit',minute:'2-digit',timeZoneName:'shortOffset'}).formatToParts(new Date());
        node.querySelector('strong').textContent = parts.filter(p => p.type === 'hour' || p.type === 'minute').map(p => p.value).join(':');
        const offset = parts.find(p => p.type === 'timeZoneName')?.value; if (offset) node.querySelector('small').textContent = offset;
      } catch { node.querySelector('strong').textContent = '—'; }
    });
  };
  tick(); setInterval(tick,1000);
})();
