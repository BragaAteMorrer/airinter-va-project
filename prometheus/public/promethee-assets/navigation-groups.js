/* Groups the existing Laravel-rendered links without changing routes or permissions. */
(() => {
  const groups = [
    { label: 'Exploitation', paths: ['operations', 'flights', 'calendar', 'acars'] },
    { label: 'Carri\u00e8re pilote', paths: ['missions', 'assignments', 'passport', 'transfers', 'jumpseat'] },
    { label: 'Communaut\u00e9', paths: ['pilots', 'pireps', 'safety'] },
    { label: 'Services', paths: ['shop'] },
  ];

  const pathFor = (link) => {
    try { return new URL(link.href, window.location.origin).pathname.toLowerCase(); } catch { return ''; }
  };
  const hasPath = (link, key) => pathFor(link).split('/').includes(key);

  const initialise = () => {
    const nav = document.querySelector('.sidebar nav');
    if (!nav || nav.dataset.grouped === 'true') return;
    const links = [...nav.querySelectorAll(':scope > a.nav-link')];
    // Public navigation is intentionally kept flat; only the long pilot navigation is grouped.
    if (links.length < 6) return;

    const dashboard = links.find((link) => hasPath(link, 'dashboard'));
    const administration = links.find((link) => hasPath(link, 'admin'));
    const fragment = document.createDocumentFragment();
    if (dashboard) fragment.append(dashboard);

    groups.forEach((group) => {
      const members = links.filter((link) => group.paths.some((path) => hasPath(link, path)));
      if (!members.length) return;
      const details = document.createElement('details');
      details.className = 'nav-group';
      if (members.some((link) => link.classList.contains('selected'))) details.classList.add('selected');
      const summary = document.createElement('summary');
      summary.append(document.createTextNode(group.label));
      const arrow = document.createElement('b'); arrow.setAttribute('aria-hidden', 'true'); arrow.textContent = 'v';
      summary.append(arrow);
      const menu = document.createElement('div'); menu.className = 'nav-menu';
      members.forEach((link) => {
        link.querySelector('span')?.remove(); link.querySelector('b')?.remove();
        menu.append(link);
      });
      details.append(summary, menu);
      details.addEventListener('toggle', () => {
        if (details.open) nav.querySelectorAll('.nav-group[open]').forEach((other) => { if (other !== details) other.open = false; });
      });
      fragment.append(details);
    });
    if (administration) fragment.append(administration);
    nav.replaceChildren(fragment);
    nav.classList.add('is-grouped'); nav.dataset.grouped = 'true';
  };

  document.addEventListener('DOMContentLoaded', initialise);
})();
