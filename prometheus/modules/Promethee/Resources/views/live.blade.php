@extends('promethee::layout')
@section('title','Live Operations')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">OCC · LIVE OPERATIONS</span><h1>La ligne en temps réel.</h1><p>Opération, phase Hermès, appareil, jalons OUT/OFF/ON/IN et fraîcheur du signal.</p></div><span class="tag" id="live-updated">Connexion au flux ACARS…</span></div>
<section class="panel"><div id="live-map" class="ops-leaflet-map" aria-live="polite"></div><p class="muted">Molette et glisser-déposer pour explorer la carte. Les estimations sont indicatives.</p></section>
<section class="panel table-wrap"><table><thead><tr><th>Opération</th><th>Vol / appareil</th><th>Phase</th><th>Ligne</th><th>Altitude</th><th>IAS / GS</th><th>OUT · OFF · ON · IN</th><th>Signal</th></tr></thead><tbody id="live-rows"><tr><td colspan="8">Connexion au flux ACARS…</td></tr></tbody></table></section>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(()=>{
  const mapNode=document.querySelector('#live-map'),rowsNode=document.querySelector('#live-rows'),updated=document.querySelector('#live-updated');
  if(!window.L){mapNode.textContent='La bibliothèque cartographique est indisponible.';return;}

  const map=L.map(mapNode,{scrollWheelZoom:true,minZoom:2,maxZoom:12}).setView([46.6,2.5],5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);

  const aircraftLayer=L.layerGroup().addTo(map);
  const selectedTrackLayer=L.layerGroup().addTo(map);
  let selectedFlightId=null;
  let fittedOnce=false;

  const safe=v=>String(v??'—').replace(/[&<>]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));
  const tm=v=>v?new Date(v).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'}):'—';
  const validPoint=p=>Array.isArray(p)&&p.length>=2&&Number.isFinite(Number(p[0]))&&Number.isFinite(Number(p[1]));

  const drawTrack=(flight,{fit=false}={})=>{
    selectedTrackLayer.clearLayers();
    const track=(flight.track||[]).filter(validPoint).map(p=>[Number(p[0]),Number(p[1])]);
    if(track.length<2)return;
    const line=L.polyline(track,{weight:4,opacity:.85}).addTo(selectedTrackLayer);
    if(fit)map.fitBounds(line.getBounds(),{padding:[42,42],maxZoom:9});
  };

  const render=flights=>{
    aircraftLayer.clearLayers();
    const points=[];
    let selectedFlight=null;

    rowsNode.innerHTML=flights.length?flights.map(f=>{
      const alert=(f.alerts||[]).map(a=>a.label).join(' · ')||'Signal OK';
      if(f.id===selectedFlightId)selectedFlight=f;

      if(f.lat!==null&&f.lon!==null){
        const marker=L.marker([f.lat,f.lon],{icon:L.divIcon({className:'aircraft-marker',html:'✈',iconSize:[24,24]})}).addTo(aircraftLayer);
        const trackCount=Array.isArray(f.track)?f.track.length:0;
        marker.bindPopup(`<strong>${safe(f.ident)}</strong><br>${safe(f.departure)} → ${safe(f.arrival)}<br>${f.remaining_nm??'—'} NM · ${f.eta?new Date(f.eta).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'}):'ETA n/a'}<br>${safe(alert)}<br><span class="muted">Trace Hermès : ${trackCount} point${trackCount>1?'s':''}</span>`);
        marker.on('click',()=>{selectedFlightId=f.id;drawTrack(f,{fit:true});});
        points.push([f.lat,f.lon]);
      }

      const m=f.milestones||{};
      return `<tr><td><code>${safe(f.operation_id)}</code></td><td><strong>${safe(f.ident)}</strong><br><span class="muted">${safe(f.aircraft)} · ${safe(f.pilot)}</span></td><td><strong>${safe(f.phase||f.state)}</strong></td><td>${safe(f.departure)} → ${safe(f.arrival)}</td><td>${f.altitude?Math.round(f.altitude)+' ft':'—'}</td><td>${f.ias?Math.round(f.ias)+' kt':'—'} / ${f.gs?Math.round(f.gs)+' kt':'—'}</td><td>OUT ${tm(m.out)} · OFF ${tm(m.off)}<br>ON ${tm(m.on)} · IN ${tm(m.in)}</td><td><strong>${safe(f.signal)}</strong><br><span class="muted">${safe(alert)}</span></td></tr>`;
    }).join(''):'<tr><td colspan="8">Aucun vol ACARS en cours.</td></tr>';

    if(selectedFlight)drawTrack(selectedFlight);
    else if(selectedFlightId!==null){selectedFlightId=null;selectedTrackLayer.clearLayers();}

    if(!fittedOnce&&points.length){
      map.fitBounds(points,{padding:[38,38],maxZoom:7});
      fittedOnce=true;
    }
  };

  const refresh=async()=>{
    try{
      const response=await fetch(@json(route('promethee.live.data')),{headers:{Accept:'application/json'}});
      if(!response.ok)throw new Error();
      const data=await response.json();
      updated.textContent='MAJ '+new Date(data.updated_at).toLocaleTimeString('fr-FR');
      render(data.flights||[]);
    }catch{
      updated.textContent='Flux indisponible';
      rowsNode.innerHTML='<tr><td colspan="8">Flux momentanément indisponible.</td></tr>';
    }
  };

  refresh();
  setInterval(refresh,5000);
})();
</script>
@endsection
