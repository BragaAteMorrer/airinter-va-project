'use strict';

const assert = require('node:assert/strict');
const runtime = require('./runtime.js');
const {
  SYSTEM_PAGES,
  EXIT_REASONS,
  buildServiceLine,
  createBootSequence,
  createSettingsPage,
  registerSystemPages,
  MinitelShell
} = require('./shell.js');

class FakeClassList {
  constructor() { this.values = new Set(); }
  add(...values) { values.forEach((value) => this.values.add(value)); }
  remove(...values) { values.forEach((value) => this.values.delete(value)); }
  contains(value) { return this.values.has(value); }
}

class FakeElement {
  constructor(tagName = 'div') {
    this.tagName = tagName.toUpperCase();
    this.children = [];
    this.classList = new FakeClassList();
    this.attributes = new Map();
    this.dataset = {};
    this.listeners = new Map();
    this.hidden = false;
    this.textContent = '';
    this.type = '';
    this._innerHTML = '';
  }
  set innerHTML(value) { this._innerHTML = String(value); if (value === '') this.children = []; }
  get innerHTML() { return this._innerHTML; }
  appendChild(child) { this.children.push(child); return child; }
  setAttribute(name, value) { this.attributes.set(name, String(value)); }
  removeAttribute(name) { this.attributes.delete(name); }
  addEventListener(name, handler) { this.listeners.set(name, handler); }
  click() { this.listeners.get('click')?.({ preventDefault() {}, stopPropagation() {} }); }
}

class FakeDocument {
  createElement(tagName) { return new FakeElement(tagName); }
}

class FakeWindow {
  constructor(desktop = true) {
    this.innerWidth = desktop ? 1440 : 390;
    this.navigator = { userAgentData: { mobile: !desktop } };
    this.listeners = new Map();
  }
  matchMedia(query) {
    if (this.innerWidth < 900) return { matches: query.includes('coarse') };
    return { matches: query.includes('fine') || query.includes('hover: hover') };
  }
  addEventListener(name, handler) { this.listeners.set(name, handler); }
  removeEventListener(name) { this.listeners.delete(name); }
  setTimeout(callback) { callback(); return 1; }
}

const tests = [];
const test = (name, fn) => tests.push([name, fn]);

test('service row remains exactly 40 columns and exposes connection state', () => {
  const line = buildServiceLine({ service: '3615 AIRINTER', identity: 'IT199', clock: '14:42', state: 'C' });
  assert.equal(line.length, runtime.WIDTH);
  assert.match(line, /^3615 AIRINTER/);
  assert.match(line, /IT199/);
  assert.match(line, /14:42 C$/);
});

test('boot sequence is made only of valid 40x25 snapshots', () => {
  const frames = createBootSequence({ product: 'PROMETHEE', identity: 'IT199' });
  assert.equal(frames.length, 3);
  for (const frame of frames) {
    assert.equal(frame.width, 40);
    assert.equal(frame.height, 25);
    assert.equal(frame.cells.length, 25);
    assert.equal(frame.cells[0].length, 40);
  }
  const text = frames.map((frame) => frame.cells.flat().map((cell) => cell.character).join('')).join('\n');
  assert.match(text, /VIDEOTEX/);
  assert.match(text, /CONNEXION ETABLIE/);
  assert.match(text, /PROMETHEE/);
  assert.match(text, /IT199/);
});

test('system pages can be installed without touching business pages', () => {
  const session = new runtime.MinitelSession({ homePageId: 'home' })
    .register(new runtime.MinitelPage('home', { onRender: () => {} }));
  registerSystemPages(session);
  assert.equal(session.pages.has('home'), true);
  assert.equal(session.pages.has(SYSTEM_PAGES.GUIDE), true);
  assert.equal(session.pages.has(SYSTEM_PAGES.EXIT), true);
});

test('shell always mounts an external emergency escape outside terminal', () => {
  const host = new FakeElement('main');
  const shell = new MinitelShell({
    document: new FakeDocument(),
    window: new FakeWindow(true),
    host
  }).mount();

  assert.ok(shell.escapeButton);
  assert.ok(shell.terminalNode);
  assert.notEqual(shell.escapeButton, shell.terminalNode);
  assert.equal(shell.escapeButton.textContent, 'Quitter le mode Minitel');
  assert.equal(shell.shellNode.children[0].children.includes(shell.escapeButton), true);
  assert.equal(shell.shellNode.children[1], shell.terminalNode);
});

test('runtime errors force the emergency escape to remain visible', () => {
  const shell = new MinitelShell({
    document: new FakeDocument(),
    window: new FakeWindow(true),
    host: new FakeElement('main')
  }).mount();

  shell.escapeButton.hidden = true;
  shell.onRuntimeError();
  assert.equal(shell.escapeButton.hidden, false);
  assert.equal(shell.escapeButton.classList.contains('is-emergency'), true);
});

test('Ctrl+Alt+M exits even if terminal navigation is unavailable', () => {
  let reason = null;
  const shell = new MinitelShell({
    document: new FakeDocument(),
    window: new FakeWindow(true),
    host: new FakeElement('main'),
    onExit: (value) => { reason = value; }
  }).mount();

  let prevented = false;
  shell.onGlobalKeyDown({
    key: 'm',
    ctrlKey: true,
    altKey: true,
    preventDefault: () => { prevented = true; },
    stopPropagation() {}
  });

  assert.equal(reason, EXIT_REASONS.HOTKEY);
  assert.equal(prevented, true);
  assert.equal(shell.active, false);
});

test('mobile fallback is modern/touch-safe and can leave Minitel', () => {
  let reason = null;
  const shell = new MinitelShell({
    document: new FakeDocument(),
    window: new FakeWindow(false),
    host: new FakeElement('main'),
    onExit: (value) => { reason = value; }
  });
  const result = shell.capability();
  assert.equal(result.allowed, false);
  shell.showFallback(result.reason);
  const button = shell.fallbackNode.children[0].children[2];
  button.click();
  assert.equal(reason, EXIT_REASONS.MOBILE);
});

test('start wires GUIDE and CONNEXION/FIN as global system commands', async () => {
  let exitReason = null;
  const session = new runtime.MinitelSession({ homePageId: 'home' })
    .register(new runtime.MinitelPage('home', { onRender: (_ctx, screen) => screen.write(1, 1, 'HOME') }));

  const renderer = { rendered: [], async render(snapshot) { this.rendered.push(snapshot); }, cancel() {} };
  const keyboard = { attachCalled: false, attach() { this.attachCalled = true; }, detach() {} };
  const shell = new MinitelShell({
    document: new FakeDocument(),
    window: new FakeWindow(true),
    host: new FakeElement('main'),
    session,
    renderer,
    keyboard,
    bootFrameDelay: 0,
    onExit: (value) => { exitReason = value; }
  }).mount();

  const started = await shell.start();
  assert.equal(started.started, true);
  assert.equal(keyboard.attachCalled, true);
  assert.ok(renderer.rendered.length >= 4);

  const guide = await keyboard.commandInterceptor({ action: runtime.ACTIONS.GUIDE });
  assert.equal(session.currentPageId, SYSTEM_PAGES.GUIDE);
  assert.equal(guide.handled, true);

  session.summary();
  const exit = await keyboard.commandInterceptor({ action: runtime.ACTIONS.CONNECT_END });
  assert.equal(session.currentPageId, SYSTEM_PAGES.EXIT);
  assert.equal(exit.handled, true);

  const outcome = session.dispatch('1');
  session.dispatch('Enter');
  keyboard.afterDispatch(outcome);
  assert.equal(exitReason, EXIT_REASONS.CONNECT_END);
});


test('M6 boot sequence contains real alphamosaic Air Inter cells', () => {
  const frames = createBootSequence({ product: 'PROMETHEE', identity: 'IT199' });
  const mosaicCells = frames.flatMap(frame => frame.cells.flat()).filter(cell => cell.attrs.mosaic);
  assert.ok(mosaicCells.length > 0);
  assert.ok(mosaicCells.some(cell => cell.attrs.foreground === 'blue'));
});

test('M6 settings page changes speed and monochrome preference through terminal input', () => {
  const session = new runtime.MinitelSession({
    homePageId: 'home',
    speed: 'fast',
    context: {}
  }).register(new runtime.MinitelPage('home', { onRender: () => {} }));
  registerSystemPages(session, { speed: 'fast', displayMode: 'color' });
  session.go(SYSTEM_PAGES.SETTINGS, { recordHistory: false });

  session.dispatch('1');
  session.dispatch('Enter');
  assert.equal(session.context.__minitelPreferenceChange.speed, 'authentic');
  assert.equal(session.context.__minitelPreferences.speed, 'authentic');

  session.dispatch('5');
  session.dispatch('Enter');
  assert.equal(session.context.__minitelPreferenceChange.displayMode, 'monochrome');
  assert.equal(session.context.__minitelPreferences.displayMode, 'monochrome');
});

test('M6 shell applies renderer preferences and reports them to the client', () => {
  let persisted = null;
  const renderer = {
    speed: null,
    mode: null,
    setSpeed(value) { this.speed = value; return true; },
    setDisplayMode(value) { this.mode = value; return true; }
  };
  const session = new runtime.MinitelSession({ homePageId: 'home' })
    .register(new runtime.MinitelPage('home', { onRender: () => {} }));
  const shell = new MinitelShell({
    document: new FakeDocument(),
    window: new FakeWindow(true),
    host: new FakeElement('main'),
    session,
    renderer,
    speed: 'fast',
    displayMode: 'color',
    onPreferencesChange: value => { persisted = value; }
  });

  const result = shell.applyPreferences({ speed: 'authentic', displayMode: 'monochrome' });
  assert.equal(result.speed, 'authentic');
  assert.equal(result.displayMode, 'monochrome');
  assert.equal(renderer.speed, 'authentic');
  assert.equal(renderer.mode, 'monochrome');
  assert.deepEqual(persisted, { speed: 'authentic', displayMode: 'monochrome' });
});


test('M6 GUIDE option 0 opens terminal settings without a mouse', () => {
  const session = new runtime.MinitelSession({ homePageId: 'home' })
    .register(new runtime.MinitelPage('home', { onRender: () => {} }));
  registerSystemPages(session, { speed: 'fast', displayMode: 'color' });
  session.go(SYSTEM_PAGES.GUIDE, { recordHistory: false });
  session.dispatch('0');
  session.dispatch('Enter');
  assert.equal(session.currentPageId, SYSTEM_PAGES.SETTINGS);
});

test('M6 shell owns cursor placement on system pages', () => {
  const positions = [];
  const renderer = {
    showCursor(row, column, visible) { positions.push([row, column, visible]); }
  };
  const session = new runtime.MinitelSession({ homePageId: 'home' })
    .register(new runtime.MinitelPage('home', { onRender: () => {} }));
  registerSystemPages(session);
  const shell = new MinitelShell({
    document: new FakeDocument(),
    window: new FakeWindow(true),
    host: new FakeElement('main'),
    session,
    renderer
  });

  session.go(SYSTEM_PAGES.SETTINGS, { recordHistory: false });
  shell.syncSystemCursor();
  assert.deepEqual(positions.at(-1), [20, 10, true]);

  session.input.append('5');
  shell.syncSystemCursor();
  assert.deepEqual(positions.at(-1), [20, 11, true]);

  session.go(SYSTEM_PAGES.EXIT, { recordHistory: false });
  shell.syncSystemCursor();
  assert.deepEqual(positions.at(-1), [15, 19, true]);
});

(async () => {
  let failures = 0;
  for (const [name, fn] of tests) {
    try {
      await fn();
      console.log('✓', name);
    } catch (error) {
      failures += 1;
      console.error('✗', name);
      console.error(error);
    }
  }
  if (failures) process.exitCode = 1;
  else console.log('\n' + tests.length + ' Minitel M1 shell tests passed.');
})();
