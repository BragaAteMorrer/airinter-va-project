/* Link the current Hermès report to the canonical Prométhée replay. */
(() => {
  const ready = () => {
    const button = document.getElementById('openPirepReplayBtn');
    if (!button) return;
    button.addEventListener('click', async () => {
      const report = typeof pirepId !== 'undefined' && pirepId
        ? pirepId
        : (typeof lastFiledReview !== 'undefined' && (lastFiledReview?.pirepId || lastFiledReview?.PirepId))
          || (typeof lastStatus !== 'undefined' && (lastStatus?.flight?.pirepId || lastStatus?.flight?.PirepId))
          || null;
      if (!report) {
        showMessage('#reviewMessage', 'Aucun PIREP associé au vol. Préparez le rapport dans Hermès pour accéder au replay.', true);
        return;
      }
      button.disabled = true;
      try {
        await call('/api/open-pirep-replay', { pirepId: String(report) });
        showMessage('#reviewMessage', 'Replay ouvert dans votre navigateur. Connectez-vous à Prométhée si nécessaire.');
      } catch (error) {
        showMessage('#reviewMessage', 'Impossible d’ouvrir le replay : ' + friendlyError(error), true);
      } finally {
        button.disabled = false;
      }
    });
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready, {once:true});
  else ready();
})();