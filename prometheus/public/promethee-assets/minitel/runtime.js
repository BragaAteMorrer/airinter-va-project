(function (root, factory) {
  const api = factory();
  if (typeof module === 'object' && module.exports) module.exports = api;
  if (root) root.AirInterMinitel = Object.assign(root.AirInterMinitel || {}, api);
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
  'use strict';

  const WIDTH = 40;
  const HEIGHT = 25;
  const SERVICE_ROW = 0;

  const ACTIONS = Object.freeze({
    SEND: 'ENVOI',
    CORRECT: 'CORRECTION',
    CANCEL: 'ANNULATION',
    SUMMARY: 'SOMMAIRE',
    GUIDE: 'GUIDE',
    REPEAT: 'REPETITION',
    BACK: 'RETOUR',
    NEXT: 'SUITE',
    CONNECT_END: 'CONNEXION_FIN',
    INPUT: 'INPUT'
  });

  const SPEEDS = Object.freeze({
    authentic: 120,
    fast: 600,
    instant: Infinity
  });

  const DEFAULT_ATTRS = Object.freeze({
    foreground: 'white',
    background: 'black',
    blink: false,
    inverse: false,
    underline: false,
    doubleWidth: false,
    doubleHeight: false,
    mosaic: false,
    concealed: false
  });

  const clamp = (value, min, max) => Math.max(min, Math.min(max, value));

  class MinitelCell {
    constructor(character = ' ', attrs = {}) {
      const chars = Array.from(String(character || ' '));
      this.character = chars.length ? chars[0] : ' ';
      this.attrs = Object.freeze({ ...DEFAULT_ATTRS, ...attrs });
      Object.freeze(this);
    }
  }

  class MinitelScreenBuffer {
    constructor(width = WIDTH, height = HEIGHT) {
      if (width !== WIDTH || height !== HEIGHT) {
        throw new RangeError('Minitel runtime uses an invariant 40x25 logical screen.');
      }
      this.width = width;
      this.height = height;
      this.clear();
    }

    clear(attrs = {}) {
      this.cells = Array.from({ length: this.height }, () =>
        Array.from({ length: this.width }, () => new MinitelCell(' ', attrs))
      );
      return this;
    }

    set(row, column, character, attrs = {}) {
      if (row < 0 || row >= this.height || column < 0 || column >= this.width) return false;
      this.cells[row][column] = new MinitelCell(character, attrs);
      return true;
    }

    write(row, column, text, attrs = {}, options = {}) {
      const wrap = Boolean(options.wrap);
      let r = row;
      let c = column;
      let written = 0;

      for (const character of Array.from(String(text ?? ''))) {
        if (character === '\n') {
          r += 1;
          c = 0;
          if (r >= this.height) break;
          continue;
        }
        if (r < 0 || r >= this.height) break;
        if (c >= this.width) {
          if (!wrap) break;
          r += 1;
          c = 0;
          if (r >= this.height) break;
        }
        if (c >= 0 && this.set(r, c, character, attrs)) written += 1;
        c += 1;
      }
      return written;
    }

    fill(row, fromColumn, toColumn, character = ' ', attrs = {}) {
      const start = clamp(fromColumn, 0, this.width - 1);
      const end = clamp(toColumn, 0, this.width - 1);
      for (let c = start; c <= end; c += 1) this.set(row, c, character, attrs);
      return this;
    }

    line(row) {
      if (row < 0 || row >= this.height) return '';
      return this.cells[row].map((cell) => cell.character).join('');
    }

    text() {
      return this.cells.map((_, row) => this.line(row)).join('\n');
    }

    snapshot() {
      return Object.freeze({
        width: this.width,
        height: this.height,
        cells: this.cells.map((row) => row.slice())
      });
    }

    clone() {
      const copy = new MinitelScreenBuffer();
      this.cells.forEach((row, r) => row.forEach((cell, c) => {
        copy.cells[r][c] = cell;
      }));
      return copy;
    }
  }

  class MinitelInputBuffer {
    constructor(maxLength = WIDTH) {
      this.maxLength = clamp(Number(maxLength) || WIDTH, 1, WIDTH);
      this.value = '';
    }

    append(character) {
      if (this.value.length >= this.maxLength) return false;
      const chars = Array.from(String(character || ''));
      if (!chars.length) return false;
      this.value += chars[0];
      return true;
    }

    correct() {
      const chars = Array.from(this.value);
      if (!chars.length) return false;
      chars.pop();
      this.value = chars.join('');
      return true;
    }

    cancel() {
      const changed = this.value.length > 0;
      this.value = '';
      return changed;
    }

    consume() {
      const value = this.value;
      this.value = '';
      return value;
    }
  }

  function mapKeyboardEvent(eventOrKey) {
    const key = typeof eventOrKey === 'string' ? eventOrKey : eventOrKey?.key;
    const shiftKey = typeof eventOrKey === 'object' && Boolean(eventOrKey?.shiftKey);

    const map = {
      Enter: ACTIONS.SEND,
      Backspace: ACTIONS.CORRECT,
      Escape: ACTIONS.CANCEL,
      Home: ACTIONS.SUMMARY,
      F1: ACTIONS.GUIDE,
      F2: ACTIONS.REPEAT,
      PageUp: ACTIONS.BACK,
      ArrowUp: ACTIONS.BACK,
      PageDown: ACTIONS.NEXT,
      ArrowDown: ACTIONS.NEXT,
      F10: ACTIONS.CONNECT_END
    };

    if (map[key]) return { action: map[key], key, refresh: key === 'F2' && shiftKey };
    if (typeof key === 'string' && key.length === 1 && !/^[\u0000-\u001f\u007f]$/.test(key)) {
      return { action: ACTIONS.INPUT, key };
    }
    return null;
  }

  class MinitelPage {
    constructor(id, handlers = {}) {
      if (!id) throw new TypeError('A Minitel page requires an id.');
      this.id = id;
      Object.assign(this, handlers);
    }

    render(context, screen, session) {
      if (typeof this.onRender === 'function') this.onRender(context, screen, session);
      return screen;
    }
  }

  class MinitelSession {
    constructor(options = {}) {
      this.screen = new MinitelScreenBuffer();
      this.input = new MinitelInputBuffer(options.inputLength || WIDTH);
      this.pages = new Map();
      this.currentPageId = null;
      this.history = [];
      this.context = options.context || {};
      this.homePageId = options.homePageId || null;
      this.speed = SPEEDS[options.speed] ? options.speed : 'fast';
      this.connectionState = 'connected';
    }

    register(page) {
      if (!(page instanceof MinitelPage)) throw new TypeError('register() expects a MinitelPage.');
      this.pages.set(page.id, page);
      return this;
    }

    start(pageId = this.homePageId) {
      if (!pageId) throw new Error('No start page configured.');
      this.history = [];
      return this.go(pageId, { recordHistory: false });
    }

    go(pageId, options = {}) {
      const page = this.pages.get(pageId);
      if (!page) throw new Error(`Unknown Minitel page: ${pageId}`);
      if (options.recordHistory !== false && this.currentPageId && this.currentPageId !== pageId) {
        this.history.push(this.currentPageId);
      }
      this.currentPageId = pageId;
      this.input.cancel();
      return this.render();
    }

    currentPage() {
      return this.pages.get(this.currentPageId) || null;
    }

    render() {
      const page = this.currentPage();
      if (!page) throw new Error('No current Minitel page.');
      this.screen.clear();
      page.render(this.context, this.screen, this);
      return this.screen.snapshot();
    }

    back() {
      const current = this.currentPage();
      if (typeof current?.back === 'function') {
        const target = current.back(this.context);
        if (target) return this.go(target);
      }
      const target = this.history.pop();
      if (!target) return this.render();
      this.currentPageId = target;
      this.input.cancel();
      return this.render();
    }

    summary() {
      if (!this.homePageId) return this.render();
      this.history = [];
      return this.go(this.homePageId, { recordHistory: false });
    }

    dispatch(eventOrKey) {
      const command = mapKeyboardEvent(eventOrKey);
      if (!command) return { handled: false, snapshot: this.screen.snapshot() };

      const page = this.currentPage();
      let result = null;

      switch (command.action) {
        case ACTIONS.INPUT:
          if (typeof page?.acceptInput === 'function' && page.acceptInput(command.key, this.context) === false) break;
          this.input.append(command.key);
          result = typeof page?.input === 'function' ? page.input(command.key, this.input.value, this.context) : null;
          break;
        case ACTIONS.CORRECT:
          this.input.correct();
          result = typeof page?.correct === 'function' ? page.correct(this.input.value, this.context) : null;
          break;
        case ACTIONS.CANCEL:
          this.input.cancel();
          result = typeof page?.cancel === 'function' ? page.cancel(this.context) : null;
          break;
        case ACTIONS.SEND: {
          const value = this.input.consume();
          result = typeof page?.send === 'function' ? page.send(value, this.context) : null;
          break;
        }
        case ACTIONS.SUMMARY:
          return { handled: true, action: command.action, snapshot: this.summary() };
        case ACTIONS.BACK:
          if (typeof page?.previous === 'function') result = page.previous(this.context);
          else return { handled: true, action: command.action, snapshot: this.back() };
          break;
        case ACTIONS.NEXT:
          result = typeof page?.next === 'function' ? page.next(this.context) : null;
          break;
        case ACTIONS.GUIDE:
          result = typeof page?.guide === 'function' ? page.guide(this.context) : null;
          break;
        case ACTIONS.REPEAT:
          result = typeof page?.repeat === 'function' ? page.repeat(this.context, command.refresh) : null;
          return { handled: true, action: command.action, refresh: command.refresh, replay: true, snapshot: this.screen.snapshot(), result };
        case ACTIONS.CONNECT_END:
          result = typeof page?.connectEnd === 'function' ? page.connectEnd(this.context) : null;
          break;
        default:
          break;
      }

      if (typeof result === 'string' && this.pages.has(result)) {
        return { handled: true, action: command.action, snapshot: this.go(result), result };
      }

      const snapshot = this.render();
      return { handled: true, action: command.action, snapshot, result, input: this.input.value };
    }
  }

  function sameAttrs(left, right) {
    if (!left || !right) return false;
    return Object.keys(DEFAULT_ATTRS).every((key) => left[key] === right[key]);
  }

  function transmissionOperations(snapshot, previousSnapshot = null) {
    const operations = [];
    for (let row = 0; row < HEIGHT; row += 1) {
      for (let column = 0; column < WIDTH; column += 1) {
        const cell = snapshot.cells[row][column];
        const previous = previousSnapshot?.cells?.[row]?.[column];
        if (!previous || previous.character !== cell.character || !sameAttrs(previous.attrs, cell.attrs)) {
          operations.push(Object.freeze({ row, column, cell }));
        }
      }
    }
    return operations;
  }

  function transmissionDelay(speed = 'fast') {
    const cps = SPEEDS[speed] ?? SPEEDS.fast;
    return Number.isFinite(cps) ? Math.max(1, Math.round(1000 / cps)) : 0;
  }

  function minitelCapability(environment = {}) {
    const width = Number(environment.innerWidth || environment.screen?.width || 0);
    const matchMedia = typeof environment.matchMedia === 'function'
      ? environment.matchMedia.bind(environment)
      : null;
    const finePointer = matchMedia ? Boolean(matchMedia('(pointer: fine)').matches) : true;
    const canHover = matchMedia ? Boolean(matchMedia('(hover: hover)').matches) : true;
    const coarseOnly = matchMedia ? Boolean(matchMedia('(pointer: coarse)').matches) && !finePointer : false;
    const hasKeyboard = environment.navigator?.userAgentData?.mobile === true ? false : !coarseOnly;
    const allowed = width >= 900 && finePointer && canHover && hasKeyboard;
    let reason = null;
    if (width && width < 900) reason = 'screen';
    else if (!finePointer || !canHover || coarseOnly) reason = 'pointer';
    else if (!hasKeyboard) reason = 'keyboard';
    return Object.freeze({ allowed, reason, width, finePointer, canHover, hasKeyboard });
  }

  return Object.freeze({
    WIDTH,
    HEIGHT,
    SERVICE_ROW,
    ACTIONS,
    SPEEDS,
    DEFAULT_ATTRS,
    MinitelCell,
    MinitelScreenBuffer,
    MinitelInputBuffer,
    MinitelPage,
    MinitelSession,
    mapKeyboardEvent,
    transmissionOperations,
    transmissionDelay,
    minitelCapability
  });
});
