@extends('promethee::layout')

@section('title', 'Édition des prix des vols')

@section('content')
<div class="page-heading">
    <div><span class="eyebrow">DIRECTION COMMERCIALE</span><h1>Édition des prix des vols.</h1><p>Sélectionnez une ou plusieurs lignes, puis appliquez la modification.</p></div>
    <a class="button outline" href="{{ route('admin.promethee.economy') }}#prix-vols">Retour aux lignes</a>
</div>

<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">1. FILTRER ET SÉLECTIONNER</span><h2>Périmètre des lignes</h2><p>Les filtres n’écrivent rien : ils servent uniquement à retrouver les lignes à modifier.</p></div></div>
    <div class="form-grid">
        <label>Compagnie <select id="filter-airline"><option value="">Toutes</option>@foreach($airlines as $airline)<option value="{{ $airline->icao }}">{{ $airline->name }}</option>@endforeach</select></label>
        <label>Pays de départ <select id="filter-origin"><option value="">Tous</option>@foreach($countries as $country)<option value="{{ $country }}">{{ $country }}</option>@endforeach</select></label>
        <label>Aéroport de départ <select id="filter-dpt-airport"><option value="">Tous</option>@foreach($airportOptions as $airport)<option value="{{ $airport->icao }}">{{ $airport->icao }} · {{ $airport->name }}</option>@endforeach</select></label>
        <label>Pays d’arrivée <select id="filter-arrival"><option value="">Tous</option>@foreach($countries as $country)<option value="{{ $country }}">{{ $country }}</option>@endforeach</select></label>
        <label>Aéroport d’arrivée <select id="filter-arr-airport"><option value="">Tous</option>@foreach($airportOptions as $airport)<option value="{{ $airport->icao }}">{{ $airport->icao }} · {{ $airport->name }}</option>@endforeach</select></label>
        <label>Recherche ligne <input id="filter-route" placeholder="ex. LFPO ou ITF452"></label>
    </div>
    <form method="post" action="{{ route('admin.promethee.economy.flight-prices') }}" id="flight-price-form">
        @csrf
        <div class="table-wrap"><table>
            <thead><tr><th><input type="checkbox" id="flight-select-all" aria-label="Tout sélectionner"></th><th>Compagnie</th><th>Ligne</th><th>Départ</th><th>Arrivée</th><th>Tarif</th><th>Prix actuel</th><th>Couleur</th></tr></thead>
            <tbody>
            @foreach($flightPricing as $flight)
                @forelse($flight->fares as $fare)
                    <tr data-airline="{{ $flight->airline?->icao }}" data-origin="{{ $flight->dpt_airport?->country }}" data-arrival="{{ $flight->arr_airport?->country }}" data-dpt-airport="{{ $flight->dpt_airport_id }}" data-arr-airport="{{ $flight->arr_airport_id }}" data-route="{{ strtolower($flight->ident.' '.$flight->dpt_airport_id.' '.$flight->arr_airport_id) }}">
                        <td><input type="checkbox" name="flight_ids[]" value="{{ $flight->id }}" @checked(in_array((string)$flight->id,$selectedFlights,true))></td><td>{{ $flight->airline?->icao }}</td><td><strong>{{ $flight->ident }}</strong></td><td>{{ $flight->dpt_airport_id }} · {{ $flight->dpt_airport?->country }}</td><td>{{ $flight->arr_airport_id }} · {{ $flight->arr_airport?->country }}</td><td>{{ $fare->code }} · {{ $fare->name }}</td><td>{{ $fare->pivot->price ?: $fare->price }} {{ setting('units.currency', 'EUR') }}{{ $fare->type === \App\Models\Enums\FareType::CARGO ? ' / kg' : ' / passager' }}</td><td>{{ $pricingBands[$flight->id.'|'.$fare->id] ?? 'rouge' }}</td>
                    </tr>
                @empty
                    @php $fareCode=$flight->airline?->icao === 'ICS' ? 'CGO' : ($flight->airline?->icao === 'ACF' ? 'T' : 'Y'); $baseFare=$baseFares[$fareCode] ?? null; $fareDefault=$baseFareDefaults[$fareCode]; @endphp
                    <tr data-airline="{{ $flight->airline?->icao }}" data-origin="{{ $flight->dpt_airport?->country }}" data-arrival="{{ $flight->arr_airport?->country }}" data-dpt-airport="{{ $flight->dpt_airport_id }}" data-arr-airport="{{ $flight->arr_airport_id }}" data-route="{{ strtolower($flight->ident.' '.$flight->dpt_airport_id.' '.$flight->arr_airport_id) }}">
                        <td><input type="checkbox" name="flight_ids[]" value="{{ $flight->id }}" @checked(in_array((string)$flight->id,$selectedFlights,true))></td><td>{{ $flight->airline?->icao }}</td><td><strong>{{ $flight->ident }}</strong></td><td>{{ $flight->dpt_airport_id }} · {{ $flight->dpt_airport?->country }}</td><td>{{ $flight->arr_airport_id }} · {{ $flight->arr_airport?->country }}</td><td>{{ $fareCode }} · {{ $baseFare?->name ?: $fareDefault['name'] }}</td><td>{{ $baseFare?->price ?: $fareDefault['price'] }} {{ setting('units.currency', 'EUR') }}{{ $fareCode === 'CGO' ? ' / kg' : ' / passager' }}</td><td>rouge</td>
                    </tr>
                @endforelse
            @endforeach
            </tbody>
        </table></div>
        <div class="panel-heading" style="margin-top:1.5rem"><div><span class="eyebrow">2. MODIFIER</span><h2>Prix des lignes sélectionnées</h2><p id="selection-count">Aucune ligne sélectionnée.</p></div></div>
        <div class="form-grid">
            <label>Opération <select name="mode"><option value="set">Fixer le prix rouge</option><option value="add">Ajouter / retirer un montant</option><option value="percent">Augmenter / réduire en %</option><option value="band">Changer uniquement la couleur</option></select></label>
            <label>Valeur <input name="value" type="number" step="0.01" placeholder="Ex. 218 ou -10"></label>
            <label>Tarif des lignes <select name="band"><option value="keep">Conserver la couleur actuelle</option><option value="rouge">Rouge · plein tarif</option><option value="blanc">Blanc · tarif réduit</option><option value="bleu">Bleu · tarif réduit</option></select></label>
            <button type="submit">Enregistrer la modification</button>
        </div>
    </form>
</section>
@endsection

@section('scripts')
<script>
(() => {
 const ids=['airline','origin','dpt-airport','arrival','arr-airport','route']; const fields=Object.fromEntries(ids.map(id=>[id,document.querySelector('#filter-'+id)]));
 const rows=[...document.querySelectorAll('#flight-price-form tbody tr')];
 const apply=()=>rows.forEach(row=>{ const v=fields.route.value.toLowerCase(); row.hidden=!!((fields.airline.value&&row.dataset.airline!==fields.airline.value)||(fields.origin.value&&row.dataset.origin!==fields.origin.value)||(fields['dpt-airport'].value&&row.dataset.dptAirport!==fields['dpt-airport'].value)||(fields.arrival.value&&row.dataset.arrival!==fields.arrival.value)||(fields['arr-airport'].value&&row.dataset.arrAirport!==fields['arr-airport'].value)||(v&&!row.dataset.route.includes(v))); });
 const count=()=>{ const n=document.querySelectorAll('input[name="flight_ids[]"]:checked').length; document.querySelector('#selection-count').textContent=n ? n+' ligne(s) sélectionnée(s).' : 'Aucune ligne sélectionnée.'; };
 Object.values(fields).forEach(field=>field.addEventListener('input',apply)); document.querySelectorAll('input[name="flight_ids[]"]').forEach(box=>box.addEventListener('change',count)); document.querySelector('#flight-select-all').addEventListener('change',event=>{ rows.filter(row=>!row.hidden).forEach(row=>row.querySelector('input[name="flight_ids[]"]').checked=event.target.checked); count(); }); count();
})();
</script>
@endsection
