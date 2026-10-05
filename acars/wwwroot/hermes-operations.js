// Hermès modularization phase 2 — operations domain.
// Classic script: preserves the existing shared runtime/global handler contract.

function simulatorCode() {
  const forced = localSettings.forcedSimulator;
  return ({ xplane: 'xplane', fs2004: 'fs2004', fsx: 'fsx', p3d: 'p3d', msfs: 'msfs2024' })[forced]
    || localSettings.preferredSimulator
    || 'msfs2024';
}

function normalizeFlight(raw) {
  const airline = raw.airline || {};
  return {
    id: raw.id,
    ident: raw.ident || [airline.icao || raw.airline_icao || '', raw.flight_number || ''].join(''),
    airline_id: raw.airline_id || airline.id,
    flight_number: raw.flight_number,
    departure: raw.departure || raw.dpt_airport_id || raw.dpt_airport?.icao,
    arrival: raw.arrival || raw.arr_airport_id || raw.arr_airport?.icao,
    alternate: raw.alternate || raw.alt_airport_id,
    route: raw.route,
    level: raw.level
  };
}

function displayFlightIdent(flight) {
  const number = String(flight.flight_number || '').trim().toUpperCase();
  if (/^[A-Z]{3}\d/.test(number)) return number;
  const ident = String(flight.ident || '').trim().toUpperCase().replace(/^([A-Z]{3})\1/, '$1');
  return ident || number || 'Vol Air Inter';
}

function operationCard(operation) {
  const flight = normalizeFlight(operation.flight || operation);
  const aircraft = operation.aircraft || {};
  const wrapper = document.createElement('div');
  wrapper.className = 'operation-entry';

  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'operation';
  if (selectedOperation && (selectedOperation.bid_id || selectedOperation.flight?.id) === (operation.bid_id || operation.flight?.id)) {
    button.classList.add('selected');
  }
  const title = document.createElement('strong');
  const route = document.createElement('span');
  const detail = document.createElement('small');
  setText(title, displayFlightIdent(flight));
  setText(route, `${flight.departure || '?'} → ${flight.arrival || '?'}`);
  setText(detail, aircraft.type_label || aircraft.subfleet || aircraft.name || 'Type d’appareil à sélectionner');
  const badge = document.createElement('em');
  badge.textContent = operation.bid_id ? 'RÉSERVÉ' : 'PROGRAMME';
  button.append(badge, title, route, detail);
  button.onclick = () => selectOperation({ ...operation, flight });
  wrapper.append(button);

  if (operation.bid_id || operation.operation_id) {
    const cancel = document.createElement('button');
    cancel.type = 'button';
    cancel.className = 'operation-cancel';
    cancel.textContent = 'Annuler la réservation';
    cancel.onclick = () => cancelReservation(operation, flight, cancel);
    wrapper.append(cancel);
  }

  return wrapper;
}

async function cancelReservation(operation, flight, button) {
  const operationId = operation.operation_id || operation.bid_id || operation.id;
  if (!operationId) return;

  const ident = displayFlightIdent(flight);
  if (!confirm(`Annuler la réservation ${ident} (${flight.departure || '?'} → ${flight.arrival || '?'}) ?\n\nCette action est possible uniquement tant qu’aucun PIREP n’a été créé.`)) return;

  button.disabled = true;
  button.textContent = 'Annulation…';
  try {
    await call('/api/cancel-operation?operation=' + encodeURIComponent(operationId));

    const selectedId = selectedOperation?.operation_id || selectedOperation?.bid_id || selectedOperation?.id;
    if (String(selectedId || '') === String(operationId)) {
      selectedOperation = null;
      selectedAircraft = null;
      selectedVariant = null;
      aircraftEligibility = null;
      pirepId = null;
      flightPlan = null;
      linkedSimBrief = null;
      serverDispatch = null;
      lastDatalinkSnapshot = null;
      clearOperationalWeather();
      $('#selectedOperation').hidden = true;
      $('#prefileForm').hidden = true;
      updateWorkflow();
      scheduleEfbContextSync();
    }

    showMessage('#flightMessage', `Réservation ${ident} annulée.`);
    await refreshOperations();
  } catch (error) {
    button.disabled = false;
    button.textContent = 'Annuler la réservation';
    showMessage('#flightMessage', friendlyError(error) || 'Impossible d’annuler cette réservation.', true);
  }
}

function setOperationsMode(mode) {
  const searchMode = mode === 'search';
  const searchPanel = $('#operationsSearchPanel');
  const bidsButton = $('#showBidsBtn');
  const searchButton = $('#showSearchBtn');
  if (searchPanel) searchPanel.hidden = !searchMode;
  if (bidsButton) {
    bidsButton.classList.toggle('active', !searchMode);
    bidsButton.setAttribute('aria-selected', String(!searchMode));
  }
  if (searchButton) {
    searchButton.classList.toggle('active', searchMode);
    searchButton.setAttribute('aria-selected', String(searchMode));
  }
  setText($('#operationsListTitle'), searchMode ? 'Résultats du programme' : 'Mes réservations');
  if (searchMode) {
    setText($('#operationsListMeta'), 'Renseignez un ou plusieurs critères puis lancez la recherche.');
    setTimeout(() => $('#flightNumberSearch')?.focus(), 0);
  }
}

function renderOperations(value, source = 'reservations') {
  const payload = unwrap(value);
  const operations = Array.isArray(payload) ? payload : (payload?.operations || payload?.data || []);
  const list = $('#flightList');
  list.replaceChildren();
  setText($('#operationsListMeta'), operations.length
    ? operations.length + ' opération' + (operations.length > 1 ? 's' : '') + (source === 'search' ? ' trouvée' + (operations.length > 1 ? 's' : '') : ' réservée' + (operations.length > 1 ? 's' : ''))
    : (source === 'search' ? 'Aucun vol ne correspond à cette recherche.' : 'Aucune réservation active.'));
  if (!operations.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    setText(empty, source === 'search'
      ? 'Aucun vol trouvé. Modifiez les critères de recherche.'
      : 'Aucune réservation active. Utilisez « Rechercher un vol » pour parcourir le programme.');
    list.append(empty);
    return;
  }
  operations.forEach(operation => list.append(operationCard(operation)));
}

async function refreshOperations() {
  setOperationsMode('bids');
  if (!connected) {
    renderOperations([], 'reservations');
    showMessage('#flightMessage', 'Connectez-vous d’abord à votre compte pilote.', true);
    return;
  }
  try {
    showMessage('#flightMessage', 'Chargement de vos réservations…');
    const simulator = simulatorCode();
    const payload = unwrap(await call('/api/v1/operations' + (simulator ? '?simulator=' + encodeURIComponent(simulator) : '')));
    const operations = Array.isArray(payload) ? payload : (payload?.operations || payload?.data || []);
    renderOperations(operations, 'reservations');

    const localFlight = lastStatus?.flight || lastStatus?.Flight || {};
    const recoveryOperationId = localFlight.operationId || localFlight.OperationId
      || lastStatus?.recovery?.operationId || lastStatus?.recovery?.OperationId || null;
    const recoveryOperation = !selectedOperation && recoveryOperationId
      ? operations.find(operation => String(operation.operation_id || operation.operationId || '') === String(recoveryOperationId))
      : null;
    const canAutoSelect = operations.length === 1
      && !selectedOperation
      && !lastStatus?.recoveryAvailable;

    if (recoveryOperation) {
      const flight = normalizeFlight(recoveryOperation.flight || recoveryOperation);
      showMessage('#flightMessage', 'Vol interrompu détecté : ' + displayFlightIdent(flight) + '. Restauration du Dispatch et de la route…');
      await selectOperation(recoveryOperation);
      showMessage('#flightMessage', 'Vol interrompu restauré. Reprenez-le depuis le Recovery Center.');
    } else if (canAutoSelect) {
      const flight = normalizeFlight(operations[0].flight || operations[0]);
      showMessage('#flightMessage', 'Réservation active détectée : ' + displayFlightIdent(flight) + '. Chargement automatique…');
      await selectOperation(operations[0]);
      showMessage('#flightMessage', 'Réservation active chargée automatiquement.');
    } else {
      showMessage('#flightMessage', '');
    }
  } catch (error) {
    showMessage('#flightMessage', friendlyError(error), true);
  }
}

function normalizedSearchValue(selector) {
  return $(selector).value.trim().toUpperCase();
}

async function searchFlights() {
  if (!connected) return showMessage('#flightMessage', 'Connectez-vous d’abord.', true);
  const params = new URLSearchParams();
  let number = normalizedSearchValue('#flightNumberSearch');
  const departure = normalizedSearchValue('#departureSearch');
  const arrival = normalizedSearchValue('#arrivalSearch');
  const aircraftType = normalizedSearchValue('#aircraftTypeSearch');
  // Keep the airline prefix (ITF, ACF, etc.) so Prométhée can scope the
  // search to the matching company. The API also accepts a bare number.
  if (number) params.set('flight_number', number);
  if (departure) params.set('dep_icao', departure);
  if (arrival) params.set('arr_icao', arrival);
  if (aircraftType) params.set('icao_type', aircraftType);
  try {
    showMessage('#flightMessage', 'Recherche dans le programme…');
    setOperationsMode('search');
    renderOperations(await call('/api/v1/flights' + (params.size ? '?' + params.toString() : '')), 'search');
    showMessage('#flightMessage', '');
  } catch (error) {
    showMessage('#flightMessage', friendlyError(error), true);
  }
}

function addAircraftOption(select, aircraft) {
  const option = document.createElement('option');
  option.value = aircraft.id || '';
  const registration = aircraft.registration || aircraft.name || aircraft.icao || 'Appareil';
  const type = aircraft.type_label || aircraft.subfleet || aircraft.icao || '';
  const airport = aircraft.airport ? ' · ' + aircraft.airport : '';
  option.textContent = registration + (type ? ' · ' + type : '') + airport;
  option.dataset.aircraft = JSON.stringify(aircraft);
  select.append(option);
}

function addAircraftTypeOption(select, type) {
  const option = document.createElement('option');
  option.value = type.type_key || '';
  const count = Number(type.available_count || 0);
  option.textContent = (type.type_label || type.type_key)
    + (count ? ' · ' + count + ' appareil' + (count > 1 ? 's' : '') + ' disponible' + (count > 1 ? 's' : '') : '')
    + ' · ' + (type.passengers ?? '—') + '/' + (type.capacity ?? '—') + ' pax'
    + ' · ' + String(type.pricing_band || 'rouge').toUpperCase();
  option.dataset.aircraftType = JSON.stringify(type);
  select.append(option);
}

function populateAircraftInstances(typeKey, preferredAircraftId = null) {
  const select = $('#aircraftId');
  if (!select) return;

  const available = Array.isArray(aircraftEligibility?.available) ? aircraftEligibility.available : [];
  const filtered = available
    .filter(aircraft => String(aircraft.type_key || '') === String(typeKey || ''))
    .sort((a, b) => String(a.registration || '').localeCompare(String(b.registration || ''), 'fr'));

  select.replaceChildren();
  const choose = document.createElement('option');
  choose.value = '';
  choose.textContent = typeKey
    ? (filtered.length ? 'Sélectionnez un appareil précis' : 'Aucun appareil disponible pour ce type')
    : 'Choisissez d’abord un type';
  select.append(choose);
  filtered.forEach(aircraft => addAircraftOption(select, aircraft));
  select.disabled = !typeKey || filtered.length === 0;

  if (preferredAircraftId && filtered.some(aircraft => String(aircraft.id) === String(preferredAircraftId))) {
    select.value = String(preferredAircraftId);
  } else {
    select.value = '';
  }
}

function renderVariantPreview(variant = selectedVariant) {
  const preview = $('#aircraftVariantPreview');
  if (!preview) return;
  preview.replaceChildren();
  if (!variant) { preview.hidden = true; return; }

  if (variant.image_url) {
    const image = document.createElement('img');
    image.src = variant.image_url;
    image.alt = variant.label || 'Airframe SimBrief';
    image.loading = 'lazy';
    preview.append(image);
  }
  const text = document.createElement('small');
  const sourceLabels = {
    phpvms_admin_airframe: 'Airframe phpVMS / Prométhée',
    simbrief_airframe: 'Catalogue SimBrief',
    hermes_catalog: 'Catalogue Hermès'
  };
  const proxy = variant.simbrief_strategy === 'proxy';
  text.textContent = (sourceLabels[variant.source] || variant.vendor || 'Profil appareil')
    + (proxy
      ? ' · Compatible via profil Air Inter · ' + (variant.icao || 'appareil réel')
      : ' · ' + (variant.simbrief_type || 'AUTO') + (variant.icao ? ' · ' + variant.icao : ''));
  preview.append(text);
  preview.hidden = false;
}

function renderRouteSuggestions(payload, initialRoute = '') {
  const select = $('#routeSuggestion');
  const input = $('#prefileForm')?.elements?.route;
  if (!select || !input) return;
  const options = Array.isArray(payload?.route_options) ? payload.route_options : [];
  select.replaceChildren();

  const auto = document.createElement('option');
  auto.value = '';
  auto.textContent = 'AUTO — laisser SimBrief calculer';
  select.append(auto);

  const seen = new Set();
  options.forEach(item => {
    const route = String(item?.route || '').trim();
    if (!route || seen.has(route.toUpperCase())) return;
    seen.add(route.toUpperCase());
    const option = document.createElement('option');
    option.value = route;
    option.textContent = (item.route_code ? item.route_code + ' — ' : '')
      + route
      + (item.source === 'scheduled_flight' ? ' · programme' : ' · variante compagnie');
    select.append(option);
  });

  const current = String(initialRoute || input.value || '').trim();
  if (current && !seen.has(current.toUpperCase())) {
    const option = document.createElement('option');
    option.value = current;
    option.textContent = 'PROGRAMME — ' + current;
    select.append(option);
  }
  select.value = Array.from(select.options).some(option => option.value === current) ? current : '';
  select.disabled = false;
}

async function loadRouteSuggestions(operationRef, initialRoute = '') {
  const select = $('#routeSuggestion');
  if (select) {
    select.disabled = true;
    select.replaceChildren(new Option('Chargement des routes…', ''));
  }
  try {
    const briefing = unwrap(await call('/api/v1/operations/' + encodeURIComponent(operationRef) + '/briefing'));
    renderRouteSuggestions(briefing, initialRoute);

    if (restorePlannedRouteFromBriefing(briefing, initialRoute)) {
      renderOperationLoad(selectedAircraft, flightPlan);
      renderSimBriefPreparationSummary();
    }
  } catch {
    renderRouteSuggestions({ route_options: [] }, initialRoute);
  }
}

function renderAircraftVariants(payload) {
  aircraftVariantState = payload || { variants: [] };
  const select = $('#aircraftVariantId');
  if (!select) return;
  const variants = Array.isArray(aircraftVariantState.variants) ? aircraftVariantState.variants : [];
  select.replaceChildren();

  if (!selectedAircraft?.id || !variants.length) {
    const option = document.createElement('option');
    option.value = '';
    option.textContent = selectedAircraft?.id ? 'Aucun add-on spécifique configuré pour cet appareil' : 'Choisissez d’abord un appareil';
    select.append(option);
    select.disabled = true;
    selectedVariant = null;
    renderVariantPreview(null);
    return;
  }

  const owned = variants.filter(item => item.owned);
  const visible = owned.length
    ? [...owned, ...variants.filter(item => !item.owned)]
    : variants;

  visible.forEach(variant => {
    const option = document.createElement('option');
    option.value = variant.id;
    option.textContent = (variant.owned ? '★ ' : '')
      + (variant.label || variant.id)
      + (variant.vendor ? ' · ' + variant.vendor : '')
      + (variant.simbrief_strategy === 'proxy'
        ? ' · Compatible via profil Air Inter'
        : (variant.simbrief_type ? ' · SimBrief ' + variant.simbrief_type : ''));
    option.dataset.variant = JSON.stringify(variant);
    select.append(option);
  });

  const selectedId = aircraftVariantState.selected_variant_id
    || selectedOperation?.simbrief?.selected_variant_id
    || visible[0]?.id;
  if (selectedId && visible.some(item => item.id === selectedId)) select.value = selectedId;
  selectedVariant = visible.find(item => item.id === select.value) || null;
  renderVariantPreview(selectedVariant);
  select.disabled = false;
}

async function refreshAircraftVariants() {
  const operationRef = selectedOperation?.operation_id || selectedOperation?.id || selectedOperation?.bid_id;
  if (!operationRef || !selectedAircraft?.id) {
    renderAircraftVariants({ variants: [] });
    return;
  }
  try {
    const simulator = simulatorCode();
    const payload = unwrap(await call('/api/v1/operations/' + encodeURIComponent(operationRef)
      + '/simulator-profiles?simulator=' + encodeURIComponent(simulator)));
    renderAircraftVariants(payload);
  } catch (error) {
    renderAircraftVariants({ variants: [] });
    showMessage('#pirepMessage', 'Add-ons indisponibles : ' + friendlyError(error), true);
  }
}

async function refreshAircraftVariantLibrary() {
  const container = $('#aircraftVariantLibrary');
  if (!container) return;
  if (!connected) {
    container.innerHTML = '<p class="empty">Connectez-vous pour charger votre bibliothèque.</p>';
    return;
  }

  try {
    const simulator = simulatorCode();
    const payload = unwrap(await call('/api/v1/me/simulator-profiles?simulator=' + encodeURIComponent(simulator)));
    const variants = (payload?.variants || []).filter(item => item.vendor !== 'Generic');
    container.replaceChildren();

    if (!variants.length) {
      const empty = document.createElement('p');
      empty.className = 'empty';
      empty.textContent = 'Aucun add-on configuré pour ce simulateur.';
      container.append(empty);
      return;
    }

    variants.forEach(variant => {
      const row = document.createElement('article');
      row.className = 'variant-library-item';

      const identity = document.createElement('div');
      identity.className = 'variant-library-identity';
      const name = document.createElement('strong');
      name.textContent = variant.label || variant.id;
      const detail = document.createElement('small');
      detail.textContent = [variant.type_key, variant.vendor, variant.simbrief_strategy === 'proxy'
        ? 'Compatible via profil Air Inter'
        : (variant.simbrief_type ? 'SimBrief ' + variant.simbrief_type : null)]
        .filter(Boolean).join(' · ');
      identity.append(name, detail);

      const controls = document.createElement('div');
      controls.className = 'variant-library-controls';

      const ownedLabel = document.createElement('label');
      ownedLabel.className = 'variant-toggle';
      const owned = document.createElement('input');
      owned.type = 'checkbox';
      owned.dataset.variantId = variant.id;
      owned.checked = Boolean(variant.owned);
      const switchUi = document.createElement('span');
      switchUi.className = 'variant-toggle-ui';
      const ownedText = document.createElement('b');
      ownedText.textContent = 'Possédé';
      ownedLabel.append(owned, switchUi, ownedText);

      const preferredLabel = document.createElement('label');
      preferredLabel.className = 'variant-preferred';
      const preferred = document.createElement('input');
      preferred.type = 'radio';
      preferred.name = 'preferredAircraftVariant';
      preferred.value = variant.id;
      preferred.checked = Boolean(variant.preferred);
      preferred.disabled = !owned.checked;
      const preferredText = document.createElement('span');
      preferredText.textContent = '★ Préférée';
      preferredLabel.append(preferred, preferredText);

      owned.onchange = () => {
        preferred.disabled = !owned.checked;
        if (!owned.checked && preferred.checked) preferred.checked = false;
        row.classList.toggle('owned', owned.checked);
      };

      controls.append(ownedLabel, preferredLabel);
      row.append(identity, controls);
      row.classList.toggle('owned', owned.checked);
      container.append(row);
    });
  } catch (error) {
    container.innerHTML = '<p class="empty">Bibliothèque indisponible.</p>';
    setText($('#aircraftVariantLibraryMessage'), friendlyError(error));
  }
}

function syncAircraftSelectors(aircraft = selectedAircraft) {
  const typeSelect = $('#aircraftTypeId');
  if (!typeSelect || !aircraftEligibility) return;

  const typeKey = aircraft?.type_key || '';
  if (typeKey && Array.from(typeSelect.options).some(option => option.value === String(typeKey))) {
    typeSelect.value = String(typeKey);
    populateAircraftInstances(typeKey, aircraft?.id || null);
  } else if (!typeSelect.value) {
    populateAircraftInstances('', null);
  }
}

function renderOperationLoad(aircraft, briefing = flightPlan) {
  const node = $('#operationLoad');
  if (!node) return;
  if (!aircraft?.id) {
    node.hidden = true;
    node.textContent = '';
    return;
  }
  const label = aircraft.type_label || aircraft.subfleet || aircraft.name || 'Appareil';
  const band = String(aircraft.band || aircraft.pricing_band || 'rouge').toUpperCase();
  const cabin = aircraft.cabin_profile?.label || aircraft.cabinProfile?.label || '';
  const simbriefPax = briefing?.passengers ?? briefing?.pax ?? null;
  const plannedPax = aircraft.passengers ?? null;
  const paxText = Number.isFinite(Number(simbriefPax))
    ? Number(simbriefPax) + ' pax OFP SimBrief'
    : ((plannedPax ?? '—') + ' pax prévus');
  node.textContent = label
    + (cabin ? ' · ' + cabin : '')
    + ' · ' + paxText + ' / ' + (aircraft.capacity ?? '—') + ' sièges'
    + ' · ' + (aircraft.load_factor_percent ?? '—') + ' % prévision commerciale · vol ' + band;
  node.hidden = false;
  renderSimBriefPreparationSummary();
}

async function selectOperation(operation) {
  if (!operation?.operation_id && !operation?.bid_id && operation?.id) {
    try {
      showMessage('#flightMessage', 'Réservation du vol dans Prométhée…');
      operation = unwrap(await call('/api/v1/flights/' + encodeURIComponent(operation.id) + '/reserve', {}));
      showMessage('#flightMessage', '');
    } catch (error) {
      showMessage('#flightMessage', 'Réservation impossible : ' + friendlyError(error), true);
      return;
    }
  }

  // Switching operations must be atomic from the UI point of view. Do not
  // render the new flight with the previous flight's terminal Dispatch/OFP
  // snapshot for even one refresh cycle (that used to show "Vol déjà terminé"
  // on a brand-new selection).
  serverDispatch = null;
  clearOperationalWeather();
  serverCompanyScore = null;
  serverCompanyScoreKey = null;
  flightPlan = null;
  linkedSimBrief = null;
  readiness.operation = false;
  readiness.aircraft = false;
  readiness.ofp = false;
  readiness.pirep = false;

  selectedOperation = operation;
  scheduleEfbContextSync();
  // Rehydrate an already-prefiled operation after a restart/reselection. The
  // server is authoritative; never keep a stale PIREP id from another flight.
  pirepId = operation.pirep_id || operation.pirep?.id || null;
  selectedAircraft = operation.aircraft?.id ? operation.aircraft : null;
  selectedVariant = operation.simbrief?.variant || null;
  renderOperationLoad(selectedAircraft);
  updateAircraftSelectionStatus();
  lastDatalinkSnapshot = null;
  setTimeout(refreshDatalink, 0);
  setTimeout(refreshNetwork, 0);
  updateWorkflow();
  $$('.operation').forEach(node => node.classList.remove('selected'));
  if (document.activeElement?.classList?.contains('operation')) document.activeElement.classList.add('selected');
  renderNetworkPrefiles(null);
  const flight = normalizeFlight(operation.flight || operation);
  const form = $('#prefileForm');
  const assign = (name, value) => { if (form.elements[name]) form.elements[name].value = value ?? ''; };
  assign('flight_id', flight.id);
  assign('airline_id', flight.airline_id);
  assign('flight_number', flight.flight_number);
  assign('aircraft_id', selectedAircraft?.id || '');
  assign('dpt_airport_id', flight.departure);
  assign('arr_airport_id', flight.arrival);
  assign('alt_airport_id', flight.alternate);
  assign('route', flight.route);
  assign('level', flight.level);
  assign('block_fuel', '');
  assign('notes', '');
  form.dataset.programDraft = JSON.stringify({
    alt_airport_id: flight.alternate || '',
    route: flight.route || '',
    level: flight.level || ''
  });
  setText($('#selectedFlight'), `${displayFlightIdent(flight)} — ${flight.departure || '?'} → ${flight.arrival || '?'}`);
  const simbrief = operation.simbrief || {};
  updateSimBriefAvailability(simbrief);
  setText($('#operationBrief'), simbrief.available
    ? `OFP SimBrief disponible · type ${simbrief.type || 'à confirmer'}`
    : `OFP à préparer · type ${simbrief.type || 'à confirmer'}`);
  $('#selectedOperation').hidden = false;
  form.hidden = false;
  showMessage('#pirepMessage', '');
  serverDispatch = null;

  const typeSelect = $('#aircraftTypeId');
  const aircraftSelect = $('#aircraftId');
  aircraftEligibility = null;

  typeSelect.replaceChildren();
  const loadingType = document.createElement('option');
  loadingType.value = '';
  loadingType.textContent = 'Chargement des types autorisés…';
  typeSelect.append(loadingType);
  typeSelect.disabled = true;

  aircraftSelect.replaceChildren();
  const loadingAircraft = document.createElement('option');
  loadingAircraft.value = '';
  loadingAircraft.textContent = 'Choisissez d’abord un type';
  aircraftSelect.append(loadingAircraft);
  aircraftSelect.disabled = true;

  try {
    const operationRef = operation.operation_id || operation.id || operation.bid_id;
    if (!operationRef) throw new Error('Cette réservation ne possède pas d’identifiant d’opération Prométhée.');

    await loadRouteSuggestions(operationRef, flight.route || '');
    const payload = unwrap(await call(`/api/v1/operations/${encodeURIComponent(operationRef)}/aircraft-eligibility`));
    aircraftEligibility = payload || {};
    renderEligibility(payload);

    const types = Array.isArray(payload?.types) ? payload.types : [];
    typeSelect.replaceChildren();
    const chooseType = document.createElement('option');
    chooseType.value = '';
    chooseType.textContent = types.length ? 'Sélectionnez un type d’appareil' : 'Aucun type disponible pour ce vol';
    typeSelect.append(chooseType);
    types.forEach(item => addAircraftTypeOption(typeSelect, item));
    typeSelect.disabled = types.length === 0;

    if (selectedAircraft?.id) {
      syncAircraftSelectors(selectedAircraft);
      await refreshAircraftVariants();
    } else {
      renderAircraftVariants({ variants: [] });
      populateAircraftInstances('', null);
    }

    if (!types.length) {
      showMessage('#pirepMessage', 'Aucun type d’appareil autorisé et disponible pour ce vol. Vérifiez la flotte, la position et les qualifications.', true);
    }
  } catch (error) {
    typeSelect.replaceChildren(loadingType);
    loadingType.textContent = 'Types indisponibles';
    typeSelect.disabled = true;
    aircraftSelect.replaceChildren(loadingAircraft);
    loadingAircraft.textContent = 'Appareils indisponibles';
    aircraftSelect.disabled = true;
    showMessage('#pirepMessage', friendlyError(error), true);
  }

  try { await refreshDispatch(); }
  catch (error) { showMessage('#pirepMessage', 'Dispatch indisponible : ' + friendlyError(error), true); }
  try { await refreshOperationalWeather(); }
  catch (error) { setText($('#weatherOpsState'), 'MÉTÉO INDISPONIBLE'); }
}
