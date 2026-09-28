(() => {
    const root = document.documentElement;
    const key = 'argos.theme';
    const saved = localStorage.getItem(key) || 'system';

    const apply = (theme) => {
        root.dataset.theme = theme;
        localStorage.setItem(key, theme);
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.title = theme === 'system' ? 'Thème système' : theme === 'dark' ? 'Thème sombre' : 'Thème clair';
            button.setAttribute('aria-label', button.title);
            button.textContent = theme === 'dark' ? '☾' : theme === 'light' ? '☀' : '◐';
        });
    };

    apply(saved);

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-theme-toggle]');
        if (!button) return;

        const current = root.dataset.theme || 'system';
        apply(current === 'system' ? 'light' : current === 'light' ? 'dark' : 'system');
    });
})();
