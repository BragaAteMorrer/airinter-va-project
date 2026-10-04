@extends('promethee::layout')
@section('title','Programme des vols')
@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-flight-results.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-flight-results.css')) }}">
@endpush
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">LE RÉSEAU AIR INTER</span><h1>Programme des vols.</h1><p>Filtrer, comparer et préparer une rotation sans quitter Prométhée.</p></div><span class="tag metric-tag">@if($flights->total()>0){{ $flights->total() }} lignes publiées @elseif($itineraries->isNotEmpty()){{ $itineraries->count() }} itinéraire{{ $itineraries->count()>1?'s':'' }} proposé{{ $itineraries->count()>1?'s':'' }} @else 0 ligne publiée @endif</span></div>
<form id="flight-filters" class="panel flight-filter" method="get">
<div class="flight-filter-primary">
<label class="filter-wide">Vol, aéroport ou ville<input name="q" value="{{ request('q') }}" list="flight-airports" placeholder="IT123, LFPO, ORY, Paris, Orly…" autocomplete="off"></label>
<label>Départ<input name="departure" value="{{ request('departure') }}" list="flight-airports" placeholder="OACI, IATA ou ville" autocomplete="off"></label>
<label>Arrivée<input name="arrival" value="{{ request('arrival') }}" list="flight-airports" placeholder="OACI, IATA ou ville" autocomplete="off"></label>
<div class="flight-filter-actions"><button type="submit">Rechercher</button><a class="text-button" href="{{ route('promethee.flights') }}">Réinitialiser</a></div>
</div>
<details class="flight-filter-advanced" @if(request()->filled('airline_id') || request()->filled('subfleet_id') || request()->filled('flight_type') || request()->filled('time_from') || request()->filled('time_to') || request()->filled('min_distance') || request()->filled('max_distance') || request('sort','departure') !== 'departure') open @endif>
<summary><strong>Filtres avancés</strong><span>Compagnie, flotte, horaires, distance et tri</span><b aria-hidden="true">⌄</b></summary>
<div class="flight-filter-advanced-grid">
<label>Compagnie<select name="airline_id"><option value="">Toutes</option>@foreach($airlines as $airline)<option value="{{ $airline->id }}" @selected((string)request('airline_id')===(string)$airline->id)>{{ $airline->icao }} · {{ $airline->name }}</option>@endforeach</select></label>
<label>Appareil / flotte<select name="subfleet_id"><option value="">Tous</option>@foreach($subfleets as $subfleet)<option value="{{ $subfleet->id }}" @selected((string)request('subfleet_id')===(string)$subfleet->id)>{{ $subfleet->type }} · {{ $subfleet->name }}</option>@endforeach</select></label>
<label>Type<select name="flight_type"><option value="">Tous</option>@foreach($flightTypes as $code=>$label)<option value="{{ $code }}" @selected(request('flight_type')===$code)>{{ $code }} · {{ $label }}</option>@endforeach</select></label>
<label>Départ après<input name="time_from" type="time" value="{{ request('time_from') }}"></label>
<label>Départ avant<input name="time_to" type="time" value="{{ request('time_to') }}"></label>
<label>Distance min. (NM)<input name="min_distance" type="number" min="0" value="{{ request('min_distance') }}"></label>
<label>Distance max. (NM)<input name="max_distance" type="number" min="0" value="{{ request('max_distance') }}"></label>
<label>Trier par<select name="sort"><option value="departure" @selected(request('sort','departure')==='departure')>Heure de départ</option><option value="ident" @selected(request('sort')==='ident')>Numéro de vol</option><option value="distance" @selected(request('sort')==='distance')>Distance</option></select></label>
</div>
<div class="flight-filter-advanced-actions"><button type="submit">Appliquer tous les filtres</button></div>
</details>
<datalist id="flight-airports">
@foreach($mapAirports as $airport)
@php
    $airportCode = (string) ($airport['code'] ?? '');
    $airportIata = trim((string) ($airport['iata'] ?? ''));
    $airportName = trim((string) ($airport['name'] ?? ''));
    $airportLocation = trim((string) ($airport['location'] ?? ''));
    $primaryLabel = collect([$airportName, $airportIata, $airportLocation])->filter()->implode(' · ');
    $iataLabel = collect([$airportCode, $airportName, $airportLocation])->filter()->implode(' · ');
    $locationPrefix = $airportIata !== '' ? $airportCode.' / '.$airportIata : $airportCode;
    $locationLabel = collect([$locationPrefix, $airportName])->filter()->implode(' · ');
    $namePrefix = $airportIata !== '' ? $airportCode.' / '.$airportIata : $airportCode;
    $nameLabel = collect([$namePrefix, $airportLocation])->filter()->implode(' · ');
@endphp
<option value="{{ $airportCode }}" label="{{ $primaryLabel }}"></option>
@if($airportIata !== '')
<option value="{{ $airportIata }}" label="{{ $iataLabel }}"></option>
@endif
@if($airportLocation !== '')
<option value="{{ $airportLocation }}" label="{{ $locationLabel }}"></option>
@endif
@if($airportName !== '' && $airportName !== $airportLocation)
<option value="{{ $airportName }}" label="{{ $nameLabel }}"></option>
@endif
@endforeach
</datalist>
</form>
<section class="panel network-map-panel" aria-labelledby="network-map-title"><div class="panel-heading"><div><span class="eyebrow">SÉLECTION PAR CARTE</span><h2 id="network-map-title">Choisir l’itinéraire</h2></div><p class="map-help">Molette ou boutons +/− pour zoomer ; glissez pour vous déplacer. Cliquez un aéroport pour le départ, puis un second pour l’arrivée.</p></div><div class="map-selection" aria-live="polite"><span>Départ : <b>{{ $selectedDeparture ?: 'à sélectionner' }}</b></span><span>Arrivée : <b>{{ $selectedArrival ?: 'à sélectionner' }}</b></span>@if(request('departure')||request('arrival'))<a href="{{ route('promethee.flights',request()->except(['departure','arrival','page'])) }}">Effacer la sélection</a>@endif</div><div id="flight-network-map" class="network-map" aria-label="Carte interactive des aéroports desservis"></div><p class="map-legend"><i></i> Aéroport desservi <span class="route-legend air-inter"></span> Air Inter <span class="route-legend air-charter"></span> Air Charter <span class="route-legend ics"></span> Inter Cargo Services</p></section>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(()=>{
  const airports=@json($mapAirports), routes=@json($mapRoutes), departure=@json($selectedDeparture), arrival=@json($selectedArrival), base=@json(route('promethee.flights'));
  const mapNode=document.querySelector('#flight-network-map');
  if(!window.L){mapNode.textContent='La bibliothèque cartographique est indisponible. Vérifiez votre connexion Internet.';return;}
  const stateKey='promethee.flight-map.viewport', scrollKey='promethee.flight-map.scroll';
  let saved=null;
  try{saved=JSON.parse(sessionStorage.getItem(stateKey)||'null');}catch(_){}
  const map=L.map('flight-network-map',{scrollWheelZoom:true,zoomControl:true,minZoom:2,maxZoom:12});
  if(saved&&Number.isFinite(saved.lat)&&Number.isFinite(saved.lng)&&Number.isFinite(saved.zoom)) map.setView([saved.lat,saved.lng],saved.zoom);
  else map.setView([46.6,2.5],5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'© OpenStreetMap'}).addTo(map);
  const saveViewport=()=>{const center=map.getCenter();sessionStorage.setItem(stateKey,JSON.stringify({lat:center.lat,lng:center.lng,zoom:map.getZoom()}));};
  const saveContext=()=>{saveViewport();sessionStorage.setItem(scrollKey,String(window.scrollY));};
  map.on('moveend zoomend',saveViewport);
  document.querySelector('#flight-filters')?.addEventListener('submit',saveContext);
  const points=[];
  const go=code=>{saveContext();const q=new URLSearchParams(location.search);q.delete('page');if(!departure||arrival){q.set('departure',code);q.delete('arrival');}else if(code!==departure){q.set('departure',departure);q.set('arrival',code);}location.href=base+'?'+q.toString();};
  airports.forEach(a=>{const selected=a.code===departure||a.code===arrival;const marker=L.circleMarker([a.lat,a.lon],{radius:selected?8:5,weight:selected?3:1,color:'#fff',fillColor:a.code===arrival?'#d92732':'#0b5cad',fillOpacity:1}).addTo(map);marker.bindTooltip(`<b>${a.code}</b><br>${a.name}`,{direction:'top'});marker.on('click',()=>go(a.code));points.push([a.lat,a.lon]);});
  const airlineColors={'air-inter':'#0b5cad','air-charter':'#d92732','ics':'#e0ad16'};
  routes.forEach(r=>{
    if(!r.from||!r.to)return;
    const color=airlineColors[r.airline]||airlineColors['air-inter'];
    const line=L.polyline([[r.from.lat,r.from.lon],[r.to.lat,r.to.lon]],{color,weight:3,opacity:.78}).addTo(map);
    if(r.airline_name) line.bindTooltip(r.airline_name,{sticky:true});
  });
  if(!saved&&points.length)map.fitBounds(points,{padding:[30,30],maxZoom:6});
  const previousScroll=Number(sessionStorage.getItem(scrollKey));
  if(Number.isFinite(previousScroll)&&previousScroll>0){requestAnimationFrame(()=>window.scrollTo({top:previousScroll,left:0,behavior:'auto'}));sessionStorage.removeItem(scrollKey);}
})();
</script>
@if($itineraries->isNotEmpty())
<section class="panel itinerary-panel" aria-labelledby="itinerary-title">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">CORRESPONDANCES PROPOSÉES</span>
      <h2 id="itinerary-title">Aucun direct : voici les itinéraires possibles.</h2>
    </div>
    <span class="tag">Maximum {{ $maxItineraryStops }} escales</span>
  </div>
  <p class="muted itinerary-intro">Prométhée utilise uniquement des lignes réellement publiées. Chaque tronçon reste un vol indépendant dans les réservations, Hermès et les PIREP.</p>
  <div class="itinerary-grid">
    @foreach($itineraries as $index=>$itinerary)
      @php
        $hours=intdiv((int)$itinerary['flight_time'],60);
        $minutes=(int)$itinerary['flight_time']%60;
        $allReserved=collect($itinerary['legs'])->every(fn($leg)=>in_array((string)$leg->id,$reservedFlightIds,true));
      @endphp
      <article class="itinerary-card">
        <div class="line-card-head">
          <div>
            <span class="eyebrow">OPTION {{ $index+1 }} · {{ $itinerary['stops'] }} ESCALE{{ $itinerary['stops']>1?'S':'' }}</span>
            <h3>{{ implode(' → ',$itinerary['airports']) }}</h3>
          </div>
          <span class="tag">{{ count($itinerary['legs']) }} vols</span>
        </div>
        <div class="itinerary-summary">
          <span>{{ number_format($itinerary['distance_nm'],0,',',' ') }} NM</span>
          <span>@if($itinerary['flight_time']>0){{ $hours }} h {{ str_pad((string)$minutes,2,'0',STR_PAD_LEFT) }} de vol @else Temps à confirmer @endif</span>
        </div>
        <div class="itinerary-legs">
          @foreach($itinerary['legs'] as $legIndex=>$leg)
            <div class="itinerary-leg">
              <div class="itinerary-leg-index">{{ $legIndex+1 }}</div>
              <div>
                <strong>{{ $leg->ident }}</strong>
                <span>{{ $leg->dpt_airport_id }} → {{ $leg->arr_airport_id }}</span>
              </div>
              <div class="itinerary-leg-time">
                <span>{{ $leg->dpt_time ?: '—' }} → {{ $leg->arr_time ?: '—' }}</span>
                <small>{{ number_format($leg->distance->toUnit('nmi'),0,',',' ') }} NM</small>
              </div>
              <div class="itinerary-leg-actions">
                @if(in_array((string)$leg->id,$reservedFlightIds,true))
                  <span class="tag">Déjà réservé</span>
                @endif
                <a class="round-link" aria-label="Voir {{ $leg->ident }}" href="{{ route('promethee.flights.show',$leg->id) }}">→</a>
              </div>
            </div>
          @endforeach
        </div>
        <form class="itinerary-reserve" method="post" action="{{ route('promethee.flights.itineraries.reserve') }}">
          @csrf
          <input type="hidden" name="departure" value="{{ $selectedDeparture }}">
          <input type="hidden" name="arrival" value="{{ $selectedArrival }}">
          @foreach($itinerary['legs'] as $leg)<input type="hidden" name="flight_ids[]" value="{{ $leg->id }}">@endforeach
          <button type="submit">{{ $allReserved ? 'Itinéraire déjà réservé' : 'Réserver les '.count($itinerary['legs']).' vols' }}</button>
        </form>
      </article>
    @endforeach
  </div>
</section>
@endif
<section class="panel flight-results-panel" aria-labelledby="flight-results-title">
<div class="panel-heading"><div><span class="eyebrow">VOLS DIRECTS</span><h2 id="flight-results-title">Lignes publiées</h2></div><span class="tag">{{ $flights->total() }} résultat{{ $flights->total()>1?'s':'' }}</span></div>
@if($flights->count())
<div class="table-wrap">
<table class="flight-results">
<thead><tr><th>Vol</th><th>Itinéraire</th><th>Horaire</th><th>Distance</th><th>Flotte</th><th>Action</th></tr></thead>
<tbody>
@foreach($flights as $flight)
<tr>
<td data-label="Vol"><a class="flight-result-ident" href="{{ route('promethee.flights.show',$flight->id) }}">{{ $flight->ident }}</a><small>{{ $flight->airline?->name ?? 'Air Inter' }}</small></td>
<td data-label="Itinéraire"><div class="flight-result-route"><strong title="{{ $flight->dpt_airport?->name }}">{{ $flight->dpt_airport_id }}</strong><span>→</span><strong title="{{ $flight->arr_airport?->name }}">{{ $flight->arr_airport_id }}</strong></div><small>{{ $flight->dpt_airport?->location ?: $flight->dpt_airport_id }} → {{ $flight->arr_airport?->location ?: $flight->arr_airport_id }}</small></td>
<td data-label="Horaire">
  <div @class(['flight-result-schedule', 'is-soon' => $flight->next_departure_soon])>
    <strong>
      <time @if($flight->next_departure_iso) datetime="{{ $flight->next_departure_iso }}" @endif>{{ $flight->next_departure_time ?: '—' }}</time>
      <span aria-hidden="true">→</span>
      <time @if($flight->next_arrival_iso) datetime="{{ $flight->next_arrival_iso }}" @endif>{{ $flight->next_arrival_time ?: '—' }}</time>
    </strong>
    @if($flight->next_departure_relative)
      <small>{{ $flight->next_departure_relative }}</small>
    @else
      <small>Horaire publié</small>
    @endif
  </div>
</td>
<td data-label="Distance" class="mono">{{ number_format($flight->distance->toUnit('nmi'),0,',',' ') }} NM</td>
<td data-label="Flotte"><span class="flight-equipment-list">@forelse($flight->subfleets as $subfleet)<span>{{ $subfleet->type }}</span>@empty<em>À confirmer</em>@endforelse</span></td>
<td data-label="Action" class="flight-result-action-cell"><a class="round-link" aria-label="Préparer {{ $flight->ident }}" href="{{ route('promethee.flights.show',$flight->id) }}">→</a></td>
</tr>
@endforeach
</tbody>
</table>
</div>
@else
<div class="empty flight-results-empty">
@if($itineraries->isNotEmpty())<h2>Aucun vol direct ne correspond à la recherche.</h2><p>Des correspondances réalisables sont proposées juste au-dessus.</p>
@elseif($selectedDeparture && $selectedArrival)<h2>Aucun vol direct ni itinéraire avec jusqu’à {{ $maxItineraryStops }} escales.</h2><p>Élargissez un filtre, changez de compagnie/appareil ou réinitialisez la recherche.</p>
@else<h2>Aucun vol ne correspond à la recherche.</h2><p>Élargissez un filtre ou réinitialisez la recherche.</p>@endif
</div>
@endif
</section>
{{ $flights->links('pagination::bootstrap-4') }}
@endsection
