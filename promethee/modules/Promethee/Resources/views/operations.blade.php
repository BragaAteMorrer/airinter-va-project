@extends('promethee::layout')
@section('title','Opérations')
@section('content')
<div class="ops-header compact">
    <div>
        <span class="eyebrow">CENTRE OPÉRATIONS AIR INTER</span>
        <h1>Salle opérations.</h1>
        <p>La situation du réseau et les prochains vols à préparer depuis {{ $homeAirportId ?: 'votre aéroport d’attache' }}.</p>
    </div>
    <div class="toolbar">
        @ability('admin','admin-access')<a class="button outline" href="{{ route('admin.promethee.catalogue') }}">Gérer le catalogue</a>@endability
        <a class="button outline" href="{{ route('promethee.flights') }}">Programme complet</a>
        <a class="button" href="{{ route('promethee.acars') }}">Ouvrir Hermès ↗</a>
    </div>
</div>

<section class="control-strip" aria-label="Indicateurs d’exploitation">
    <article><span>Vols en cours</span><strong>{{ $activeFlights }}</strong><small>Hermès / PIREP ouverts</small></article>
    <article><span>PIREP en attente</span><strong>{{ $pendingPireps }}</strong><small>Validation exploitation</small></article>
    <article><span>Aujourd’hui</span><strong>{{ $acceptedToday }}</strong><small>Vols acceptés</small></article>
    <article><span>Mois courant</span><strong>{{ $acceptedMonth }}</strong><small>Vols acceptés</small></article>
    <article><span>Télémétrie jour</span><strong>{{ number_format($telemetrySamples,0,',',' ') }}</strong><small>Échantillons reçus</small></article>
</section>

<div class="ops-layout">
    <section class="panel">
        <div class="panel-heading">
            <div><span class="eyebrow">PROCHAINS DÉPARTS · HEURE DE PARIS</span><h2>Vols à préparer</h2></div>
            @if($homeAirportId)<span class="tag">Base {{ $homeAirportId }}</span>@endif
        </div>
        <div class="ops-timeline">
            @forelse($upcoming as $flight)
                <article>
                    <time datetime="{{ optional($flight->next_departure_at)->toIso8601String() }}">{{ $flight->display_departure_time ?: '--:--' }}</time>
                    <strong>{{ $flight->ident }}</strong>
                    <span>{{ $flight->dpt_airport_id }} → {{ $flight->arr_airport_id }}</span>
                    <small>{{ optional($flight->next_departure_at)->translatedFormat('D d/m') }} · LF {{ $flight->load_factor ?? setting('flights.default_load_factor') }} %</small>
                    <a class="button outline" href="{{ route('promethee.flights.briefing', $flight->id) }}">Préparer ↗</a>
                </article>
            @empty
                <div class="empty">
                    <p>Aucun départ réservable depuis votre base dans les sept prochains jours.</p>
                    <a href="{{ route('promethee.flights') }}">Consulter tout le programme ↗</a>
                </div>
            @endforelse
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">RETOUR D’EXPÉRIENCE</span><h2>Derniers rapports acceptés</h2></div><a href="{{ route('promethee.public.pireps') }}">Tous les rapports ↗</a></div>
        <div class="report-feed">
            @forelse($recentPireps as $pirep)
                <article>
                    <strong>{{ $pirep->ident }}</strong>
                    <span>{{ $pirep->dpt_airport_id }} → {{ $pirep->arr_airport_id }}</span>
                    <small>{{ optional($pirep->submitted_at)->setTimezone('Europe/Paris')->format('d/m H:i') }} · {{ $pirep->landing_rate ? number_format($pirep->landing_rate,0,',',' ') . ' ft/min' : 'Atterrissage non mesuré' }}</small>
                    <a href="{{ route('promethee.pireps.show', $pirep->id) }}">Rapport ↗</a>
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
                <time datetime="{{ \Carbon\Carbon::parse($event->starts_at)->toIso8601String() }}">{{ \Carbon\Carbon::parse($event->starts_at)->setTimezone('Europe/Paris')->format('d/m H:i') }}</time>
                <h3>{{ $event->title }}</h3>
                <p>{{ $event->departure ?: 'Interne' }} {{ $event->arrival ? '→ '.$event->arrival : '' }}</p>
            </article>
        @empty
            <p class="empty">Aucun rendez-vous programmé.</p>
        @endforelse
    </section>
</div>
@endsection
