// Flight setup preferences: keep the spoken radio identity distinct from
// the ICAO/ATC callsign used by SimBrief and flight plans.
(() => {
  'use strict';

  const $ = selector => document.querySelector(selector);

  function initialize(settings) {
    const quick = $('#flightRadioCallsign');
    const saved = document.querySelector('#settingsForm [name="radioCallsign"]');
    if (!quick || !saved) return;

    quick.value = saved.value || settings?.radioCallsign || 'AIRINTER';
    saved.addEventListener('change', () => { quick.value = saved.value; });
    quick.addEventListener('change', () => {
      saved.value = quick.value;
      // A flight setup preference should take effect immediately without
      // navigating to Settings. Preserve all other local preferences.
      settings.radioCallsign = quick.value;
      try {
        const previous = JSON.parse(localStorage.prometheeAcarsSettings || '{}');
        localStorage.prometheeAcarsSettings = JSON.stringify({
          ...previous, radioCallsign: quick.value
        });
      } catch {
        localStorage.prometheeAcarsSettings = JSON.stringify(settings);
      }
    });
  }

  function radioCallsign(operation, briefing) {
    const preference = $('#flightRadioCallsign')?.value
      || document.querySelector('#settingsForm [name="radioCallsign"]')?.value
      || 'AIRINTER';
    const airline = String(briefing?.resolved?.airline?.name
      || operation?.flight?.airline?.name || operation?.airline?.name || '').toUpperCase();

    if (preference === 'STANDARD') {
      const flight = operation?.flight || operation || {};
      const ident = String(flight.ident || flight.flight_number || '').trim();
      return ident ? 'indicatif standard du vol ' + ident + ' (à confirmer)'
        : 'indicatif radio standard (à confirmer)';
    }
    if (/INTER\s*CARGO/.test(airline)) return 'INTERCARGO';
    if (/AIR\s*CHARTER/.test(airline)) return 'AIR CHARTER';
    if (/AIR\s*INTER/.test(airline)) return 'AIRINTER';
    return 'indicatif de la compagnie (à vérifier dans le FPL)';
  }

  window.HermesFlightSetup = { initialize, radioCallsign };
})();
