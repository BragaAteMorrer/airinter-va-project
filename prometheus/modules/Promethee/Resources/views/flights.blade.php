@extends('promethee::layout')
@section('title','Programme des vols')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">LE RÉSEAU AIR INTER</span><h1>Programme des vols.</h1><p>Filtrer, comparer et préparer une rotation sans quitter Prométhée.</p></div><span class="tag metric-tag">{{ $flights->total() }} lignes publiées</span></div>
<form id="flight-filters" class="panel filters flight-filter" method="get">
<label class="filter-wide">Vol ou aéroport<input name="q" value="{{ request('q') }}" placeholder="IT123, LFPO, code ligne…"></label>
<label>Départ<input name="departure" value="{{ request('departure') }}" list="flight-airports" placeholder="OACI, IATA ou ville" autocomplete="off"></label>
<label>Arrivée<input name="arrival" value="{{ request('arrival') }}" list="flight-airports" placeholder="OACI, IATA ou ville" autocomplete="off"></label>
<datalist id="flight-airports">@foreach($mapAirports as $airport)<option value="{{ $airport['code'] }}">{{ $airport['name'] }}@if($airport['location']) · {{ $airport['location'] }}@endif</option>@endforeach</datalist>
<label>Compagnie<select name="airline_id"><option value="">Toutes</option>@foreach($airlines as $airline)<option value="{{ $airline->id }}" @selected((string)request('airline_id')===(string)$airline->id)>{{ $airline->icao }} · {{ $airline->name }}</option>@endforeach</select></label>
<label>Appareil / flotte<select name="subfleet_id"><option value="">Tous</option>@foreach($subfleets as $subfleet)<option value="{{ $subfleet->id }}" @selected((string)request('subfleet_id')===(string)$subfleet->id)>{{ $subfleet->type }} · {{ $subfleet->name }}</option>@endforeach</select></label>
<label>Type<select name="flight_type"><option value="">Tous</option>@foreach($flightTypes as $code=>$label)<option value="{{ $code }}" @selected(request('flight_type')===$code)>{{ $code }} · {{ $label }}</option>@endforeach</select></label>
<label>Départ après<input name="time_from" type="time" value="{{ request('time_from') }}"></label><label>Départ avant<input name="time_to" type="time" value="{{ request('time_to') }}"></label><label>Distance min. (NM)<input name="min_distance" type="number" min="0" value="{{ request('min_distance') }}"></label><label>Distance max. (NM)<input name="max_distance" type="number" min="0" value="{{ request('max_distance') }}"></label>
<label>Trier par<select name="sort"><option value="departure" @selected(request('sort','departure')==='departure')>Heure de départ</option><option value="ident" @selected(request('sort')==='ident')>Numéro de vol</option><option value="distance" @selected(request('sort')==='distance')>Distance</option></select></label><button>Appliquer les filtres</button><a href="{{ route('promethee.flights') }}">Réinitialiser</a>
</form>
<section class="panel network-map-panel" aria-labelledby="network-map-title"><div class="panel-heading"><div><span class="eyebrow">SÉLECTION PAR CARTE</span><h2 id="network-map-title">Choisir l’itinéraire</h2></div><p class="map-help">Molette ou boutons +/− pour zoomer ; glissez pour vous déplacer. Cliquez un aéroport pour le départ, puis un second pour l’arrivée.</p></div><div class="map-selection" aria-live="polite"><span>Départ : <b>{{ $selectedDeparture ?: 'à sélectionner' }}</b></span><span>Arrivée : <b>{{ $selectedArrival ?: 'à sélectionner' }}</b></span>@if(request('departure')||request('arrival'))<a href="{{ route('promethee.flights',request()->except(['departure','arrival','page'])) }}">Effacer la sélection</a>@endif</div><div id="flight-network-map" class="network-map" aria-label="Carte interactive des aéroports desservis"></div><p class="map-legend"><i></i> Aéroport desservi <i class="legend-route"></i> Route correspondant aux filtres</p></section>
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
  routes.forEach(r=>r.from&&r.to&&L.polyline([[r.from.lat,r.from.lon],[r.to.lat,r.to.lon]],{color:'#0b5cad',weight:2,opacity:.65}).addTo(map));
  if(!saved&&points.length)map.fitBounds(points,{padding:[30,30],maxZoom:6});
  const previousScroll=Number(sessionStorage.getItem(scrollKey));
  if(Number.isFinite(previousScroll)&&previousScroll>0){requestAnimationFrame(()=>window.scrollTo({top:previousScroll,left:0,behavior:'auto'}));sessionStorage.removeItem(scrollKey);}
})();
</script>
<section class="flight-cards">@forelse($flights as $flight)
@php
    $airlineName = strtolower($flight->airline?->name ?? 'Air Inter');
    $airlineClass = str_contains($airlineName, 'air charter')
        ? 'airline-air-charter'
        : (str_contains($airlineName, 'inter cargo') ? 'airline-ics' : 'airline-air-inter');
@endphp
<article class="panel line-card {{ $airlineClass }}"><div class="line-card-head"><div><span class="eyebrow">{{ $flight->airline?->name ?? 'Air Inter' }}</span><h2>{{ $flight->ident }}</h2></div><a class="round-link" aria-label="Préparer {{ $flight->ident }}" href="{{ route('promethee.flights.show',$flight->id) }}">→</a></div><div class="airport-pair"><strong title="{{ $flight->dpt_airport?->name }}">{{ $flight->dpt_airport_id }}</strong><i></i><strong title="{{ $flight->arr_airport?->name }}">{{ $flight->arr_airport_id }}</strong></div><p class="muted">{{ $flight->dpt_airport?->location ?: $flight->dpt_airport_id }} → {{ $flight->arr_airport?->location ?: $flight->arr_airport_id }}</p><dl class="line-meta"><div><dt>Départ</dt><dd>{{ $flight->dpt_time ?: '—' }}</dd></div><div><dt>Arrivée</dt><dd>{{ $flight->arr_time ?: '—' }}</dd></div><div><dt>Distance</dt><dd>{{ number_format($flight->distance->toUnit('nmi'),0,',',' ') }} NM</dd></div></dl><div class="fare-chips">@forelse($flight->subfleets as $subfleet)<span>{{ $subfleet->type }}</span>@empty<span>Flotte à confirmer</span>@endforelse</div></article>@empty<section class="panel empty"><h2>Aucun vol ne correspond à la recherche.</h2><p>Élargissez un filtre ou réinitialisez la recherche.</p></section>@endforelse</section>
{{ $flights->links('pagination::bootstrap-4') }}
@endsection
