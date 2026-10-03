/* The server renders the groups; this adds workspace filtering and accordion behaviour. */
(() => {
  document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('.sidebar nav');
    if (!nav) return;
    const groups = [...nav.querySelectorAll(':scope > .nav-group')];
    const workspaceButtons = [...document.querySelectorAll('[data-workspace-choice]')];
    const staffAvailable = workspaceButtons.some(button => button.dataset.workspaceChoice === 'staff');
    const shell = document.querySelector('[data-promethee-shell]');
    const shellMenuToggle = shell?.querySelector('.shell-menu-toggle');
    const shellMedia = window.matchMedia('(max-width: 900px)');

    const setShellMenu = (open) => {
      if (!shell || !shellMenuToggle) return;
      const next = Boolean(open && shellMedia.matches);
      shell.classList.toggle('is-menu-open', next);
      shellMenuToggle.setAttribute('aria-expanded', String(next));
      if (next) {
        const activeGroup = groups.find(group => group.classList.contains('selected') && !group.hidden);
        if (activeGroup) activeGroup.open = true;
      }
    };

    shellMenuToggle?.addEventListener('click', () => setShellMenu(!shell?.classList.contains('is-menu-open')));
    shellMedia.addEventListener('change', event => { if (!event.matches) setShellMenu(false); });

    const activeScopedGroup = groups.find(group => group.classList.contains('selected')
      && ['pilot','staff'].includes(group.dataset.workspaceGroup));
    let storedWorkspace = null;
    try { storedWorkspace = localStorage.getItem('promethee-workspace'); } catch {}
    let workspace = activeScopedGroup?.dataset.workspaceGroup
      || (['pilot','staff'].includes(storedWorkspace) ? storedWorkspace : nav.dataset.defaultWorkspace || 'pilot');
    if (workspace === 'staff' && !staffAvailable) workspace = 'pilot';

    const focusableGroups = () => groups.filter(group => !group.hidden).map(group => group.querySelector('summary')).filter(Boolean);
    const focusRelativeSummary = (current, delta) => {
      const summaries = focusableGroups();
      const index = summaries.indexOf(current);
      if (index < 0 || !summaries.length) return;
      summaries[(index + delta + summaries.length) % summaries.length].focus();
    };
    groups.forEach(group => {
      const summary = group.querySelector('summary');
      const menu = group.querySelector('.nav-menu');
      summary?.addEventListener('keydown', event => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
          event.preventDefault(); focusRelativeSummary(summary, 1);
        } else if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
          event.preventDefault(); focusRelativeSummary(summary, -1);
        } else if (event.key === 'Home') {
          event.preventDefault(); focusableGroups()[0]?.focus();
        } else if (event.key === 'End') {
          event.preventDefault(); focusableGroups().at(-1)?.focus();
        }
      });
      menu?.addEventListener('keydown', event => {
        const links = [...menu.querySelectorAll('a:not([hidden])')];
        const index = links.indexOf(document.activeElement);
        if (event.key === 'Escape') {
          event.preventDefault(); group.open = false; summary?.focus(); return;
        }
        if (index < 0 || !['ArrowDown','ArrowUp','Home','End'].includes(event.key)) return;
        event.preventDefault();
        const next = event.key === 'Home' ? 0
          : event.key === 'End' ? links.length - 1
          : (index + (event.key === 'ArrowDown' ? 1 : -1) + links.length) % links.length;
        links[next]?.focus();
      });
    });

    const setWorkspace = (next, persist = true) => {
      if (!['pilot','staff'].includes(next) || (next === 'staff' && !staffAvailable)) next = 'pilot';
      workspace = next;
      document.documentElement.dataset.workspace = workspace;
      groups.forEach(group => {
        const scope = group.dataset.workspaceGroup || 'shared';
        const hidden = (scope === 'pilot' && workspace === 'staff') || (scope === 'staff' && workspace === 'pilot');
        group.hidden = hidden;
        if (hidden) group.open = false;
      });
      workspaceButtons.forEach(button => {
        const selected = button.dataset.workspaceChoice === workspace;
        button.setAttribute('aria-pressed', String(selected));
        button.classList.toggle('selected', selected);
      });
      if (persist) {
        try { localStorage.setItem('promethee-workspace', workspace); } catch {}
      }
    };

    workspaceButtons.forEach(button => button.addEventListener('click', () => setWorkspace(button.dataset.workspaceChoice)));
    setWorkspace(workspace, false);

    groups.forEach((group) => group.addEventListener('toggle', () => {
      if (group.open) groups.forEach((other) => { if (other !== group && !other.hidden) other.open = false; });
    }));
    nav.addEventListener('click', event => {
      if (shellMedia.matches && event.target.closest?.('a')) setShellMenu(false);
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') {
        groups.forEach(group => { group.open = false; });
        setShellMenu(false);
      }
    });
    document.addEventListener('click', (event) => {
      if (!nav.contains(event.target) && !event.target.closest?.('.workspace-switch') && !event.target.closest?.('.shell-menu-toggle')) {
        groups.forEach((group) => group.open = false);
      }
    });
  });
})();
