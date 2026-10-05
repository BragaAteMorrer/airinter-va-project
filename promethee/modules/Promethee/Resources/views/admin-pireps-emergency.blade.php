@extends('promethee::layout')
@section('title','PIREP · remise à zéro urgence')

@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush

@section('content')
<div class="admin-workspace-page">
  <div class="ops-header compact">
    <div>
      <span class="eyebrow">OUTIL ADMINISTRATEUR · URGENCE</span>
      <h1>Remise à zéro d’un PIREP.</h1>
      <p>Supprime définitivement un PIREP bloqué afin que le pilote puisse recréer son opération. Utilisez cet outil uniquement pour un incident avéré.</p>
    </div>
    <a class="button outline" href="{{ route('admin.promethee.dashboard') }}">← Administration</a>
  </div>

  <section class="panel">
    <div class="notice error" style="margin-bottom:1rem">
      <strong>Action destructive.</strong>
      La suppression efface le PIREP et ses données associées. Un PIREP accepté est d’abord rejeté pour retirer ses heures/compteurs avant suppression. L’action est journalisée.
    </div>

    <form class="filters" method="get">
      <label style="min-width:280px">Pilote, ID PIREP, vol ou aéroport
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="IT199, IT749, LFPO, ID du PIREP…">
      </label>
      <label>État
        <select name="state">
          @foreach(['all'=>'Tous','pending'=>'En attente','accepted'=>'Accepté','rejected'=>'Rejeté','in_progress'=>'En cours','cancelled'=>'Annulé'] as $value=>$label)
            <option value="{{ $value }}" @selected(($filters['state'] ?? 'all') === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </label>
      <button class="button" type="submit">Rechercher</button>
      <a class="button outline" href="{{ route('admin.promethee.pireps-emergency') }}">Réinitialiser</a>
    </form>
  </section>

  @if($pireps->count())
    <div class="admin-master-detail"
         id="pirep-emergency-workspace"
         data-admin-master-detail
         data-workspace-key="pirep-emergency">
      <aside class="admin-master-pane" aria-label="PIREP trouvés">
        <div class="admin-master-toolbar">
          <label>Filtrer les résultats chargés
            <input type="search" data-master-filter placeholder="Pilote, vol, route…">
          </label>
        </div>

        <div class="admin-master-list" role="tablist" aria-orientation="vertical">
          @foreach($pireps as $pirep)
            <button type="button"
                    class="admin-master-row"
                    data-master-target="pirep-emergency-{{ $pirep->id }}"
                    data-master-search="{{ $pirep->id }} {{ $pirep->user?->pilot_id }} {{ $pirep->user?->name }} {{ $pirep->ident }} {{ $pirep->flight?->ident }} {{ $pirep->dpt_airport_id }} {{ $pirep->arr_airport_id }} {{ $pirep->aircraft?->registration }} {{ $pirep->state }}">
              <span class="admin-master-row-main">
                <strong>{{ $pirep->ident ?: ($pirep->flight?->ident ?? $pirep->id) }}</strong>
                <small>{{ $pirep->user?->pilot_id ?? '—' }} · {{ $pirep->dpt_airport_id ?: '—' }} → {{ $pirep->arr_airport_id ?: '—' }}</small>
              </span>
              <span class="tag">{{ strtoupper($pirep->state) }}</span>
            </button>
          @endforeach
          <div class="admin-master-empty" data-master-empty hidden>Aucun PIREP ne correspond au filtre local.</div>
        </div>
      </aside>

      <div class="admin-detail-pane">
        <button type="button" class="button outline admin-master-back" data-master-back>← Retour à la liste</button>

        @foreach($pireps as $pirep)
          <section class="admin-detail-panel" data-detail-panel="pirep-emergency-{{ $pirep->id }}" @if(!$loop->first) hidden @endif>
            <article class="panel">
              <div class="admin-detail-heading">
                <div>
                  <span class="eyebrow">PIREP · ACTION D’URGENCE</span>
                  <h2 data-detail-focus>{{ $pirep->ident ?: ($pirep->flight?->ident ?? $pirep->id) }}</h2>
                  <p>{{ $pirep->user?->pilot_id ?? '—' }} · {{ $pirep->user?->name ?? 'Pilote inconnu' }}</p>
                </div>
                <span class="tag">{{ strtoupper((string) $pirep->state) ?: '—' }} · {{ strtoupper((string) $pirep->status) ?: '—' }}</span>
              </div>

              <div class="admin-detail-metrics admin-workspace-spaced">
                <article><span>Départ</span><strong>{{ $pirep->dpt_airport_id ?: '—' }}</strong></article>
                <article><span>Arrivée</span><strong>{{ $pirep->arr_airport_id ?: '—' }}</strong></article>
                <article><span>Appareil</span><strong>{{ $pirep->aircraft?->registration ?? '—' }}</strong></article>
                <article><span>Source</span><strong>{{ $pirep->source_name ?: '—' }}</strong></article>
              </div>

              <div class="admin-workspace-spaced">
                <p><strong>ID PIREP :</strong> <code>{{ $pirep->id }}</code></p>
                <p><strong>Déposé :</strong> {{ optional($pirep->submitted_at ?? $pirep->created_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') }}</p>
              </div>

              <div class="notice error admin-workspace-spaced">
                <strong>Vérifiez une dernière fois le pilote, le vol et l’ID.</strong>
                Cette action est définitive et journalisée.
              </div>

              <form method="POST"
                    action="{{ route('admin.promethee.pireps-emergency.delete', $pirep->id) }}"
                    onsubmit="return confirm('SUPPRESSION DÉFINITIVE du PIREP {{ $pirep->id }} ? Vérifiez bien le pilote et le vol avant de continuer.');"
                    class="form-grid admin-workspace-spaced">
                @csrf
                @method('DELETE')
                <label>Confirmation
                  <input name="confirmation" required autocomplete="off" placeholder="Tapez SUPPRIMER">
                </label>
                <div class="admin-workspace-form-action">
                  <button class="button danger" type="submit">Supprimer en urgence</button>
                </div>
              </form>
            </article>
          </section>
        @endforeach
      </div>
    </div>
  @else
    <section class="panel"><p>Aucun PIREP ne correspond à la recherche.</p></section>
  @endif

  {{ $pireps->links('pagination::bootstrap-4') }}
</div>
@endsection

@push('scripts')
<script src="{{ asset('promethee-assets/promethee-admin-workspaces.js') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.js')) }}"></script>
@endpush
