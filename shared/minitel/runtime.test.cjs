'use strict';

const assert = require('node:assert/strict');
const {
  WIDTH, HEIGHT, ACTIONS, MinitelScreenBuffer, MinitelInputBuffer,
  MinitelPage, MinitelSession, mapKeyboardEvent,
  transmissionOperations, transmissionDelay
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
    onRender: (_ctx, screen) => {
      screen.write(0, 0, '3615 AIRINTER');
      screen.write(4, 2, '1 VOLS');
      screen.write(20, 2, 'CHOIX:');
    },
    send: (value) => value === '1' ? 'flights' : null
  });
  const flights = new MinitelPage('flights', {
    onRender: (_ctx, screen) => screen.write(0, 0, 'VOLS AIR INTER')
  });
  const session = new MinitelSession({ homePageId: 'home' });
  session.register(home).register(flights);
  session.start();
  session.dispatch('1');
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

test('speed profiles remain bounded', () => {
  assert.equal(transmissionDelay('instant'), 0);
  assert.ok(transmissionDelay('authentic') > transmissionDelay('fast'));
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
