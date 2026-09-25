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

  function telemetrySummary(status) {
    const latest = status?.latest || status?.Latest || {};
    const flight = status?.flight || status?.Flight || {};
    const read = (camel, pascal) => snapshotValue(latest, camel, pascal);
    const number = value => value == null || Number.isNaN(Number(value)) ? null : Number(value);

    return Object.freeze({
      phase: flight.phase || flight.Phase || 'STANDBY',
      recording: Boolean(flight.recording ?? flight.Recording),
      pirepId: flight.pirepId || flight.PirepId || null,
      operationId: flight.operationId || flight.OperationId || null,
      altitude: number(read('altitudeMslFeet', 'AltitudeMslFeet') ?? read('altitude', 'Altitude')),
      agl: number(read('altitudeAglFeet', 'AltitudeAglFeet') ?? read('agl', 'Agl')),
      ias: number(read('indicatedAirspeedKnots', 'IndicatedAirspeedKnots') ?? read('ias', 'Ias')),
      gs: number(read('groundSpeedKnots', 'GroundSpeedKnots') ?? read('gs', 'Gs')),
      mach: number(read('mach', 'Mach')),
      heading: number(read('headingDegrees', 'HeadingDegrees') ?? read('heading', 'Heading')),
      verticalSpeed: number(read('verticalSpeedFeetPerMinute', 'VerticalSpeedFeetPerMinute') ?? read('vs', 'Vs')),
      fuel: number(read('fuelWeight', 'FuelWeight') ?? read('fuel', 'Fuel')),
      distance: number(flight.distance ?? flight.Distance) ?? 0,
      airborneMinutes: Math.round((number(flight.airborneSeconds ?? flight.AirborneSeconds) ?? 0) / 60),
      landingRate: number(flight.landingRate ?? flight.LandingRate),
      pending: Number(status?.pending ?? status?.Pending ?? 0),
      syncState: String(status?.syncState ?? status?.SyncState ?? 'IDLE').toUpperCase(),
      warning: status?.warning ?? status?.Warning ?? null,
      onGround: read('onGround', 'OnGround'),
      parkingBrake: read('parkingBrake', 'ParkingBrake'),
      paused: read('paused', 'Paused'),
      simRate: number(read('simulationRate', 'SimulationRate'))
    });
  }

  function datalinkSnapshot(snapshot) {
    const read = (object, camel, pascal = camel) => object?.[camel] ?? object?.[pascal] ?? null;
    const messages = read(snapshot, 'messages', 'Messages') || [];
    return Object.freeze({
      operationId: read(snapshot, 'operationId', 'OperationId'),
      messages: messages.map(message => ({
        id: read(message, 'id', 'Id'),
        direction: String(read(message, 'direction', 'Direction') || ''),
        category: String(read(message, 'category', 'Category') || 'OPS'),
        priority: String(read(message, 'priority', 'Priority') || 'ROUTINE'),
        body: String(read(message, 'body', 'Body') || ''),
        requiresAck: Boolean(read(message, 'requiresAck', 'RequiresAck')),
        status: String(read(message, 'status', 'Status') || 'SENT'),
        sender: read(message, 'senderLabel', 'SenderLabel') || '',
        createdAt: read(message, 'createdAt', 'CreatedAt'),
        readAt: read(message, 'readAt', 'ReadAt'),
        acknowledgedAt: read(message, 'acknowledgedAt', 'AcknowledgedAt'),
        localPending: Boolean(read(message, 'localPending', 'LocalPending'))
      })),
      pendingOutbound: Number(read(snapshot, 'pendingOutbound', 'PendingOutbound') || 0),
      pendingReads: Number(read(snapshot, 'pendingReads', 'PendingReads') || 0),
      pendingAcks: Number(read(snapshot, 'pendingAcks', 'PendingAcks') || 0),
      unreadCount: Number(read(snapshot, 'unreadCount', 'UnreadCount') || 0),
      pendingRequiredAcks: Number(read(snapshot, 'pendingRequiredAcks', 'PendingRequiredAcks') || 0),
      syncState: String(read(snapshot, 'syncState', 'SyncState') || 'LOCAL').toUpperCase(),
      error: read(snapshot, 'error', 'Error')
    });
  }

  function reviewSummary(review) {
    const read = (camel, pascal = camel) => review?.[camel] ?? review?.[pascal] ?? null;
    return Object.freeze({
      pirepId: read('pirepId', 'PirepId'),
      phase: String(read('phase', 'Phase') || '—'),
      readyToFile: Boolean(read('readyToFile', 'ReadyToFile')),
      distance: Number(read('distance', 'Distance') || 0),
      airborneMinutes: Number(read('airborneMinutes', 'AirborneMinutes') || 0),
      blockMinutes: Number(read('blockMinutes', 'BlockMinutes') || 0),
      fuelUsed: Number(read('fuelUsed', 'FuelUsed') || 0),
      landingRate: read('landingRate', 'LandingRate'),
      approach1000: read('approach1000Status', 'Approach1000Status') || 'NON OBSERVE',
      approach500: read('approach500Status', 'Approach500Status') || 'NON OBSERVE',
      bounceCount: Number(read('bounceCount', 'BounceCount') || 0),
      goAroundCount: Number(read('goAroundCount', 'GoAroundCount') || 0),
      maxBank: read('maxBankDegrees', 'MaxBankDegrees'),
      fuelAdded: Number(read('fuelAdded', 'FuelAdded') || 0),
      maxSimulationRate: read('maxSimulationRate', 'MaxSimulationRate'),
      issues: read('issues', 'Issues') || [],
      observations: read('observations', 'Observations') || [],
      timeline: read('timeline', 'Timeline') || []
    });
  }

  function timeLabel(value) {
    if (!value) return '--:--';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '--:--';
    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', hour12: false });
  }

  return Object.freeze({
    normalise,
    fit,
    parseFlightSearch,
    snapshotValue,
    preflightState,
    operationId,
    operationFlight,
    operationChecks,
    telemetrySummary,
    datalinkSnapshot,
    reviewSummary,
    timeLabel
  });
});
