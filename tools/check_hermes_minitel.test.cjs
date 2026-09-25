'use strict';

const assert = require('node:assert/strict');
const {
  normalise,
  fit,
  parseFlightSearch,
  preflightState,
  operationId,
  operationFlight,
  operationChecks,
  telemetrySummary,
  datalinkSnapshot,
  reviewSummary,
  timeLabel
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


test('live telemetry normalizes simulator snapshots and recorder state', () => {
  const summary = telemetrySummary({
    latest: {
      altitudeMslFeet: 35020,
      altitudeAglFeet: 34100,
      indicatedAirspeedKnots: 281,
      groundSpeedKnots: 462,
      headingDegrees: 154,
      verticalSpeedFeetPerMinute: -320,
      fuelWeight: 11840,
      onGround: false,
      parkingBrake: false,
      simulationRate: 1
    },
    flight: {
      phase: 'CRUISE',
      recording: true,
      pirepId: '8473',
      operationId: 'op_749',
      distance: 412.35,
      airborneSeconds: 3720
    },
    pending: 2,
    syncState: 'RETRYING'
  });

  assert.equal(summary.phase, 'CRUISE');
  assert.equal(summary.recording, true);
  assert.equal(summary.altitude, 35020);
  assert.equal(summary.ias, 281);
  assert.equal(summary.gs, 462);
  assert.equal(summary.distance, 412.35);
  assert.equal(summary.airborneMinutes, 62);
  assert.equal(summary.pending, 2);
  assert.equal(summary.syncState, 'RETRYING');
});

test('datalink adapter preserves read and acknowledgement semantics', () => {
  const snapshot = datalinkSnapshot({
    operationId: 'op_749',
    unreadCount: 1,
    pendingRequiredAcks: 1,
    syncState: 'SYNCED',
    messages: [{
      id: 'msg-1',
      direction: 'OPS_TO_COCKPIT',
      category: 'DISPATCH',
      priority: 'IMPORTANT',
      body: 'Prévoir piste 16L',
      requiresAck: true,
      status: 'DELIVERED',
      senderLabel: 'DISPATCH ORY',
      createdAt: '2026-09-25T13:42:00Z'
    }]
  });

  assert.equal(snapshot.operationId, 'op_749');
  assert.equal(snapshot.unreadCount, 1);
  assert.equal(snapshot.pendingRequiredAcks, 1);
  assert.equal(snapshot.messages[0].requiresAck, true);
  assert.equal(snapshot.messages[0].direction, 'OPS_TO_COCKPIT');
  assert.equal(snapshot.messages[0].body, 'Prévoir piste 16L');
});

test('flight review adapter exposes filing gate and FDM details', () => {
  const review = reviewSummary({
    pirepId: '8473',
    phase: 'IN',
    readyToFile: true,
    distance: 621.8,
    airborneMinutes: 92,
    blockMinutes: 111,
    fuelUsed: 7120,
    landingRate: -182,
    approach1000Status: 'STABLE',
    approach500Status: 'STABLE',
    bounceCount: 0,
    goAroundCount: 1,
    maxBankDegrees: 31.2,
    fuelAdded: 0,
    maxSimulationRate: 1,
    observations: [{ code: 'GO_AROUND' }],
    issues: []
  });

  assert.equal(review.readyToFile, true);
  assert.equal(review.phase, 'IN');
  assert.equal(review.landingRate, -182);
  assert.equal(review.goAroundCount, 1);
  assert.equal(review.observations.length, 1);
  assert.equal(review.issues.length, 0);
});

test('time label is safe for terminal rendering', () => {
  assert.match(timeLabel('2026-09-25T13:42:00Z'), /^\d{2}:\d{2}$/);
  assert.equal(timeLabel(null), '--:--');
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
  else console.log('\n' + tests.length + ' Hermès M4-M5 Minitel tests passed.');
})();
