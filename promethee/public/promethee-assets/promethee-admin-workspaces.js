(() => {
  'use strict';

  const normalize = value => (value || '')
    .toString()
    .trim()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');

  function initWorkspace(root, index) {
    const buttons = Array.from(root.querySelectorAll('[data-master-target]'));
    const panels = Array.from(root.querySelectorAll('[data-detail-panel]'));
    if (!buttons.length || !panels.length) return;

    const key = root.dataset.workspaceKey || root.id || ('workspace-' + index);
    const storageKey = 'promethee-master-detail:' + key;
    const filter = root.querySelector('[data-master-filter]');
    const empty = root.querySelector('[data-master-empty]');
    const backButtons = Array.from(root.querySelectorAll('[data-master-back]'));

    const panelById = new Map(panels.map(panel => [panel.dataset.detailPanel, panel]));
    const validTargets = buttons.map(button => button.dataset.masterTarget).filter(target => panelById.has(target));

    const storedTarget = (() => {
      try { return sessionStorage.getItem(storageKey); } catch (_) { return null; }
    })();

    const hashTarget = window.location.hash ? window.location.hash.slice(1) : null;
    const requestedDefault =
      (hashTarget && validTargets.includes(hashTarget) && hashTarget) ||
      (storedTarget && validTargets.includes(storedTarget) && storedTarget) ||
      root.dataset.masterDefault ||
      validTargets[0];

    function remember(target) {
      try { sessionStorage.setItem(storageKey, target); } catch (_) {}
    }

    function select(target, options = {}) {
      if (!panelById.has(target)) return;
      const { focusPanel = false, updateHash = false } = options;

      buttons.forEach(button => {
        const selected = button.dataset.masterTarget === target;
        button.classList.toggle('is-selected', selected);
        button.setAttribute('aria-selected', selected ? 'true' : 'false');
        if (button.hasAttribute('aria-pressed')) {
          button.setAttribute('aria-pressed', selected ? 'true' : 'false');
        }
        button.tabIndex = selected ? 0 : -1;
      });

      panels.forEach(panel => {
        panel.hidden = panel.dataset.detailPanel !== target;
      });

      root.classList.add('is-detail-open');
      root.dataset.activeDetail = target;
      remember(target);

      if (updateHash && history.replaceState) {
        history.replaceState(null, '', '#' + target);
      }

      if (focusPanel) {
        const panel = panelById.get(target);
        const focusTarget = panel.querySelector('[data-detail-focus], h1, h2, h3, input, select, textarea, button, a[href]');
        if (focusTarget) {
          if (!focusTarget.matches('input,select,textarea,button,a[href]')) focusTarget.tabIndex = -1;
          focusTarget.focus({ preventScroll: true });
        }
      }

      root.dispatchEvent(new CustomEvent('promethee:master-detail-change', {
        bubbles: true,
        detail: { target }
      }));
    }

    function closeMobileDetail() {
      root.classList.remove('is-detail-open');
      const active = buttons.find(button => button.dataset.masterTarget === root.dataset.activeDetail);
      active?.focus({ preventScroll: true });
    }

    buttons.forEach(button => {
      if (!panelById.has(button.dataset.masterTarget)) {
        button.disabled = true;
        return;
      }
      button.setAttribute('role', 'tab');
      button.addEventListener('click', () => select(button.dataset.masterTarget, { focusPanel: false }));
      button.addEventListener('keydown', event => {
        if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const visible = buttons.filter(item => !item.hidden && !item.disabled);
        if (!visible.length) return;
        const current = visible.indexOf(button);
        let next = current;
        if (event.key === 'ArrowDown') next = (current + 1) % visible.length;
        if (event.key === 'ArrowUp') next = (current - 1 + visible.length) % visible.length;
        if (event.key === 'Home') next = 0;
        if (event.key === 'End') next = visible.length - 1;
        visible[next].focus();
      });
    });

    backButtons.forEach(button => button.addEventListener('click', closeMobileDetail));

    if (filter) {
      const applyFilter = () => {
        const query = normalize(filter.value);
        let visibleCount = 0;

        buttons.forEach(button => {
          const haystack = normalize(button.dataset.masterSearch || button.textContent);
          const visible = !query || haystack.includes(query);
          button.hidden = !visible;
          if (visible) visibleCount += 1;
        });

        if (empty) empty.hidden = visibleCount !== 0;

        const current = buttons.find(button => button.dataset.masterTarget === root.dataset.activeDetail);
        if (current?.hidden) {
          const firstVisible = buttons.find(button => !button.hidden && !button.disabled);
          if (firstVisible) select(firstVisible.dataset.masterTarget);
        }
      };

      filter.addEventListener('input', applyFilter);
      applyFilter();
    }

    if (requestedDefault && panelById.has(requestedDefault)) {
      select(requestedDefault);
    } else if (validTargets.length) {
      select(validTargets[0]);
    }

    window.addEventListener('hashchange', () => {
      const target = window.location.hash.slice(1);
      if (panelById.has(target)) select(target);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-admin-master-detail]').forEach(initWorkspace);
  });
})();