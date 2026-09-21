@extends('promethee::layout')

@section('title', 'Préparation de vol')

@section('content')
<div class="ops-header compact">
    <div>
        <span class="eyebrow">BRIEFING PILOTE</span>
        <h1>{{ $flight->ident }}</h1>
        <p>{{ $flight->dpt_airport_id }} → {{ $flight->arr_airport_id }} · {{ number_format($flight->distance->toUnit('nmi'), 0, ',', ' ') }} NM</p>
    </div>
    <a class="button outline" href="{{ route('promethee.flights.show', $flight->id) }}">Fiche de ligne</a>
</div>

<section class="control-strip">
    <article><span>Carburant indicatif</span><strong>{{ number_format($suggestedFuel, 0, ',', ' ') }} {{ strtoupper($fuelUnit) }}</strong><small>Estimation simulation, à confirmer par OFP</small></article>
    <article><span>Alternat publié</span><strong>{{ $flight->alt_airport_id ?: '—' }}</strong><small>À vérifier selon météo</small></article>
    <article><span>Flottes possibles</span><strong>{{ $flight->subfleets->count() }}</strong><small>Appareil compatible requis</small></article>
    <article><span>Météo</span><strong>{{ count(array_filter($weather, fn ($report) => !empty($report['metar']))) }}</strong><small>METAR reçu(s)</small></article>
</section>

<div class="two-columns">
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">MÉTÉO ET ROUTE</span><h2>Informations disponibles</h2></div></div>
        @foreach($weather as $airport => $report)
            <article class="event">
                <h3>{{ $airport }}</h3>
                <p><strong>METAR</strong></p>
                <p class="mono preserve">{{ is_string($report['metar'] ?? null) ? $report['metar'] : 'METAR momentanément indisponible.' }}</p>
                <p><strong>TAF</strong></p>
                <p class="mono preserve">{{ is_string($report['taf'] ?? null) ? $report['taf'] : 'TAF momentanément indisponible.' }}</p>
            </article>
        @endforeach
        @if($weather === [])
            <p class="empty">La météo sera affichée lorsque le fournisseur est accessible.</p>
        @endif
        @if($flight->route)
            <article class="event"><h3>Route publiée</h3><p class="mono preserve">{{ $flight->route }}</p></article>
        @endif
        <p class="muted">L’import direct SimBrief/NOTAM nécessite une clé ou un compte tiers : aucun briefing externe n’est envoyé automatiquement.</p>
    </section>

    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">MON DOSSIER DE VOL</span><h2>OFP et décisions</h2></div></div>
        <form method="post" action="{{ route('promethee.flights.briefing.save', $flight->id) }}" class="form-grid">
            @csrf
            <label>Référence OFP / SimBrief<input name="ofp_reference" value="{{ old('ofp_reference', $briefing->ofp_reference ?? '') }}" placeholder="SB-… ou référence personnelle"></label>
            <label>Alternat retenu<input name="alternate" maxlength="8" value="{{ old('alternate', $briefing->alternate ?? $flight->alt_airport_id) }}"></label>
            <label>Carburant prévu ({{ strtoupper($fuelUnit) }})<input name="planned_fuel" type="number" min="0" value="{{ old('planned_fuel', $briefing->planned_fuel ?? $suggestedFuel) }}"></label>
            <label class="full">Notes pilote<textarea name="notes" rows="7" placeholder="Météo, choix de route, contraintes…">{{ old('notes', $briefing->notes ?? '') }}</textarea></label>
            <button>Enregistrer le briefing</button>
        </form>
    </section>
</div>
@endsection
