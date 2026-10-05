@extends('promethee::layout')
@section('title','Critères tarifaires ITF')

@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush

@section('content')
<div class="page-heading">
  <div>
    <span class="eyebrow">DIRECTION COMMERCIALE · AIR INTER</span>
    <h1>Critères tarifaires ITF.</h1>
    <p>Classez les lignes Air Inter entre réseau principal et diagonales régionales, puis utilisez ces groupes dans l’éditeur de tarifs.</p>
  </div>
  <div>
    <a class="button outline" href="{{ route('admin.promethee.economy') }}">Économie</a>
    <a class="button outline" href="{{ route('admin.promethee.bbr') }}">Bleu-Blanc-Rouge</a>
  </div>
</div>

@if(!$itf)
<section class="panel"><div class="notice error">La compagnie ITF n’a pas été trouvée.</div></section>
@else
<section class="control-strip">
  <article><span>Lignes principales</span><strong>{{ $counts['principal'] }}</strong><small>réseau structurant</small></article>
  <article><span>Diagonales</span><strong>{{ $counts['diagonal'] }}</strong><small>liaisons régionales transversales</small></article>
  <article><span>À classer</span><strong>{{ $counts['unclassified'] }}</strong><small>aucun critère défini</small></article>
  <article><span>Périodes</span><strong>{{ $seasons->count() }}</strong><small>saisons tarifaires disponibles</small></article>
</section>


<div class="admin-master-detail"
     id="pricing-criteria-workspace"
     data-admin-master-detail
     data-workspace-key="pricing-criteria"
     data-master-default="pricing-network">
  <aside class="admin-master-pane" aria-label="Gestion tarifaire">
    <div class="admin-master-toolbar">
      <label>Rechercher une section
        <input type="search" data-master-filter placeholder="Réseau, saisons…">
      </label>
    </div>
    <div class="admin-master-list" role="tablist" aria-orientation="vertical">
      <button type="button" class="admin-master-row" data-master-target="pricing-network" data-master-search="typologie réseau principales diagonales lignes classement">
        <span class="admin-master-row-main">
          <strong>Typologie du réseau</strong>
          <small>{{ $counts['principal'] }} principale(s) · {{ $counts['diagonal'] }} diagonale(s)</small>
        </span>
        @if($counts['unclassified'] > 0)
          <span class="tag">{{ $counts['unclassified'] }} à classer</span>
        @endif
      </button>
      <button type="button" class="admin-master-row" data-master-target="pricing-seasons" data-master-search="saisons périodes année tarifs">
        <span class="admin-master-row-main">
          <strong>Saisons tarifaires</strong>
          <small>{{ $seasons->count() }} période(s) disponible(s)</small>
        </span>
        <span class="tag">PÉRIODES</span>
      </button>
      <button type="button" class="admin-master-row" data-master-target="pricing-evolution" data-master-search="évolution décote diagonales règles futures">
        <span class="admin-master-row-main">
          <strong>Évolution des règles</strong>
          <small>Décote et automatisations prévues</small>
        </span>
      </button>
      <div class="admin-master-empty" data-master-empty hidden>Aucune section ne correspond.</div>
    </div>
  </aside>

  <div class="admin-detail-pane">
    <button type="button" class="button outline admin-master-back" data-master-back>← Retour à la liste</button>

    <div class="admin-detail-panel" data-detail-panel="pricing-network">
      <section class="panel">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">TYPOLOGIE DU RÉSEAU</span>
      <h2>Principales / diagonales régionales</h2>
      <p>Cette classification ne modifie aucun vol. Elle sert uniquement de critère de gestion tarifaire.</p>
    </div>
  </div>

  <form method="post" action="{{ route('admin.promethee.pricing-criteria.save') }}" id="pricing-criteria-form">
    @csrf

    <div class="form-grid" style="margin-bottom:16px">
      <label class="full">Rechercher une ligne
        <input type="search" id="criteria-search" placeholder="N° de vol, aéroport, ville…" autocomplete="off">
      </label>
      <label>Départ
        <input type="search" id="criteria-departure" list="criteria-departure-airports" placeholder="Ville, nom ou code ICAO" autocomplete="off">
        <datalist id="criteria-departure-airports">
          @foreach($flights->map(fn($flight) => $flight->dpt_airport)->filter()->unique('id')->sortBy('icao') as $airport)
            <option value="{{ $airport->icao }}" label="{{ $airport->location ?: $airport->name }} · {{ $airport->name }}"></option>
          @endforeach
        </datalist>
      </label>
      <label>Arrivée
        <input type="search" id="criteria-arrival" list="criteria-arrival-airports" placeholder="Ville, nom ou code ICAO" autocomplete="off">
        <datalist id="criteria-arrival-airports">
          @foreach($flights->map(fn($flight) => $flight->arr_airport)->filter()->unique('id')->sortBy('icao') as $airport)
            <option value="{{ $airport->icao }}" label="{{ $airport->location ?: $airport->name }} · {{ $airport->name }}"></option>
          @endforeach
        </datalist>
      </label>
      <label>Classement actuel
        <select id="criteria-class">
          <option value="">Tous les classements</option>
          <option value="principal">Lignes principales</option>
          <option value="diagonal">Diagonales régionales</option>
          <option value="unclassified">À classer</option>
        </select>
      </label>
      <div style="align-self:end">
        <button type="button" class="button outline" id="criteria-reset-filters">Réinitialiser les filtres</button>
      </div>
    </div>

    <div class="panel-heading" style="margin-bottom:12px">
      <div>
        <strong id="criteria-result-count">{{ $flights->count() }} ligne(s) affichée(s)</strong>
        <p><span id="criteria-selected-count">0</span> ligne(s) sélectionnée(s) sur {{ $flights->count() }} au total.</p>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button type="button" class="button outline" id="criteria-select-visible">Sélectionner les lignes affichées</button>
        <button type="button" class="button outline" id="criteria-select-everything">Tout sélectionner</button>
        <button type="button" class="button outline" id="criteria-unselect-all">Tout désélectionner</button>
      </div>
    </div>

    <div class="form-grid">
      <label>Classer les lignes sélectionnées
        <select name="network_class" required>
          <option value="principal">Lignes principales</option>
          <option value="diagonal">Diagonales régionales</option>
          <option value="unclassified">Retirer le classement</option>
        </select>
      </label>
      <div style="align-self:end"><button type="submit">Appliquer le classement</button></div>
    </div>

    <div class="table-wrap">
      <table id="criteria-table">
        <thead><tr><th><input type="checkbox" id="criteria-select-all" aria-label="Sélectionner toutes les lignes affichées"></th><th>Vol</th><th>Départ</th><th>Arrivée</th><th>Classement</th></tr></thead>
        <tbody>
        @forelse($flights as $flight)
          @php
            $departureLabel = trim($flight->dpt_airport_id.' '.($flight->dpt_airport?->location ?: $flight->dpt_airport?->name));
            $arrivalLabel = trim($flight->arr_airport_id.' '.($flight->arr_airport?->location ?: $flight->arr_airport?->name));
          @endphp
          <tr class="criteria-row"
              data-search="{{ strtolower($flight->ident.' '.$departureLabel.' '.$arrivalLabel) }}"
              data-departure="{{ strtolower($departureLabel.' '.$flight->dpt_airport?->name) }}"
              data-arrival="{{ strtolower($arrivalLabel.' '.$flight->arr_airport?->name) }}"
              data-class="{{ $flight->pricing_network_class }}">
            <td><input class="criteria-flight-checkbox" type="checkbox" name="flight_ids[]" value="{{ $flight->id }}"></td>
            <td><strong>{{ $flight->ident }}</strong></td>
            <td>{{ $flight->dpt_airport_id }} · {{ $flight->dpt_airport?->location ?: $flight->dpt_airport?->name }}</td>
            <td>{{ $flight->arr_airport_id }} · {{ $flight->arr_airport?->location ?: $flight->arr_airport?->name }}</td>
            <td>
              @if($flight->pricing_network_class === 'principal')
                <span class="tag">PRINCIPALE</span>
              @elseif($flight->pricing_network_class === 'diagonal')
                <span class="tag">DIAGONALE</span>
              @else
                <span class="muted">À classer</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="5">Aucune ligne ITF active.</td></tr>
        @endforelse
        <tr id="criteria-no-results" style="display:none"><td colspan="5">Aucune ligne ne correspond aux filtres.</td></tr>
        </tbody>
      </table>
    </div>
  </form>

  <div class="panel-heading" style="margin-top:20px">
    <div><strong>Actions tarifaires rapides</strong><p>Ouvre l’économie directement filtrée sur le groupe choisi. Tu peux ensuite « Tout sélectionner les résultats » et changer les tarifs en une seule opération.</p></div>
    <div>
      <a class="button" href="{{ route('admin.promethee.economy',['flight_airline'=>'ITF','flight_network_class'=>'principal']) }}#prix-vols">Tarifs lignes principales</a>
      <a class="button outline" href="{{ route('admin.promethee.economy',['flight_airline'=>'ITF','flight_network_class'=>'diagonal']) }}#prix-vols">Tarifs diagonales</a>
    </div>
  </div>
</section>
    </div>

    <div class="admin-detail-panel" data-detail-panel="pricing-seasons" hidden>
      <section class="panel">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">PÉRIODES DE L’ANNÉE</span>
      <h2>Saisons tarifaires</h2>
      <p>Les règles tarifaires peuvent déjà être rattachées à une saison. Les périodes créées ici restent compatibles avec le moteur existant de Prométhée.</p>
    </div>
    <a class="button outline" href="{{ route('admin.promethee.seasons') }}">Gestion avancée des saisons</a>
  </div>

  <form method="post" action="{{ route('admin.promethee.seasons.save') }}" class="form-grid">
    @csrf
    <label>Nom de la période<input name="name" required maxlength="80" placeholder="Ex. Hiver, Été, Vacances de Noël"></label>
    <label>Début<input type="date" name="starts_on" required></label>
    <label>Fin<input type="date" name="ends_on" required></label>
    <label class="full">Notes<textarea name="notes" rows="2" placeholder="Critères ou consignes tarifaires de la période"></textarea></label>
    <label><input type="checkbox" name="active" value="1"> Définir comme période active</label>
    <div><button type="submit">Créer la période</button></div>
  </form>

  <div class="table-wrap" style="margin-top:18px">
    <table>
      <thead><tr><th>Période</th><th>Début</th><th>Fin</th><th>État</th></tr></thead>
      <tbody>
      @forelse($seasons as $season)
        <tr><td><strong>{{ $season->name }}</strong></td><td>{{ $season->starts_on }}</td><td>{{ $season->ends_on }}</td><td>{{ $season->active ? 'Active' : 'Inactive' }}</td></tr>
      @empty
        <tr><td colspan="4">Aucune période définie.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</section>
    </div>

    <div class="admin-detail-panel" data-detail-panel="pricing-evolution" hidden>
      <section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">ÉVOLUTION PRÉVUE</span><h2>Décote des diagonales</h2></div></div>
  <p>La classification est prête pour appliquer plus tard une décote automatique aux diagonales (par exemple un cran tarifaire ou un pourcentage inférieur). Aucune décote automatique n’est activée pour l’instant : le niveau reste volontairement à définir.</p>
</section>
    </div>
  </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('pricing-criteria-form');
  const master = document.getElementById('criteria-select-all');
  const search = document.getElementById('criteria-search');
  const departure = document.getElementById('criteria-departure');
  const arrival = document.getElementById('criteria-arrival');
  const networkClass = document.getElementById('criteria-class');
  const reset = document.getElementById('criteria-reset-filters');
  const selectVisible = document.getElementById('criteria-select-visible');
  const selectEverything = document.getElementById('criteria-select-everything');
  const unselectAll = document.getElementById('criteria-unselect-all');
  const selectedCount = document.getElementById('criteria-selected-count');
  const resultCount = document.getElementById('criteria-result-count');
  const noResults = document.getElementById('criteria-no-results');
  const rows = Array.from(document.querySelectorAll('.criteria-row'));

  if (!form || !master) return;

  const normalize = (value) => (value || '').toString().trim().toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '');

  const visibleRows = () => rows.filter((row) => row.style.display !== 'none');
  const boxes = () => rows.map((row) => row.querySelector('.criteria-flight-checkbox')).filter(Boolean);

  const updateSelectionState = () => {
    const visible = visibleRows();
    const visibleBoxes = visible.map((row) => row.querySelector('.criteria-flight-checkbox')).filter(Boolean);
    const selected = boxes().filter((box) => box.checked).length;
    const selectedVisible = visibleBoxes.filter((box) => box.checked).length;

    selectedCount.textContent = selected;
    master.checked = visibleBoxes.length > 0 && selectedVisible === visibleBoxes.length;
    master.indeterminate = selectedVisible > 0 && selectedVisible < visibleBoxes.length;
  };

  const applyFilters = () => {
    const q = normalize(search.value);
    const dpt = normalize(departure.value);
    const arr = normalize(arrival.value);
    const cls = networkClass.value;
    let visible = 0;

    rows.forEach((row) => {
      const matches =
        (!q || normalize(row.dataset.search).includes(q)) &&
        (!dpt || normalize(row.dataset.departure).includes(dpt)) &&
        (!arr || normalize(row.dataset.arrival).includes(arr)) &&
        (!cls || row.dataset.class === cls);

      row.style.display = matches ? '' : 'none';
      if (matches) visible++;
    });

    resultCount.textContent = visible + ' ligne(s) affichée(s)';
    noResults.style.display = visible === 0 ? '' : 'none';
    updateSelectionState();
  };

  [search, departure, arrival].forEach((input) => input.addEventListener('input', applyFilters));
  networkClass.addEventListener('change', applyFilters);

  reset.addEventListener('click', () => {
    search.value = '';
    departure.value = '';
    arrival.value = '';
    networkClass.value = '';
    applyFilters();
  });

  master.addEventListener('change', () => {
    visibleRows().forEach((row) => {
      const box = row.querySelector('.criteria-flight-checkbox');
      if (box) box.checked = master.checked;
    });
    updateSelectionState();
  });

  selectVisible.addEventListener('click', () => {
    visibleRows().forEach((row) => {
      const box = row.querySelector('.criteria-flight-checkbox');
      if (box) box.checked = true;
    });
    updateSelectionState();
  });

  selectEverything.addEventListener('click', () => {
    boxes().forEach((box) => { box.checked = true; });
    updateSelectionState();
  });

  unselectAll.addEventListener('click', () => {
    boxes().forEach((box) => { box.checked = false; });
    updateSelectionState();
  });

  boxes().forEach((box) => box.addEventListener('change', updateSelectionState));

  form.addEventListener('submit', (event) => {
    if (!boxes().some((box) => box.checked)) {
      event.preventDefault();
      alert('Sélectionnez au moins une ligne avant d’appliquer un classement.');
    }
  });

  applyFilters();
});
</script>
<script src="{{ asset('promethee-assets/promethee-admin-workspaces.js') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.js')) }}"></script>
@endpush
