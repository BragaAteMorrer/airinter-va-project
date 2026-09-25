(function (root, factory) {
  const api = factory();
  if (typeof module === 'object' && module.exports) module.exports = api;
  if (root) root.HermesMinitelCore = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
  'use strict';

  const normalise = (value) => String(value ?? '')
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^\x20-\x7E]/g, ' ')
    .toUpperCase();

  const fit = (value, width) => normalise(value).slice(0, width).padEnd(width, ' ');

  function parseFlightSearch(value) {
    const raw = normalise(value).trim();
    const route = raw.match(/^([A-Z0-9]{3,8})\s*(?:>|-|\s)\s*([A-Z0-9]{3,8})$/);
    if (route) return { dep_icao: route[1], arr_icao: route[2] };
    const flight = raw.replace(/^ITF[ -]?/, '');
    return flight ? { flight_number: flight } : {};
  }

  function snapshotValue(snapshot, camel, pascal = camel) {
    return snapshot?.[camel] ?? snapshot?.[pascal] ?? null;
  }

  function preflightState(status, dispatch) {
    const latest = status?.latest || status?.Latest || {};
    const flight = status?.flight || status?.Flight || null;
    const onGround = snapshotValue(latest, 'onGround', 'OnGround');
    const parkingBrake = snapshotValue(latest, 'parkingBrake', 'ParkingBrake');
    const engines = snapshotValue(latest, 'enginesRunning', 'EnginesRunning');
    const enginesStopped = !Array.isArray(engines) || engines.length === 0 || !engines.some(Boolean);
    const simulator = Boolean(status?.latest || status?.Latest);
    const serverReady = Boolean(dispatch?.can_start ?? dispatch?.canStart ?? dispatch?.ready);
    const safe = onGround === true && parkingBrake !== false && enginesStopped;

    return Object.freeze({
      simulator,
      serverReady,
      safe,
      ready: simulator && serverReady && safe,
      recording: Boolean(flight?.recording ?? flight?.Recording),
      onGround,
      parkingBrake,
      enginesStopped
    });
  }

  function operationId(operation) {
    return operation?.operation_id || operation?.operationId || operation?.id || operation?.bid_id || operation?.bidId || null;
  }

  function operationFlight(operation) {
    const flight = operation?.flight || operation || {};
    return {
      id: flight.id,
      ident: flight.ident || flight.flight_number || flight.flightNumber || '',
      departure: flight.departure || flight.dpt_airport_id || flight.dptAirportId || '',
      arrival: flight.arrival || flight.arr_airport_id || flight.arrAirportId || '',
      alternate: flight.alternate || flight.alt_airport_id || flight.altAirportId || '',
      route: flight.route || '',
      level: flight.level || null
    };
  }

  function operationChecks(operation, dispatch, status) {
    const server = dispatch?.server_checks || dispatch?.serverChecks || {};
    const preflight = preflightState(status, dispatch);
    return [
      ['OPERATION', Boolean(operation), Boolean(operation) ? 'SELECTIONNEE' : 'A SELECTIONNER'],
      ['APPAREIL', Boolean(server.aircraft ?? operation?.aircraft?.id), Boolean(server.aircraft ?? operation?.aircraft?.id) ? 'PRET' : 'A CHOISIR'],
      ['OFP', Boolean(server.ofp ?? operation?.simbrief?.available), Boolean(server.ofp ?? operation?.simbrief?.available) ? 'PRET' : 'A PREPARER'],
      ['PIREP', Boolean(server.pirep ?? operation?.pirep_id ?? operation?.pirepId), Boolean(server.pirep ?? operation?.pirep_id ?? operation?.pirepId) ? 'PRET' : 'A PREPARER'],
      ['SIM', preflight.simulator, preflight.simulator ? 'CONNECTE' : 'EN ATTENTE']
    ];
  }

  return Object.freeze({
    normalise,
    fit,
    parseFlightSearch,
    snapshotValue,
    preflightState,
    operationId,
    operationFlight,
    operationChecks
  });
});
