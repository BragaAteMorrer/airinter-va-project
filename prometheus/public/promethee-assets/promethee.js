(() => {
  const allowed = ['modern','2000','minitel'];
  const setEra = (era) => {
    if (!allowed.includes(era)) era = 'modern';
    document.documentElement.dataset.era = era;
    try { localStorage.setItem('promethee-era',era); } catch {}
    const control = document.getElementById('era'); if (control) control.value = era;
    document.querySelectorAll('[data-era-choice]').forEach(button => button.setAttribute('aria-pressed',String(button.dataset.eraChoice === era)));
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
  };
  tick(); setInterval(tick,1000);
})();
