// Local FPL import is an editable route suggestion, never a filed OFP.
(() => {
  'use strict';
  const $ = selector => document.querySelector(selector);
  const fileInput = $('#planFile');
  const clearButton = $('#clearPlanBtn');
  if (!fileInput || !clearButton) return;
  let lastImport = null;

  const reference = () =>
    String(selectedOperation?.operation_id || selectedOperation?.bid_id || selectedOperation?.id || '');

  function clear({ restoreDraft = true } = {}) {
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
    updateWorkflow();
  }

  fileInput.onchange = async event => {
    const file = event.target.files?.[0];
    if (!file) return;
    try {
      if (file.size <= 0 || file.size > 256 * 1024)
        throw new Error('Le fichier est vide ou dépasse la limite de 256 Ko.');
      const operation = selectedOperation;
      const flight = operation && normalizeFlight(operation.flight || operation);
      if (!flight || !reference())
        throw new Error('Sélectionnez et réservez le vol avant d’importer un plan local.');
      if (flightPlan || operation.simbrief?.available)
        throw new Error('Un OFP SimBrief existe déjà pour cette opération. Utilisez sa route ou réinitialisez ce briefing avant d’importer un autre plan.');

      const parsed = window.HermesLocalPlan.verifyAirports(
        window.HermesLocalPlan.parse(await file.text(), file.name), flight);
      const form = $('#prefileForm');
      if (!form?.elements?.route) throw new Error('Le brouillon de vol est indisponible.');
      if (!parsed.route || parsed.route.length > 2000)
        throw new Error('La route de ce fichier ne peut pas être appliquée à Hermès.');

      const old = lastImport?.reference === reference() ? lastImport : null;
      const previousRoute = old ? old.previousRoute : form.elements.route.value;
      const previousLevel = old ? old.previousLevel : form.elements.level?.value || '';
      const importedLevel = parsed.level && !form.elements.level?.value
        ? String(parsed.level) : null;
      form.elements.route.value = parsed.route;
      if (importedLevel !== null && form.elements.level) form.elements.level.value = importedLevel;
      lastImport = {
        reference: reference(), previousRoute, previousLevel,
        importedRoute: parsed.route, importedLevel
      };
      renderNetworkPrefiles(null);
      const preview = $('#planBox');
      if (preview) {
        preview.hidden = false;
        preview.textContent = [
          file.name + ' · ' + parsed.format,
          parsed.departure + ' → ' + parsed.arrival,
          'Route : ' + parsed.route,
          parsed.waypointCount == null ? '' : 'Points lus : ' + parsed.waypointCount,
          parsed.notes || '',
          'Navigation uniquement : aucun OFP SimBrief n’a été enregistré.'
        ].filter(Boolean).join('\n');
      }
      showMessage('#simbriefState', 'Route importée dans le brouillon. Vérifiez-la, puis générez ou récupérez un OFP SimBrief pour préparer le PIREP.');
      updateWorkflow();
    } catch (error) {
      fileInput.value = '';
      showMessage('#simbriefState', 'Import du plan refusé : ' + (error?.message || 'format invalide'), true);
    }
  };

  clearButton.onclick = () => {
    clear();
    showMessage('#simbriefState', 'Plan local effacé. Préparez un OFP SimBrief pour poursuivre.');
  };
  $('#resetDraftBtn')?.addEventListener('click', () => clear({ restoreDraft: false }));

  window.HermesLocalPlanUi = { reset: () => clear({ restoreDraft: false }) };
})();
