@extends('promethee::layout')
@section('title','Affectations mensuelles')

@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush

@section('content')
<div class="admin-workspace-page">
  <div class="ops-header compact">
    <div>
      <span class="eyebrow">ADMINISTRATION</span>
      <h1>Affectations mensuelles.</h1>
      <p>Filtrez, sélectionnez et gérez les lignes sans perdre le contexte du pilote ou du vol.</p>
    </div>
    <span class="tag">{{ $assignments->count() }} affichée(s)</span>
  </div>

  <section class="panel">
    <form method="get" class="flight-filter">
      <label>Mois<input type="month" name="month" value="{{ $month }}"></label>
      <label class="filter-wide">Recherche<input name="q" value="{{ request('q') }}" placeholder="Pilote, numéro de vol, route ou aéroport"></label>
      <label>Pilote
        <select name="pilot">
          <option value="">Tous</option>
          @foreach($pilots as $pilot)
            <option value="{{ $pilot->id }}" @selected((string) request('pilot') === (string) $pilot->id)>{{ $pilot->pilot_id }} · {{ $pilot->name }}</option>
          @endforeach
        </select>
      </label>
      <label>Ligne
        <select name="route">
          <option value="">Toutes</option>
          @foreach($assignments->pluck('route_code')->filter()->unique()->sort() as $route)
            <option value="{{ $route }}" @selected(request('route') === $route)>{{ $route }}</option>
          @endforeach
        </select>
      </label>
      <button>Filtrer</button>
      <a class="button outline" href="{{ route('admin.promethee.assignments',['month'=>$month]) }}">Réinitialiser</a>
    </form>
  </section>

  <div class="admin-master-detail"
       id="assignments-workspace"
       data-admin-master-detail
       data-workspace-key="assignments-{{ $month }}"
       data-master-default="assignment-create">
    <aside class="admin-master-pane" aria-label="Affectations du mois">
      <div class="admin-master-toolbar">
        <label>Filtrer la liste affichée
          <input type="search" data-master-filter placeholder="Pilote, vol, aéroport…">
        </label>
      </div>
      <div class="admin-master-list" role="tablist" aria-orientation="vertical">
        <button type="button" class="admin-master-row" data-master-target="assignment-create" data-master-search="nouvelle affectation attribuer ligne objectif">
          <span class="admin-master-row-main"><strong>Nouvelle affectation</strong><small>Attribuer une ligne à un pilote</small></span>
          <span class="tag">+</span>
        </button>
        <button type="button" class="admin-master-row" data-master-target="assignment-bulk" data-master-search="gestion groupée sélection suppression multiple">
          <span class="admin-master-row-main"><strong>Gestion groupée</strong><small>Sélection et suppression multiple</small></span>
          <span class="tag">{{ $assignments->count() }}</span>
        </button>

        <div class="admin-master-section-label">Affectations</div>
        @forelse($assignments as $assignment)
          <button type="button"
                  class="admin-master-row"
                  data-master-target="assignment-{{ $assignment->id }}"
                  data-master-search="{{ $assignment->pilot_id }} {{ $assignment->user_name }} {{ $assignment->route_code }} {{ $assignment->flight_number }} {{ $assignment->dpt_airport_id }} {{ $assignment->arr_airport_id }}">
            <span class="admin-master-row-main">
              <strong>{{ $assignment->pilot_id }} · {{ $assignment->user_name }}</strong>
              <small>{{ $assignment->route_code ?: $assignment->flight_number }} · {{ $assignment->dpt_airport_id }} → {{ $assignment->arr_airport_id }}</small>
            </span>
          </button>
        @empty
          <div class="admin-master-empty">Aucune affectation pour ces filtres.</div>
        @endforelse
        <div class="admin-master-empty" data-master-empty hidden>Aucune affectation ne correspond.</div>
      </div>
    </aside>

    <div class="admin-detail-pane">
      <button type="button" class="button outline admin-master-back" data-master-back>← Retour à la liste</button>

      <section class="admin-detail-panel" data-detail-panel="assignment-create">
        <section class="panel">
          <div class="panel-heading">
            <div><span class="eyebrow">NOUVEL OBJECTIF</span><h2 data-detail-focus>Attribuer une ligne</h2></div>
          </div>
          <form method="post" action="{{ route('admin.promethee.assignments.save') }}" class="form-grid">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <label>Pilote
              <select name="user_id" required>
                @foreach($pilots as $pilot)
                  <option value="{{ $pilot->id }}">{{ $pilot->pilot_id }} · {{ $pilot->name }}</option>
                @endforeach
              </select>
            </label>
            <label>Vol
              <select name="flight_id" required>
                @foreach($flights as $flight)
                  <option value="{{ $flight->id }}">{{ $flight->route_code ?: $flight->flight_number }} · {{ $flight->dpt_airport_id }} → {{ $flight->arr_airport_id }}</option>
                @endforeach
              </select>
            </label>
            <label class="full">Note<textarea name="notes" rows="3"></textarea></label>
            <button>Attribuer</button>
          </form>
        </section>
      </section>

      <section class="admin-detail-panel" data-detail-panel="assignment-bulk" hidden>
        <form id="bulk-assignments" method="post" action="{{ route('admin.promethee.assignments.bulk-delete') }}" onsubmit="return confirm('Supprimer les affectations sélectionnées ?')">
          @csrf
          @method('DELETE')
          <section class="panel table-wrap admin-table-scroll">
            <div class="panel-heading">
              <div><span class="eyebrow">GESTION GROUPÉE</span><h2 data-detail-focus>Détail des lignes</h2></div>
              <button class="button outline" id="delete-selected" disabled>Supprimer la sélection</button>
            </div>
            <table>
              <thead><tr><th><input type="checkbox" id="select-all" aria-label="Sélectionner toutes les affectations"></th><th>Pilote</th><th>Vol</th><th>Départ → arrivée</th><th>Note</th></tr></thead>
              <tbody>
                @forelse($assignments as $assignment)
                  <tr>
                    <td><input class="assignment-select" type="checkbox" name="ids[]" value="{{ $assignment->id }}" aria-label="Sélectionner l’affectation de {{ $assignment->user_name }}"></td>
                    <td><strong>{{ $assignment->pilot_id }}</strong><br><small>{{ $assignment->user_name }}</small></td>
                    <td><strong>{{ $assignment->route_code ?: $assignment->flight_number }}</strong><br><small>{{ $assignment->flight_number }}</small></td>
                    <td>{{ $assignment->dpt_airport_id }} → {{ $assignment->arr_airport_id }}</td>
                    <td>{{ $assignment->notes ?: '—' }}</td>
                  </tr>
                @empty
                  <tr><td colspan="5">Aucune affectation ne correspond aux filtres.</td></tr>
                @endforelse
              </tbody>
            </table>
          </section>
        </form>
      </section>

      @foreach($assignments as $assignment)
        <section class="admin-detail-panel" data-detail-panel="assignment-{{ $assignment->id }}" hidden>
          <article class="panel">
            <div class="admin-detail-heading">
              <div>
                <span class="eyebrow">AFFECTATION</span>
                <h2 data-detail-focus>{{ $assignment->pilot_id }} · {{ $assignment->user_name }}</h2>
                <p>{{ $assignment->route_code ?: $assignment->flight_number }}</p>
              </div>
              <span class="tag">{{ $month }}</span>
            </div>

            <div class="admin-detail-metrics admin-workspace-spaced">
              <article><span>Vol</span><strong>{{ $assignment->flight_number ?: '—' }}</strong></article>
              <article><span>Départ</span><strong>{{ $assignment->dpt_airport_id ?: '—' }}</strong></article>
              <article><span>Arrivée</span><strong>{{ $assignment->arr_airport_id ?: '—' }}</strong></article>
            </div>

            <div class="admin-workspace-spaced">
              <span class="eyebrow">NOTE</span>
              <p>{{ $assignment->notes ?: 'Aucune note.' }}</p>
            </div>

            <form method="post" action="{{ route('admin.promethee.assignments.delete',$assignment->id) }}" onsubmit="return confirm('Supprimer cette affectation ?')" class="admin-workspace-spaced">
              @csrf
              @method('DELETE')
              <button class="button outline" type="submit">Supprimer l’affectation</button>
            </form>
          </article>
        </section>
      @endforeach
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('load', () => {
  const all = document.getElementById('select-all');
  const items = [...document.querySelectorAll('.assignment-select')];
  const button = document.getElementById('delete-selected');
  const sync = () => {
    const count = items.filter(item => item.checked).length;
    if (button) {
      button.disabled = !count;
      button.textContent = count ? `Supprimer la sélection (${count})` : 'Supprimer la sélection';
    }
    if (all) {
      all.checked = !!count && count === items.length;
      all.indeterminate = !!count && count < items.length;
    }
  };
  all?.addEventListener('change', () => {
    items.forEach(item => { item.checked = all.checked; });
    sync();
  });
  items.forEach(item => item.addEventListener('change', sync));
  sync();
});
</script>
<script src="{{ asset('promethee-assets/promethee-admin-workspaces.js') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.js')) }}"></script>
@endpush
