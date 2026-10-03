@extends('promethee::layout')

@section('title', 'OFP SimBrief · '.$simbrief->id)

@section('content')
<div class="ops-header compact">
    <div>
        <span class="eyebrow">OFP SimBrief</span>
        <h1>{{ $flight?->ident ?: $simbrief->id }}</h1>
        <p>{{ (string) $ofp->origin->icao_code }} → {{ (string) $ofp->destination->icao_code }} · {{ $aircraft?->registration ?: 'Appareil non renseigné' }}</p>
    </div>
    @if($flight)
        <a class="button outline" href="{{ route('promethee.flights.briefing', $flight->id) }}">Retour au briefing</a>
    @endif
</div>

<section class="control-strip">
    <article><span>Appareil</span><strong>{{ $aircraft?->registration ?: '—' }}</strong><small>{{ (string) $ofp->aircraft->icaocode ?: ($aircraft?->icao ?: '—') }}</small></article>
    <article><span>Carburant bloc</span><strong>{{ number_format((float) $ofp->fuel->plan_ramp, 0, ',', ' ') }} KG</strong><small>OFP SimBrief</small></article>
    <article><span>Altitude initiale</span><strong>{{ (string) $ofp->general->initial_altitude ?: '—' }}</strong><small>{{ (string) $ofp->alternate->icao_code ? 'ALT '.(string) $ofp->alternate->icao_code : 'Sans alternat' }}</small></article>
    <article><span>PIREP</span><strong>{{ $simbrief->pirep_id ? 'Lié' : 'À préparer' }}</strong><small>OFP {{ $simbrief->id }}</small></article>
</section>

<div class="two-columns">
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">Navigation</span><h2>Plan de vol</h2></div></div>
        <article class="event"><h3>Route</h3><p class="mono preserve">{{ (string) $ofp->general->route ?: '—' }}</p></article>
        <article class="event"><h3>Départ / arrivée</h3><p>{{ (string) $ofp->origin->icao_code }} → {{ (string) $ofp->destination->icao_code }}</p></article>
        @if((string) $ofp->alternate->icao_code)
            <article class="event"><h3>Alternat</h3><p>{{ (string) $ofp->alternate->icao_code }}</p></article>
        @endif
    </section>

    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">Chargement</span><h2>Tarifs et appareils</h2></div></div>
        @forelse($fares as $fare)
            <article class="event">
                <h3>{{ $fare['name'] ?? $fare['code'] ?? 'Fare' }}</h3>
                <p><strong>Capacité {{ (int) ($fare['capacity'] ?? 0) }}</strong>@if(isset($fare['count'])) · Chargement {{ (int) $fare['count'] }}@endif</p>
                <p class="muted">{{ $fare['code'] ?? '—' }}</p>
            </article>
        @empty
            <p class="empty">Aucune donnée de chargement enregistrée avec cet OFP.</p>
        @endforelse
    </section>
</div>

<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">Données opérationnelles</span><h2>Résumé SimBrief</h2></div></div>
    <div class="control-strip">
        <article><span>Temps estimé</span><strong>{{ gmdate('H:i', (int) $ofp->times->est_time_enroute) }}</strong><small>En route</small></article>
        <article><span>Distance</span><strong>{{ number_format((float) $ofp->general->route_distance, 0, ',', ' ') }} NM</strong><small>Route OFP</small></article>
        <article><span>Cost index</span><strong>{{ (string) $ofp->general->costindex ?: '—' }}</strong><small>Planification</small></article>
        <article><span>Format</span><strong>SimBrief</strong><small>Source phpVMS</small></article>
    </div>
</section>
@endsection
