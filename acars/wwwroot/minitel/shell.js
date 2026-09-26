(function (root, factory) {
  const runtime = root?.AirInterMinitel || (typeof require === 'function' ? require('./runtime.js') : null);
  const api = factory(runtime);
  if (typeof module === 'object' && module.exports) module.exports = api;
  if (root) root.AirInterMinitel = Object.assign(root.AirInterMinitel || {}, api);
})(typeof globalThis !== 'undefined' ? globalThis : this, function (runtime) {
  'use strict';

  if (!runtime) throw new Error('AirInterMinitel runtime must be loaded before shell.js.');

  const SYSTEM_PAGES = Object.freeze({
    GUIDE: '__system_guide',
    SETTINGS: '__system_settings',
    EXIT: '__system_exit'
  });

  const EXIT_REASONS = Object.freeze({
    USER: 'user',
    MOBILE: 'mobile',
    RUNTIME_ERROR: 'runtime-error',
    HOTKEY: 'hotkey',
    CONNECT_END: 'connect-end'
  });

  const DEFAULT_LABELS = Object.freeze({
    exit: 'Quitter le mode Minitel',
    fallbackTitle: 'Mode Minitel indisponible',
    fallbackBody: 'Cette interface nécessite un ordinateur avec clavier et pointeur précis.',
    fallbackAction: 'Utiliser le mode moderne'
  });

  function pad(text, width, side = 'right') {
    const value = String(text ?? '').slice(0, width);
    const missing = Math.max(0, width - Array.from(value).length);
    return side === 'left' ? ' '.repeat(missing) + value : value + ' '.repeat(missing);
  }

  function formatClock(date = new Date()) {
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return hours + ':' + minutes;
  }

  function buildServiceLine(options = {}) {
    const service = String(options.service || '3615 AIRINTER').toUpperCase().slice(0, 18);
    const identity = String(options.identity || '').toUpperCase().slice(0, 8);
    const clock = options.clock || formatClock(options.date);
    const state = String(options.state || 'C').slice(0, 1).toUpperCase();
    const right = pad(clock, 5, 'left') + ' ' + state;
    const leftWidth = runtime.WIDTH - right.length;
    const left = (pad(service, 18) + pad(identity, 8)).slice(0, leftWidth).padEnd(leftWidth, ' ');
    return left + right;
  }

  const AIR_INTER_MOSAIC_MARK = Object.freeze([
    Object.freeze([1, 3, 15, 63, 62, 60, 56, 48, 32]),
    Object.freeze([3, 15, 63, 62, 60, 56, 48, 32, 0]),
    Object.freeze([15, 63, 62, 60, 56, 48, 32, 0, 0])
  ]);

  function writeAirInterMosaic(screen, row, column, attrs = {}) {
    AIR_INTER_MOSAIC_MARK.forEach((masks, offset) => {
      screen.writeMosaic(row + offset, column, masks, {
        foreground: 'blue',
        ...attrs
      });
    });
    return screen;
  }

  function writeCentered(screen, row, text, attrs = {}) {
    const value = String(text ?? '').slice(0, runtime.WIDTH);
    const column = Math.max(0, Math.floor((runtime.WIDTH - Array.from(value).length) / 2));
    screen.write(row, column, value, attrs);
    return screen;
  }

  function createBootSequence(options = {}) {
    const service = options.service || '3615 AIRINTER';
    const product = options.product || 'PROMETHEE';
    const identity = options.identity || '';
    const frames = [];

    const frame = (writer) => {
      const screen = new runtime.MinitelScreenBuffer();
      screen.write(0, 0, buildServiceLine({ service, identity, state: 'C', date: options.date }), { foreground: 'cyan' });
      writer(screen);
      frames.push(screen.snapshot());
    };

    frame((screen) => {
      writeCentered(screen, 7, 'VIDEOTEX', { foreground: 'cyan' });
      writeCentered(screen, 9, '1200/75 BAUD', { foreground: 'blue' });
      writeCentered(screen, 13, 'CONNEXION EN COURS...', { foreground: 'yellow' });
    });

    frame((screen) => {
      writeAirInterMosaic(screen, 5, 6, { foreground: 'blue' });
      screen.write(6, 17, 'AIR INTER', { foreground: 'yellow', doubleWidth: true });
      writeCentered(screen, 11, service, { foreground: 'cyan' });
      writeCentered(screen, 14, 'CONNEXION ETABLIE', { foreground: 'green' });
    });

    frame((screen) => {
      writeAirInterMosaic(screen, 4, 6, { foreground: 'blue', separatedMosaic: true });
      screen.write(5, 17, 'AIR INTER', { foreground: 'yellow' });
      writeCentered(screen, 9, product, { foreground: 'cyan' });
      writeCentered(screen, 13, identity ? 'IDENTIFICATION ' + identity : 'IDENTIFICATION PILOTE');
      writeCentered(screen, 16, 'SESSION OUVERTE', { foreground: 'green' });
    });

    return Object.freeze(frames);
  }

  function createGuidePage(options = {}) {
    const service = options.service || '3615 AIRINTER';
    return new runtime.MinitelPage(SYSTEM_PAGES.GUIDE, {
      onRender: (_context, screen, session) => {
        screen.write(0, 0, buildServiceLine({ service, state: 'C' }), { foreground: 'cyan' });
        screen.write(2, 2, 'GUIDE CLAVIER', { foreground: 'yellow' });
        screen.write(4, 2, 'ENTER      ENVOI');
        screen.write(5, 2, 'BACKSPACE  CORRECTION');
        screen.write(6, 2, 'ESC        ANNULATION');
        screen.write(7, 2, 'HOME       SOMMAIRE');
        screen.write(8, 2, 'F1         GUIDE');
        screen.write(9, 2, 'F2         REPETITION');
        screen.write(10, 2, 'PAGE UP    RETOUR');
        screen.write(11, 2, 'PAGE DOWN  SUITE');
        screen.write(12, 2, 'F10        CONNEXION/FIN');
        screen.write(15, 2, '0          REGLAGES VIDEOTEX', { foreground: 'cyan' });
        screen.write(18, 2, 'CHOIX : ' + session.input.value, { foreground: 'yellow' });
        screen.write(21, 2, 'CTRL+ALT+M SORTIE URGENCE', { foreground: 'red' });
        screen.write(23, 0, 'SOMMAIRE  RETOUR                ENVOI', { foreground: 'cyan' });
      },
      acceptInput: (key) => key === '0',
      send: (value, context) => {
        if (value !== '0') return null;
        context.__minitelSettingsReturnPage = SYSTEM_PAGES.GUIDE;
        return SYSTEM_PAGES.SETTINGS;
      }
    });
  }

  function createSettingsPage(options = {}) {
    const service = options.service || '3615 AIRINTER';
    return new runtime.MinitelPage(SYSTEM_PAGES.SETTINGS, {
      onRender: (context, screen, session) => {
        const preferences = context.__minitelPreferences || {
          speed: options.speed || 'fast',
          displayMode: options.displayMode || 'color'
        };
        screen.write(0, 0, buildServiceLine({ service, state: 'C' }), { foreground: 'cyan' });
        screen.write(2, 2, 'REGLAGES VIDEOTEX', { foreground: 'yellow' });
        screen.write(4, 2, 'VITESSE DE TRANSMISSION', { foreground: 'cyan' });
        screen.write(6, 4, '1 AUTHENTIQUE  1200/75');
        screen.write(7, 4, '2 RAPIDE');
        screen.write(8, 4, '3 INSTANTANEE');
        screen.write(10, 2, 'AFFICHAGE', { foreground: 'cyan' });
        screen.write(12, 4, '4 COULEUR');
        screen.write(13, 4, '5 MONOCHROME / LUMINANCE');
        screen.write(16, 2, 'VITESSE : ' + String(preferences.speed || 'fast').toUpperCase(), { foreground: 'green' });
        screen.write(17, 2, 'ECRAN   : ' + (preferences.displayMode === 'monochrome' ? 'MONOCHROME' : 'COULEUR'), { foreground: 'green' });
        screen.write(20, 2, 'CHOIX : ' + session.input.value, { foreground: 'yellow' });
        screen.write(23, 0, 'SOMMAIRE  RETOUR                ENVOI', { foreground: 'cyan' });
      },
      acceptInput: (key) => /^[1-5]$/.test(key),
      send: (value, context) => {
        const current = context.__minitelPreferences || {
          speed: options.speed || 'fast',
          displayMode: options.displayMode || 'color'
        };
        const next = { ...current };
        if (value === '1') next.speed = 'authentic';
        if (value === '2') next.speed = 'fast';
        if (value === '3') next.speed = 'instant';
        if (value === '4') next.displayMode = 'color';
        if (value === '5') next.displayMode = 'monochrome';
        context.__minitelPreferences = next;
        context.__minitelPreferenceChange = { ...next };
        return SYSTEM_PAGES.SETTINGS;
      },
      previous: (context) => context.__minitelSettingsReturnPage || SYSTEM_PAGES.GUIDE
    });
  }

  function createExitPage(options = {}) {
    const service = options.service || '3615 AIRINTER';
    return new runtime.MinitelPage(SYSTEM_PAGES.EXIT, {
      onRender: (_context, screen, session) => {
        screen.write(0, 0, buildServiceLine({ service, state: 'C' }), { foreground: 'cyan' });
        screen.write(4, 6, 'FIN DE COMMUNICATION', { foreground: 'yellow' });
        screen.write(8, 4, '1 QUITTER LE MODE MINITEL');
        screen.write(10, 4, '2 ANNULER');
        screen.write(12, 4, '3 REGLAGES VIDEOTEX');
        screen.write(15, 4, 'VOTRE CHOIX : ' + session.input.value, { foreground: 'cyan' });
        screen.write(23, 0, 'SOMMAIRE  RETOUR                ENVOI', { foreground: 'cyan' });
      },
      acceptInput: (key) => /^[123]$/.test(key),
      send: (value, context) => {
        if (value === '1') {
          context.__minitelExitRequested = true;
          return null;
        }
        if (value === '3') {
          context.__minitelSettingsReturnPage = SYSTEM_PAGES.EXIT;
          return SYSTEM_PAGES.SETTINGS;
        }
        return value === '2' ? context.__minitelExitReturnPage || null : null;
      }
    });
  }

  function registerSystemPages(session, options = {}) {
    session.context.__minitelPreferences = {
      speed: options.speed || session.speed || 'fast',
      displayMode: options.displayMode || 'color',
      ...(session.context.__minitelPreferences || {})
    };
    if (!session.pages.has(SYSTEM_PAGES.GUIDE)) session.register(createGuidePage(options));
    if (!session.pages.has(SYSTEM_PAGES.SETTINGS)) session.register(createSettingsPage(options));
    if (!session.pages.has(SYSTEM_PAGES.EXIT)) session.register(createExitPage(options));
    return session;
  }

  class MinitelShell {
    constructor(options = {}) {
      this.document = options.document || (typeof document !== 'undefined' ? document : null);
      this.window = options.window || (typeof window !== 'undefined' ? window : null);
      this.host = options.host || null;
      this.renderer = options.renderer || null;
      this.session = options.session || null;
      this.keyboard = options.keyboard || null;
      this.service = options.service || '3615 AIRINTER';
      this.product = options.product || 'PROMETHEE';
      this.identity = options.identity || '';
      this.labels = { ...DEFAULT_LABELS, ...(options.labels || {}) };
      this.onExit = typeof options.onExit === 'function' ? options.onExit : () => {};
      this.onPreferencesChange = typeof options.onPreferencesChange === 'function' ? options.onPreferencesChange : () => {};
      this.speed = runtime.SPEEDS[options.speed] ? options.speed : 'fast';
      this.displayMode = runtime.DISPLAY_MODES[options.displayMode] ? options.displayMode : 'color';
      this.bootFrameDelay = Number.isFinite(options.bootFrameDelay) ? Math.max(0, options.bootFrameDelay) : 320;
      this.shellNode = null;
      this.terminalNode = null;
      this.escapeButton = null;
      this.fallbackNode = null;
      this.controlDeck = null;
      this.active = false;
      this.onGlobalKeyDown = this.onGlobalKeyDown.bind(this);
      this.onRuntimeError = this.onRuntimeError.bind(this);
    }

    capability() {
      return runtime.minitelCapability(this.window || {});
    }

    mount() {
      if (!this.document || !this.host) throw new Error('MinitelShell requires document and host.');
      this.host.innerHTML = '';
      this.host.classList.add('ai-minitel-shell-host');

      const shell = this.document.createElement('section');
      shell.className = 'ai-minitel-shell';
      shell.setAttribute('data-product', this.product.toLowerCase());

      const toolbar = this.document.createElement('div');
      toolbar.className = 'ai-minitel-shell-toolbar';
      toolbar.setAttribute('aria-label', 'Contrôles de sécurité du mode Minitel');

      const service = this.document.createElement('span');
      service.className = 'ai-minitel-shell-service';
      service.textContent = this.service;

      const exitButton = this.document.createElement('button');
      exitButton.type = 'button';
      exitButton.className = 'ai-minitel-shell-exit';
      exitButton.textContent = this.labels.exit;
      exitButton.addEventListener('click', () => this.exit(EXIT_REASONS.USER));

      const terminal = this.document.createElement('div');
      terminal.className = 'ai-minitel-shell-terminal';

      toolbar.appendChild(service);
      toolbar.appendChild(exitButton);
      shell.appendChild(toolbar);
      shell.appendChild(terminal);
      this.host.appendChild(shell);

      this.shellNode = shell;
      this.terminalNode = terminal;
      this.escapeButton = exitButton;
      this.active = true;

      this.window?.addEventListener?.('keydown', this.onGlobalKeyDown, true);
      this.window?.addEventListener?.('error', this.onRuntimeError);
      this.window?.addEventListener?.('unhandledrejection', this.onRuntimeError);
      return this;
    }

    showFallback(reason = 'unsupported') {
      if (!this.document || !this.host) throw new Error('MinitelShell requires document and host.');
      this.host.innerHTML = '';

      const fallback = this.document.createElement('section');
      fallback.className = 'ai-minitel-fallback';
      fallback.dataset.reason = reason;
      fallback.setAttribute('aria-live', 'polite');

      const card = this.document.createElement('div');
      card.className = 'ai-minitel-fallback-card';

      const title = this.document.createElement('h1');
      title.textContent = this.labels.fallbackTitle;
      const body = this.document.createElement('p');
      body.textContent = this.labels.fallbackBody;
      const button = this.document.createElement('button');
      button.type = 'button';
      button.textContent = this.labels.fallbackAction;
      button.addEventListener('click', () => this.exit(EXIT_REASONS.MOBILE));

      card.appendChild(title);
      card.appendChild(body);
      card.appendChild(button);
      fallback.appendChild(card);
      this.host.appendChild(fallback);
      this.fallbackNode = fallback;
      this.active = false;
      return this;
    }

    async start() {
      const capability = this.capability();
      if (!capability.allowed) {
        this.showFallback(capability.reason || 'unsupported');
        return { started: false, capability };
      }

      if (!this.shellNode) this.mount();
      if (!this.renderer) throw new Error('MinitelShell requires a renderer before start().');
      if (!this.session) throw new Error('MinitelShell requires a session before start().');

      registerSystemPages(this.session, {
        service: this.service,
        speed: this.speed,
        displayMode: this.displayMode
      });
      this.renderer.setSpeed?.(this.speed);
      this.renderer.setDisplayMode?.(this.displayMode);
      this.mountControls();

      if (this.keyboard) {
        const downstreamInterceptor = this.keyboard.commandInterceptor;
        const downstreamAfterDispatch = this.keyboard.afterDispatch;

        this.keyboard.commandInterceptor = async (command, event, activeSession) => {
          if (command.action === runtime.ACTIONS.GUIDE) {
            return { handled: true, action: command.action, snapshot: this.openGuide() };
          }
          if (command.action === runtime.ACTIONS.CONNECT_END) {
            return { handled: true, action: command.action, snapshot: this.openExit() };
          }
          return downstreamInterceptor ? downstreamInterceptor(command, event, activeSession) : null;
        };

        this.keyboard.afterDispatch = (outcome, command, event) => {
          downstreamAfterDispatch?.(outcome, command, event);
          this.syncSystemCursor();
          const preferenceChange = this.session?.context?.__minitelPreferenceChange;
          if (preferenceChange) {
            this.session.context.__minitelPreferenceChange = null;
            this.applyPreferences(preferenceChange);
          }
          if (this.session?.context?.__minitelExitRequested) {
            this.session.context.__minitelExitRequested = false;
            this.exit(EXIT_REASONS.CONNECT_END);
          }
        };
      }

      const frames = createBootSequence({
        service: this.service,
        product: this.product,
        identity: this.identity
      });

      for (const snapshot of frames) {
        await this.renderer.render(snapshot, { replay: true });
        if (this.bootFrameDelay > 0) await new Promise((resolve) => this.window.setTimeout(resolve, this.bootFrameDelay));
      }

      const snapshot = this.session.start();
      await this.renderer.render(snapshot, { replay: true });
      this.keyboard?.attach?.();
      return { started: true, capability, snapshot };
    }

    mountControls() {
      if (!this.document || !this.terminalNode) return null;
      this.controlDeck?.remove?.();

      const deck = this.document.createElement('div');
      deck.className = 'ai-minitel-control-deck';
      deck.setAttribute('aria-label', 'Clavier Minitel virtuel');

      const brand = this.document.createElement('div');
      brand.className = 'ai-minitel-deck-brand';
      deck.appendChild(brand);

      const rows = [
        {
          className: 'ai-minitel-control-row is-system',
          keys: [
            ['Début enr.', 'Home', 'is-function is-orange'],
            ['Sommaire', 'Home', 'is-function'],
            ['Annulation', 'Escape', 'is-function'],
            ['Retour', 'PageUp', 'is-function'],
            ['Répétition', 'F2', 'is-function'],
            ['Envoi', 'Enter', 'is-function is-send']
          ]
        },
        {
          className: 'ai-minitel-control-row is-system',
          keys: [
            ['Fnct', 'F1', 'is-function'],
            ['Cnx/fin', 'F10', 'is-function'],
            ['Guide', 'F1', 'is-function'],
            ['Correction', 'Backspace', 'is-function'],
            ['Suite', 'PageDown', 'is-function'],
            ['Envoi', 'Enter', 'is-function is-send']
          ]
        },
        {
          className: 'ai-minitel-control-row is-alpha',
          keys: 'AZERTYUIOP'.split('').map((key) => [key, key.toLowerCase(), ''])
        },
        {
          className: 'ai-minitel-control-row is-alpha',
          keys: 'QSDFGHJKLM'.split('').map((key) => [key, key.toLowerCase(), ''])
        },
        {
          className: 'ai-minitel-control-row is-alpha-short',
          keys: [
            ['Ctrl', 'Control', ''], ['W', 'w', ''], ['X', 'x', ''], ['C', 'c', ''], ['V', 'v', ''],
            ['B', 'b', ''], ['N', 'n', ''], ['Maj', 'Shift', ''], ['.', '.', ''], ['?', '?', '']
          ]
        },
        {
          className: 'ai-minitel-control-row is-space',
          keys: [['↑', 'ArrowUp', ''], ['↓', 'ArrowDown', ''], ['Espace', ' ', ''], ['←', 'ArrowLeft', ''], ['→', 'ArrowRight', '']]
        }
      ];

      const dispatchKey = (key) => {
        if (!this.active || !this.document) return;
        if (key === 'Control' || key === 'Shift' || /^Arrow/.test(key)) {
          this.terminalNode?.focus?.();
          return;
        }
        const printable = key.length === 1 && key !== ' ';
        const event = new KeyboardEvent('keydown', {
          key,
          code: printable && /[a-z]/i.test(key) ? 'Key' + key.toUpperCase() : '',
          bubbles: true,
          cancelable: true
        });
        this.document.dispatchEvent(event);
        this.terminalNode?.focus?.();
      };

      rows.forEach((rowSpec) => {
        const row = this.document.createElement('div');
        row.className = rowSpec.className;
        rowSpec.keys.forEach(([label, key, extraClass]) => {
          const button = this.document.createElement('button');
          button.type = 'button';
          button.className = ('ai-minitel-key ' + (extraClass || '')).trim();
          button.setAttribute('aria-label', label);
          const kbd = this.document.createElement('kbd');
          kbd.textContent = label;
          button.appendChild(kbd);
          button.addEventListener('click', () => dispatchKey(key));
          row.appendChild(button);
        });
        deck.appendChild(row);
      });

      this.terminalNode.appendChild(deck);
      this.controlDeck = deck;
      return deck;
    }

    syncSystemCursor() {
      if (!this.session || !this.renderer?.showCursor) return;
      const page = this.session.currentPageId;
      const length = this.session.input?.value?.length || 0;
      if (page === SYSTEM_PAGES.GUIDE) {
        this.renderer.showCursor(18, Math.min(runtime.WIDTH - 1, 10 + length), true);
        return;
      }
      if (page === SYSTEM_PAGES.SETTINGS) {
        this.renderer.showCursor(20, Math.min(runtime.WIDTH - 1, 10 + length), true);
        return;
      }
      if (page === SYSTEM_PAGES.EXIT) {
        this.renderer.showCursor(15, Math.min(runtime.WIDTH - 1, 19 + length), true);
      }
    }

    openGuide() {
      if (!this.session) return null;
      const snapshot = this.session.go(SYSTEM_PAGES.GUIDE);
      this.syncSystemCursor();
      return snapshot;
    }

    openSettings(returnPage = null) {
      if (!this.session) return null;
      if (returnPage) this.session.context.__minitelSettingsReturnPage = returnPage;
      const snapshot = this.session.go(SYSTEM_PAGES.SETTINGS);
      this.syncSystemCursor();
      return snapshot;
    }

    applyPreferences(preferences = {}) {
      if (runtime.SPEEDS[preferences.speed]) {
        this.speed = preferences.speed;
        if (this.session) this.session.speed = preferences.speed;
        this.renderer?.setSpeed?.(preferences.speed);
      }
      if (runtime.DISPLAY_MODES[preferences.displayMode]) {
        this.displayMode = preferences.displayMode;
        this.renderer?.setDisplayMode?.(preferences.displayMode);
      }
      const current = Object.freeze({ speed: this.speed, displayMode: this.displayMode });
      if (this.session) this.session.context.__minitelPreferences = { ...current };
      this.onPreferencesChange(current);
      return current;
    }

    openExit() {
      if (!this.session) return null;
      this.session.context.__minitelExitReturnPage = this.session.currentPageId;
      const snapshot = this.session.go(SYSTEM_PAGES.EXIT);
      this.syncSystemCursor();
      return snapshot;
    }

    onGlobalKeyDown(event) {
      if (event.ctrlKey && event.altKey && String(event.key || '').toLowerCase() === 'm') {
        event.preventDefault?.();
        event.stopPropagation?.();
        this.exit(EXIT_REASONS.HOTKEY);
      }
    }

    onRuntimeError() {
      if (this.active) this.ensureEscapeVisible();
    }

    ensureEscapeVisible() {
      if (!this.escapeButton) return false;
      this.escapeButton.hidden = false;
      this.escapeButton.removeAttribute?.('aria-hidden');
      this.escapeButton.classList?.add('is-emergency');
      return true;
    }

    exit(reason = EXIT_REASONS.USER) {
      this.keyboard?.detach?.();
      this.renderer?.cancel?.();
      this.active = false;
      this.onExit(reason);
      return reason;
    }

    destroy() {
      this.keyboard?.detach?.();
      this.renderer?.destroy?.();
      this.window?.removeEventListener?.('keydown', this.onGlobalKeyDown, true);
      this.window?.removeEventListener?.('error', this.onRuntimeError);
      this.window?.removeEventListener?.('unhandledrejection', this.onRuntimeError);
      this.controlDeck = null;
      if (this.host) this.host.innerHTML = '';
      this.active = false;
    }
  }

  return Object.freeze({
    SYSTEM_PAGES,
    EXIT_REASONS,
    DEFAULT_LABELS,
    buildServiceLine,
    createBootSequence,
    createGuidePage,
    createSettingsPage,
    createExitPage,
    writeAirInterMosaic,
    registerSystemPages,
    MinitelShell
  });
});
