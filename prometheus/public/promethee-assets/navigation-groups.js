/* The server renders the groups; this only gives them accordion behaviour. */
(() => {
  document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('.sidebar nav');
    if (!nav) return;
    const groups = [...nav.querySelectorAll(':scope > .nav-group')];
    groups.forEach((group) => group.addEventListener('toggle', () => {
      if (group.open) groups.forEach((other) => { if (other !== group) other.open = false; });
    }));
    document.addEventListener('click', (event) => {
      if (!nav.contains(event.target)) groups.forEach((group) => group.open = false);
    });
  });
})();
