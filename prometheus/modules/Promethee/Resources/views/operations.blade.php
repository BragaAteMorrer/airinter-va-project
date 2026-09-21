@extends('promethee::layout')
@section('title','Opérations')
@section('content')
<div class="ops-header compact">
    <div>
        <span class="eyebrow">CENTRE OPÉRATIONS AIR INTER</span>
        <h1>Salle opérations.</h1>
        <p>Vue de suivi pour la journée : départs, PIREP, télémétrie, rendez-vous et lignes actives.</p>
    </div>
    <div class="toolbar">@ability('admin','admin-access')<a class="button outline" href="{{ route('admin.promethee.catalogue') }}">Gérer le catalogue</a>@endability<a class="button" href="{{ route('promethee.acars') }}">Ouvrir ACARS ↗</a></div>
</div>

<section class="control-strip">
    <article><span>Vols en cours</span><strong>{{ $activeFlights }}</strong><small>ACARS / PIREP ouverts</small></article>
    <article><span>PIREP en attente</span><strong>{{ $pendingPireps }}</strong><small>Validation admin</small></article>
    <article><span>Mois courant</span><strong>{{ $acceptedMonth }}</strong><small>Vols acceptés</small></article>
    <article><span>Télémétrie jour</span><strong>{{ number_format($telemetrySamples,0,',',' ') }}</strong><small>Échantillons reçus</small></article>
    <article><span>Tarifs Prométhée</span><strong>{{ $pricingRules }}</strong><small>Lignes tarifées</small></article>
</section>

<div class="ops-layout">
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">DÉPARTS À SURVEILLER</span><h2>File programme</h2></div></div>
        <div class="ops-timeline">
            @forelse($upcoming as $flight)
                <article>
                    <time>{{ $flight->dpt_time ?: '--:--' }}</time>
                    <strong>{{ $flight->ident }}</strong>
                    <span>{{ $flight->dpt_airport_id }} → {{ $flight->arr_airport_id }}</span>
                    <small>LF {{ $flight->load_factor ?? setting('flights.default_load_factor') }} %</small>
                </article>
            @empty
                <p class="empty">Aucun départ programmé.</p>
            @endforelse
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">RETEX</span><h2>Derniers rapports</h2></div></div>
        <div class="report-feed">
            @forelse($recentPireps as $pirep)
                <article>
                    <strong>{{ $pirep->ident }}</strong>
                    <span>{{ $pirep->dpt_airport_id }} → {{ $pirep->arr_airport_id }}</span>
                    <small>{{ optional($pirep->submitted_at)->setTimezone('Europe/Paris')->format('d/m H:i') }} · {{ $pirep->landing_rate ? number_format($pirep->landing_rate,0,',',' ') . ' ft/min' : 'LDG n/a' }}</small>
                </article>
            @empty
                <p class="empty">Aucun rapport récent.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="two-columns">
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">AXES ACTIFS</span><h2>Routes du mois</h2></div></div>
        <div class="route-board">
            @forelse($topRoutes as $route)
                <article><b>{{ $route->dpt_airport_id }}</b><i></i><b>{{ $route->arr_airport_id }}</b><span>{{ $route->total }} vols</span></article>
            @empty
                <p class="empty">Les routes apparaîtront après acceptation des PIREP.</p>
            @endforelse
        </div>
    </section>
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">CALENDRIER</span><h2>Prochains rendez-vous</h2></div><a href="{{ route('promethee.calendar') }}">Calendrier ↗</a></div>
        @forelse($events as $event)
            <article class="event">
                <time>{{ \Carbon\Carbon::parse($event->starts_at)->setTimezone('Europe/Paris')->format('d/m H:i') }}</time>
                <h3>{{ $event->title }}</h3>
                <p>{{ $event->departure ?: 'Interne' }} {{ $event->arrival ? '→ '.$event->arrival : '' }}</p>
            </article>
        @empty
            <p class="empty">Aucun rendez-vous programmé.</p>
        @endforelse
    </section>
</div>
@endsection
