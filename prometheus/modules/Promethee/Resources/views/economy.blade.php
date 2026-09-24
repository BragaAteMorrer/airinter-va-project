@extends('promethee::layout')

@section('title', 'Économie')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">DIRECTION COMMERCIALE</span>
        <h1>Économie.</h1>
        <p>Édition des prix des vols et du carburant.</p>
    </div>
    <a class="button outline" href="{{ route('admin.promethee.bbr') }}">Tarifs Bleu-Blanc-Rouge</a>
</div>

<section class="panel" id="prix-vols">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">PRIX DES VOLS</span>
            <h2>Lignes existantes</h2>
            <p>Air Inter et Air Charter sont facturés au passager. Inter Cargo Service est facturé au kilogramme.</p>
        </div>
    </div>
    <form method="get" action="{{ route('admin.promethee.economy') }}#prix-vols" class="form-grid">
            <label>Compagnie <select id="filter-airline" name="flight_airline"><option value="">Toutes</option>@foreach($airlines as $airline)<option value="{{ $airline->icao }}" @selected(($flightFilters['flight_airline'] ?? '') === $airline->icao)>{{ $airline->name }}</option>@endforeach</select></label>
            <label>Pays de départ <select id="filter-origin" name="flight_origin"><option value="">Tous</option>@foreach($countries as $country)<option value="{{ $country }}" @selected(($flightFilters['flight_origin'] ?? '') === $country)>{{ $country }}</option>@endforeach</select></label>
            <label>Aéroport de départ <input type="search" id="filter-dpt-airport" name="flight_dpt_airport" list="economy-airports" value="{{ $flightFilters['flight_dpt_airport'] ?? '' }}" placeholder="Rechercher ICAO ou nom" autocomplete="off"></label>
            <label>Pays d’arrivée <select id="filter-arrival" name="flight_arrival"><option value="">Tous</option>@foreach($countries as $country)<option value="{{ $country }}" @selected(($flightFilters['flight_arrival'] ?? '') === $country)>{{ $country }}</option>@endforeach</select></label>
            <label>Aéroport d’arrivée <input type="search" id="filter-arr-airport" name="flight_arr_airport" list="economy-airports" value="{{ $flightFilters['flight_arr_airport'] ?? '' }}" placeholder="Rechercher ICAO ou nom" autocomplete="off"></label>
            <datalist id="economy-airports">@foreach($airportOptions as $airport)<option value="{{ $airport->icao }}" label="{{ $airport->name }} · {{ $airport->country }}"></option>@endforeach</datalist>
            <label>Recherche ligne <input id="filter-route" name="flight_search" value="{{ $flightFilters['flight_search'] ?? '' }}" placeholder="ex. LFPO ou ITF452"></label>
            <button type="submit" id="apply-flight-filters">Appliquer les filtres</button>
            <a class="button outline" href="{{ route('admin.promethee.economy') }}#prix-vols">Réinitialiser</a>
            <a class="button outline" href="{{ route('admin.promethee.economy',array_filter(array_merge($flightFilters,['flight_select_all'=>1]))) }}#prix-vols">Tout sélectionner les résultats</a>
            <a class="button" href="{{ route('admin.promethee.economy.flight-prices.edit') }}">Ouvrir l’éditeur des prix</a>
    </form>
    <form method="get" action="{{ route('admin.promethee.economy.flight-prices.edit') }}" id="flight-scope-form">
    <div class="table-wrap">
        <table>
            <thead><tr><th><input type="checkbox" id="flight-main-select-all" aria-label="Tout sélectionner" @checked($flightSelectAll)></th><th>Compagnie</th><th>Ligne</th><th>Départ</th><th>Arrivée</th><th>Tarif</th><th>Prix actuel</th><th>Couleur</th><th>Édition</th></tr></thead>
            <tbody>
            @forelse($flightPricing as $flight)
                @php
                    $displayFares = collect($flightFareDisplay[(string)$flight->id] ?? []);
                    $fareSummary = $displayFares->map(fn($row) => $row['code'].' · '.$row['name'])->implode(' / ');
                    $priceSummary = $displayFares->map(function($row) {
                        if ($row['price'] === null) {
                            return $row['code'].' · prix variable selon sous-flotte';
                        }
                        return $row['code'].' '.number_format((float)$row['price'], 2, ',', ' ').' '.setting('units.currency', 'EUR').($row['type'] === \App\Models\Enums\FareType::CARGO ? '/kg' : '/pax');
                    })->implode(' · ');
                    $bandSummary = $displayFares->pluck('band')->unique()->map(fn($band) => ucfirst($band))->implode(' / ');
                    if ($displayFares->isEmpty()) {
                        $fareSummary = 'Aucun tarif';
                        $priceSummary = '—';
                        $bandSummary = '—';
                    }
                @endphp
                <tr data-airline="{{ $flight->airline?->icao }}" data-origin="{{ $flight->dpt_airport?->country }}" data-arrival="{{ $flight->arr_airport?->country }}" data-dpt-airport="{{ $flight->dpt_airport_id }}" data-arr-airport="{{ $flight->arr_airport_id }}" data-route="{{ strtolower($flight->ident.' '.$flight->dpt_airport_id.' '.$flight->arr_airport_id) }}">
                    <td><input type="checkbox" name="flights[]" value="{{ $flight->id }}" @checked($flightSelectAll)></td>
                    <td>{{ $flight->airline?->icao }}</td>
                    <td><strong>{{ $flight->ident }}</strong></td>
                    <td>{{ $flight->dpt_airport_id }} · {{ $flight->dpt_airport?->country }}</td>
                    <td>{{ $flight->arr_airport_id }} · {{ $flight->arr_airport?->country }}</td>
                    <td>{{ $fareSummary }}</td>
                    <td>{{ $priceSummary }}</td>
                    <td>{{ $bandSummary }}</td>
                    <td><a class="button outline" href="{{ route('admin.promethee.economy.flight-prices.line-edit',['flight'=>$flight->id]) }}">Éditer</a></td>
                </tr>
            @empty
                <tr><td colspan="9">Aucune ligne active.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-heading" style="margin-top:16px"><p id="flight-selection-count">Aucune ligne sélectionnée.</p><button type="submit">Modifier les lignes cochées</button></div>
    </form>
</section>

<section class="panel" id="prix-carburant">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">PRIX DU CARBURANT</span>
            <h2>Pays et provinces</h2>
            <p>Les prix carburant sont gérés par pays et province/région, jamais par ligne.</p>
        </div>
    </div>
    <form method="get" action="{{ route('admin.promethee.economy') }}#prix-carburant" class="form-grid">
        <label>Pays <select name="fuel_country"><option value="">Tous les pays</option>@foreach($countries as $country)<option value="{{ $country }}" @selected(($fuelFilters['fuel_country'] ?? '') === $country)>{{ $country }}</option>@endforeach</select></label>
        <label>Province / région <select name="fuel_region"><option value="">Toutes les provinces</option>@foreach($regions as $region)<option value="{{ $region['region'] }}" @selected(($fuelFilters['fuel_region'] ?? '') === $region['region'])>{{ $region['country'] }} · {{ $region['region'] }}</option>@endforeach</select></label>
        <label>Recherche <input name="fuel_search" value="{{ $fuelFilters['fuel_search'] ?? '' }}" placeholder="Pays ou province"></label>
        <button type="submit">Appliquer les filtres</button>
        <a class="button outline" href="{{ route('admin.promethee.economy') }}#prix-carburant">Réinitialiser</a>
        <a class="button outline" href="{{ route('admin.promethee.economy',array_filter(array_merge($fuelFilters,['fuel_select_all'=>1]))) }}#prix-carburant">Tout sélectionner les résultats</a>
    </form>
    <form method="get" action="{{ route('admin.promethee.economy.fuel-prices.editor') }}" id="fuel-scope-form">
    <div class="table-wrap">
        <table>
            <thead><tr><th><input type="checkbox" id="fuel-select-all" aria-label="Tout sélectionner" @checked($fuelSelectAll)></th><th>Pays</th><th>Province / région</th><th>Aéroports concernés</th><th>Jet A €/L</th><th>100LL €/L</th><th>Mogas €/L</th><th>Édition</th></tr></thead>
            <tbody>
            @forelse($fuelScopes as $scope)
                <tr data-fuel-country="{{ $scope->country }}" data-fuel-region="{{ $scope->region }}" data-fuel-search="{{ strtolower($scope->country.' '.$scope->region) }}"><td><input type="checkbox" name="scopes[]" value="{{ $scope->country }}|{{ $scope->region }}" @checked($fuelSelectAll)></td><td>{{ $scope->country }}</td><td>{{ $scope->region ?: 'Toutes les provinces' }}</td><td>{{ $scope->count }}</td><td>{{ $scope->jeta }}</td><td>{{ $scope->{'100ll'} }}</td><td>{{ $scope->mogas }}</td><td><a class="button outline" href="{{ route('admin.promethee.economy.fuel-prices.edit',['country'=>$scope->country,'region'=>$scope->region]) }}">Éditer</a></td></tr>
            @empty
                <tr><td colspan="8">Aucun aéroport.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-heading" style="margin-top:16px"><p id="fuel-selection-count">Aucun périmètre sélectionné.</p><button type="submit">Modifier les périmètres cochés</button></div>
    </form>
</section>
@endsection

@push('scripts')
<script>
(() => {
  const fields=['airline','origin','dpt-airport','arrival','arr-airport','route'].map(name=>document.querySelector('#filter-'+name));
  const apply=()=>document.querySelectorAll('#prix-vols tbody tr[data-route]').forEach(row=>{
    const departure=fields[2].value.trim().toUpperCase(), arrival=fields[4].value.trim().toUpperCase();
    const visible=(!fields[0].value||row.dataset.airline===fields[0].value)&&(!fields[1].value||row.dataset.origin===fields[1].value)&&(!departure||row.dataset.dptAirport===departure)&&(!fields[3].value||row.dataset.arrival===fields[3].value)&&(!arrival||row.dataset.arrAirport===arrival)&&(!fields[5].value||row.dataset.route.includes(fields[5].value.toLowerCase()));
    row.hidden=!visible;
  });
  fields.forEach(field=>field.addEventListener('input',apply));
  const flightRows=[...document.querySelectorAll('#flight-scope-form tbody tr[data-route]')], flightSelectAll=document.querySelector('#flight-main-select-all'), flightCount=document.querySelector('#flight-selection-count');
  const countFlights=()=>{ const count=document.querySelectorAll('#flight-scope-form input[name="flights[]"]:checked').length; flightCount.textContent=count ? count+' ligne(s) sélectionnée(s).' : 'Aucune ligne sélectionnée.'; };
  flightSelectAll.addEventListener('change',()=>{ flightRows.filter(row=>!row.hidden).forEach(row=>row.querySelector('input[name="flights[]"]').checked=flightSelectAll.checked); countFlights(); });
  document.querySelectorAll('#flight-scope-form input[name="flights[]"]').forEach(box=>box.addEventListener('change',countFlights)); countFlights();
  const fuelRows=[...document.querySelectorAll('#fuel-scope-form tbody tr[data-fuel-country]')];
  const selectAll=document.querySelector('#fuel-select-all'), fuelCount=document.querySelector('#fuel-selection-count');
  const countFuel=()=>{ const boxes=document.querySelectorAll('#fuel-scope-form input[name="scopes[]"]:checked'); fuelCount.textContent=boxes.length ? boxes.length+' périmètre(s) sélectionné(s).' : 'Aucun périmètre sélectionné.'; };
  selectAll.addEventListener('change',()=>{ fuelRows.filter(row=>!row.hidden).forEach(row=>row.querySelector('input[name="scopes[]"]').checked=selectAll.checked); countFuel(); });
  document.querySelectorAll('#fuel-scope-form input[name="scopes[]"]').forEach(box=>box.addEventListener('change',countFuel)); countFuel();
})();
</script>
@endpush
