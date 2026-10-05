@extends('promethee::layout')
@section('title','Automatisations')

@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush

@section('content')
<div class="admin-workspace-page">
  <div class="ops-header compact">
    <div>
      <span class="eyebrow">BADGES & GRADES</span>
      <h1>Règles de progression.</h1>
      <p>Les règles actives sont appliquées automatiquement à la validation des vols et lors des changements de statistiques. Un contrôle de sécurité repasse toutes les 5 minutes pour les critères temporels.</p>
    </div>
    <span class="tag">AUTOMATIQUE · AUCUNE ACTION REQUISE</span>
  </div>

  @if(session('automation_preview'))
    <section class="panel">
      <h2>{{ session('automation_preview.count') }} pilote(s) éligible(s)</h2>
      <p>{{ implode(' · ', session('automation_preview.pilots')) ?: 'Aucun pilote ne correspond actuellement.' }}</p>
    </section>
  @endif

  <div class="admin-master-detail"
       id="automation-workspace"
       data-admin-master-detail
       data-workspace-key="automation"
       data-master-default="badge-rules">
    <aside class="admin-master-pane" aria-label="Sections automatisation">
      <div class="admin-master-toolbar">
        <label>Rechercher une section
          <input type="search" data-master-filter placeholder="Badge, grade, catalogue…">
        </label>
      </div>
      <div class="admin-master-list" role="tablist" aria-orientation="vertical">
        <div class="admin-master-section-label">Règles</div>
        <button type="button" class="admin-master-row" data-master-target="badge-rules" data-master-search="badges attribution automatisation">
          <span class="admin-master-row-main">
            <strong>Badges</strong>
            <small>{{ $badgeRules->count() }} règle(s) configurée(s)</small>
          </span>
          <span class="tag">ATTRIBUTION</span>
        </button>
        <button type="button" class="admin-master-row" data-master-target="rank-rules" data-master-search="grades promotions demotions automatisation">
          <span class="admin-master-row-main">
            <strong>Grades</strong>
            <small>{{ $rankRules->count() }} règle(s) configurée(s)</small>
          </span>
          <span class="tag">PROMOTION</span>
        </button>

        <div class="admin-master-section-label">Catalogue</div>
        <button type="button" class="admin-master-row" data-master-target="catalogue" data-master-search="catalogue ajouter badge grade">
          <span class="admin-master-row-main">
            <strong>Ajouter</strong>
            <small>Nouveaux badges et grades</small>
          </span>
          <span class="tag">NOUVEAU</span>
        </button>
        <button type="button" class="admin-master-row" data-master-target="catalogue-edit" data-master-search="modifier catalogue badges grades">
          <span class="admin-master-row-main">
            <strong>Modifier</strong>
            <small>{{ $awards->count() }} badge(s) · {{ $ranks->count() }} grade(s)</small>
          </span>
          <span class="tag">ÉDITION</span>
        </button>
        <div class="admin-master-empty" data-master-empty hidden>Aucune section ne correspond.</div>
      </div>
    </aside>

    <div class="admin-detail-pane">
      <button type="button" class="button outline admin-master-back" data-master-back>← Retour à la liste</button>

      <section class="admin-detail-panel" data-detail-panel="badge-rules" id="badge-rules">
        <section class="panel admin-workspace-section">
          <div class="panel-heading">
            <div>
              <span class="eyebrow">BADGES</span>
              <h2 data-detail-focus>Règle d’attribution</h2>
              <p>Définissez les critères qui attribuent automatiquement un badge aux pilotes éligibles.</p>
            </div>
            <span class="tag">{{ $badgeRules->count() }} règle(s)</span>
          </div>
          <form method="post" action="{{ route('admin.promethee.automation.badges.save') }}" class="form-grid rule-form" data-rule-kind="badge">
            @csrf
            <label>Badge
              <select name="award_id">
                @foreach($awards as $award)
                  <option value="{{ $award->id }}">{{ $award->name }}</option>
                @endforeach
              </select>
            </label>
            @include('promethee::admin.rule-fields')
            <label><input type="checkbox" name="active" value="1"> Activer dès l’enregistrement</label>
            <div class="admin-workspace-form-action">
              <button>Enregistrer</button>
              <button type="submit" formaction="{{ route('admin.promethee.automation.preview') }}" class="button outline">Prévisualiser</button>
            </div>
          </form>

          <div class="route-list admin-workspace-spaced">
            @forelse($badgeRules as $rule)
              <div>
                <strong>{{ optional($awards->firstWhere('id',$rule->award_id))->name ?: 'Badge supprimé' }}</strong>
                <span>{{ strtoupper($rule->operator) }} · {{ $rule->active ? 'active' : 'brouillon' }} · {{ collect(json_decode($rule->criteria,true) ?: [])->pluck('metric')->join(', ') }}</span>
              </div>
            @empty
              <p class="muted">Aucune règle enregistrée.</p>
            @endforelse
          </div>
        </section>
      </section>

      <section class="admin-detail-panel" data-detail-panel="rank-rules" id="rank-rules" hidden>
        <section class="panel admin-workspace-section">
          <div class="panel-heading">
            <div>
              <span class="eyebrow">GRADES</span>
              <h2 data-detail-focus>Règle de promotion</h2>
              <p>Centralisez ici les promotions automatiques et, si nécessaire, les rétrogradations.</p>
            </div>
            <span class="tag">{{ $rankRules->count() }} règle(s)</span>
          </div>
          <form method="post" action="{{ route('admin.promethee.automation.ranks.save') }}" class="form-grid rule-form" data-rule-kind="rank">
            @csrf
            <label>Grade
              <select name="rank_id">
                @foreach($ranks as $rank)
                  <option value="{{ $rank->id }}">{{ $rank->name }}</option>
                @endforeach
              </select>
            </label>
            @include('promethee::admin.rule-fields')
            <label><input type="checkbox" name="active" value="1"> Activer dès l’enregistrement</label>
            <label><input type="checkbox" name="allow_demotion" value="1"> Autoriser une rétrogradation</label>
            <div class="admin-workspace-form-action">
              <button>Enregistrer</button>
              <button type="submit" formaction="{{ route('admin.promethee.automation.preview') }}" class="button outline">Prévisualiser</button>
            </div>
          </form>

          <div class="route-list admin-workspace-spaced">
            @forelse($rankRules as $rule)
              <div>
                <strong>{{ optional($ranks->firstWhere('id',$rule->rank_id))->name ?: 'Grade supprimé' }}</strong>
                <span>{{ strtoupper($rule->operator) }} · {{ $rule->active ? 'active' : 'brouillon' }} · {{ collect(json_decode($rule->criteria,true) ?: [])->pluck('metric')->join(', ') }}</span>
              </div>
            @empty
              <p class="muted">Aucune règle enregistrée.</p>
            @endforelse
          </div>
        </section>
      </section>

      <section class="admin-detail-panel" data-detail-panel="catalogue" id="catalogue" hidden>
        <div class="two-columns admin-workspace-grid">
          <section class="panel admin-workspace-section">
            <div class="panel-heading">
              <div><span class="eyebrow">CATALOGUE</span><h2 data-detail-focus>Ajouter un badge</h2></div>
            </div>
            <form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.automation.awards.create') }}" class="form-grid">
              @csrf
              <label>Nom<input name="name" required></label>
              <label>Image URL (optionnel)<input name="image_url" type="url"></label>
              <label class="full">Ou envoyer une image<input name="image" type="file" accept="image/*"></label>
              <label class="full">Description<textarea name="description" rows="3"></textarea></label>
              <button>Ajouter le badge</button>
            </form>
          </section>

          <section class="panel">
            <div class="panel-heading">
              <div><span class="eyebrow">CATALOGUE</span><h2>Ajouter un grade</h2></div>
            </div>
            <form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.automation.ranks.create') }}" class="form-grid">
              @csrf
              <label>Nom<input name="name" required></label>
              <label>Heures de référence<input name="hours" type="number" min="0" value="0" required></label>
              <label>Image URL (optionnel)<input name="image_url" type="url"></label>
              <label>Ou envoyer une image<input name="image" type="file" accept="image/*"></label>
              <button>Ajouter le grade</button>
            </form>
          </section>
        </div>
      </section>

      <section class="admin-detail-panel" data-detail-panel="catalogue-edit" id="catalogue-edit" hidden>
        <section class="panel admin-workspace-section">
          <div class="panel-heading">
            <div>
              <span class="eyebrow">CATALOGUE</span>
              <h2 data-detail-focus>Modifier une distinction</h2>
              <p>Les formulaires restent repliés pour garder une fiche lisible même avec un catalogue important.</p>
            </div>
          </div>

          <div class="two-columns admin-workspace-grid">
            <div>
              <h3>Badges</h3>
              @forelse($awards as $award)
                <details class="route-list">
                  <summary>{{ $award->name }} — modifier</summary>
                  <form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.automation.awards.update',$award) }}" class="form-grid">
                    @csrf
                    @method('PUT')
                    <label>Nom<input name="name" value="{{ $award->name }}" required></label>
                    <label>Nouvelle URL d’image<input name="image_url" type="url" placeholder="{{ $award->image_url ?: 'Conserver l’image actuelle' }}"></label>
                    <label class="full">Remplacer par une image<input name="image" type="file" accept="image/*"></label>
                    <label class="full">Description<textarea name="description" rows="3">{{ $award->description }}</textarea></label>
                    <button>Enregistrer le badge</button>
                  </form>
                </details>
              @empty
                <p class="muted">Aucun badge dans le catalogue.</p>
              @endforelse
            </div>

            <div>
              <h3>Grades</h3>
              @forelse($ranks as $rank)
                <details class="route-list">
                  <summary>{{ $rank->name }} — modifier</summary>
                  <form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.automation.ranks.update',$rank) }}" class="form-grid">
                    @csrf
                    @method('PUT')
                    <label>Nom<input name="name" value="{{ $rank->name }}" required></label>
                    <label>Heures de référence<input name="hours" type="number" min="0" value="{{ $rank->hours }}" required></label>
                    <label>Nouvelle URL d’image<input name="image_url" type="url" placeholder="{{ $rank->image_url ?: 'Conserver l’image actuelle' }}"></label>
                    <label>Remplacer par une image<input name="image" type="file" accept="image/*"></label>
                    <button>Enregistrer le grade</button>
                  </form>
                </details>
              @empty
                <p class="muted">Aucun grade dans le catalogue.</p>
              @endforelse
            </div>
          </div>
        </section>
      </section>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
window.prometheeAutomationRules={badge:@json($badgeRuleData),rank:@json($rankRuleData)};
window.prometheeAutomationCatalogue={badge:@json($awardData),rank:@json($rankData)};
</script>
<script src="{{ asset('promethee-assets/promethee-admin-workspaces.js') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.js')) }}"></script>
@endpush
