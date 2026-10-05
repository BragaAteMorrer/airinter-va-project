@extends('promethee::layout')
@section('title','Missions et circuits')

@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush

@section('content')
<div class="admin-workspace-page">
  <div class="ops-header compact">
    <div>
      <span class="eyebrow">ADMINISTRATION</span>
      <h1>Missions et circuits.</h1>
      <p>Les validations sont calculées sur les PIREP acceptés ; aucune validation manuelle n’est nécessaire.</p>
    </div>
    <span class="tag">{{ $missions->count() }} mission(s)</span>
  </div>

  <div class="admin-master-detail"
       id="missions-workspace"
       data-admin-master-detail
       data-workspace-key="missions"
       data-master-default="mission-create">
    <aside class="admin-master-pane" aria-label="Missions et circuits">
      <div class="admin-master-toolbar">
        <label>Rechercher une mission
          <input type="search" data-master-filter placeholder="Titre, LFPO, LFLL…">
        </label>
      </div>

      <div class="admin-master-list" role="tablist" aria-orientation="vertical">
        <div class="admin-master-section-label">Créer</div>
        <button type="button" class="admin-master-row" data-master-target="mission-create" data-master-search="nouvelle mission creer publier">
          <span class="admin-master-row-main">
            <strong>Nouvelle mission</strong>
            <small>Créer une étape simple</small>
          </span>
          <span class="tag">+</span>
        </button>
        <button type="button" class="admin-master-row" data-master-target="circuit-create" data-master-search="nouveau circuit creer etapes">
          <span class="admin-master-row-main">
            <strong>Nouveau circuit</strong>
            <small>Créer un parcours multi-étapes</small>
          </span>
          <span class="tag">+</span>
        </button>

        <div class="admin-master-section-label">Missions publiées</div>
        @forelse($missions as $mission)
          <button type="button"
                  class="admin-master-row"
                  data-master-target="mission-{{ $mission->id }}"
                  data-master-search="{{ $mission->title }} {{ $mission->dpt_airport_id }} {{ $mission->arr_airport_id }}">
            <span class="admin-master-row-main">
              <strong>{{ $mission->title }}</strong>
              <small>{{ $mission->dpt_airport_id ?: '—' }} → {{ $mission->arr_airport_id ?: '—' }}</small>
            </span>
            @if($mission->active)
              <span class="tag">ACTIVE</span>
            @else
              <span class="tag">INACTIVE</span>
            @endif
          </button>
        @empty
          <div class="admin-master-empty">Aucune mission publiée.</div>
        @endforelse

        <div class="admin-master-empty" data-master-empty hidden>Aucune mission ne correspond à la recherche.</div>
      </div>
    </aside>

    <div class="admin-detail-pane">
      <button type="button" class="button outline admin-master-back" data-master-back>← Retour à la liste</button>

      <section class="admin-detail-panel" data-detail-panel="mission-create">
        <section class="panel">
          <div class="panel-heading">
            <div>
              <span class="eyebrow">NOUVELLE MISSION</span>
              <h2 data-detail-focus>Une étape</h2>
              <p>Publiez une mission simple avec un départ, une arrivée et une période éventuelle.</p>
            </div>
          </div>
          <form method="post" action="{{ route('admin.promethee.missions.save') }}" class="form-grid">
            @csrf
            <label>Titre<input name="title" required maxlength="160"></label>
            <label>Départ<input name="dpt_airport_id" maxlength="8"></label>
            <label>Arrivée<input name="arr_airport_id" maxlength="8"></label>
            <label>Début<input type="date" name="starts_on"></label>
            <label>Fin<input type="date" name="ends_on"></label>
            <label class="full">Description<textarea name="description" rows="4"></textarea></label>
            <label><input type="checkbox" name="active" value="1" checked> Active</label>
            <button>Publier la mission</button>
          </form>
        </section>
      </section>

      <section class="admin-detail-panel" data-detail-panel="circuit-create" hidden>
        <section class="panel">
          <div class="panel-heading">
            <div>
              <span class="eyebrow">NOUVEAU CIRCUIT</span>
              <h2 data-detail-focus>Étapes</h2>
              <p>Chaque ligne représente une étape du circuit au format départ, arrivée.</p>
            </div>
          </div>
          <form method="post" action="{{ route('admin.promethee.circuits.save') }}" class="form-grid" data-circuit-form>
            @csrf
            <label>Titre<input name="title" required maxlength="160"></label>
            <label>Début<input type="date" name="starts_on"></label>
            <label>Fin<input type="date" name="ends_on"></label>
            <label class="full">Description<textarea name="description" rows="3"></textarea></label>
            <label class="full">Étapes (une par ligne : ICAO départ, ICAO arrivée)
              <textarea name="legs_text" id="legs-text" rows="7" required placeholder="LFPO, LFML&#10;LFML, LFKJ"></textarea>
            </label>
            <label><input type="checkbox" name="active" value="1" checked> Actif</label>
            <button>Publier le circuit</button>
          </form>
        </section>
      </section>

      @foreach($missions as $mission)
        <section class="admin-detail-panel" data-detail-panel="mission-{{ $mission->id }}" hidden>
          <article class="panel">
            <div class="admin-detail-heading">
              <div>
                <span class="eyebrow">MISSION PUBLIÉE</span>
                <h2 data-detail-focus>{{ $mission->title }}</h2>
                <p>{{ $mission->dpt_airport_id ?: 'Départ non défini' }} → {{ $mission->arr_airport_id ?: 'Arrivée non définie' }}</p>
              </div>
              <span class="tag">{{ $mission->active ? 'ACTIVE' : 'INACTIVE' }}</span>
            </div>

            <div class="admin-detail-metrics admin-workspace-spaced">
              <article>
                <span>Départ</span>
                <strong>{{ $mission->dpt_airport_id ?: '—' }}</strong>
              </article>
              <article>
                <span>Arrivée</span>
                <strong>{{ $mission->arr_airport_id ?: '—' }}</strong>
              </article>
              <article>
                <span>Début</span>
                <strong>{{ $mission->starts_on ?: '—' }}</strong>
              </article>
              <article>
                <span>Fin</span>
                <strong>{{ $mission->ends_on ?: '—' }}</strong>
              </article>
            </div>

            @if($mission->description)
              <div class="admin-workspace-spaced">
                <span class="eyebrow">DESCRIPTION</span>
                <p>{{ $mission->description }}</p>
              </div>
            @endif

            <div class="admin-workspace-form-action admin-workspace-spaced">
              <form method="post" action="{{ route('admin.promethee.missions.delete',$mission->id) }}" onsubmit="return confirm('Supprimer cette mission ?');">
                @csrf
                @method('DELETE')
                <button class="button outline" type="submit">Supprimer la mission</button>
              </form>
            </div>
          </article>
        </section>
      @endforeach
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-circuit-form]').forEach(form => {
  form.addEventListener('submit', () => {
    const textarea = form.querySelector('#legs-text');
    if (!textarea) return;
    textarea.value.trim().split(/\n+/).forEach((line, index) => {
      const [departure, arrival] = line.split(',').map(value => value.trim());
      for (const [key, value] of [['departure', departure], ['arrival', arrival]]) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `legs[${index}][${key}]`;
        input.value = value || '';
        form.append(input);
      }
    });
  });
});
</script>
<script src="{{ asset('promethee-assets/promethee-admin-workspaces.js') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.js')) }}"></script>
@endpush
