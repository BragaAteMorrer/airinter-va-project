@extends('promethee::layout')
@section('title', 'Rapport de vol')
@section('content')
@php
    use App\Models\Enums\PirepState;
    use App\Models\Enums\PirepStatus;
    use App\Models\Enums\PirepSource;
    $duration = fn ($minutes) => $minutes === null ? '—' : floor($minutes / 60).' h '.str_pad($minutes % 60, 2, '0', STR_PAD_LEFT).' min';
    $state = PirepState::label($pirep->state);
    $status = $pirep->status ? PirepStatus::label($pirep->status) : null;
    $actualPath = $pirep->acars->filter(fn ($point) => is_numeric($point->lat) && is_numeric($point->lon))->map(fn ($point) => [(float) $point->lat, (float) $point->lon])->values();
    $plannedPath = $pirep->acars_route->filter(fn ($point) => is_numeric($point->lat) && is_numeric($point->lon))->map(fn ($point) => [(float) $point->lat, (float) $point->lon])->values();
    $airportPath = collect([$pirep->dpt_airport, $pirep->arr_airport])->filter(fn ($airport) => is_numeric($airport->lat) && is_numeric($airport->lon))->map(fn ($airport) => [(float) $airport->lat, (float) $airport->lon])->values();
    $mapPath = $actualPath->isNotEmpty() ? $actualPath : ($plannedPath->isNotEmpty() ? $plannedPath : $airportPath);
    $isOwner = auth()->check() && auth()->id() === $pirep->user_id;
@endphp
<div class="ops-header compact report-heading">
  <div><span class="eyebrow">RAPPORT DE VOL · AIR INTER</span><h1>{{ $pirep->ident }}</h1><p>{{ $pirep->dpt_airport_id }} → {{ $pirep->arr_airport_id }} · {{ optional($pirep->submitted_at)->setTimezone('Europe/Paris')->format('d/m/Y') ?? 'En préparation' }}</p></div>
  <div class="toolbar no-print">
    @if($isOwner && !$pirep->read_only)
      <a class="button outline" href="{{ route('frontend.pireps.edit', $pirep->id) }}">Modifier le rapport</a>
    @endif
    @if($isOwner && !$pirep->read_only)
      <form method="post" action="{{ route('frontend.pireps.submit', $pirep->id) }}">@csrf<button type="submit">Soumettre</button></form>
    @endif
    @if($pirep->simbrief)
      <a class="button outline" href="{{ route('frontend.simbrief.briefing', $pirep->simbrief->id) }}">Ouvrir SimBrief</a>
    @endif
  </div>
</div>
<section class="control-strip report-strip">
  <article><span>État</span><strong>{{ $state }}</strong><small>{{ $status ?: 'Rapport de vol' }}</small></article><article><span>Temps de vol</span><strong>{{ $duration($pirep->flight_time) }}</strong><small>Bloc : {{ $duration($pirep->block_time) }}</small></article><article><span>Distance</span><strong>{{ $pirep->distance ? number_format($pirep->distance->toUnit('nmi'), 0, ',', ' ') : '—' }}</strong><small>milles nautiques</small></article><article><span>Atterrissage</span><strong>{{ $pirep->landing_rate !== null ? number_format($pirep->landing_rate, 0, ',', ' ') : '—' }}</strong><small>ft/min{{ $pirep->score !== null ? ' · score '.$pirep->score : '' }}</small></article>
</section>
<section class="panel route-summary"><div class="airport-card"><span class="eyebrow">DÉPART</span><strong>{{ $pirep->dpt_airport_id }}</strong><p>{{ $pirep->dpt_airport?->full_name ?: $pirep->dpt_airport?->name }}</p><small>{{ $pirep->block_off_time ? $pirep->block_off_time->setTimezone('Europe/Paris')->format('d/m/Y · H:i') : 'Heure non relevée' }}</small></div><div class="route-line"><i>✈</i><span>{{ $pirep->progress_percent }}% du trajet</span></div><div class="airport-card arrival"><span class="eyebrow">ARRIVÉE</span><strong>{{ $pirep->arr_airport_id }}</strong><p>{{ $pirep->arr_airport?->full_name ?: $pirep->arr_airport?->name }}</p><small>{{ $pirep->block_on_time ? $pirep->block_on_time->setTimezone('Europe/Paris')->format('d/m/Y · H:i') : 'Heure non relevée' }}</small></div></section>
<div class="report-tabs" role="tablist"><button class="active" data-report-tab="map" role="tab">Carte et trace</button><button data-report-tab="log" role="tab">Journal de vol <span>{{ $pirep->acars_logs->count() }}</span></button><button data-report-tab="details" role="tab">Informations</button></div>
<section class="panel report-tab-panel" data-report-panel="map"><div class="panel-heading"><div><span class="eyebrow">TRAJECTOIRE</span><h2>Route effectuée</h2></div><small class="mono muted">{{ $actualPath->isNotEmpty() ? 'Trace ACARS' : ($plannedPath->isNotEmpty() ? 'Route planifiée' : 'Liaison aéroports') }}</small></div><div id="pirep-map" class="ops-leaflet-map"></div>@if($pirep->route)<p class="report-route mono">{{ $pirep->route }}</p>@endif</section>
<section class="panel report-tab-panel" data-report-panel="log" hidden><div class="panel-heading"><div><span class="eyebrow">ACARS</span><h2>Journal chronologique</h2></div></div><div class="flight-log">@forelse($pirep->acars_logs->sortBy('created_at') as $log)<article><time>{{ optional($log->created_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i:s') }}</time><p>{{ $log->log }}</p></article>@empty<p class="empty">Aucun journal ACARS n’a été enregistré pour ce rapport.</p>@endforelse</div></section>
<section class="report-tab-panel" data-report-panel="details" hidden>
  <div class="two-columns report-details-grid"><section class="panel"><div class="panel-heading"><div><span class="eyebrow">VOL</span><h2>Informations de mission</h2></div></div><dl class="report-list"><div><dt>Pilote</dt><dd>{{ $pirep->user?->pilot_id }} · {{ $pirep->user?->name ?: '—' }}</dd></div><div><dt>Appareil</dt><dd>{{ $pirep->aircraft?->registration ?: '—' }} {{ $pirep->aircraft?->icao ? '· '.$pirep->aircraft->icao : '' }}</dd></div><div><dt>Source</dt><dd>{{ PirepSource::label($pirep->source) }}</dd></div><div><dt>Niveau / type</dt><dd>{{ $pirep->level ? 'FL'.$pirep->level : '—' }} · {{ \App\Models\Enums\FlightType::label($pirep->flight_type) }}</dd></div><div><dt>Route déposée</dt><dd class="mono">{{ $pirep->route ?: '—' }}</dd></div>
@if($pirep->notes)<div><dt>Notes</dt><dd>{{ $pirep->notes }}</dd></div>@endif
  </dl></section><section class="panel"><div class="panel-heading"><div><span class="eyebrow">EXPLOITATION</span><h2>Carburant et classes</h2></div></div><dl class="report-list"><div><dt>Carburant bloc</dt><dd>{{ $pirep->block_fuel ? number_format($pirep->block_fuel->local(), 0, ',', ' ') .' '.$pirep->block_fuel->localUnit : '—' }}</dd></div><div><dt>Carburant utilisé</dt><dd>{{ $pirep->fuel_used ? number_format($pirep->fuel_used->local(), 0, ',', ' ') .' '.$pirep->fuel_used->localUnit : '—' }}</dd></div>
@forelse($pirep->fares as $fare)<div><dt>{{ $fare->name }} ({{ $fare->code }})</dt><dd>{{ $fare->count }} pax</dd></div>@empty<div><dt>Classes</dt><dd>Aucune donnée passager</dd></div>@endforelse
  </dl></section></div>
@if($pirep->field_values->isNotEmpty())
  <section class="panel"><div class="panel-heading"><div><span class="eyebrow">CHAMPS COMPLÉMENTAIRES</span><h2>Rapport détaillé</h2></div></div><dl class="report-list two-up">@foreach($pirep->field_values as $field)<div><dt>{{ $field->name }}</dt><dd>{{ $field->value ?: '—' }}</dd></div>@endforeach</dl></section>
@endif
@if($pirep->comments->isNotEmpty())
  <section class="panel"><div class="panel-heading"><div><span class="eyebrow">ÉCHANGES</span><h2>Commentaires</h2></div></div><div class="flight-log">@foreach($pirep->comments as $comment)<article><time>{{ $comment->user?->name ?: 'Équipe Air Inter' }} · {{ optional($comment->created_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') }}</time><p>{{ $comment->comment }}</p></article>@endforeach</div></section>
@endif
</section>
@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script><script>(()=>{const tabs=document.querySelectorAll('[data-report-tab]'),panels=document.querySelectorAll('[data-report-panel]');tabs.forEach(tab=>tab.addEventListener('click',()=>{tabs.forEach(t=>t.classList.toggle('active',t===tab));panels.forEach(p=>p.hidden=p.dataset.reportPanel!==tab.dataset.reportTab);window.dispatchEvent(new Event('resize'));}));const path=@json($mapPath),mapNode=document.querySelector('#pirep-map');if(!mapNode||!window.L)return;const map=L.map(mapNode,{scrollWheelZoom:false,minZoom:2,maxZoom:14});L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);if(path.length>1){L.polyline(path,{color:'#155cb8',weight:4,opacity:.8}).addTo(map);L.marker(path[0]).bindTooltip('Départ').addTo(map);L.marker(path[path.length-1]).bindTooltip('Arrivée').addTo(map);map.fitBounds(path,{padding:[35,35],maxZoom:9});}else if(path.length){L.marker(path[0]).addTo(map);map.setView(path[0],7);}else{mapNode.innerHTML='<p class="empty">Aucune coordonnée n’est disponible pour tracer ce vol.</p>';}})();</script>
@endpush
@endsection
