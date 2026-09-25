'use strict';

const assert = require('node:assert/strict');
const {
  WIDTH, HEIGHT, ACTIONS, DISPLAY_MODES, MinitelScreenBuffer, MinitelInputBuffer,
  MinitelPage, MinitelSession, mapKeyboardEvent, normalizeMosaicMask, mosaicBits,
  transmissionOperations, transmissionDelay, minitelCapability
} = require('./runtime.js');

const tests = [];
const test = (name, fn) => tests.push([name, fn]);

test('screen is strictly 40x25', () => {
  const screen = new MinitelScreenBuffer();
  assert.equal(screen.width, 40);
  assert.equal(screen.height, 25);
  assert.equal(screen.text().split('\n').length, 25);
  assert.throws(() => new MinitelScreenBuffer(80, 25), /40x25/);
});

test('writes clip at 40 columns by default', () => {
  const screen = new MinitelScreenBuffer();
  screen.write(1, 38, 'ABCD');
  assert.equal(screen.line(1).slice(38), 'AB');
  assert.equal(screen.line(2).trim(), '');
});

test('writes may wrap when explicitly requested', () => {
  const screen = new MinitelScreenBuffer();
  screen.write(1, 39, 'ABC', {}, { wrap: true });
  assert.equal(screen.line(1)[39], 'A');
  assert.equal(screen.line(2).slice(0, 2), 'BC');
});

test('input correction and cancellation are deterministic', () => {
  const input = new MinitelInputBuffer(3);
  input.append('1'); input.append('2'); input.append('3');
  assert.equal(input.append('4'), false);
  assert.equal(input.value, '123');
  input.correct();
  assert.equal(input.value, '12');
  input.cancel();
  assert.equal(input.value, '');
});

test('keyboard maps historical commands', () => {
  assert.equal(mapKeyboardEvent('Enter').action, ACTIONS.SEND);
  assert.equal(mapKeyboardEvent('Backspace').action, ACTIONS.CORRECT);
  assert.equal(mapKeyboardEvent('Escape').action, ACTIONS.CANCEL);
  assert.equal(mapKeyboardEvent('Home').action, ACTIONS.SUMMARY);
  assert.equal(mapKeyboardEvent('F1').action, ACTIONS.GUIDE);
  assert.equal(mapKeyboardEvent('F2').action, ACTIONS.REPEAT);
  assert.equal(mapKeyboardEvent('PageUp').action, ACTIONS.BACK);
  assert.equal(mapKeyboardEvent('PageDown').action, ACTIONS.NEXT);
  assert.equal(mapKeyboardEvent('F10').action, ACTIONS.CONNECT_END);
  assert.deepEqual(mapKeyboardEvent({ key: 'F2', shiftKey: true }), { action: ACTIONS.REPEAT, key: 'F2', refresh: true });
});

test('session supports numeric menus and history without a mouse', () => {
  const home = new MinitelPage('home', {
    onRender: (_ctx, screen, session) => {
      screen.write(0, 0, '3615 AIRINTER');
      screen.write(4, 2, '1 VOLS');
      screen.write(20, 2, 'CHOIX: ' + session.input.value);
    },
    send: (value) => value === '1' ? 'flights' : null
  });
  const flights = new MinitelPage('flights', {
    onRender: (_ctx, screen) => screen.write(0, 0, 'VOLS AIR INTER')
  });
  const session = new MinitelSession({ homePageId: 'home' });
  session.register(home).register(flights);
  session.start();
  const typed = session.dispatch('1');
  assert.match(typed.snapshot.cells[20].map(c => c.character).join(''), /CHOIX: 1/);
  const entered = session.dispatch('Enter');
  assert.equal(session.currentPageId, 'flights');
  assert.match(entered.snapshot.cells[0].map(c => c.character).join(''), /^VOLS AIR INTER/);
  session.dispatch('PageUp');
  assert.equal(session.currentPageId, 'home');
});

test('SOMMAIRE always returns home and clears history', () => {
  const session = new MinitelSession({ homePageId: 'home' })
    .register(new MinitelPage('home', { onRender: () => {} }))
    .register(new MinitelPage('other', { onRender: () => {} }));
  session.start();
  session.go('other');
  session.dispatch('Home');
  assert.equal(session.currentPageId, 'home');
  assert.equal(session.history.length, 0);
});

test('REPETITION replays the same buffer and Shift+F2 requests refresh', () => {
  const session = new MinitelSession({ homePageId: 'home' })
    .register(new MinitelPage('home', { onRender: (_ctx, screen) => screen.write(1, 1, 'AIR INTER') }));
  session.start();
  const repeat = session.dispatch('F2');
  assert.equal(repeat.replay, true);
  assert.equal(repeat.refresh, false);
  const refresh = session.dispatch({ key: 'F2', shiftKey: true });
  assert.equal(refresh.replay, true);
  assert.equal(refresh.refresh, true);
});

test('transmission order is row-major, left to right then top to bottom', () => {
  const screen = new MinitelScreenBuffer();
  screen.write(0, 0, 'AB');
  screen.write(1, 0, 'C');
  const ops = transmissionOperations(screen.snapshot());
  assert.deepEqual(ops.slice(0, 3).map(op => [op.row, op.column]), [[0,0],[0,1],[0,2]]);
  assert.equal(ops.length, WIDTH * HEIGHT);
});

test('unchanged snapshots do not retransmit unchanged cells', () => {
  const screen = new MinitelScreenBuffer();
  screen.write(0, 0, 'AIR INTER', { foreground: 'yellow' });
  const before = screen.snapshot();
  const after = screen.clone().snapshot();
  assert.equal(transmissionOperations(after, before).length, 0);
});

test('speed profiles remain bounded', () => {
  assert.equal(transmissionDelay('instant'), 0);
  assert.ok(transmissionDelay('authentic') > transmissionDelay('fast'));
});

test('mobile/coarse environments are rejected without changing preferences', () => {
  const desktop = {
    innerWidth: 1440,
    matchMedia: (query) => ({ matches: query.includes('fine') || query.includes('hover: hover') }),
    navigator: { userAgentData: { mobile: false } }
  };
  assert.equal(minitelCapability(desktop).allowed, true);

  const mobile = {
    innerWidth: 390,
    matchMedia: (query) => ({ matches: query.includes('coarse') }),
    navigator: { userAgentData: { mobile: true } }
  };
  const result = minitelCapability(mobile);
  assert.equal(result.allowed, false);
  assert.equal(result.reason, 'screen');
});


test('M6 alphamosaic cells expose six deterministic 2x3 subcells', () => {
  const screen = new MinitelScreenBuffer();
  screen.mosaic(3, 4, 63, { foreground: 'blue' });
  const cell = screen.snapshot().cells[3][4];
  assert.equal(cell.attrs.mosaic, true);
  assert.equal(cell.attrs.mosaicMask, 63);
  assert.deepEqual(mosaicBits(cell.attrs.mosaicMask), [true, true, true, true, true, true]);
  assert.equal(normalizeMosaicMask(99), 63);
  assert.equal(normalizeMosaicMask(-5), 0);
});

test('M6 writeMosaic preserves masks and transmission detects pattern changes', () => {
  const before = new MinitelScreenBuffer();
  before.writeMosaic(5, 2, [1, 3, 7], { foreground: 'cyan' });
  const after = before.clone();
  after.mosaic(5, 3, 63, { foreground: 'cyan' });
  const ops = transmissionOperations(after.snapshot(), before.snapshot());
  assert.equal(ops.length, 1);
  assert.deepEqual([ops[0].row, ops[0].column, ops[0].cell.attrs.mosaicMask], [5, 3, 63]);
});

test('M6 exposes color and monochrome display modes without altering logical colors', () => {
  assert.equal(DISPLAY_MODES.color, 'color');
  assert.equal(DISPLAY_MODES.monochrome, 'monochrome');
  const screen = new MinitelScreenBuffer();
  screen.write(1, 1, 'A', { foreground: 'yellow' });
  assert.equal(screen.snapshot().cells[1][1].attrs.foreground, 'yellow');
});


test('logical input may exceed 40 display columns for M5/M6 forms', () => {
  const input = new MinitelInputBuffer(160);
  for (let i = 0; i < 160; i += 1) assert.equal(input.append('A'), true);
  assert.equal(input.value.length, 160);
  assert.equal(input.append('B'), false);
});

test('fresh Videotex transmission may skip untouched blank cells after clear-screen', () => {
  const screen = new MinitelScreenBuffer();
  screen.write(2, 3, 'AIR');
  const ops = transmissionOperations(screen.snapshot(), null, { skipDefaultBlank: true });
  assert.deepEqual(ops.map(op => [op.row, op.column]), [[2,3],[2,4],[2,5]]);
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
  else console.log(`\n${tests.length} Minitel M0 tests passed.`);
})();
