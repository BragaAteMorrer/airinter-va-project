'use strict';

const assert = require('node:assert/strict');

class FakeClassList {
  constructor() { this.values = new Set(); }
  add(...values) { values.forEach(value => this.values.add(value)); }
  remove(...values) { values.forEach(value => this.values.delete(value)); }
}

class FakeElement {
  constructor(tagName = 'div') {
    this.tagName = tagName.toUpperCase();
    this.children = [];
    this.classList = new FakeClassList();
    this.dataset = {};
    this.attributes = new Map();
    this._textContent = '';
    this._innerHTML = '';
    this.listeners = new Map();
    this.focused = false;
  }
  appendChild(child) { this.children.push(child); return child; }
  addEventListener(name, handler, options) { this.listeners.set(name, { handler, options }); }
  removeEventListener(name) { this.listeners.delete(name); }
  focus() { this.focused = true; }
  setAttribute(name, value) { this.attributes.set(name, String(value)); }
  removeAttribute(name) { this.attributes.delete(name); }
  toggleAttribute(name, force) {
    if (force) this.attributes.set(name, '');
    else this.attributes.delete(name);
  }
  set innerHTML(value) {
    this._innerHTML = String(value);
    if (value === '') this.children = [];
  }
  get innerHTML() { return this._innerHTML; }
  set textContent(value) {
    this._textContent = String(value);
    this.children = [];
  }
  get textContent() { return this._textContent; }
}

class FakeDocument {
  createElement(tagName) { return new FakeElement(tagName); }
}

global.document = new FakeDocument();
global.window = {
  matchMedia: () => ({ matches: false }),
  setTimeout(callback) { callback(); return 1; }
};

const runtime = require('../shared/minitel/runtime.js');
const { MinitelDomRenderer } = require('../shared/minitel/renderer.js');

const host = new FakeElement('main');
const renderer = new MinitelDomRenderer(host, {
  speed: 'authentic',
  displayMode: 'monochrome'
});

assert.equal(host.dataset.displayMode, 'monochrome');
assert.equal(renderer.screenNode.dataset.displayMode, 'monochrome');
assert.equal(renderer.speed, 'authentic');
assert.equal(host.attributes.get('tabindex'), '0');
assert.equal(host.focused, true);
assert.equal(host.listeners.has('pointerdown'), true);

const screen = new runtime.MinitelScreenBuffer();
screen.mosaic(3, 4, 21, {
  foreground: 'blue',
  separatedMosaic: true
});

renderer.applyCell(3, 4, screen.snapshot().cells[3][4]);

const cell = renderer.cellNode(3, 4);
assert.equal(cell.dataset.mosaicMask, '21');
assert.equal(cell.children.length, 1);
assert.equal(cell.children[0].children.length, 6);
assert.equal(cell.children[0].children.filter(bit => bit.attributes.has('data-on')).length, 3);
assert.equal(cell.attributes.has('data-mosaic'), true);
assert.equal(cell.attributes.has('data-separated-mosaic'), true);

assert.equal(renderer.setDisplayMode('color'), true);
assert.equal(host.dataset.displayMode, 'color');
assert.equal(renderer.setSpeed('instant'), true);
assert.equal(renderer.speed, 'instant');
assert.equal(renderer.setDisplayMode('invalid'), false);
assert.equal(renderer.setSpeed('warp'), false);

console.log('✓ M6 renderer materializes 2x3 mosaics and terminal display preferences');
