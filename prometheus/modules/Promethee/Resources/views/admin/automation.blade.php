@extends('promethee::layout')
@section('title','Automatisations')
@section('content')
<div class="ops-header compact">
  <div>
    <span class="eyebrow">BADGES & GRADES</span>
    <h1>Règles de progression.</h1>
    <p>Les règles actives sont appliquées automatiquement à la validation des vols et lors des changements de statistiques. Un contrôle de sécurité repasse toutes les 5 minutes pour les critères temporels.</p>
  </div>
  <span class="tag">AUTOMATIQUE · AUCUNE ACTION REQUISE</span>
</div>

@if(session('automation_preview'))
<section class="panel admin-preview-result" aria-live="polite">
  <span class="eyebrow">PRÉVISUALISATION</span>
  <h2>{{ session('automation_preview.count') }} pilote(s) éligible(s)</h2>
  <p>{{ implode(' · ', session('automation_preview.pilots')) ?: 'Aucun pilote ne correspond actuellement.' }}</p>
</section>
@endif

<div data-admin-workspace-root="automation">
  <div class="admin-task-tabs" role="tablist" aria-label="Tâches d’automatisation">
    <button type="button" role="tab" data-automation-workspace-tab="rules" aria-controls="automation-workspace-rules">
      <strong>Règles</strong>
      <span>Attribution et promotion automatiques</span>
    </button>
    <button type="button" role="tab" data-automation-workspace-tab="catalogue" aria-controls="automation-workspace-catalogue">
      <strong>Catalogue</strong>
      <span>Badges et grades disponibles</span>
    </button>
  </div>

  <section class="admin-workspace" id="automation-workspace-rules" data-automation-workspace-panel="rules">
    <aside class="panel admin-workspace-master">
      <div class="panel-heading">
        <div>
          <span class="eyebrow">MASTER</span>
          <h2>Type de règle</h2>
          <p>Choisissez la famille à configurer. Votre contexte est conservé lors de votre prochaine visite.</p>
        </div>
      </div>
      <div class="admin-master-list" role="tablist" aria-label="Familles de règles">
        <button type="button" class="admin-master-item" data-automation-detail="rules:badge" role="tab" aria-controls="automation-rules-badge">
          <span>
            <strong>Attribution de badges</strong>
            <small>{{ $badgeRules->where('active', true)->count() }} active(s) · {{ $badgeRules->count() }} enregistrée(s)</small>
          </span>
          <b>{{ $awards->count() }}</b>
        </button>
        <button type="button" class="admin-master-item" data-automation-detail="rules:rank" role="tab" aria-controls="automation-rules-rank">
          <span>
            <strong>Promotion de grades</strong>
            <small>{{ $rankRules->where('active', true)->count() }} active(s) · {{ $rankRules->count() }} enregistrée(s)</small>
          </span>
          <b>{{ $ranks->count() }}</b>
        </button>
      </div>
    </aside>

    <div class="admin-workspace-detail">
      <section class="panel" id="automation-rules-badge" data-automation-detail-panel="rules:badge">
        <div class="panel-heading">
          <div>
            <span class="eyebrow">BADGES</span>
            <h2>Règle d’attribution</h2>
            <p>Le badge sélectionné charge sa règle existante. Prévisualisez avant d’activer un changement important.</p>
          </div>
        </div>
        <form method="post" action="{{ route('admin.promethee.automation.badges.save') }}" class="form-grid rule-form" data-rule-kind="badge">
          @csrf
          <label>Badge
            <select name="award_id">
              @foreach($awards as $award)<option value="{{ $award->id }}">{{ $award->name }}</option>@endforeach
            </select>
          </label>
          @include('promethee::admin.rule-fields')
          <label><input type="checkbox" name="active" value="1"> Activer dès l’enregistrement</label>
          <div class="admin-detail-actions">
            <button>Enregistrer</button>
            <button type="submit" formaction="{{ route('admin.promethee.automation.preview') }}" class="button outline">Prévisualiser</button>
          </div>
        </form>

        <div class="admin-detail-summary">
          <span class="eyebrow">RÈGLES ENREGISTRÉES</span>
          <div class="route-list">
            @forelse($badgeRules as $rule)
              <div>
                <strong>{{ optional($awards->firstWhere('id',$rule->award_id))->name ?: 'Badge supprimé' }}</strong>
                <span>{{ strtoupper($rule->operator) }} · {{ $rule->active ? 'active' : 'brouillon' }} · {{ collect(json_decode($rule->criteria,true) ?: [])->pluck('metric')->join(', ') }}</span>
              </div>
            @empty
              <p class="muted">Aucune règle enregistrée.</p>
            @endforelse
          </div>
        </div>
      </section>

      <section class="panel" id="automation-rules-rank" data-automation-detail-panel="rules:rank" hidden>
        <div class="panel-heading">
          <div>
            <span class="eyebrow">GRADES</span>
            <h2>Règle de promotion</h2>
            <p>Configurez les critères de promotion et, si nécessaire, l’autorisation explicite de rétrogradation.</p>
          </div>
        </div>
        <form method="post" action="{{ route('admin.promethee.automation.ranks.save') }}" class="form-grid rule-form" data-rule-kind="rank">
          @csrf
          <label>Grade
            <select name="rank_id">
              @foreach($ranks as $rank)<option value="{{ $rank->id }}">{{ $rank->name }}</option>@endforeach
            </select>
          </label>
          @include('promethee::admin.rule-fields')
          <label><input type="checkbox" name="active" value="1"> Activer dès l’enregistrement</label>
          <label><input type="checkbox" name="allow_demotion" value="1"> Autoriser une rétrogradation</label>
          <div class="admin-detail-actions">
            <button>Enregistrer</button>
            <button type="submit" formaction="{{ route('admin.promethee.automation.preview') }}" class="button outline">Prévisualiser</button>
          </div>
        </form>

        <div class="admin-detail-summary">
          <span class="eyebrow">RÈGLES ENREGISTRÉES</span>
          <div class="route-list">
            @forelse($rankRules as $rule)
              <div>
                <strong>{{ optional($ranks->firstWhere('id',$rule->rank_id))->name ?: 'Grade supprimé' }}</strong>
                <span>{{ strtoupper($rule->operator) }} · {{ $rule->active ? 'active' : 'brouillon' }} · {{ collect(json_decode($rule->criteria,true) ?: [])->pluck('metric')->join(', ') }}</span>
              </div>
            @empty
              <p class="muted">Aucune règle enregistrée.</p>
            @endforelse
          </div>
        </div>
      </section>
    </div>
  </section>

  <section class="admin-workspace" id="automation-workspace-catalogue" data-automation-workspace-panel="catalogue" hidden>
    <aside class="panel admin-workspace-master">
      <div class="panel-heading">
        <div>
          <span class="eyebrow">MASTER</span>
          <h2>Catalogue</h2>
          <p>Créez une distinction ou sélectionnez la famille à maintenir sans empiler tous les formulaires.</p>
        </div>
      </div>
      <div class="admin-master-list" role="tablist" aria-label="Actions du catalogue">
        <button type="button" class="admin-master-item" data-automation-detail="catalogue:add-badge" role="tab" aria-controls="automation-catalogue-add-badge">
          <span><strong>Ajouter un badge</strong><small>Nouvelle distinction pilote</small></span><b>+</b>
        </button>
        <button type="button" class="admin-master-item" data-automation-detail="catalogue:add-rank" role="tab" aria-controls="automation-catalogue-add-rank">
          <span><strong>Ajouter un grade</strong><small>Nouveau niveau de carrière</small></span><b>+</b>
        </button>
        <button type="button" class="admin-master-item" data-automation-detail="catalogue:badges" role="tab" aria-controls="automation-catalogue-badges">
          <span><strong>Badges existants</strong><small>Modifier nom, image et description</small></span><b>{{ $awards->count() }}</b>
        </button>
        <button type="button" class="admin-master-item" data-automation-detail="catalogue:ranks" role="tab" aria-controls="automation-catalogue-ranks">
          <span><strong>Grades existants</strong><small>Modifier seuil, nom et image</small></span><b>{{ $ranks->count() }}</b>
        </button>
      </div>
    </aside>

    <div class="admin-workspace-detail">
      <section class="panel" id="automation-catalogue-add-badge" data-automation-detail-panel="catalogue:add-badge">
        <div class="panel-heading"><div><span class="eyebrow">CATALOGUE</span><h2>Ajouter un badge</h2><p>Créez la distinction avant de lui associer une règle automatique.</p></div></div>
        <form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.automation.awards.create') }}" class="form-grid">
          @csrf
          <label>Nom<input name="name" required></label>
          <label>Image URL (optionnel)<input name="image_url" type="url"></label>
          <label class="full">Ou envoyer une image<input name="image" type="file" accept="image/*"></label>
          <label class="full">Description<textarea name="description" rows="3"></textarea></label>
          <div class="admin-detail-actions"><button>Ajouter le badge</button></div>
        </form>
      </section>

      <section class="panel" id="automation-catalogue-add-rank" data-automation-detail-panel="catalogue:add-rank" hidden>
        <div class="panel-heading"><div><span class="eyebrow">CATALOGUE</span><h2>Ajouter un grade</h2><p>Le nombre d’heures reste un seuil de référence du catalogue, distinct des critères automatiques détaillés.</p></div></div>
        <form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.automation.ranks.create') }}" class="form-grid">
          @csrf
          <label>Nom<input name="name" required></label>
          <label>Heures de référence<input name="hours" type="number" min="0" value="0" required></label>
          <label>Image URL (optionnel)<input name="image_url" type="url"></label>
          <label>Ou envoyer une image<input name="image" type="file" accept="image/*"></label>
          <div class="admin-detail-actions"><button>Ajouter le grade</button></div>
        </form>
      </section>

      <section class="panel" id="automation-catalogue-badges" data-automation-detail-panel="catalogue:badges" hidden>
        <div class="panel-heading"><div><span class="eyebrow">CATALOGUE</span><h2>Badges existants</h2><p>Ouvrez uniquement la distinction à modifier.</p></div></div>
        <div class="admin-editor-list">
          @forelse($awards as $award)
          <details class="admin-editor-item">
            <summary><strong>{{ $award->name }}</strong><span>{{ $award->description ?: 'Aucune description' }}</span></summary>
            <form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.automation.awards.update',$award) }}" class="form-grid">
              @csrf @method('PUT')
              <label>Nom<input name="name" value="{{ $award->name }}" required></label>
              <label>Nouvelle URL d’image<input name="image_url" type="url" placeholder="{{ $award->image_url ?: 'Conserver l’image actuelle' }}"></label>
              <label class="full">Remplacer par une image<input name="image" type="file" accept="image/*"></label>
              <label class="full">Description<textarea name="description" rows="3">{{ $award->description }}</textarea></label>
              <div class="admin-detail-actions"><button>Enregistrer le badge</button></div>
            </form>
          </details>
          @empty
          <p class="empty">Aucun badge dans le catalogue.</p>
          @endforelse
        </div>
      </section>

      <section class="panel" id="automation-catalogue-ranks" data-automation-detail-panel="catalogue:ranks" hidden>
        <div class="panel-heading"><div><span class="eyebrow">CATALOGUE</span><h2>Grades existants</h2><p>Ouvrez uniquement le grade à modifier.</p></div></div>
        <div class="admin-editor-list">
          @forelse($ranks as $rank)
          <details class="admin-editor-item">
            <summary><strong>{{ $rank->name }}</strong><span>{{ $rank->hours }} h de référence</span></summary>
            <form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.automation.ranks.update',$rank) }}" class="form-grid">
              @csrf @method('PUT')
              <label>Nom<input name="name" value="{{ $rank->name }}" required></label>
              <label>Heures de référence<input name="hours" type="number" min="0" value="{{ $rank->hours }}" required></label>
              <label>Nouvelle URL d’image<input name="image_url" type="url" placeholder="{{ $rank->image_url ?: 'Conserver l’image actuelle' }}"></label>
              <label>Remplacer par une image<input name="image" type="file" accept="image/*"></label>
              <div class="admin-detail-actions"><button>Enregistrer le grade</button></div>
            </form>
          </details>
          @empty
          <p class="empty">Aucun grade dans le catalogue.</p>
          @endforelse
        </div>
      </section>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
window.prometheeAutomationRules={badge:@json($badgeRuleData),rank:@json($rankRuleData)};
window.prometheeAutomationCatalogue={badge:@json($awardData),rank:@json($rankData)};

document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-admin-workspace-root="automation"]');
  if (!root) return;

  const storage = {
    workspace: 'promethee-admin-automation-workspace',
    rules: 'promethee-admin-automation-rules-detail',
    catalogue: 'promethee-admin-automation-catalogue-detail'
  };
  const safeGet = (key, fallback) => {
    try { return localStorage.getItem(key) || fallback; } catch { return fallback; }
  };
  const safeSet = (key, value) => {
    try { localStorage.setItem(key, value); } catch {}
  };

  const workspaceTabs = [...root.querySelectorAll('[data-automation-workspace-tab]')];
  const workspacePanels = [...root.querySelectorAll('[data-automation-workspace-panel]')];

  const selectDetail = (workspace, requested, persist = true) => {
    const panel = root.querySelector(`[data-automation-workspace-panel="${workspace}"]`);
    if (!panel) return;
    const buttons = [...panel.querySelectorAll('[data-automation-detail]')];
    const details = [...panel.querySelectorAll('[data-automation-detail-panel]')];
    const fallback = buttons[0]?.dataset.automationDetail;
    const value = buttons.some(button => button.dataset.automationDetail === requested) ? requested : fallback;
    if (!value) return;

    buttons.forEach(button => {
      const selected = button.dataset.automationDetail === value;
      button.classList.toggle('selected', selected);
      button.setAttribute('aria-selected', String(selected));
      button.tabIndex = selected ? 0 : -1;
    });
    details.forEach(detail => { detail.hidden = detail.dataset.automationDetailPanel !== value; });
    if (persist) safeSet(storage[workspace], value);
  };

  const selectWorkspace = (requested, persist = true) => {
    const value = workspacePanels.some(panel => panel.dataset.automationWorkspacePanel === requested) ? requested : 'rules';
    workspaceTabs.forEach(button => {
      const selected = button.dataset.automationWorkspaceTab === value;
      button.classList.toggle('selected', selected);
      button.setAttribute('aria-selected', String(selected));
      button.tabIndex = selected ? 0 : -1;
    });
    workspacePanels.forEach(panel => { panel.hidden = panel.dataset.automationWorkspacePanel !== value; });
    selectDetail(value, safeGet(storage[value], value === 'rules' ? 'rules:badge' : 'catalogue:add-badge'), false);
    if (persist) safeSet(storage.workspace, value);
  };

  workspaceTabs.forEach(button => button.addEventListener('click', () => selectWorkspace(button.dataset.automationWorkspaceTab)));
  root.querySelectorAll('[data-automation-detail]').forEach(button => button.addEventListener('click', () => {
    const workspace = button.closest('[data-automation-workspace-panel]')?.dataset.automationWorkspacePanel;
    if (workspace) selectDetail(workspace, button.dataset.automationDetail);
  }));

  selectWorkspace(safeGet(storage.workspace, 'rules'), false);
});
</script>
@endpush
