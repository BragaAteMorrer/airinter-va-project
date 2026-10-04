(() => {
  'use strict';

  const $ = selector => document.querySelector(selector);
  const text = (node, value) => { if (node) node.textContent = value ?? '—'; };
  const number = (value, digits = 0, suffix = '') =>
    value === null || value === undefined || !Number.isFinite(Number(value))
      ? '—'
      : Number(value).toFixed(digits) + suffix;

  function windLabel(metar = {}) {
    const wind = metar.wind || {};
    const direction = wind.variable
      ? 'VRB'
      : (wind.direction === null || wind.direction === undefined
          ? '—'
          : String(Math.round(Number(wind.direction))).padStart(3, '0') + '°');
    if (wind.speed_kt === null || wind.speed_kt === undefined) return direction;
    return direction + ' ' + number(wind.speed_kt, 0, ' kt')
      + (wind.gust_kt === null || wind.gust_kt === undefined ? '' : ' G' + number(wind.gust_kt, 0, ' kt'));
  }

  function stationCard(role, station) {
    const labels = { departure: 'DÉPART', arrival: 'DESTINATION', alternate: 'DÉGAGEMENT' };
    const article = document.createElement('article');
    article.className = 'weather-station-card';

    const heading = document.createElement('div');
    heading.className = 'weather-station-heading';
    const title = document.createElement('div');
    const eyebrow = document.createElement('span');
    eyebrow.className = 'kicker';
    eyebrow.textContent = labels[role] || role.toUpperCase();
    const name = document.createElement('strong');
    name.textContent = station?.icao || '—';
    title.append(eyebrow, name);
    const category = document.createElement('span');
    category.className = 'weather-category';
    category.textContent = station?.metar?.category || 'N/D';
    heading.append(title, category);
    article.append(heading);

    const metar = station?.metar || {};
    const runway = station?.recommended_runway || null;
    const metrics = document.createElement('div');
    metrics.className = 'weather-station-metrics';
    [
      ['Vent', windLabel(metar)],
      ['Visibilité', number(metar.visibility_km, 1, ' km')],
      ['Plafond', number(metar.ceiling_ft, 0, ' ft')],
      ['QNH', number(metar.qnh_hpa, 0, ' hPa')],
    ].forEach(([label, value]) => {
      const item = document.createElement('div');
      const key = document.createElement('span');
      const val = document.createElement('strong');
      key.textContent = label;
      val.textContent = value;
      item.append(key, val);
      metrics.append(item);
    });
    article.append(metrics);

    const runwayLine = document.createElement('p');
    runwayLine.className = 'weather-runway-line';
    runwayLine.textContent = runway
      ? `Piste probable RWY ${runway.ident} · face ${number(runway.headwind_kt, 1, ' kt')} · travers ${number(runway.crosswind_kt, 1, ' kt')}`
      : 'Piste probable non déterminée · vent calme/variable ou données piste absentes';
    article.append(runwayLine);

    if (metar.phenomena || metar.wind_shear_all_runways || (metar.wind_shear_runways || []).length) {
      const alert = document.createElement('p');
      alert.className = 'weather-station-alert';
      alert.textContent = [
        metar.phenomena,
        metar.wind_shear_all_runways ? 'WS toutes pistes' : null,
        (metar.wind_shear_runways || []).length ? 'WS piste signalé' : null,
      ].filter(Boolean).join(' · ');
      article.append(alert);
    }

    const details = document.createElement('details');
    const summary = document.createElement('summary');
    summary.textContent = 'METAR / TAF bruts';
    const metarRaw = document.createElement('pre');
    metarRaw.textContent = metar.raw || 'METAR indisponible';
    const tafRaw = document.createElement('pre');
    tafRaw.textContent = station?.taf?.raw || 'TAF indisponible';
    details.append(summary, metarRaw, tafRaw);
    article.append(details);

    return article;
  }

  function renderSigmets(weather) {
    const node = $('#weatherOpsSigmets');
    if (!node) return;
    node.replaceChildren();
    const sigmets = Array.isArray(weather?.sigmets) ? weather.sigmets : [];
    if (!sigmets.length) {
      const empty = document.createElement('p');
      empty.className = 'empty';
      empty.textContent = weather?.sigmet_status === 'AVAILABLE'
        ? 'Aucun SIGMET ne recoupe le corridor élargi.'
        : 'Flux SIGMET temporairement indisponible.';
      node.append(empty);
      return;
    }
    sigmets.forEach(sigmet => {
      const item = document.createElement('article');
      item.className = 'weather-sigmet';
      const title = document.createElement('strong');
      title.textContent = [sigmet.hazard || 'SIGMET', sigmet.fir].filter(Boolean).join(' · ');
      const validity = document.createElement('span');
      validity.textContent = [sigmet.valid_from || 'Validité ?', sigmet.valid_to || '—'].join(' → ');
      item.append(title, validity);
      if (sigmet.raw) {
        const raw = document.createElement('p');
        raw.textContent = sigmet.raw;
        item.append(raw);
      }
      node.append(item);
    });
  }

  function renderOperationalWeather(weather) {
    const section = $('#operationalWeather');
    if (!section) return;
    if (!weather || !weather.stations) {
      section.hidden = true;
      return;
    }

    section.hidden = false;
    text($('#weatherOpsState'), weather.status || 'DEGRADED');
    text($('#weatherWorst'), weather.summary?.worst_category || '—');
    text($('#weatherSigmetCount'), String(weather.summary?.sigmet_count ?? (weather.sigmets || []).length));
    text($('#weatherArrivalRunway'), weather.summary?.arrival_runway || '—');
    text($('#weatherOpsNote'), weather.note || 'Météo opérationnelle indicative.');

    const stations = $('#weatherOpsStations');
    stations?.replaceChildren();
    ['departure', 'arrival', 'alternate'].forEach(role => {
      const station = weather.stations?.[role];
      if (station && stations) stations.append(stationCard(role, station));
    });
    renderSigmets(weather);
  }

  function clearOperationalWeather() {
    const section = $('#operationalWeather');
    if (section) section.hidden = true;
  }

  function initializeOperationalWeather(refresh) {
    const button = $('#weatherRefreshBtn');
    if (!button) return;
    button.onclick = async () => {
      button.disabled = true;
      text($('#weatherOpsState'), 'ACTUALISATION…');
      try { await refresh?.(); }
      finally { button.disabled = false; }
    };
  }

  Object.assign(window, {
    renderOperationalWeather,
    clearOperationalWeather,
    initializeOperationalWeather,
  });
})();
