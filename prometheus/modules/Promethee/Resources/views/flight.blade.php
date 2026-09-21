@extends('promethee::layout')
@section('title','Fiche vol')
@section('content')
<div class="ops-header compact">
    <div>
        <span class="eyebrow">{{ $flight->airline?->name ?? 'Air Inter' }}</span>
        <h1>{{ $flight->ident }}</h1>
        <p>{{ $flight->dpt_airport_id }} → {{ $flight->arr_airport_id }} · {{ $flight->flight_type }}</p>
    </div>
    <div class="toolbar">@if($reservation)<span class="tag">Vol déjà réservé</span>@else<form method="post" action="{{ route('promethee.flights.reserve',$flight->id) }}">@csrf<button class="button">Réserver ce vol</button></form>@endif<a class="button outline" href="{{ route('promethee.flights.briefing',$flight->id) }}">Préparer le vol</a><a class="button" href="{{ route('promethee.acars') }}">Ouvrir ACARS ↗</a></div>
</div>

<section class="control-strip">
    <article><span>Départ publié</span><strong>{{ $flight->dpt_time ?: '—' }}</strong><small>{{ $flight->dpt_airport?->name }}</small></article>
    <article><span>Arrivée publiée</span><strong>{{ $flight->arr_time ?: '—' }}</strong><small>{{ $flight->arr_airport?->name }}</small></article>
    <article><span>Remplissage</span><strong>{{ $flight->load_factor ?? setting('flights.default_load_factor') }}%</strong><small>Facteur phpVMS</small></article>
    <article><span>PIREP acceptés</span><strong>{{ $stats['pireps'] }}</strong><small>Sur cette ligne</small></article>
    <article><span>Niveau</span><strong>{{ $flight->level ?: '—' }}</strong><small>Planifié</small></article>
</section>

<section class="panel" aria-label="Carte de la ligne">
    <div class="panel-heading"><div><span class="eyebrow">ITINÉRAIRE</span><h2>{{ $flight->dpt_airport_id }} → {{ $flight->arr_airport_id }}</h2></div><span>{{ $flight->distance ?: '—' }} NM · {{ $flight->flight_time ? floor($flight->flight_time / 60).'h '.str_pad($flight->flight_time % 60,2,'0',STR_PAD_LEFT) : 'Durée non renseignée' }}</span></div>
    @if($flight->dpt_airport?->lat !== null && $flight->dpt_airport?->lon !== null && $flight->arr_airport?->lat !== null && $flight->arr_airport?->lon !== null)
        <div id="flight-route-map" style="height:360px" role="application" aria-label="Carte interactive de la route {{ $flight->dpt_airport_id }} vers {{ $flight->arr_airport_id }}"></div>
        @push('scripts')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script><script>document.addEventListener('DOMContentLoaded',()=>{const a=[{{ $flight->dpt_airport->lat }},{{ $flight->dpt_airport->lon }}],b=[{{ $flight->arr_airport->lat }},{{ $flight->arr_airport->lon }}],map=L.map('flight-route-map');L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);L.marker(a).addTo(map).bindPopup({!! json_encode($flight->dpt_airport_id.' · '.$flight->dpt_airport->name) !!});L.marker(b).addTo(map).bindPopup({!! json_encode($flight->arr_airport_id.' · '.$flight->arr_airport->name) !!});const line=L.polyline([a,b],{color:'#0b55a1',weight:4}).addTo(map);map.fitBounds(line.getBounds(),{padding:[35,35]});});</script>@endpush
    @else
        <p class="empty">Carte indisponible : les coordonnées de départ ou d’arrivée ne sont pas encore renseignées.</p>
    @endif
</section>

<div class="two-columns">
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">PRÉPARATION</span><h2>Dossier de vol</h2></div></div>
        <div class="airport-pair"><strong>{{ $flight->dpt_airport_id }}</strong><i></i><strong>{{ $flight->arr_airport_id }}</strong></div>
        <dl class="line-meta">
            <div><dt>Départ</dt><dd>{{ $flight->dpt_airport?->location ?: '—' }}</dd></div>
            <div><dt>Arrivée</dt><dd>{{ $flight->arr_airport?->location ?: '—' }}</dd></div>
            <div><dt>Distance</dt><dd>{{ $flight->distance ?: '—' }}</dd></div>
        </dl>
        @if($flight->route)
            <h3>Route publiée</h3>
            <p class="preserve mono">{{ $flight->route }}</p>
        @endif
        @if($flight->notes)
            <h3>Notes exploitation</h3>
            <p class="preserve">{{ $flight->notes }}</p>
        @endif
    </section>

    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">TARIFS ET APPAREILS</span><h2>Paramètres</h2></div></div>
        <div class="fare-chips">
            @forelse($flight->fares as $fare)
                <span>{{ $fare->name }} · {{ $fare->pivot->price ?: $fare->price }}</span>
            @empty
                <span>Cabines par défaut phpVMS</span>
            @endforelse
        </div>
        <div class="route-list">
            @forelse($flight->subfleets as $subfleet)
                <div><strong>{{ $subfleet->name }}</strong><span>{{ $subfleet->type }}</span></div>
            @empty
                <p class="empty">Aucune flotte associée.</p>
            @endforelse
        </div>
        @if($stats['last'])
            <div class="mini-bulletin"><strong>{{ optional($stats['last']->submitted_at)->setTimezone('Europe/Paris')->format('d/m') }}</strong><span>Dernier PIREP accepté sur cette ligne</span></div>
        @endif
    </section>
</div>
<div class="two-columns"><section class="panel"><div class="panel-heading"><div><span class="eyebrow">HISTORIQUE LIGNE</span><h2>Performance opérationnelle</h2></div></div><div class="route-list"><div><strong>{{ $routeHistory['flights'] }}</strong><span>PIREPs validés</span></div><div><strong>{{ $routeHistory['average_time'] ? floor($routeHistory['average_time']/60).'h '.str_pad($routeHistory['average_time']%60,2,'0',STR_PAD_LEFT) : '—' }}</strong><span>Temps moyen</span></div><div><strong>{{ $routeHistory['best_time'] ? floor($routeHistory['best_time']/60).'h '.str_pad($routeHistory['best_time']%60,2,'0',STR_PAD_LEFT) : '—' }}</strong><span>Meilleur temps</span></div></div>@if($stats['last'])<a href="{{ route('promethee.replay',$stats['last']->id) }}">Rejouer le dernier PIREP →</a>@endif</section><section class="panel"><div class="panel-heading"><div><span class="eyebrow">MÉTÉO PRÉPARATION</span><h2>METAR &amp; TAF</h2></div></div>@forelse($weather as $icao=>$report)<article class="event"><h3>{{ $icao }}</h3><p><strong>METAR</strong></p><p class="mono preserve">{{ is_string($report['metar'] ?? null) ? $report['metar'] : 'METAR indisponible.' }}</p><p><strong>TAF</strong></p><p class="mono preserve">{{ is_string($report['taf'] ?? null) ? $report['taf'] : 'TAF indisponible.' }}</p></article>@empty<p>La météo sera affichée lorsque le fournisseur est accessible.</p>@endforelse</section></div>
<section class="panel table-wrap"><div class="panel-heading"><div><span class="eyebrow">HISTORIQUE</span><h2>Derniers rapports sur cette ligne</h2></div></div><table><thead><tr><th>Vol</th><th>Pilote</th><th>Appareil</th><th>Durée</th><th>Atterrissage</th><th>Date</th><th></th></tr></thead><tbody>@forelse($recentPireps as $pirep)<tr><td>{{ $pirep->ident }}</td><td>{{ $pirep->user?->pilot_id }} · {{ $pirep->user?->name }}</td><td>{{ $pirep->aircraft?->registration ?: '—' }}</td><td>{{ floor($pirep->flight_time / 60) }} h {{ $pirep->flight_time % 60 }} min</td><td>{{ $pirep->landing_rate !== null ? $pirep->landing_rate.' ft/min' : '—' }}</td><td>{{ optional($pirep->submitted_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') }}</td><td><a href="{{ route('promethee.replay',$pirep->id) }}">Revoir →</a></td></tr>@empty<tr><td colspan="7">Aucun PIREP validé pour cette ligne.</td></tr>@endforelse</tbody></table></section>
@endsection
