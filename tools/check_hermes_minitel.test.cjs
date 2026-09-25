'use strict';

const assert = require('node:assert/strict');
const {
  normalise,
  fit,
  parseFlightSearch,
  preflightState,
  operationId,
  operationFlight,
  operationChecks
} = require('../acars/wwwroot/hermes-minitel-core.js');

const tests = [];
const test = (name, fn) => tests.push([name, fn]);

test('normalise keeps the 40-column terminal ASCII-safe', () => {
  assert.equal(normalise('Préparation Équipage'), 'PREPARATION EQUIPAGE');
  assert.equal(fit('Hermès', 8).length, 8);
});

test('flight search accepts Air Inter number or ICAO route', () => {
  assert.deepEqual(parseFlightSearch('ITF749'), { flight_number: '749' });
  assert.deepEqual(parseFlightSearch('749'), { flight_number: '749' });
  assert.deepEqual(parseFlightSearch('LFPO>LIRF'), { dep_icao: 'LFPO', arr_icao: 'LIRF' });
  assert.deepEqual(parseFlightSearch('LFPO LIRF'), { dep_icao: 'LFPO', arr_icao: 'LIRF' });
});

test('operation identity accepts public and legacy DTO shapes', () => {
  assert.equal(operationId({ operation_id: 'op_42' }), 'op_42');
  assert.equal(operationId({ operationId: 'op_43' }), 'op_43');
  assert.equal(operationId({ bid_id: 44 }), 44);
});

test('operation flight normalizes the preparation DTO', () => {
  assert.deepEqual(operationFlight({
    flight: {
      id: 7,
      ident: 'ITF749',
      departure: 'LFPO',
      arrival: 'LIRF',
      alternate: 'LIRA',
      route: 'DCT',
      level: 350
    }
  }), {
    id: 7,
    ident: 'ITF749',
    departure: 'LFPO',
    arrival: 'LIRF',
    alternate: 'LIRA',
    route: 'DCT',
    level: 350
  });
});

test('preflight requires server READY, simulator data and safe ground state', () => {
  const ready = preflightState({
    latest: {
      onGround: true,
      parkingBrake: true,
      enginesRunning: [false, false]
    },
    flight: { recording: false }
  }, { can_start: true });

  assert.equal(ready.simulator, true);
  assert.equal(ready.serverReady, true);
  assert.equal(ready.safe, true);
  assert.equal(ready.ready, true);

  const unsafe = preflightState({
    latest: {
      onGround: false,
      parkingBrake: false,
      enginesRunning: [true, true]
    }
  }, { can_start: true });
  assert.equal(unsafe.ready, false);
});

test('preparation checks use the same server readiness fields as Hermès modern UI', () => {
  const checks = operationChecks({
    operation_id: 'op_1',
    aircraft: { id: 8 },
    simbrief: { available: true },
    pirep_id: 12
  }, {
    server_checks: { aircraft: true, ofp: true, pirep: true },
    can_start: true
  }, {
    latest: { onGround: true, parkingBrake: true, enginesRunning: [] }
  });

  assert.deepEqual(checks.map(check => [check[0], check[1]]), [
    ['OPERATION', true],
    ['APPAREIL', true],
    ['OFP', true],
    ['PIREP', true],
    ['SIM', true]
  ]);
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
  else console.log('\n' + tests.length + ' Hermès M4 Minitel tests passed.');
})();
