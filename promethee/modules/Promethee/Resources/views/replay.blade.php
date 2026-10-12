@extends('promethee::layout')
@section('title','Relecture de vol')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">RETOUR D’EXPÉRIENCE</span><h1>{{ $pirep->ident }}</h1><p>{{ $pirep->dpt_airport_id }} → {{ $pirep->arr_airport_id }} · {{ $pirep->user?->name }}</p></div><span class="tag">Score {{ $score['score'] }}/100</span></div>
<section class="control-strip"><article><span>Échantillons</span><strong>{{ $samples->count() }}</strong><small>Trace enregistrée</small></article><article><span>Atterrissage</span><strong>{{ $pirep->landing_rate ? number_format($pirep->landing_rate,0,',',' ') : '—' }}</strong><small>ft/min</small></article><article><span>Survitesse</span><strong>{{ $analysis['overspeed'] === null ? 'n/a' : ($analysis['overspeed'] ? 'Oui' : 'Non') }}</strong><small>{{ $analysis['speed_events'] }} événement(s)</small></article><article><span>Approche</span><strong>{{ count($analysis['approaches']) }}</strong><small>Checkpoint(s) évalué(s)</small></article></section>
<section class="panel"><div class="panel-heading"><div><span class="eyebrow">LECTEUR DE VOL · PIREP {{ $pirep->id }}</span><h2>Replay synchronisé</h2><a href="{{ route('promethee.pireps.show', $pirep->id) }}">Retour au PIREP</a></div><div class="replay-controls"><button type="button" id="replay-play">▶ Lecture</button><label for="replay-speed">Vitesse</label><select id="replay-speed"><option value="1">×1</option><option value="5" selected>×5</option><option value="15">×15</option><option value="60">×60</option></select><span id="replay-clock" aria-live="off">—</span></div></div><div class="replay-graphs"><article><h3>Altitude · ft</h3><svg id="replay-altitude" viewBox="0 0 640 140" role="img" aria-label="Profil vertical synchronisé"></svg></article><article><h3>IAS et GS · kt</h3><svg id="replay-speeds" viewBox="0 0 640 140" role="img" aria-label="Vitesses synchronisées"></svg></article><article><h3>Carburant · unités simulateur</h3><svg id="replay-fuel" viewBox="0 0 640 140" role="img" aria-label="Carburant synchronisé"></svg></article></div></section>
<section class="panel"><div class="panel-heading"><div><span class="eyebrow">TRACE ET CURSEUR</span><h2>Comparaison des routes</h2><div class="replay-route-legend" aria-label="Légende des routes"><span><i class="actual"></i> Route effectuée par le pilote</span><span><i class="planned"></i> Route prévue du PIREP</span></div></div><input id="replay-range" aria-label="Position dans le vol" type="range" min="0" max="{{ max(0,$samples->count()-1) }}" value="0" {{ $samples->isEmpty() ? 'disabled' : '' }}></div><div id="replay-map" class="ops-leaflet-map"></div><p id="replay-detail" class="mono muted"></p></section>
<section class="panel"><div class="panel-heading"><div><span class="eyebrow">JOURNAL ACARS</span><h2>Chronologie des événements</h2></div></div><div id="replay-events" class="replay-timeline"></div></section>
<div class="two-columns"><section class="panel"><div class="panel-heading"><div><span class="eyebrow">ÉVÉNEMENTS DÉTECTÉS</span><h2>Phases de vol</h2></div></div>@forelse($score['events'] as $event)<article class="event"><time>{{ \Carbon\Carbon::parse($event['at'])->setTimezone('Europe/Paris')->format('H:i:s') }}</time><h3>{{ $event['type'] }}</h3><p>{{ $event['detail'] }}</p></article>@empty<p class="empty">Aucune phase ne peut être confirmée : la télémétrie est incomplète.</p>@endforelse</section><section class="panel"><div class="panel-heading"><div><span class="eyebrow">SCORE EXPLICABLE</span><h2>Qualité du vol</h2></div></div>@forelse($score['items'] as $item)<article class="event"><h3>{{ $item['label'] }} · {{ $item['score'] }}/{{ $item['max'] }}</h3><p>{{ $item['detail'] }}</p></article>@empty<p class="empty">Aucun indicateur ne peut être calculé sans données correspondantes.</p>@endforelse</section></div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
.replay-route-legend{display:flex;flex-wrap:wrap;gap:.65rem 1rem;margin-top:.45rem;font-size:.75rem;color:#66798c}.replay-route-legend span{display:inline-flex;align-items:center;gap:.4rem}.replay-route-legend i{display:inline-block;width:28px;border-top:4px solid;border-radius:999px}.replay-route-legend i.actual{border-color:#155eef}.replay-route-legend i.planned{border-color:#f4c430;border-top-style:dashed}
.replay-controls{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap}.replay-controls button,.replay-controls select{padding:.45rem .7rem;border:1px solid #8899ae;border-radius:6px;background:transparent;color:inherit}.replay-graphs{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,300px),1fr));gap:1rem;margin:1rem 0}.replay-graphs article{min-width:0}.replay-graphs svg{width:100%;background:rgba(100,120,150,.06);border-radius:6px}.replay-graphs h3{font-size:.85rem}.replay-timeline{display:flex;overflow-x:auto;gap:.5rem;padding:.75rem 0}.replay-event{flex:0 0 auto;border:1px solid #8899ae;border-radius:8px;background:transparent;color:inherit;padding:.6rem;cursor:pointer}.replay-event[aria-current=true]{border-color:#155eef;box-shadow:inset 0 0 0 1px #155eef}.replay-event time{display:block;font-size:.75rem}
</style>
<script>
(() => {
  const samples = @json($samples);
  const planned = @json($plannedPath ?? collect());
  const rawEvents = @json($score['events'] ?? []);
  const mapNode = document.getElementById('replay-map');
  const detail = document.getElementById('replay-detail');
  const range = document.getElementById('replay-range');
  const playButton = document.getElementById('replay-play');
  const speed = document.getElementById('replay-speed');
  const clock = document.getElementById('replay-clock');
  const timeline = document.getElementById('replay-events');
  const time = s => { const n = Date.parse(s?.recorded_at); return Number.isFinite(n) ? n : NaN; };
  const numeric = value => value === null || value === undefined || value === '' ? null : Number.isFinite(Number(value)) ? Number(value) : null;
  const validCoordinate = s => numeric(s?.lat) !== null && numeric(s?.lon) !== null && Math.abs(Number(s.lat)) <= 90 && Math.abs(Number(s.lon)) <= 180;
  const trace = samples.filter(validCoordinate).map(s => [Number(s.lat), Number(s.lon)]);
  const plannedTrace = (Array.isArray(planned) ? planned : []).filter(p => Array.isArray(p) && numeric(p[0]) !== null && numeric(p[1]) !== null).map(p => [Number(p[0]), Number(p[1])]);
  let map = null, marker = null, travelled = null, playing = false, frame = null, lastTick = null, virtualTime = null, selected = 0;
  if (window.L) {
    map = L.map(mapNode, {scrollWheelZoom:true,minZoom:2,maxZoom:14});
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);
    if (trace.length) L.polyline(trace,{color:'#155eef',weight:4,opacity:.4}).addTo(map);
    if (plannedTrace.length) L.polyline(plannedTrace,{color:'#f4c430',weight:4,dashArray:'10 8'}).addTo(map);
    travelled = L.polyline([],{color:'#155eef',weight:5}).addTo(map);
    const bounds = [...trace,...plannedTrace];
    if (bounds.length > 1) map.fitBounds(bounds,{padding:[35,35],maxZoom:9});
    else map.setView(bounds[0] || [46.6,2.5], bounds.length ? 7 : 5);
    if (trace.length) marker = L.marker(trace[0],{icon:L.divIcon({className:'aircraft-marker',html:'✈',iconSize:[24,24]})}).addTo(map);
  } else mapNode.textContent = 'Cartographie indisponible : graphiques et chronologie restent accessibles.';
  const graphs = [
    {id:'replay-altitude',keys:['altitude_msl'],colors:['#155eef']},
    {id:'replay-speeds',keys:['ias','gs'],colors:['#155eef','#e5a500']},
    {id:'replay-fuel',keys:['fuel'],colors:['#0b906b']}
  ];
  const svgNs='http://www.w3.org/2000/svg';
  function svgNode(tag, attrs) { const el=document.createElementNS(svgNs,tag);Object.entries(attrs).forEach(([key,value])=>el.setAttribute(key,String(value)));return el; }
  const chartCursors=[];
  graphs.forEach(graph => {
    const svg=document.getElementById(graph.id);
    const series=graph.keys.map(key=>samples.map((s,i)=>({i,value:numeric(s[key])})).filter(point=>point.value!==null));
    const all=series.flat().map(point=>point.value);
    if (!all.length) { const message=svgNode('text',{x:14,y:70,fill:'currentColor'});message.textContent='Données indisponibles';svg.append(message);chartCursors.push(null);return; }
    const min=Math.min(...all),max=Math.max(...all),span=Math.max(1,max-min),x=i=>20+600*i/Math.max(1,samples.length-1),y=v=>120-100*(v-min)/span;
    for(let j=0;j<=4;j++)svg.append(svgNode('line',{x1:20,y1:20+j*25,x2:620,y2:20+j*25,stroke:'#8899ae','stroke-opacity':'.25'}));
    series.forEach((points,i)=>{if(!points.length)return;svg.append(svgNode('path',{d:points.map((p,j)=>(j?'L':'M')+x(p.i).toFixed(2)+' '+y(p.value).toFixed(2)).join(' '),fill:'none',stroke:graph.colors[i],'stroke-width':2}));});
    const cursor=svgNode('line',{x1:20,x2:20,y1:12,y2:127,stroke:'#e45050','stroke-width':2});svg.append(cursor);chartCursors.push(cursor);
    svg.addEventListener('click',event=>{const box=svg.getBoundingClientRect();const ratio=(event.clientX-box.left)/box.width;seek(Math.round(Math.max(0,Math.min(1,(ratio*640-20)/600))*(samples.length-1)));});
  });
  const events=rawEvents.map(e=>({...e,ms:Date.parse(e.at)})).filter(e=>Number.isFinite(e.ms)).sort((a,b)=>a.ms-b.ms);
  const eventButtons=[];
  events.forEach(e=>{
    const button=document.createElement('button');button.type='button';button.className='replay-event';
    const label=document.createElement('strong');label.textContent=e.type || 'ACARS';
    const date=document.createElement('time');date.textContent=new Date(e.ms).toLocaleTimeString('fr-FR');
    const description=document.createElement('small');description.textContent=e.detail || '';
    button.append(date,label,description);
    button.addEventListener('click',()=>{const index=samples.reduce((best,s,i)=>Math.abs(time(s)-e.ms)<Math.abs(time(samples[best])-e.ms)?i:best,0);seek(index);});
    timeline.append(button);eventButtons.push(button);
  });
  if(!events.length)timeline.textContent='Aucun événement daté enregistré dans l’analyse.';
  function seek(index) {
    if(!samples.length)return;
    selected=Math.max(0,Math.min(samples.length-1,Math.round(index)));
    range.value=String(selected);
    const s=samples[selected],ms=time(s);
    clock.textContent=Number.isFinite(ms)?new Date(ms).toLocaleString('fr-FR'):'Horodatage indisponible';
    const fields=[['Altitude',s.altitude_msl,'ft'],['IAS',s.ias,'kt'],['GS',s.gs,'kt'],['VS',s.vs,'ft/min'],['Carburant',s.fuel,'unité(s)']];
    detail.textContent=fields.map(([name,value,unit])=>name+' : '+(numeric(value)===null?'n/d':Math.round(Number(value)).toLocaleString('fr-FR')+' '+unit)).join(' · ')+(s.phase?' · '+s.phase:'');
    if(marker && validCoordinate(s))marker.setLatLng([Number(s.lat),Number(s.lon)]);
    if(travelled)travelled.setLatLngs(samples.slice(0,selected+1).filter(validCoordinate).map(p=>[Number(p.lat),Number(p.lon)]));
    chartCursors.forEach(c=>{if(!c)return;const x=20+600*selected/Math.max(1,samples.length-1);c.setAttribute('x1',x);c.setAttribute('x2',x);});
    const closest=events.reduce((best,e,i)=>e.ms<=ms?i:best,-1);
    eventButtons.forEach((b,i)=>b.setAttribute('aria-current',String(i===closest)));
  }
  function stop() { playing=false;playButton.textContent='▶ Lecture';if(frame!==null)cancelAnimationFrame(frame);frame=null;lastTick=null; }
  function tick(now) {
    if(!playing)return;
    if(lastTick!==null && samples.length) {
      virtualTime += (now-lastTick)*Number(speed.value);
      let next=time(samples[selected+1]);
      while(selected<samples.length-1 && (!Number.isFinite(next) || next<=virtualTime)) {
        seek(selected+1);
        next=time(samples[selected+1]);
      }
    }
    lastTick=now;
    if(selected>=samples.length-1){stop();return;}
    frame=requestAnimationFrame(tick);
  }
  playButton.addEventListener('click',()=>{if(playing){stop();return;}if(selected>=samples.length-1)seek(0);playing=true;virtualTime=time(samples[selected]);playButton.textContent='⏸ Pause';frame=requestAnimationFrame(tick);});
  range.addEventListener('input',()=>{seek(Number(range.value));virtualTime=time(samples[selected]);lastTick=null;});
  if(samples.length)seek(0);else{playButton.disabled=true;clock.textContent='Aucune télémétrie';detail.textContent='Le replay sera disponible lorsque Hermès aura synchronisé ses échantillons.';}
})();
</script>
@endsection
