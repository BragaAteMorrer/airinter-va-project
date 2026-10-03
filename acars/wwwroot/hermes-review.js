/* Hermès Flight Review module.
   Loaded before app.js; rendering/scoring functions remain globally callable for compatibility. */
(() => {
function reviewValue(review, camel, pascal = camel) {
  return review?.[camel] ?? review?.[pascal] ?? null;
}

const reviewSvgNamespace = 'http://www.w3.org/2000/svg';

function normalizeReviewProfile(review) {
  return (reviewValue(review, 'profile', 'Profile') || [])
    .map((point, index) => {
      const recordedAt = point.recordedAt ?? point.RecordedAt ?? null;
      return {
        index,
        recordedAt,
        time: recordedAt ? Date.parse(recordedAt) : index,
        altitude: Number(point.altitude ?? point.Altitude),
        fuel: Number(point.fuel ?? point.Fuel),
        groundSpeed: Number(point.groundSpeed ?? point.GroundSpeed)
      };
    })
    .filter(point => Number.isFinite(point.altitude) && Number.isFinite(point.fuel));
}

function svgElement(name, attributes = {}) {
  const node = document.createElementNS(reviewSvgNamespace, name);
  Object.entries(attributes).forEach(([key, value]) => node.setAttribute(key, String(value)));
  return node;
}

function renderReviewSeries(selector, profile, key, label, baselineZero = false) {
  const svg = $(selector);
  if (!svg) return;
  svg.replaceChildren();

  const title = svgElement('title');
  title.textContent = label;
  svg.append(title);

  if (!profile.length) {
    const empty = svgElement('text', { x: 320, y: 92, 'text-anchor': 'middle', class: 'review-chart-empty' });
    empty.textContent = 'En attente de données de vol';
    svg.append(empty);
    return;
  }

  const width = 640;
  const height = 180;
  const padX = 12;
  const padY = 12;
  const values = profile.map(point => Number(point[key])).filter(Number.isFinite);
  if (!values.length) return;

  const minValue = Math.min(...values);
  const maxValue = Math.max(...values);
  const lower = baselineZero ? 0 : Math.max(0, minValue - Math.max(1, (maxValue - minValue) * .12));
  const upper = Math.max(lower + 1, maxValue + Math.max(1, (maxValue - lower) * .06));
  const times = profile.map(point => Number.isFinite(point.time) ? point.time : point.index);
  const minTime = Math.min(...times);
  const maxTime = Math.max(...times);
  const timeSpan = Math.max(1, maxTime - minTime);

  for (let step = 0; step <= 4; step += 1) {
    const y = padY + ((height - padY * 2) * step / 4);
    svg.append(svgElement('line', { x1: padX, y1: y, x2: width - padX, y2: y, class: 'review-chart-gridline' }));
  }

  const points = profile.map((point, index) => {
    const time = Number.isFinite(point.time) ? point.time : index;
    const x = padX + ((time - minTime) / timeSpan) * (width - padX * 2);
    const value = Math.min(upper, Math.max(lower, Number(point[key])));
    const y = height - padY - ((value - lower) / (upper - lower)) * (height - padY * 2);
    return [x, y];
  });

  const path = svgElement('path', {
    d: points.map(([x, y], index) => `${index ? 'L' : 'M'} ${x.toFixed(2)} ${y.toFixed(2)}`).join(' '),
    class: 'review-chart-path'
  });
  svg.append(path);
}

function renderReviewCharts(review) {
  const profile = normalizeReviewProfile(review);
  renderReviewSeries('#reviewAltitudeChart', profile, 'altitude', 'Profil d’altitude du vol', true);
  renderReviewSeries('#reviewFuelChart', profile, 'fuel', 'Évolution du carburant du vol');

  if (!profile.length) {
    setText($('#reviewAltitudeChartMeta'), 'En attente de données');
    setText($('#reviewFuelChartMeta'), 'En attente de données');
    return;
  }

  const altitudeMax = Math.max(...profile.map(point => point.altitude));
  const first = profile[0];
  const last = profile[profile.length - 1];
  const firstTime = Number.isFinite(first.time) ? first.time : 0;
  const lastTime = Number.isFinite(last.time) ? last.time : firstTime;
  const duration = Math.max(0, Math.round((lastTime - firstTime) / 60000));
  const fuelDelta = last.fuel - first.fuel;
  const signedFuel = (fuelDelta > 0 ? '+' : '') + Math.round(fuelDelta);

  setText($('#reviewAltitudeChartMeta'),
    'Max ' + Math.round(altitudeMax).toLocaleString('fr-FR') + ' ft · ' + profile.length + ' points · ' + duration + ' min');
  setText($('#reviewFuelChartMeta'),
    Math.round(first.fuel).toLocaleString('fr-FR') + ' → ' + Math.round(last.fuel).toLocaleString('fr-FR') + ' lb · Δ ' + signedFuel + ' lb');
}

function renderObservations(selector, entries, emptyText) {
  const node = $(selector);
  if (!node) return;
  node.replaceChildren();
  if (!entries?.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = emptyText;
    node.append(empty);
    return;
  }
  entries.forEach(entry => {
    const item = document.createElement('article');
    const severity = String(entry.severity ?? entry.Severity ?? 'info').toLowerCase();
    item.className = 'observation ' + severity;
    const code = entry.code ?? entry.Code ?? 'OBS';
    const message = entry.message ?? entry.Message ?? '';
    const occurredAt = entry.occurredAt ?? entry.OccurredAt;
    const phase = entry.phase ?? entry.Phase;
    const value = entry.value ?? entry.Value;
    const unit = entry.unit ?? entry.Unit ?? '';
    const meta = document.createElement('span');
    const time = occurredAt ? new Date(occurredAt).toLocaleTimeString('fr-FR', { timeZone: localSettings.timeFormat === 'utc' ? 'UTC' : undefined }) : null;
    meta.textContent = [time, phase, code].filter(Boolean).join(' · ');
    const title = document.createElement('strong');
    title.textContent = message || code;
    const detail = document.createElement('small');
    detail.textContent = value == null ? severity.toUpperCase() : (Number(value).toFixed(Number.isInteger(Number(value)) ? 0 : 1) + ' ' + unit).trim();
    item.append(meta, title, detail);
    node.append(item);
  });
}

function renderReviewContext(current) {
  const flight = normalizeFlight(selectedOperation?.flight || selectedOperation || {});
  const form = $('#prefileForm');
  const route = flightPlan?.route || form?.elements?.route?.value || flight.route || 'AUTO';
  const level = flightPlan?.level || normalizeFlightLevel(form?.elements?.level?.value || flight.level);
  const plannedFuel = Number(flightPlan?.block_fuel || form?.elements?.block_fuel?.value || 0);
  const source = flightPlan?.source || (selectedOperation?.simbrief?.available ? 'SimBrief lié' : 'Programme Air Inter');

  const ident = displayFlightIdent(flight);
  const cityPair = [flight.departure, flight.arrival].filter(Boolean).join(' → ');
  const aircraft = selectedAircraft?.registration || selectedAircraft?.name || '';
  setText($('#reviewOperationIdentity'), [ident, cityPair, aircraft].filter(Boolean).join(' · ') || 'Opération en cours');

  setText($('#reviewPlannedRoute'), route || 'AUTO');
  const plannedMeta = [
    level ? 'FL' + String(level).padStart(3, '0') : null,
    plannedFuel > 0 ? Math.round(plannedFuel) + ' lb block fuel' : null,
    source
  ].filter(Boolean).join(' · ');
  setText($('#reviewPlannedMeta'), plannedMeta || 'Planification non disponible');

  if (!current) {
    setText($('#reviewActualSummary'), 'En attente du vol');
    setText($('#reviewActualMeta'), 'Les données réalisées apparaîtront pendant l’enregistrement.');
    renderTimeline('#reviewTimeline', []);
    return;
  }

  const distance = Number(reviewValue(current,'distance','Distance') || 0);
  const block = Number(reviewValue(current,'blockMinutes','BlockMinutes') || 0);
  const airborne = Number(reviewValue(current,'airborneMinutes','AirborneMinutes') || 0);
  const fuel = Number(reviewValue(current,'fuelUsed','FuelUsed') || 0);
  setText($('#reviewActualSummary'), distance.toFixed(1) + ' NM · ' + block + ' min block');
  setText($('#reviewActualMeta'), airborne + ' min airborne · ' + Math.round(fuel) + ' lb utilisés');
  renderTimeline('#reviewTimeline', reviewValue(current,'timeline','Timeline') || []);
}

function renderCompanyScore(score = serverCompanyScore) {
  const value = $('#reviewCompanyScore');
  const meta = $('#reviewScoreMeta');
  const breakdown = $('#reviewScoreBreakdown');
  if (!value || !breakdown) return;

  breakdown.replaceChildren();
  if (!score?.available) {
    value.textContent = '— / 100';
    value.classList.remove('ready');
    if (meta) meta.textContent = score?.message || 'Le score sera calculé par Prométhée dès que les données de vol seront disponibles.';
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = 'Aucune pénalité calculée pour le moment.';
    breakdown.append(empty);
    return;
  }

  const numericScore = Number(score.score);
  const penalty = Number(score.penalty_total || 0);
  value.textContent = Number.isFinite(numericScore) ? Math.round(numericScore) + ' / 100' : '— / 100';
  value.classList.toggle('ready', Number.isFinite(numericScore) && numericScore >= 80);
  if (meta) meta.textContent = (score.persisted ? 'Score définitif du PIREP' : 'Prévisualisation avant dépôt')
    + ' · ' + penalty + ' point' + (penalty > 1 ? 's' : '') + ' retiré' + (penalty > 1 ? 's' : '')
    + ' · règles ' + (score.rules_source || 'VMSAcars') + '.';

  const items = Array.isArray(score.items) ? score.items : [];
  if (!items.length) {
    const empty = document.createElement('p');
    empty.className = 'empty';
    empty.textContent = penalty === 0 ? 'Aucun retrait de points.' : 'Détail des pénalités indisponible.';
    breakdown.append(empty);
  } else {
    items.forEach(item => {
      const row = document.createElement('article');
      const identity = document.createElement('div');
      const name = document.createElement('strong');
      name.textContent = item.name || item.rule_id || 'Règle VMSAcars';
      const detail = document.createElement('small');
      const count = Number(item.occurrences || 0);
      detail.textContent = (item.rule_id || '')
        + (count > 1 ? ' · ' + count + ' occurrences' : '')
        + (item.parameter != null ? ' · seuil ' + item.parameter : '');
      identity.append(name, detail);
      const deduction = document.createElement('strong');
      deduction.textContent = '−' + Number(item.deduction || 0) + ' pt';
      row.append(identity, deduction);
      breakdown.append(row);
    });
  }

  const unavailable = Array.isArray(score.unavailable_rules) ? score.unavailable_rules : [];
  if (unavailable.length && meta) {
    meta.textContent += ' ' + unavailable.length + ' règle' + (unavailable.length > 1 ? 's' : '')
      + ' historique' + (unavailable.length > 1 ? 's' : '') + ' non évaluée'
      + (unavailable.length > 1 ? 's' : '') + ' faute de télémétrie compatible.';
  }
}

async function refreshCompanyScore(force = false) {
  const activeFlight = lastStatus?.flight || lastStatus?.Flight || {};
  const operationRef = selectedOperation?.operation_id || selectedOperation?.id || selectedOperation?.bid_id
    || activeFlight.operationId || activeFlight.OperationId || null;
  if (!operationRef || !connected) return;

  const key = String(operationRef) + '|' + String(pirepId || activeFlight.pirepId || activeFlight.PirepId || '');
  if (!force && serverCompanyScoreKey === key) return;

  try {
    const debrief = unwrap(await call('/api/v1/operations/' + encodeURIComponent(operationRef) + '/debrief'));
    serverCompanyScore = debrief?.score || null;
    serverCompanyScoreKey = key;
    renderCompanyScore(serverCompanyScore);
  } catch (error) {
    if (force) showMessage('#reviewMessage', 'Score compagnie indisponible : ' + friendlyError(error), true);
  }
}

function renderReview(review) {
  const current = review || lastFiledReview;
  const state = $('#reviewState');
  if (!current) {
    renderReviewContext(null);
    renderReviewCharts(null);
    if (state) { state.textContent = 'AUCUN VOL'; state.classList.remove('ready'); }
    ['#reviewDistance','#reviewAirborne','#reviewBlock','#reviewFuel','#reviewLandingRate','#reviewMaxBank','#reviewFuelAdded','#reviewSimRate','#reviewPause'].forEach(id => setText($(id), '—'));
    setText($('#review1000'), 'NON OBSERVÉ');
    setText($('#review500'), 'NON OBSERVÉ');
    setText($('#reviewGoAround'), '0 remise de gaz');
    setText($('#reviewBounce'), '0 rebond');
    renderObservations('#fdmObservations', [], 'Aucune observation pour le moment.');
    renderObservations('#reviewIssues', [], 'Aucune anomalie détectée.');
    renderCompanyScore(null);
    if ($('#submitReviewBtn')) $('#submitReviewBtn').disabled = true;
    setText($('#reviewHint'), 'Le dépôt du PIREP devient disponible après l’événement IN.');
    return;
  }

  renderReviewContext(current);
  renderReviewCharts(current);
  const phase = String(reviewValue(current, 'phase', 'Phase') || '—');
  const ready = Boolean(reviewValue(current, 'readyToFile', 'ReadyToFile'));
  const filed = Boolean(lastFiledReview && !lastStatus?.review && !lastStatus?.Review);
  renderCompanyScore(serverCompanyScore);
  if (ready || filed) refreshCompanyScore(false);
  if (state) {
    state.textContent = filed ? 'PIREP DÉPOSÉ' : (ready ? 'READY TO FILE' : phase);
    state.classList.toggle('ready', ready || filed);
  }

  setText($('#reviewDistance'), Number(reviewValue(current,'distance','Distance') || 0).toFixed(1) + ' NM');
  setText($('#reviewAirborne'), Number(reviewValue(current,'airborneMinutes','AirborneMinutes') || 0) + ' min');
  setText($('#reviewBlock'), Number(reviewValue(current,'blockMinutes','BlockMinutes') || 0) + ' min');
  setText($('#reviewFuel'), Math.round(Number(reviewValue(current,'fuelUsed','FuelUsed') || 0)) + ' lb');
  const landing = reviewValue(current,'landingRate','LandingRate');
  setText($('#reviewLandingRate'), landing == null ? '—' : Math.round(Number(landing)) + ' ft/min');
  const maxBank = reviewValue(current,'maxBankDegrees','MaxBankDegrees');
  setText($('#reviewMaxBank'), maxBank == null ? '—' : Number(maxBank).toFixed(1) + '°');
  setText($('#reviewFuelAdded'), Math.round(Number(reviewValue(current,'fuelAdded','FuelAdded') || 0)) + ' lb');
  const simRate = reviewValue(current,'maxSimulationRate','MaxSimulationRate');
  setText($('#reviewSimRate'), simRate == null ? 'x1' : 'x' + Number(simRate).toFixed(2).replace(/\.00$/,''));
  const pauseCount = Number(reviewValue(current,'pauseCount','PauseCount') || 0);
  const pausedSeconds = Number(reviewValue(current,'pausedSeconds','PausedSeconds') || 0);
  const pausedMinutes = pausedSeconds >= 60 ? Math.floor(pausedSeconds / 60) + ' min ' + Math.round(pausedSeconds % 60) + ' s' : Math.round(pausedSeconds) + ' s';
  setText($('#reviewPause'), pauseCount > 0 ? pausedMinutes + ' · ' + pauseCount + ' pause' + (pauseCount > 1 ? 's' : '') : '0 s');

  setText($('#review1000'), reviewValue(current,'approach1000Status','Approach1000Status') || 'NON OBSERVÉ');
  setText($('#review500'), reviewValue(current,'approach500Status','Approach500Status') || 'NON OBSERVÉ');
  const goArounds = Number(reviewValue(current,'goAroundCount','GoAroundCount') || 0);
  const bounces = Number(reviewValue(current,'bounceCount','BounceCount') || 0);
  setText($('#reviewGoAround'), goArounds + ' remise' + (goArounds > 1 ? 's' : '') + ' de gaz');
  setText($('#reviewBounce'), bounces + ' rebond' + (bounces > 1 ? 's' : ''));

  renderObservations('#fdmObservations', reviewValue(current,'observations','Observations') || [], 'Aucune observation FDM.');
  renderObservations('#reviewIssues', reviewValue(current,'issues','Issues') || [], 'Aucune anomalie détectée.');

  const button = $('#submitReviewBtn');
  if (button) button.disabled = !ready || filed;
  const comment = $('#reviewComment');
  if (comment) comment.disabled = filed;
  setText($('#reviewHint'), ready
    ? 'Vol arrivé au parking. Vérifiez la synthèse puis déposez le PIREP.'
    : 'Flight Review en cours · phase ' + phase + '. Le dépôt sera disponible après IN.');
}

  Object.assign(window, {
    reviewValue,
    normalizeReviewProfile,
    svgElement,
    renderReviewSeries,
    renderReviewCharts,
    renderObservations,
    renderReviewContext,
    renderCompanyScore,
    refreshCompanyScore,
    renderReview
  });
})();
