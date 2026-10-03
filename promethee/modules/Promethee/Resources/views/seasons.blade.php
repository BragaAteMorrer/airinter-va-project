@extends('promethee::layout')

@section('title', 'Saisons & périodes')

@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush
@section('content')
<div class="admin-workspace-page">
<div class="ops-header compact">
    <div>
        <span class="eyebrow">ADMINISTRATION · ÉCONOMIE</span>
        <h1>Saisons & périodes.</h1>
        <p>Définissez les périodes commerciales utilisées par Prométhée pour organiser les règles tarifaires et les opérations saisonnières.</p>
    </div>
    <a class="button outline" href="{{ route('admin.promethee.economy') }}">Retour à l’économie</a>
</div>
<nav class="admin-workspace-nav" aria-label="Navigation locale">
  <a href="#season-create">Créer</a>
  <a href="#season-calendar">Calendrier</a>
  <a href="#season-pricing">Tarification</a>
  <a href="#season-import">Import CSV</a>
</nav>


@if($errors->any())
<div class="notice danger" role="alert">
    <strong>Impossible d’enregistrer la saison.</strong>
    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<section class="panel admin-workspace-section" id="season-create">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">NOUVELLE PÉRIODE</span>
            <h2>Créer une saison</h2>
            <p>Une saison active peut servir de période de référence pour les fonctions tarifaires de Prométhée.</p>
        </div>
    </div>

    <form method="post" action="{{ route('admin.promethee.seasons.save') }}" class="form-grid">
        @csrf
        <label class="full">Nom
            <input name="name" maxlength="80" value="{{ old('name') }}" required placeholder="Ex. Hiver 2026-2027">
        </label>
        <label>Début
            <input type="date" name="starts_on" value="{{ old('starts_on') }}" required>
        </label>
        <label>Fin
            <input type="date" name="ends_on" value="{{ old('ends_on') }}" required>
        </label>
        <label class="full">Notes
            <textarea name="notes" maxlength="2000" placeholder="Contexte, lignes concernées, règles prévues…">{{ old('notes') }}</textarea>
        </label>
        <label>
            <input type="checkbox" name="active" value="1" @checked(old('active'))>
            Définir comme saison active
        </label>
        <div class="admin-workspace-form-action">
            <button type="submit">Enregistrer la saison</button>
        </div>
    </form>
</section>

<section class="panel admin-workspace-section" id="season-calendar">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">CALENDRIER COMMERCIAL</span>
            <h2>Périodes enregistrées</h2>
        </div>
        <span class="tag">{{ $seasons->count() }} saison(s)</span>
    </div>

    <div class="table-wrap admin-table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Statut</th>
                    <th>Nom</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Durée</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
            @forelse($seasons as $season)
                @php
                    $start = \Illuminate\Support\Carbon::parse($season->starts_on);
                    $end = \Illuminate\Support\Carbon::parse($season->ends_on);
                @endphp
                <tr>
                    <td>
                        @if($season->active)
                            <span class="tag">ACTIVE</span>
                        @else
                            <span class="muted">Inactive</span>
                        @endif
                    </td>
                    <td><strong>{{ $season->name }}</strong></td>
                    <td>{{ $start->format('d/m/Y') }}</td>
                    <td>{{ $end->format('d/m/Y') }}</td>
                    <td>{{ $start->diffInDays($end) + 1 }} j</td>
                    <td>{{ $season->notes ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Aucune saison configurée.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="panel admin-workspace-section" id="season-pricing">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">PÉRIODES DE L’ANNÉE</span>
            <h2>Saisons tarifaires</h2>
            <p>Définissez une hausse ou une baisse pendant une saison, sur tout le réseau ou sur une ligne précise.</p>
        </div>
    </div>

    <form method="post" action="{{ route('admin.promethee.seasons.pricing-adjustments.save') }}" class="form-grid" id="season-pricing-form">
        @csrf
        <label>Saison
            <select name="season_id" required>
                <option value="">Choisir…</option>
                @foreach($seasons as $season)
                    <option value="{{ $season->id }}">{{ $season->name }} · {{ $season->starts_on }} → {{ $season->ends_on }}</option>
                @endforeach
            </select>
        </label>
        <label>Périmètre
            <select name="scope" id="season-pricing-scope" required>
                <option value="global">Global · tout le réseau</option>
                <option value="flight">Une ligne spécifique</option>
            </select>
        </label>
        <label id="season-pricing-flight-wrap" hidden>Ligne
            <input type="search" id="season-pricing-flight-search" list="season-pricing-flights" placeholder="N° de vol, départ, arrivée…" autocomplete="off">
            <input type="hidden" name="flight_id" id="season-pricing-flight-id">
            <datalist id="season-pricing-flights">
                @foreach($seasonFlights as $flight)
                    <option value="{{ $flight->airline?->icao }}{{ $flight->flight_number }} · {{ $flight->dpt_airport_id }} → {{ $flight->arr_airport_id }}" data-id="{{ $flight->id }}"></option>
                @endforeach
            </datalist>
        </label>
        <label>Sens
            <select name="direction">
                <option value="increase">Augmentation</option>
                <option value="decrease">Diminution</option>
            </select>
        </label>
        <label>Type
            <select name="mode">
                <option value="percent">Pourcentage (%)</option>
                <option value="amount">Montant fixe</option>
            </select>
        </label>
        <label>Valeur
            <input type="number" name="value" min="0.01" step="0.01" required placeholder="Ex. 10">
        </label>
        <label class="full">Note
            <textarea name="notes" maxlength="1000" placeholder="Ex. vacances de Noël, ligne très demandée, promotion régionale…"></textarea>
        </label>
        <label><input type="checkbox" name="active" value="1" checked> Règle active</label>
        <div class="admin-workspace-form-action"><button type="submit">Ajouter l’ajustement</button></div>
    </form>

    <div class="notice" role="note">
        Les ajustements globaux s’appliquent à toutes les lignes pendant la période. Les ajustements spécifiques s’ajoutent ensuite uniquement à la ligne choisie.
    </div>

    <div class="table-wrap admin-table-scroll admin-workspace-spaced">
        <table>
            <thead><tr><th>Saison</th><th>Périmètre</th><th>Ajustement</th><th>Période</th><th>État</th><th>Note</th><th></th></tr></thead>
            <tbody>
            @forelse($seasonAdjustments as $adjustment)
                @php
                    $sign = $adjustment->direction === 'decrease' ? '−' : '+';
                    $unit = $adjustment->mode === 'percent' ? '%' : ' '.setting('units.currency', 'EUR');
                    $flightLabel = $adjustment->scope === 'flight'
                        ? (($adjustment->airline_icao ?: '').$adjustment->flight_number.' · '.$adjustment->dpt_airport_id.' → '.$adjustment->arr_airport_id)
                        : 'Tout le réseau';
                @endphp
                <tr>
                    <td><strong>{{ $adjustment->season_name }}</strong></td>
                    <td>{{ $flightLabel }}</td>
                    <td><strong>{{ $sign }}{{ number_format((float)$adjustment->value, 2, ',', ' ') }}{{ $unit }}</strong></td>
                    <td>{{ IlluminateSupportCarbon::parse($adjustment->starts_on)->format('d/m/Y') }} → {{ IlluminateSupportCarbon::parse($adjustment->ends_on)->format('d/m/Y') }}</td>
                    <td>{{ $adjustment->active ? 'Active' : 'Inactive' }}</td>
                    <td>{{ $adjustment->notes ?: '—' }}</td>
                    <td>
                        <form method="post" action="{{ route('admin.promethee.seasons.pricing-adjustments.delete', $adjustment->id) }}" onsubmit="return confirm('Supprimer cet ajustement saisonnier ?')">
                            @csrf
                            @method('delete')
                            <button class="button outline" type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">Aucun ajustement tarifaire saisonnier.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="panel admin-workspace-section" id="season-import">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">IMPORT PROGRAMME</span>
            <h2>Importer un programme de vols CSV</h2>
            <p>Importe ou met à jour des lignes depuis un fichier CSV séparé par des points-virgules.</p>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.seasons.import') }}" class="form-grid">
        @csrf
        <label class="full">Fichier CSV
            <input type="file" name="schedule" accept=".csv,.txt,text/csv,text/plain" required>
        </label>
        <div class="notice" role="note">
            Colonnes obligatoires : <code>airline_id;flight_number;departure;arrival</code>. Colonnes facultatives : <code>dpt_time;arr_time;route;flight_type</code>.
        </div>
        <div><button type="submit">Importer le programme</button></div>
    </form>
</section>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const scope = document.getElementById('season-pricing-scope');
  const wrap = document.getElementById('season-pricing-flight-wrap');
  const search = document.getElementById('season-pricing-flight-search');
  const hidden = document.getElementById('season-pricing-flight-id');
  const options = [...document.querySelectorAll('#season-pricing-flights option')];

  const syncScope = () => {
    const specific = scope.value === 'flight';
    wrap.hidden = !specific;
    search.required = specific;
    if (!specific) {
      search.value = '';
      hidden.value = '';
    }
  };

  const syncFlight = () => {
    const match = options.find((option) => option.value === search.value);
    hidden.value = match?.dataset.id || '';
  };

  scope.addEventListener('change', syncScope);
  search.addEventListener('input', syncFlight);
  syncScope();
});
</script>
@endpush

</div>
@endsection
