// Hermès modularization phase 2 — workflow domain.
// Classic script: preserves the existing shared runtime/global handler contract.

function snapshotValue(snapshot, camel, pascal = camel) {
  return snapshot?.[camel] ?? snapshot?.[pascal] ?? null;
}

function buildWorkflowState() {
  const server = serverDispatch?.server_checks;
  return server ? {
    operation: Boolean(server.operation || selectedOperation),
    aircraft: Boolean(server.aircraft || selectedAircraft?.id),
    // The desktop already owns the imported briefing before Prométhée's next
    // dispatch refresh. Do not visually regress to step 03 during that window.
    ofp: Boolean(server.ofp || flightPlan || selectedOperation?.simbrief?.available),
    pirep: Boolean(server.pirep || pirepId)
  } : {
    operation: Boolean(selectedOperation),
    aircraft: Boolean(selectedAircraft?.id),
    ofp: Boolean(flightPlan || selectedOperation?.simbrief?.available),
    pirep: Boolean(pirepId)
  };
}

function currentCanonicalWorkflow(localState = buildWorkflowState()) {
  if (lastStatus?.recoveryAvailable) {
    return {
      state: 'RECOVERY',
      preparation_progress: 100,
      next_action: { code: 'OPEN_RECOVERY', label: 'Ouvrir le Recovery Center' },
      terminal: false,
      source: 'hermes-local'
    };
  }

  const remote = serverDispatch?.workflow_state || serverDispatch?.workflowState;
  if (remote?.state) return { ...remote, state: String(remote.state).toUpperCase(), source: 'promethee' };

  const legacy = String(serverDispatch?.status || '').toUpperCase();
  if (legacy && legacy !== 'PREPARATION_REQUIRED') {
    return { state: legacy, terminal: ['COMPLETED','CANCELLED'].includes(legacy), source: 'legacy-dispatch' };
  }
  if (!localState.operation) return { state: null, terminal: false, source: 'local' };
  if (!localState.aircraft) return { state: 'RESERVED', terminal: false, source: 'local' };
  if (!localState.ofp || !localState.pirep) return { state: 'PLANNING', terminal: false, source: 'local' };
  return { state: 'READY', terminal: false, source: 'local' };
}

function simBriefPreparationData(resolved = null) {
  const form = $('#prefileForm');
  const flight = normalizeFlight(selectedOperation?.flight || selectedOperation || {});
  const authoritative = resolved || {};
  const demand = authoritative.demand || {};
  const params = authoritative.parameters || {};
  const aircraft = authoritative.aircraft || selectedAircraft || {};
  const paxOverride = form?.elements?.pax?.value?.trim();
  const plannedPax = paxOverride !== ''
    ? Number(paxOverride)
    : Number(params.pax ?? demand.passengers ?? aircraft.passengers);
  const rawCostIndex = flightPlan?.cost_index
    ?? authoritative.cost_index
    ?? params.civalue
    ?? form?.elements?.civalue?.value
    ?? null;
  const costIndex = rawCostIndex === null || rawCostIndex === undefined || String(rawCostIndex).trim() === ''
    ? null
    : String(rawCostIndex).trim().toUpperCase();
  return {
    ident: authoritative.flight?.ident || displayFlightIdent(flight),
    departure: authoritative.origin?.icao || flight.departure || '—',
    arrival: authoritative.destination?.icao || flight.arrival || '—',
    registration: aircraft.registration || '—',
    type: aircraft.simbrief_display_type || aircraft.icao || selectedOperation?.simbrief?.type || 'AUTO',
    calculationType: aircraft.simbrief_type || selectedOperation?.simbrief?.calculation_type || selectedVariant?.simbrief_type || null,
    strategy: aircraft.simbrief_strategy || selectedOperation?.simbrief?.strategy || selectedVariant?.simbrief_strategy || 'native',
    historicalVariant: aircraft.historical_variant?.name
      || aircraft.resolved_profile?.variant?.name
      || selectedAircraft?.historical_variant?.name
      || '',
    configuration: aircraft.configuration?.name
      || aircraft.resolved_profile?.configuration?.name
      || selectedAircraft?.configuration?.name
      || '',
    addon: selectedVariant?.label || aircraft.simulator_profile?.label || selectedOperation?.simbrief?.addon || '',
    variant: selectedVariant?.label || aircraft.simulator_profile?.label || selectedOperation?.simbrief?.addon || '',
    pax: Number.isFinite(plannedPax) ? plannedPax : null,
    capacity: Number(demand.capacity ?? aircraft.capacity) || null,
    loadFactor: Number(demand.load_factor_percent ?? aircraft.load_factor_percent),
    level: params.fl || normalizeFlightLevel(form?.elements?.level?.value || flight.level) || null,
    costIndex
  };
}

function renderSimBriefPreparationSummary(resolved = null) {
  const data = simBriefPreparationData(resolved);
  const nodes = [$('#simbriefAccountSummary'), $('#simbriefApiSummary')].filter(Boolean);
  const hasAircraft = Boolean(selectedAircraft?.id || resolved?.aircraft?.id);
  nodes.forEach(node => {
    node.hidden = !hasAircraft;
    if (!hasAircraft) { node.replaceChildren(); return; }

    const entries = [
      ['VOL', data.ident],
      ['ROUTE', data.departure + ' → ' + data.arrival],
      ['APPAREIL', data.registration + ' · ' + data.type],
      ['VARIANTE RÉELLE', data.historicalVariant || 'Non renseignée dans sb-airframe'],
      ['CONFIGURATION', data.configuration || 'Configuration héritée'],
      ['ADD-ON', data.addon || 'AUTO / générique'],
      ['PAX ENVOYÉS', data.pax === null ? 'AUTO' : String(data.pax) + (data.capacity ? ' / ' + data.capacity : '')],
      ['REMPLISSAGE', Number.isFinite(data.loadFactor) ? data.loadFactor.toFixed(1).replace('.0','') + ' %' : '—'],
      ['NIVEAU', data.level ? 'FL' + String(data.level).padStart(3, '0') : 'AUTO'],
      ['CI', data.costIndex ?? 'AUTO']
    ];

    node.replaceChildren();
    entries.forEach(([label, value]) => {
      const item = document.createElement('span');
      const key = document.createElement('small');
      const strong = document.createElement('strong');
      key.textContent = label;
      strong.textContent = value;
      item.append(key, strong);
      node.append(item);
    });
  });
}

function showActionTooltip(button, message) {
  if (!button) return;
  document.querySelectorAll('.hermes-action-tooltip').forEach(node => node.remove());
  const bubble = document.createElement('div');
  bubble.className = 'hermes-action-tooltip';
  bubble.setAttribute('role', 'status');
  bubble.textContent = message;
  document.body.append(bubble);
  const rect = button.getBoundingClientRect();
  const width = Math.min(380, Math.max(250, bubble.offsetWidth || 300));
  const left = Math.min(window.innerWidth - width - 12, Math.max(12, rect.left + rect.width / 2 - width / 2));
  bubble.style.width = width + 'px';
  bubble.style.left = left + 'px';
  bubble.style.top = Math.min(window.innerHeight - bubble.offsetHeight - 12, rect.bottom + 10) + 'px';
  button.classList.add('needs-attention');
  setTimeout(() => {
    bubble.classList.add('closing');
    button.classList.remove('needs-attention');
    setTimeout(() => bubble.remove(), 180);
  }, 3600);
}

function requireAircraftForSimBrief(button) {
  if (selectedAircraft?.id && $('#prefileForm')?.elements?.aircraft_id?.value) return true;
  showActionTooltip(button, 'Sélectionnez d’abord un type puis une immatriculation. Hermès doit connaître l’appareil précis avant d’envoyer ou récupérer un OFP SimBrief.');
  $('#aircraftTypeId')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
  setTimeout(() => $('#aircraftTypeId')?.focus(), 250);
  return false;
}

function updateAircraftSelectionStatus() {
  const node = $('#aircraftSelectionStatus');
  if (!node) return;
  const typeSelected = Boolean($('#aircraftTypeId')?.value);
  const registration = selectedAircraft?.registration || selectedAircraft?.name || '';
  const label = selectedAircraft?.type_label || selectedAircraft?.subfleet || selectedAircraft?.icao || '';
  const historicalVariant = selectedAircraft?.historical_variant?.name
    || selectedAircraft?.resolved_profile?.variant?.name
    || '';
  const configuration = selectedAircraft?.configuration?.name
    || selectedAircraft?.resolved_profile?.configuration?.name
    || '';

  node.classList.toggle('ready', Boolean(selectedAircraft?.id));
  node.classList.toggle('pending', !selectedAircraft?.id);
  const strong = node.querySelector('strong');
  const detail = node.querySelector('span');

  if (selectedAircraft?.id) {
    setText(strong, 'APPAREIL PRÊT');
    setText(detail, [registration, label, historicalVariant, configuration].filter(Boolean).join(' · ')
      + ' — configuration résolue automatiquement, SimBrief peut maintenant être préparé.');
  } else if (typeSelected) {
    setText(strong, 'IMMATRICULATION REQUISE');
    setText(detail, 'Type sélectionné. Choisissez maintenant l’appareil précis / l’immatriculation.');
  } else {
    setText(strong, 'APPAREIL REQUIS');
    setText(detail, 'Choisissez un type d’appareil puis une immatriculation avant de préparer SimBrief.');
  }
  renderSimBriefPreparationSummary();
}

function updateNextAction(state, ready) {
  const title = $('#nextActionTitle');
  const text = $('#nextActionText');
  const button = $('#nextActionBtn');
  if (!title || !text || !button) return;

  const workflow = currentCanonicalWorkflow(state);
  const dispatchStatus = String(workflow.state || serverDispatch?.status || '').toUpperCase();
  const terminal = Boolean(workflow.terminal) || ['COMPLETED', 'CANCELLED'].includes(dispatchStatus);
  const awaitingFiling = dispatchStatus === 'AWAITING_FILING';
  const recoveryRequired = dispatchStatus === 'RECOVERY';
  let action = () => {};
  if (recoveryRequired) {
    setText(title, 'Reprendre le vol interrompu');
    setText(text, 'Hermès a retrouvé un enregistrement local interrompu. Restaurez-le avant toute nouvelle opération.');
    setText(button, 'Ouvrir le Recovery Center');
    action = () => {
      document.querySelector('[data-tab="record"]')?.click();
      $('#recoveryCenter')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };
  } else if (terminal) {
    setText(title, dispatchStatus === 'COMPLETED' ? 'Vol déjà terminé' : 'Opération annulée');
    setText(text, dispatchStatus === 'COMPLETED'
      ? 'Ce PIREP a déjà été déposé. Sélectionnez une autre réservation ou un nouveau vol du programme.'
      : 'Cette opération ne peut plus être démarrée. Sélectionnez une autre réservation.');
    setText(button, 'Choisir un autre vol');
    action = () => document.querySelector('[data-tab="flight"]')?.click();
  } else if (awaitingFiling) {
    setText(title, 'Vol arrivé · PIREP à déposer');
    setText(text, 'Hermès a reçu l’événement IN. Ouvrez Flight Review, vérifiez la synthèse puis déposez le rapport final.');
    setText(button, 'Ouvrir Flight Review');
    action = () => document.querySelector('[data-tab="review"]')?.click();
  } else if (!state.operation) {
    setText(title, 'Choisissez votre vol');
    setText(text, 'Sélectionnez une réservation ou recherchez une ligne du programme Air Inter.');
    setText(button, 'Choisir un vol');
    action = () => { setOperationsMode('bids'); document.querySelector('.operations-browser')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); };
  } else if (!state.aircraft) {
    setText(title, 'Affectez un appareil');
    setText(text, 'Hermès n’affiche que les appareils autorisés et disponibles pour cette opération.');
    setText(button, 'Choisir l’appareil');
    action = () => { $('#aircraftTypeId')?.focus(); $('#aircraftTypeId')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); };
  } else if (!state.ofp) {
    const mode = localSettings.flightPlanMode || 'account';
    const hasSimBriefIdentity = Boolean($('#simbriefUsername')?.value.trim() || $('#simbriefPilotId')?.value.trim());
    setText(title, mode === 'account' && !hasSimBriefIdentity ? 'Reliez votre compte SimBrief' : 'Préparez le briefing');
    setText(text, mode === 'account'
      ? (hasSimBriefIdentity
          ? 'Envoyez les données Air Inter et vos ajustements vers SimBrief, puis importez l’OFP généré.'
          : 'Renseignez votre alias Navigraph / SimBrief ou votre Pilot ID. Hermès ne demande jamais votre mot de passe.')
      : 'Préparez l’OFP avant le pré-dépôt du PIREP.');
    setText(button, mode === 'account' ? (hasSimBriefIdentity ? 'Envoyer vers SimBrief' : 'Ajouter mon alias') : (mode === 'api' ? 'Générer l’OFP' : 'Charger un plan'));
    action = () => {
      if (mode === 'account' && !hasSimBriefIdentity) {
        $('#simbriefUsername')?.focus();
        $('#simbriefUsername')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } else if (mode === 'account') $('#simbriefAccountOpenBtn')?.click();
      else if (mode === 'api') $('#simbriefBtn')?.click();
      else $('#planFile')?.click();
    };
  } else if (!state.pirep) {
    setText(title, 'Finalisez la préparation');
    setText(text, 'Le briefing est prêt. Hermès va enregistrer la préparation opérationnelle avant le départ.');
    setText(button, 'Finaliser la préparation');
    action = () => $('#prefileForm')?.requestSubmit();
  } else if (!readiness.simulator) {
    setText(title, 'Connectez le simulateur');
    setText(text, 'La préparation est terminée. Hermès attend maintenant une télémétrie valide du simulateur.');
    setText(button, 'Voir l’état simulateur');
    action = () => document.querySelector('[data-tab="record"]')?.click();
  } else {
    setText(title, ready ? 'Prêt pour le départ' : 'Contrôles avant départ');
    setText(text, ready
      ? 'Prométhée, le simulateur et la préparation sont alignés. Vous pouvez démarrer l’enregistrement.'
      : 'Un contrôle obligatoire reste à satisfaire avant le démarrage.');
    setText(button, ready ? 'Passer au vol' : 'Voir les contrôles');
    action = () => document.querySelector('[data-tab="record"]')?.click();
  }
  button.onclick = action;
}

function updatePreflight(status, state, ready) {
  const container = $('#preflightChecks');
  if (!container) return;
  const latest = status?.latest || {};
  const onGround = snapshotValue(latest, 'onGround', 'OnGround');
  const parkingBrake = snapshotValue(latest, 'parkingBrake', 'ParkingBrake');
  const engines = snapshotValue(latest, 'enginesRunning', 'EnginesRunning');
  const enginesKnown = Array.isArray(engines) && engines.length > 0;
  const enginesStopped = enginesKnown ? !engines.some(Boolean) : null;
  const active = status?.activeConnector;
  const connectorName = active?.name || active?.Name || status?.sim || 'Simulateur';

  const capabilityReport = status?.aircraftCapabilities || status?.AircraftCapabilities || {};
  const detectedAdapter = capabilityReport.adapterId || capabilityReport.AdapterId || null;
  const acceptedAdapters = Array.isArray(selectedVariant?.adapter_ids) ? selectedVariant.adapter_ids : [];
  const variantMatch = acceptedAdapters.length === 0 ? null : (detectedAdapter ? acceptedAdapters.includes(detectedAdapter) : null);

  const checks = [
    ['VOL', state.operation, state.operation ? 'opération sélectionnée' : 'à sélectionner', 'blocking'],
    ['APPAREIL', state.aircraft, state.aircraft ? 'appareil affecté' : 'à sélectionner', 'blocking'],
    ['OFP', state.ofp, state.ofp ? 'briefing disponible' : 'à préparer', 'blocking'],
    ['PRÉPARATION', state.pirep, state.pirep ? 'enregistrée' : 'à finaliser', 'blocking'],
    ['SIMULATEUR', readiness.simulator, readiness.simulator ? connectorName : 'télémétrie en attente', 'blocking'],
    ['ADD-ON', selectedVariant ? true : null, !selectedVariant
      ? 'non sélectionné · profil simulateur facultatif'
      : (variantMatch === true
          ? (selectedVariant.label + ' · add-on Prométhée · détecté')
          : variantMatch === false
            ? (selectedVariant.label + ' · add-on Prométhée · simulateur détecté '
                + (capabilityReport.adapterName || capabilityReport.AdapterName || detectedAdapter || 'inconnu')
                + ' · comparaison informative')
            : (selectedVariant.label + ' · add-on sélectionné · identité simulateur informative')), 'informational'],
    ['AU SOL', onGround === null ? null : onGround === true, onGround === null ? 'information indisponible' : (onGround ? 'confirmé' : 'avion en vol'), 'blocking'],
    ['FREIN DE PARC', parkingBrake === null ? null : parkingBrake === true, parkingBrake === null ? 'information indisponible' : (parkingBrake ? 'serré' : 'desserré'), 'verify'],
    ['MOTEURS', enginesStopped, enginesStopped === null ? 'information indisponible' : (enginesStopped ? 'arrêtés' : 'en fonctionnement'), 'verify']
  ];

  container.replaceChildren();
  checks.forEach(([name, passed, detail, level]) => {
    const item = document.createElement('span');
    const stateClass = passed === true ? 'ok' : passed === null ? 'unknown' : 'pending';
    const effectiveLevel = level === 'informational' ? 'informational' : (passed === false ? 'blocking' : (passed === null ? 'verify' : level));
    item.className = 'preflight-check ' + stateClass + ' ' + effectiveLevel;
    item.dataset.level = effectiveLevel;
    const mark = passed === true ? '✓' : passed === null ? '?' : '•';
    item.textContent = `${mark} ${name} — ${detail}`;
    container.append(item);
  });

  const safety = $('#flightSafetyState');
  if (safety) {
    const recording = Boolean(status?.flight?.recording ?? status?.flight?.Recording);
    setText(safety, recording ? 'TRACKING' : (ready ? 'READY' : 'STANDBY'));
    safety.classList.toggle('ready', ready || recording);
  }
}

function updateWorkflow() {
  const state = buildWorkflowState();
  const phases = {
    flight: state.operation && state.aircraft,
    briefing: state.operation && state.aircraft && state.ofp && state.pirep,
    ready: state.operation && state.aircraft && state.ofp && state.pirep && readiness.simulator
  };
  const currentPhase = !phases.flight ? 'flight' : (!phases.briefing ? 'briefing' : 'ready');
  $$('#workflow [data-phase]').forEach(node => {
    const key = node.dataset.phase;
    const passed = Boolean(phases[key]);
    node.classList.toggle('done', passed);
    node.classList.toggle('current', key === currentPhase && !passed);
  });
  const latest = lastStatus?.latest || {};
  const onGround = snapshotValue(latest, 'onGround', 'OnGround');
  const parkingBrake = snapshotValue(latest, 'parkingBrake', 'ParkingBrake');
  const engines = snapshotValue(latest, 'enginesRunning', 'EnginesRunning');
  const enginesStoppedOrUnknown = !Array.isArray(engines) || engines.length === 0 || !engines.some(Boolean);
  const preflightSafe = onGround === true && parkingBrake !== false && enginesStoppedOrUnknown;
  const acceptedAdapters = Array.isArray(selectedVariant?.adapter_ids) ? selectedVariant.adapter_ids : [];
  const capabilityReport = lastStatus?.aircraftCapabilities || lastStatus?.AircraftCapabilities || {};
  const detectedAdapter = capabilityReport.adapterId || capabilityReport.AdapterId || null;
  const serverChecksReady = serverDispatch
    ? ['operation', 'aircraft', 'ofp', 'pirep'].every(key => serverDispatch?.server_checks?.[key] === true)
    : false;
  const dispatchReady = serverDispatch
    ? (serverDispatch.can_start === true || (serverDispatch.status === 'IN_PROGRESS' && serverChecksReady))
    : (state.operation && state.aircraft && state.ofp && state.pirep);

  // The visible three-phase preparation state is allowed to unlock the action
  // even if a stale dispatch snapshot still exposes can_start=false. Clicking
  // the button ALWAYS refreshes and re-validates the authoritative dispatch in
  // assertDispatchCanStart(), so this removes a UI deadlock without bypassing
  // any Prométhée safety/server rule.
  const visiblePreparationReady = state.operation && state.aircraft && state.ofp && state.pirep;

  // Adapter/variant detection is useful diagnostics, but add-on title strings
  // are not reliable enough to hard-block an otherwise valid flight. Keep the
  // READY badge strict on real simulator safety data and let /api/start remain
  // the final authority for refusal reasons.
  const ready = dispatchReady && readiness.simulator && preflightSafe;
  const workflow = currentCanonicalWorkflow(state);
  const status = String(workflow.state || '').toUpperCase();
  const terminal = Boolean(workflow.terminal) || ['COMPLETED', 'CANCELLED'].includes(status);
  const awaitingFiling = status === 'AWAITING_FILING';
  const recoveryRequired = status === 'RECOVERY';

  // A stale PREPARATION_REQUIRED snapshot may still be revalidated on click,
  // but a server-confirmed terminal/arrival state must never offer a new START.
  // The pilot files the report from Flight Review after ARRIVAL/IN.
  const canAttemptStart = visiblePreparationReady
    && readiness.simulator
    && !terminal
    && !awaitingFiling
    && !recoveryRequired;

  const node = $('#readyState');
  if (node) {
    node.textContent = status === 'COMPLETED'
      ? 'FLIGHT COMPLETED'
      : status === 'CANCELLED'
        ? 'CANCELLED'
        : status === 'RECOVERY'
          ? 'RECOVERY REQUIRED'
          : status === 'AWAITING_FILING'
            ? 'ARRIVED · PIREP TO FILE'
            : status === 'IN_PROGRESS'
              ? 'FLIGHT IN PROGRESS'
              : status === 'PLANNING'
                ? 'PREPARATION'
                : status === 'RESERVED'
                  ? 'FLIGHT RESERVED'
                  : (ready ? 'READY FOR DEPARTURE' : 'NOT READY');
    node.classList.toggle('ready', ready || awaitingFiling || status === 'IN_PROGRESS');
    node.title = workflow.next_action?.label || '';
  }
  const startButton = $('#startBtn');
  if (startButton) {
    startButton.disabled = !canAttemptStart;
    startButton.textContent = serverDispatch?.status === 'IN_PROGRESS'
      ? 'Reprendre l’enregistrement'
      : (ready ? 'Démarrer l’enregistrement' : 'Vérifier et démarrer');
    startButton.title = awaitingFiling
      ? 'Le vol est arrivé. Déposez maintenant le PIREP final depuis Flight Review.'
      : (terminal
          ? 'Cette opération est terminée et ne peut pas être redémarrée.'
          : (canAttemptStart && !ready
              ? 'Hermès vérifiera les contrôles au clic et affichera précisément ce qui bloque.'
              : ''));
  }

  const activeLocalFlight = lastStatus?.flight || lastStatus?.Flight || null;
  const localRecording = Boolean(activeLocalFlight?.recording ?? activeLocalFlight?.Recording);
  const localRecovery = Boolean(lastStatus?.recoveryAvailable);
  const pauseButton = $('#pauseBtn');
  const resumeButton = $('#resumeBtn');
  const abortPirepButton = $('#abortPirepBtn');

  if (pauseButton) {
    pauseButton.disabled = !localRecording;
    pauseButton.title = localRecording ? 'Mettre l’enregistrement ACARS en pause.' : 'Aucun enregistrement actif à mettre en pause.';
  }

  if (resumeButton) {
    const canResumeLocalPause = Boolean(activeLocalFlight) && !localRecording && !localRecovery;
    resumeButton.disabled = !canResumeLocalPause;
    resumeButton.textContent = 'Reprendre une pause';
    resumeButton.title = canResumeLocalPause
      ? 'Reprendre un enregistrement ACARS local mis en pause.'
      : (localRecovery
          ? 'Utilisez le Recovery Center pour un vol interrompu.'
          : 'Aucun vol local en pause. Utilisez « Démarrer l’enregistrement » pour cette nouvelle opération.');
  }

  if (abortPirepButton) {
    const canAbandon = connected && Boolean(pirepId) && !localRecovery && !terminal;
    abortPirepButton.hidden = !pirepId || localRecovery || terminal;
    abortPirepButton.disabled = !canAbandon;
    abortPirepButton.title = canAbandon
      ? 'Supprimer ce PIREP de Prométhée et abandonner cette tentative.'
      : 'Connectez-vous à Prométhée pour abandonner ce PIREP.';
  }

  updateAircraftSelectionStatus();
  updateNextAction(state, ready);
  updatePreflight(lastStatus, state, ready);
}

function renderEligibility(payload) {
  const box = $('#aircraftEligibility');
  if (!box) return;
  const types = payload?.types || [];
  box.replaceChildren();
  box.hidden = false;

  const heading = document.createElement('div');
  heading.className = 'eligibility-heading';
  heading.innerHTML = '<div><span class="kicker">DISPATCH</span><h3>Types d’appareil disponibles</h3><p class="hint">Affichez un type à la demande pour consulter sa disponibilité sans dérouler toute la flotte.</p></div>';
  const count = document.createElement('strong');
  count.textContent = types.length + ' type' + (types.length > 1 ? 's' : '');
  heading.append(count);
  box.append(heading);

  if (!types.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = 'Aucun type d’appareil n’est actuellement disponible pour cette opération.';
    box.append(empty);
    return;
  }

  const picker = document.createElement('select');
  picker.className = 'eligibility-type-picker';
  picker.setAttribute('aria-label', 'Afficher les disponibilités par type d’appareil');
  const placeholder = document.createElement('option');
  placeholder.value = '';
  placeholder.textContent = 'Afficher un type d’appareil…';
  picker.append(placeholder);
  types.forEach(type => {
    const option = document.createElement('option');
    option.value = String(type.type_key || type.type_label || '');
    option.textContent = (type.type_label || type.type_key || 'Appareil')
      + ' · ' + (type.available_count || 0) + ' disponible' + (Number(type.available_count || 0) > 1 ? 's' : '');
    picker.append(option);
  });
  box.append(picker);

  const detail = document.createElement('div');
  detail.className = 'eligibility-selected-detail';
  detail.innerHTML = '<p class="hint">Choisissez un type dans le menu pour afficher sa prévision passagers et son remplissage.</p>';
  box.append(detail);

  picker.onchange = () => {
    detail.replaceChildren();
    const type = types.find(item => String(item.type_key || item.type_label || '') === picker.value);
    if (!type) {
      detail.innerHTML = '<p class="hint">Choisissez un type dans le menu pour afficher sa prévision passagers et son remplissage.</p>';
      return;
    }

    const card = document.createElement('article');
    card.className = 'aircraft-card eligible';
    const title = document.createElement('div');
    const name = document.createElement('strong');
    name.textContent = type.type_label || type.type_key || 'Appareil';
    const status = document.createElement('em');
    status.textContent = (type.available_count || 0) + ' DISPONIBLE' + (Number(type.available_count || 0) > 1 ? 'S' : '');
    title.append(name, status);
    card.append(title);

    const details = document.createElement('ul');
    const band = String(type.pricing_band || 'rouge').toUpperCase();
    [
      'Prévision passagers : ' + (type.passengers ?? '—') + ' / ' + (type.capacity ?? '—'),
      'Remplissage : ' + (type.load_factor_percent ?? '—') + ' %',
      'Vol ' + band + ' · tarif ' + (type.fare_percent ?? 100) + ' % du plein tarif'
    ].forEach(value => {
      const li = document.createElement('li');
      li.textContent = '✓ ' + value;
      details.append(li);
    });
    card.append(details);
    detail.append(card);
  };
}
