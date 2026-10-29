// A local FPL supplies a navigation route; only an operation-linked SimBrief OFP
// can authorize a PIREP. The pilot must review the route before OFP generation.
(() => {
  'use strict';
  const $ = selector => document.querySelector(selector);
  const fileInput = $('#planFile');
  const clearButton = $('#clearPlanBtn');
  const confirmButton = $('#confirmLocalPlanBtn');
  if (!fileInput || !clearButton) return;
  let lastImport = null;
  let revision = 0;

  const reference = () =>
    String(selectedOperation?.operation_id || selectedOperation?.bid_id || selectedOperation?.id || '');

  function refreshConfirmation() {
    if (!confirmButton) return;
    confirmButton.hidden = !lastImport;
    confirmButton.disabled = !lastImport || lastImport.confirmed;
    confirmButton.textContent = lastImport?.confirmed
      ? 'Route vérifiée ✓' : 'Valider cette route pour SimBrief';
  }

  function clear({ restoreDraft = true } = {}) {
    revision++;
    const form = $('#prefileForm');
    if (restoreDraft && lastImport && lastImport.reference === reference() && form) {
      const route = form.elements.route;
      const level = form.elements.level;
      if (route?.value === lastImport.importedRoute)
        route.value = lastImport.previousRoute;
      if (level?.value === lastImport.importedLevel && lastImport.importedLevel !== null)
        level.value = lastImport.previousLevel;
    }
    lastImport = null;
    fileInput.value = '';
    const preview = $('#planBox');
    if (preview) {
      preview.hidden = true;
      preview.textContent = 'Aucun plan chargé.';
    }
    refreshConfirmation();
    updateWorkflow();
  }

  function assertReady() {
    if (!lastImport) return true;
    if (lastImport.reference !== reference())
      throw new Error('Le vol a changé depuis l’import du plan. Réimportez le fichier.');
    const route = $('#prefileForm')?.elements?.route?.value.trim() || '';
    if (!lastImport.confirmed || route !== lastImport.confirmedRoute) {
      lastImport.confirmed = false;
      refreshConfirmation();
      throw new Error('Vérifiez la route du plan local puis cliquez « Valider cette route pour SimBrief » avant de générer un OFP.');
    }
    return true;
  }

  fileInput.onchange = async event => {
    const file = event.target.files?.[0];
    if (!file) return;
    const ticket = ++revision;
    const selectedRef = reference();
    try {
      if (file.size <= 0 || file.size > 256 * 1024)
        throw new Error('Le fichier est vide ou dépasse la limite de 256 Ko.');
      const operation = selectedOperation;
      const flight = operation && normalizeFlight(operation.flight || operation);
      if (!flight || !selectedRef)
        throw new Error('Sélectionnez et réservez le vol avant d’importer un plan local.');
      if (flightPlan || operation.simbrief?.available)
        throw new Error('Un OFP SimBrief existe déjà pour cette opération. Utilisez sa route avant d’importer un autre plan.');

      const raw = await file.text();
      // A slow file read must never overwrite a different flight or newer import.
      if (ticket !== revision || selectedRef !== reference()) return;
      const parsed = window.HermesLocalPlan.verifyAirports(
        window.HermesLocalPlan.parse(raw, file.name), flight);
      const form = $('#prefileForm');
      if (!form?.elements?.route) throw new Error('Le brouillon de vol est indisponible.');
      if (!parsed.route || parsed.route.length > 2000)
        throw new Error('La route de ce fichier ne peut pas être appliquée à Hermès.');

      const old = lastImport?.reference === selectedRef ? lastImport : null;
      const previousRoute = old ? old.previousRoute : form.elements.route.value;
      const previousLevel = old ? old.previousLevel : form.elements.level?.value || '';
      const importedLevel = parsed.level && !form.elements.level?.value
        ? String(parsed.level) : null;

      form.elements.route.value = parsed.route;
      if (importedLevel !== null && form.elements.level) form.elements.level.value = importedLevel;
      lastImport = {
        reference: selectedRef, previousRoute, previousLevel,
        importedRoute: parsed.route, importedLevel,
        confirmed: false, confirmedRoute: null
      };
      refreshConfirmation();
      renderNetworkPrefiles(null);
      scheduleEfbContextSync();
      const preview = $('#planBox');
      if (preview) {
        preview.hidden = false;
        preview.textContent = [
          file.name + ' · ' + parsed.format,
          parsed.departure + ' → ' + parsed.arrival,
          'Route proposée : ' + parsed.route,
          parsed.waypointCount == null ? '' : 'Points lus : ' + parsed.waypointCount,
          parsed.notes || '',
          'Route à valider. Aucun OFP SimBrief n’a été enregistré.'
        ].filter(Boolean).join('\n');
      }
      showMessage('#simbriefState',
        'Route importée dans le brouillon. Vérifiez les points puis validez la route avant de préparer SimBrief.');
      updateWorkflow();
    } catch (error) {
      if (ticket !== revision) return;
      fileInput.value = '';
      showMessage('#simbriefState', 'Import du plan refusé : ' + (error?.message || 'format invalide'), true);
    }
  };

  $('#prefileForm')?.elements?.route?.addEventListener('input', () => {
    if (!lastImport) return;
    lastImport.confirmed = false;
    refreshConfirmation();
    scheduleEfbContextSync();
  });

  if (confirmButton) confirmButton.onclick = () => {
    if (!lastImport || lastImport.reference !== reference()) return;
    const value = $('#prefileForm')?.elements?.route?.value.trim() || '';
    if (!value || value.length > 2000) {
      showMessage('#simbriefState', 'Corrigez la route avant de la valider.', true);
      return;
    }
    lastImport.confirmed = true;
    lastImport.confirmedRoute = value;
    refreshConfirmation();
    const preview = $('#planBox');
    if (preview) preview.textContent = preview.textContent.replace(
      /^Route (?:proposée|validée) : .*$/m, 'Route validée : ' + value);
    scheduleEfbContextSync();
    const mode = recommendedPlanMode();
    setPlanMode(mode);
    showMessage('#simbriefState', 'Route vérifiée et transmise à la prochaine préparation SimBrief. Générez puis importez l’OFP avant le PIREP.');
  };

  clearButton.onclick = () => {
    clear();
    showMessage('#simbriefState', 'Plan local effacé. Préparez un OFP SimBrief pour poursuivre.');
  };
  $('#resetDraftBtn')?.addEventListener('click', () => clear({ restoreDraft: false }));

  window.HermesLocalPlanUi = {
    reset: () => clear({ restoreDraft: false }),
    complete: () => clear({ restoreDraft: false }),
    assertReady
  };
})();
