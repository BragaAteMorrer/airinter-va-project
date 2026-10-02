/* The server renders the groups; this adds workspace filtering and accordion behaviour. */
(() => {
  document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('.sidebar nav');
    if (!nav) return;
    const groups = [...nav.querySelectorAll(':scope > .nav-group')];
    const workspaceButtons = [...document.querySelectorAll('[data-workspace-choice]')];
    const staffAvailable = workspaceButtons.some(button => button.dataset.workspaceChoice === 'staff');

    const activeScopedGroup = groups.find(group => group.classList.contains('selected')
      && ['pilot','staff'].includes(group.dataset.workspaceGroup));
    let storedWorkspace = null;
    try { storedWorkspace = localStorage.getItem('promethee-workspace'); } catch {}
    let workspace = activeScopedGroup?.dataset.workspaceGroup
      || (['pilot','staff'].includes(storedWorkspace) ? storedWorkspace : nav.dataset.defaultWorkspace || 'pilot');
    if (workspace === 'staff' && !staffAvailable) workspace = 'pilot';

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
    document.addEventListener('click', (event) => {
      if (!nav.contains(event.target) && !event.target.closest?.('.workspace-switch')) {
        groups.forEach((group) => group.open = false);
      }
    });
  });
})();
