(function (root, factory) {
  const runtime = root?.AirInterMinitel || (typeof require === 'function' ? require('./runtime.js') : null);
  const api = factory(runtime);
  if (typeof module === 'object' && module.exports) module.exports = api;
  if (root) root.AirInterMinitel = Object.assign(root.AirInterMinitel || {}, api);
})(typeof globalThis !== 'undefined' ? globalThis : this, function (runtime) {
  'use strict';

  if (!runtime) throw new Error('AirInterMinitel runtime must be loaded before renderer.js.');

  class MinitelDomRenderer {
    constructor(host, options = {}) {
      if (!host || typeof host.appendChild !== 'function') throw new TypeError('A DOM host element is required.');
      this.host = host;
      this.speed = options.speed || 'fast';
      this.cursor = { row: 24, column: 0, visible: false };
      this.cells = [];
      this.lastSnapshot = null;
      this.abortController = null;
      this.build();
    }

    build() {
      this.host.classList.add('ai-minitel-terminal');
      this.host.setAttribute('role', 'application');
      this.host.setAttribute('aria-label', '3615 AIRINTER');
      this.host.innerHTML = '';

      const screen = document.createElement('div');
      screen.className = 'ai-minitel-screen';
      screen.setAttribute('aria-live', 'polite');
      screen.setAttribute('aria-atomic', 'true');

      for (let row = 0; row < runtime.HEIGHT; row += 1) {
        const rowNode = document.createElement('div');
        rowNode.className = 'ai-minitel-row';
        rowNode.dataset.row = String(row);
        for (let column = 0; column < runtime.WIDTH; column += 1) {
          const cell = document.createElement('span');
          cell.className = 'ai-minitel-cell';
          cell.dataset.row = String(row);
          cell.dataset.column = String(column);
          cell.textContent = ' ';
          rowNode.appendChild(cell);
          this.cells.push(cell);
        }
        screen.appendChild(rowNode);
      }

      this.host.appendChild(screen);
      this.screenNode = screen;
    }

    cellNode(row, column) {
      return this.cells[(row * runtime.WIDTH) + column];
    }

    applyCell(row, column, cell) {
      const node = this.cellNode(row, column);
      if (!node) return;
      node.textContent = cell.character === ' ' ? '\u00a0' : cell.character;
      node.dataset.fg = cell.attrs.foreground;
      node.dataset.bg = cell.attrs.background;
      for (const name of ['blink', 'inverse', 'underline', 'doubleWidth', 'doubleHeight', 'mosaic', 'concealed']) {
        node.toggleAttribute(`data-${name.replace(/[A-Z]/g, (m) => '-' + m.toLowerCase())}`, Boolean(cell.attrs[name]));
      }
    }

    showCursor(row, column, visible = true) {
      this.cells.forEach((cell) => cell.removeAttribute('data-cursor'));
      this.cursor = {
        row: Math.max(0, Math.min(runtime.HEIGHT - 1, row)),
        column: Math.max(0, Math.min(runtime.WIDTH - 1, column)),
        visible: Boolean(visible)
      };
      if (this.cursor.visible) this.cellNode(this.cursor.row, this.cursor.column)?.setAttribute('data-cursor', '');
    }

    renderInstant(snapshot) {
      for (let row = 0; row < runtime.HEIGHT; row += 1) {
        for (let column = 0; column < runtime.WIDTH; column += 1) {
          this.applyCell(row, column, snapshot.cells[row][column]);
        }
      }
      this.lastSnapshot = snapshot;
      return Promise.resolve();
    }

    async render(snapshot, options = {}) {
      this.cancel();
      const speed = options.speed || this.speed;
      const replay = Boolean(options.replay);
      const previous = replay ? null : this.lastSnapshot;

      if (speed === 'instant' || window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
        return this.renderInstant(snapshot);
      }

      const controller = new AbortController();
      this.abortController = controller;
      if (replay) {
        this.cells.forEach((cell) => {
          cell.textContent = '\u00a0';
          [...cell.attributes].forEach((attribute) => {
            if (attribute.name.startsWith('data-') && !['data-row', 'data-column'].includes(attribute.name)) {
              cell.removeAttribute(attribute.name);
            }
          });
        });
      }

      const operations = runtime.transmissionOperations(snapshot, previous);
      const delay = runtime.transmissionDelay(speed);
      for (const operation of operations) {
        if (controller.signal.aborted) return;
        this.applyCell(operation.row, operation.column, operation.cell);
        if (delay > 0) await new Promise((resolve) => window.setTimeout(resolve, delay));
      }
      this.lastSnapshot = snapshot;
      this.abortController = null;
    }

    repeat() {
      if (!this.lastSnapshot) return Promise.resolve();
      return this.render(this.lastSnapshot, { replay: true });
    }

    cancel() {
      this.abortController?.abort();
      this.abortController = null;
    }

    destroy() {
      this.cancel();
      this.host.innerHTML = '';
      this.host.classList.remove('ai-minitel-terminal');
    }
  }

  class MinitelKeyboardController {
    constructor(session, renderer, target = document) {
      this.session = session;
      this.renderer = renderer;
      this.target = target;
      this.onKeyDown = this.onKeyDown.bind(this);
    }

    attach() {
      this.target.addEventListener('keydown', this.onKeyDown);
      return this;
    }

    detach() {
      this.target.removeEventListener('keydown', this.onKeyDown);
      return this;
    }

    async onKeyDown(event) {
      const command = runtime.mapKeyboardEvent(event);
      if (!command) return;
      event.preventDefault();
      event.stopPropagation();
      const outcome = this.session.dispatch(event);
      await this.renderer.render(outcome.snapshot, { replay: Boolean(outcome.replay) });
    }
  }

  return Object.freeze({ MinitelDomRenderer, MinitelKeyboardController });
});
