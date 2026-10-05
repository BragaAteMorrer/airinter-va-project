// Hermès modularization phase 2 — planning domain.
// Kept as a classic script to preserve the existing shared runtime contract.

function refreshDispatch() {
  const operationRef = selectedOperation?.operation_id || selectedOperation?.id;
  if (!operationRef) { serverDispatch = null; clearOperationalWeather(); return null; }

  const currentWeather = serverDispatch?.weather || null;
  serverDispatch = unwrap(await call(`/api/v1/operations/${encodeURIComponent(operationRef)}/dispatch`));
  if (currentWeather) serverDispatch.weather = currentWeather;

  // A dispatch refresh is the canonical recovery path for Hermès. Hydrate all
  // identifiers that may have been lost when the desktop app was restarted.
  if (serverDispatch?.operation) {
    selectedOperation = { ...(selectedOperation || {}), ...serverDispatch.operation };
  }
  const dispatchPirepId = serverDispatch?.pirep?.id
    || serverDispatch?.pirep?.pirep_id
    || serverDispatch?.operation?.pirep_id
    || null;
  if (dispatchPirepId) pirepId = dispatchPirepId;

  if (serverDispatch?.operation?.aircraft?.id) {
    selectedAircraft = serverDispatch.operation.aircraft;
    const form = $('#prefileForm');
    if (form?.elements?.aircraft_id) form.elements.aircraft_id.value = selectedAircraft.id;
    renderOperationLoad(selectedAircraft);
    syncAircraftSelectors(selectedAircraft);
    selectedVariant = serverDispatch.operation?.simbrief?.variant || selectedVariant;
    renderAircraftVariants(serverDispatch.operation?.simbrief || aircraftVariantState || { variants: [] });
  }
  const checks = serverDispatch?.server_checks || {};
  readiness.operation = Boolean(checks.operation);
  readiness.aircraft = Boolean(checks.aircraft);
  readiness.ofp = Boolean(checks.ofp);
  readiness.pirep = Boolean(checks.pirep);

  const labels = { PREPARATION_REQUIRED: 'PRÉPARATION REQUISE', READY: 'PRÊT POUR HERMÈS', IN_PROGRESS: 'VOL EN COURS', AWAITING_FILING: 'ARRIVÉ · PIREP À DÉPOSER', COMPLETED: 'VOL TERMINÉ', CANCELLED: 'OPÉRATION ANNULÉE' };
  setText($('#operationBrief'), `${labels[serverDispatch?.status] || serverDispatch?.status || 'DISPATCH'} · Dispatch Prométhée`);
  updateWorkflow();
  scheduleEfbContextSync();
  return serverDispatch;
}

function assertDispatchCanStart() {
  const dispatch = await refreshDispatch();
  const checks = dispatch?.server_checks || {};
  const checksReady = ['operation', 'aircraft', 'ofp', 'pirep'].every(key => checks[key] === true);
  const serverPirepId = dispatch?.pirep?.id || dispatch?.pirep?.pirep_id || dispatch?.operation?.pirep_id || null;
  if (serverPirepId) pirepId = serverPirepId;
  const resumable = dispatch?.status === 'IN_PROGRESS' && checksReady && Boolean(serverPirepId);

  if (!dispatch?.can_start && !resumable) {
    const actions = Array.isArray(dispatch?.actions) ? dispatch.actions.filter(Boolean).join(' ') : '';
    throw new Error(actions || `Prométhée refuse le démarrage : ${dispatch?.status || 'opération non prête'}.`);
  }

  // An operation can already be IN_PROGRESS on Prométhée while the desktop
  // recorder was restarted/reinstalled. Reattach to the authoritative PIREP
  // instead of trapping the pilot behind a disabled START button.
  if (resumable) pirepId = serverPirepId;
  return { ...dispatch, resumable };
}

function simbriefPath(suffix) {
  const operationRef = selectedOperation?.operation_id || selectedOperation?.id;
  if (!operationRef) throw new Error('Sélectionnez une opération Prométhée avant de préparer SimBrief.');
  return `/api/v1/operations/${encodeURIComponent(operationRef)}/simbrief/${suffix}`;
}

function normalizeFlightLevel(value) {
  if (value === undefined || value === null || value === '') return undefined;
  let raw = String(value).trim().toUpperCase();
  raw = raw.replace(/^FL\s*/, '').replace(/^F(?=\d)/, '');
  raw = raw.replace(/\s*(?:FT|FEET|PIEDS?)$/, '').replace(/\s+/g, '').replace(',', '.');
  const altitude = Number(raw);
  if (!Number.isFinite(altitude) || altitude <= 0) return undefined;

  // Accept both flight levels (350) and altitudes in feet (35000).
  // A few imported schedules use hundreds of feet with an extra zero;
  // repeatedly collapse only while the value is clearly outside FL range.
  let level = altitude;
  while (level > 60000) level /= 10;
  if (level > 600) level /= 100;
  level = Math.round(level);

  return level >= 10 && level <= 600 ? level : undefined;
}

function simBriefPlanningPayload(form) {
  const payload = {};
  const aircraftId = form.elements.aircraft_id.value;
  if (aircraftId) payload.aircraft_id = aircraftId;

  const alternate = form.elements.alt_airport_id.value.trim().toUpperCase();
  if (alternate) payload.alternate = alternate;

  const route = form.elements.route.value.trim();
  if (route) payload.route = route;

  const rawLevel = form.elements.level.value.trim();
  if (rawLevel) {
    const level = normalizeFlightLevel(rawLevel);
    if (level) {
      payload.level = level;
      form.elements.level.value = String(level);
    } else {
      // Never block OFP generation because of a malformed level copied from
      // schedule data. Omitting the override lets Prométhée resolve and
      // normalize the canonical flight level server-side.
      form.elements.level.value = '';
      console.warn('Hermès ignored an invalid flight-level override:', rawLevel);
    }
  }

  // Advanced controls remain ephemeral. Empty fields mean "use Prométhée /
  // SimBrief defaults"; only explicit pilot choices cross the API boundary.
  const advanced = [
    'callsign', 'units', 'planformat', 'maps', 'navlog',
    'tlr', 'notams', 'firnot', 'stepclimbs', 'etops', 'find_sidstar',
    'cruise', 'civalue', 'contpct', 'resvrule', 'selcal', 'deprwy',
    'arrrwy', 'taxiout', 'taxiin', 'pax', 'manualrmk'
  ];
  advanced.forEach(name => {
    const field = form.elements[name];
    if (!field) return;
    const raw = String(field.value ?? '').trim();
    if (raw === '') return;

    if (['taxiout', 'taxiin', 'pax'].includes(name)) {
      const number = Number(raw);
      if (Number.isFinite(number)) payload[name] = Math.round(number);
      return;
    }

    payload[name] = raw;
  });

  return payload;
}

function assertSimBriefReady(form, mode = 'company') {
  const resolved = unwrap(await call(simbriefPath('readiness'), simBriefPlanningPayload(form)));
  const ready = mode === 'account'
    ? Boolean(resolved?.ready_account)
    : Boolean(resolved?.ready_company_api ?? resolved?.ready);

  if (!ready) {
    const failed = (resolved?.checks || [])
      .filter(check => check?.ready === false && (mode !== 'account' || check?.code !== 'COMPANY_API'))
      .map(check => check?.label)
      .filter(Boolean);
    throw new Error('SimBrief non prêt' + (failed.length ? ' : ' + failed.join(' · ') : '.'));
  }

  const flight = resolved?.flight?.ident || resolved?.flight?.number || 'vol';
  const origin = resolved?.origin?.icao || '—';
  const destination = resolved?.destination?.icao || '—';
  const registration = resolved?.aircraft?.registration || 'appareil';
  const type = resolved?.aircraft?.simbrief_display_type || resolved?.aircraft?.icao || 'type inconnu';
  const strategy = resolved?.aircraft?.simbrief_strategy;
  const planningLabel = strategy === 'proxy' ? ' · compatible via profil Air Inter' : '';
  const pax = resolved?.demand?.passengers;
  const cabin = resolved?.demand?.cabin_profile?.label || resolved?.demand?.cabinProfile?.label || '';
  showMessage(
    '#simbriefState',
    `Résolution BDD OK · ${flight} · ${origin} → ${destination} · ${registration} · ${type}${planningLabel}${cabin ? ' · ' + cabin : ''}${Number.isFinite(Number(pax)) ? ' · ' + pax + ' pax' : ''}.`
  );
  renderSimBriefPreparationSummary(resolved);
  return resolved;
}

function renderNetworkPrefiles(prefiles) {
  const panel = $('#networkPrefilePanel');
  const preview = $('#icaoFlightPlanPreview');
  if (!panel) return;
  const available = Boolean(prefiles?.vatsim?.url || prefiles?.ivao?.url);
  panel.hidden = !available;
  if (preview) {
    preview.hidden = !prefiles?.icao_flightplan;
    preview.textContent = prefiles?.icao_flightplan || '';
  }
  const vatsimButton = $('#vatsimPrefileBtn');
  const ivaoButton = $('#ivaoPrefileBtn');
  if (vatsimButton) vatsimButton.disabled = !prefiles?.vatsim?.url;
  if (ivaoButton) ivaoButton.disabled = !prefiles?.ivao?.url;
  showMessage('#networkPrefileMessage', available
    ? 'Plan ICAO prêt. Vérifiez-le avant l’envoi sur le réseau.'
    : '');
}

function applyBriefing(briefing, sourceLabel) {
  const form = $('#prefileForm');
  const flightLevel = normalizeFlightLevel(briefing.initial_altitude);
  const importedPax = briefing.passengers
    ?? briefing.pax
    ?? briefing.weights?.passengers
    ?? briefing.general?.passengers
    ?? undefined;
  const importedCostIndex = briefing.cost_index
    ?? briefing.general?.costindex
    ?? briefing.general?.cost_index
    ?? undefined;
  flightPlan = {
    source: briefing.source || sourceLabel,
    simbrief_id: briefing.id,
    route: briefing.route,
    level: flightLevel,
    block_fuel: briefing.block_fuel || undefined,
    passengers: Number.isFinite(Number(importedPax)) ? Number(importedPax) : undefined,
    cost_index: importedCostIndex === undefined || importedCostIndex === null || String(importedCostIndex).trim() === ''
      ? undefined
      : String(importedCostIndex).trim(),
    estimated_time_enroute: Number(briefing.estimated_time_enroute || 0) || null,
    route_points: Array.isArray(briefing.route_points) ? briefing.route_points : [],
    network_prefiles: briefing.network_prefiles || null
  };
  if (briefing.block_fuel) form.elements.block_fuel.value = Math.round(briefing.block_fuel);
  if (briefing.route) form.elements.route.value = briefing.route;
  if (flightLevel) form.elements.level.value = flightLevel;
  if (flightPlan.cost_index !== undefined && form.elements.civalue) form.elements.civalue.value = String(flightPlan.cost_index);
  if (briefing.alternate) form.elements.alt_airport_id.value = briefing.alternate;
  $('#planBox').textContent = JSON.stringify(briefing, null, 2);
  renderNetworkPrefiles(flightPlan.network_prefiles);
  renderOperationLoad(selectedAircraft, flightPlan);
  renderSimBriefPreparationSummary(briefing.resolved || null);
  scheduleEfbContextSync();

  const commercialPax = Number(selectedAircraft?.passengers);
  const actualPax = Number(flightPlan.passengers);
  if (Number.isFinite(actualPax) && Number.isFinite(commercialPax) && actualPax !== commercialPax) {
    showMessage(
      '#pirepMessage',
      `SimBrief a retenu ${actualPax} pax contre ${commercialPax} prévus par Air Inter. Le PIREP utilisera les ${actualPax} pax réellement présents dans l’OFP.`,
      false
    );
  }

  // The imported OFP is authoritative for the client immediately. Refresh the
  // dispatch so step 03 cannot remain stuck on a stale server snapshot.
  try { await refreshDispatch(); } catch {}
  updateWorkflow();

  showMessage('#simbriefState', 'OFP importé depuis ' + sourceLabel + '. Enregistrement automatique de la préparation opérationnelle…');
  if (!pirepId) await prefilePreparedOperation({ navigate: true, automatic: true });
  drawMap(flightMapState.lastTrack, lastStatus?.latest || {});
}

function recommendedPlanMode() {
  const hasIdentity = Boolean($('#simbriefUsername')?.value.trim() || $('#simbriefPilotId')?.value.trim());
  if (hasIdentity) return 'account';
  if (selectedOperation?.simbrief?.company_api_available === true) return 'api';
  return 'file';
}

function updateRecommendedPlanSource() {
  const labels = { account:'Compte SimBrief · alias pilote détecté', api:'API SimBrief compagnie · source automatique', file:'Plan local · aucun compte/API disponible' };
  setText($('#ofpSourceHint'), 'Source recommandée : ' + labels[recommendedPlanMode()]);
}

function updateSimBriefAvailability(simbrief = selectedOperation?.simbrief || {}

function setPlanMode(mode) {
  if (!['account', 'api', 'file'].includes(mode)) mode = 'account';
  if (mode === 'api' && selectedOperation?.simbrief?.company_api_available === false) {
    mode = 'account';
  }

  localSettings.flightPlanMode = mode;
  localStorage.prometheeAcarsSettings = JSON.stringify(localSettings);

  $$('[data-plan-mode]').forEach(button => {
    const active = button.dataset.planMode === mode;
    button.classList.toggle('active', active);
    button.setAttribute('aria-selected', String(active));
    button.tabIndex = active ? 0 : -1;
  });

  $$('[data-plan-panel]').forEach(panel => {
    const active = panel.dataset.planPanel === mode;
    panel.classList.toggle('active', active);
    panel.hidden = !active;
    panel.setAttribute('aria-hidden', String(!active));
  });

  const labels = {
    account: 'Compte SimBrief : envoyez la préparation, générez l’OFP sur SimBrief puis récupérez-le dans Hermès.',
    api: 'API SimBrief : Prométhée prépare la génération et Hermès récupère automatiquement l’OFP terminé.',
    file: 'Plan local : importez un fichier PLN/XML depuis votre ordinateur.'
  };
  if (!flightPlan) showMessage('#simbriefState', labels[mode] || '');
}

function prefilePreparedOperation({ navigate = true, automatic = false }
