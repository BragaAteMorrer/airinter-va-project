// Hermès modularization phase 2 — recovery domain.
// Classic script: preserves the existing shared runtime/global handler contract.

function renderTimeline(selector, entries) {
  const node = $(selector);
  node.replaceChildren();
  if (!entries?.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = 'En attente d’un vol.';
    node.append(empty);
    return;
  }
  entries.forEach(entry => {
    const item = document.createElement('span');
    const occurredAt = entry.occurredAt || entry.OccurredAt;
    const value = entry.value ?? entry.Value;
    const time = new Date(occurredAt).toLocaleTimeString('fr-FR', { timeZone: localSettings.timeFormat === 'utc' ? 'UTC' : undefined });
    item.textContent = `${time} — ${entry.name || entry.Name}${value == null ? '' : ' · ' + Number(value).toFixed(0)}`;
    node.append(item);
  });
}

function historyValue(entry, camel, pascal = camel) {
  return entry?.[camel] ?? entry?.[pascal] ?? null;
}

function renderJournalHistory(entries) {
  const node = $('#journalHistory');
  if (!node) return;
  node.replaceChildren();

  const flights = Array.isArray(entries) ? entries : [];
  setText($('#journalHistoryMeta'), flights.length
    ? flights.length + ' vol' + (flights.length > 1 ? 's' : '') + ' conservé' + (flights.length > 1 ? 's' : '') + ' sur ce poste'
    : 'Aucun vol local');

  if (!flights.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = 'Aucun vol terminé enregistré sur ce poste.';
    node.append(empty);
    return;
  }

  flights.forEach(entry => {
    const pirep = historyValue(entry, 'pirepId', 'PirepId') || 'PIREP';
    const completedAt = historyValue(entry, 'completedAt', 'CompletedAt');
    const distance = Number(historyValue(entry, 'distance', 'Distance') || 0);
    const airborne = Number(historyValue(entry, 'airborneMinutes', 'AirborneMinutes') || 0);
    const block = Number(historyValue(entry, 'blockMinutes', 'BlockMinutes') || 0);
    const fuel = Number(historyValue(entry, 'fuelUsed', 'FuelUsed') || 0);
    const landingRate = historyValue(entry, 'landingRate', 'LandingRate');
    const issues = historyValue(entry, 'issues', 'Issues') || [];
    const observations = historyValue(entry, 'observations', 'Observations') || [];

    const card = document.createElement('article');
    card.className = 'journal-history-card';

    const header = document.createElement('header');
    const identity = document.createElement('div');
    const title = document.createElement('strong');
    title.textContent = String(pirep);
    const date = document.createElement('span');
    date.textContent = completedAt
      ? new Date(completedAt).toLocaleString('fr-FR', { timeZone: localSettings.timeFormat === 'utc' ? 'UTC' : undefined })
      : 'Date inconnue';
    identity.append(title, date);

    const landing = document.createElement('em');
    landing.textContent = landingRate == null ? 'LANDING —' : Math.round(Number(landingRate)) + ' ft/min';
    header.append(identity, landing);

    const metrics = document.createElement('div');
    metrics.className = 'journal-history-metrics';
    [
      ['Distance', distance.toFixed(1) + ' NM'],
      ['Airborne', airborne + ' min'],
      ['Block', block + ' min'],
      ['Fuel', Math.round(fuel).toLocaleString('fr-FR') + ' lb'],
      ['Événements', String(observations.length)],
      ['Alertes', String(issues.length)]
    ].forEach(([label, value]) => {
      const item = document.createElement('span');
      const small = document.createElement('small');
      small.textContent = label;
      const strong = document.createElement('b');
      strong.textContent = value;
      item.append(small, strong);
      metrics.append(item);
    });

    card.append(header, metrics);
    node.append(card);
  });
}

async function refreshJournal() {
  try {
    const history = await call('/api/history');
    renderJournalHistory(history);
    showMessage('#journalMessage', '');
  } catch (error) {
    renderJournalHistory([]);
    showMessage('#journalMessage', 'Historique local indisponible : ' + friendlyError(error), true);
  }
}

function renderAircraftCapabilities(report) {
  const panel = $('#aircraftCapabilities');
  if (!panel) return;
  if (!report) { panel.hidden = true; return; }
  panel.hidden = false;

  const read = (camel, pascal) => report?.[camel] ?? report?.[pascal];
  const plannedIcao = String(
    selectedAircraft?.icao
      || selectedOperation?.aircraft?.icao
      || ''
  ).trim().toUpperCase();
  const simulatorIcao = String(read('aircraftIcao','AircraftIcao') || '').trim().toUpperCase();

  // AircraftIcao is telemetry metadata, not a flight-safety sensor. Some
  // FSUIPC/MSFS aircraft do not expose it even though Prométhée already knows
  // the exact assigned airframe. In that case use the authoritative operation
  // identity for the capability display without inventing simulator telemetry.
  const rawEntries = read('capabilities','Capabilities') || [];
  const entries = rawEntries.map(entry => {
    const capability = entry.capability ?? entry.Capability;
    const availability = String(entry.availability ?? entry.Availability ?? 'Unknown');
    if (capability === 'AircraftIcao' && availability === 'Unknown' && plannedIcao) {
      return {
        ...entry,
        capability,
        availability: 'Supported',
        source: 'promethee-preparation',
        effectiveValue: plannedIcao
      };
    }
    return entry;
  });

  const effectiveCounts = entries.reduce((counts, entry) => {
    const availability = String(entry.availability ?? entry.Availability ?? 'Unknown');
    if (availability === 'Supported') counts.supported += 1;
    else if (availability === 'Unsupported') counts.unsupported += 1;
    else counts.unknown += 1;
    return counts;
  }, { supported: 0, unknown: 0, unsupported: 0 });

  const reportedLabel = read('aircraftLabel','AircraftLabel') || 'Appareil non identifié';
  const effectiveLabel = plannedIcao && !simulatorIcao && !reportedLabel.toUpperCase().includes(plannedIcao)
    ? plannedIcao + ' · ' + reportedLabel
    : reportedLabel;
  setText($('#capabilityAircraft'), effectiveLabel);

  const adapterName = read('adapterName','AdapterName') || 'Generic aircraft';
  const connector = read('connectorId','ConnectorId') || 'connector';
  setText($('#capabilityAdapter'), adapterName + ' · ' + connector);
  setText($('#capabilitySupported'), String(effectiveCounts.supported));
  setText($('#capabilityUnknown'), String(effectiveCounts.unknown));
  setText($('#capabilityUnsupported'), String(effectiveCounts.unsupported));

  const matrix = $('#capabilityMatrix');
  matrix.replaceChildren();
  entries.forEach(entry => {
    const capability = entry.capability ?? entry.Capability;
    const availability = String(entry.availability ?? entry.Availability ?? 'Unknown');
    const source = entry.source ?? entry.Source ?? '';
    const effectiveValue = entry.effectiveValue || '';
    const item = document.createElement('span');
    item.className = 'capability-item ' + availability.toLowerCase();
    const label = document.createElement('strong');
    label.textContent = capabilityLabels[capability] || capability;
    const state = document.createElement('small');
    state.textContent = availability === 'Supported'
      ? (source === 'promethee-preparation' ? 'DISPONIBLE · PRÉPARATION' : 'DISPONIBLE')
      : availability === 'Unsupported' ? 'NON PRIS EN CHARGE'
      : 'À COMPLÉTER';
    item.title = [source, effectiveValue].filter(Boolean).join(' · ');
    item.append(label, state);
    matrix.append(item);
  });
}

function renderRecovery(status) {
  const center = $('#recoveryCenter');
  if (!center) return;
  const available = Boolean(status?.recoveryAvailable);
  center.hidden = !available;
  if (!available) {
    recoveryWasVisible = false;
    return;
  }

  if (!recoveryWasVisible) {
    recoveryWasVisible = true;
    setTimeout(() => center.scrollIntoView({ behavior: 'smooth', block: 'start' }), 0);
  }

  const info = status.recovery || {};
  const read = (camel, pascal) => info[camel] ?? info[pascal];
  const phase = read('phase', 'Phase') || 'INTERROMPU';
  const pending = Number(read('pendingMessages', 'PendingMessages') || status.pending || 0);
  const distance = Number(read('distance', 'Distance') || 0);
  const airborne = Number(read('airborneMinutes', 'AirborneMinutes') || 0);
  const started = read('started', 'Started');
  const pirep = read('pirepId', 'PirepId') || '—';

  setText($('#recoveryPirep'), pirep);
  setText($('#recoveryPhase'), 'RECOVERY · ' + phase);
  setText($('#recoveryPhaseValue'), phase);
  setText($('#recoveryDistance'), distance.toFixed(1) + ' NM');
  setText($('#recoveryAirborne'), airborne + ' min');
  setText($('#recoveryPending'), String(pending));
  setText($('#recoverySummary'), started
    ? `Hermès a retrouvé le vol ${pirep} commencé le ${new Date(started).toLocaleString('fr-FR')}.`
    : `Hermès a retrouvé le vol ${pirep} enregistré localement.`);

  const resume = $('#recoveryResumeBtn');
  const abandon = $('#recoveryAbandonBtn');
  const simReady = Boolean(status.latest);
  resume.disabled = !connected || !simReady;
  if (abandon) {
    abandon.disabled = !connected;
    abandon.title = connected
      ? 'Supprimer ce PIREP de Prométhée et archiver la récupération locale.'
      : 'Reconnectez-vous à votre compte Air Inter avant de supprimer le PIREP.';
  }
  if (!connected) setText($('#recoveryHint'), 'Connectez-vous à votre compte Air Inter pour reprendre ce vol.');
  else if (!simReady) setText($('#recoveryHint'), status.simLinkState === 'RECONNECTING'
    ? 'Le simulateur a été perdu. Hermès attend sa reconnexion avant de reprendre.'
    : 'Reconnectez le simulateur avant de reprendre ce vol.');
  else {
    const warning = String(status.warning || '');
    if (warning.includes('autre serveur Prométhée')) {
      resume.disabled = true;
      setText($('#recoveryHint'), 'Cet ancien état local ne correspond pas au serveur Prométhée actuel. Cliquez « Abandonner ce vol » pour l’archiver localement puis démarrez la nouvelle réservation.');
    } else {
      setText($('#recoveryHint'), 'Compte et simulateur disponibles. La reprise peut continuer sans recréer le vol.');
    }
  }
}
