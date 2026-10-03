/* Hermès flight-map module.
   Loaded before app.js; keeps the historical global function contract while isolating map rendering. */
(() => {
const flightMapState = {
  centerLat: 46.5,
  centerLon: 2.5,
  zoom: 5,
  autoFit: true,
  dragging: false,
  pointerId: null,
  dragStart: null,
  dragCenterWorld: null,
  lastTrack: [],
  hadTrack: false
};

function clampMapLatitude(value) {
  return Math.max(-85.05112878, Math.min(85.05112878, Number(value)));
}

function mapWorldPoint(lat, lon, zoom = flightMapState.zoom) {
  const scale = 256 * Math.pow(2, zoom);
  const x = (Number(lon) + 180) / 360 * scale;
  const latitude = clampMapLatitude(lat) * Math.PI / 180;
  const y = (1 - Math.log(Math.tan(latitude) + 1 / Math.cos(latitude)) / Math.PI) / 2 * scale;
  return { x, y };
}

function mapGeoPoint(x, y, zoom = flightMapState.zoom) {
  const scale = 256 * Math.pow(2, zoom);
  const lon = x / scale * 360 - 180;
  const n = Math.PI - 2 * Math.PI * y / scale;
  const lat = 180 / Math.PI * Math.atan(Math.sinh(n));
  return { lat: clampMapLatitude(lat), lon };
}

function normalizedTrackPoint(point) {
  const lat = Number(point?.lat ?? point?.Lat);
  const lon = Number(point?.lon ?? point?.Lon);
  const altitude = Number(point?.altitude ?? point?.Altitude);
  if (!Number.isFinite(lat) || !Number.isFinite(lon) || Math.abs(lat) > 90 || Math.abs(lon) > 180) return null;
  return { lat, lon, altitude: Number.isFinite(altitude) ? altitude : null };
}

function normalizedPlanPoint(point) {
  const lat = Number(point?.lat ?? point?.Lat), lon = Number(point?.lon ?? point?.Lon);
  if (!Number.isFinite(lat) || !Number.isFinite(lon) || Math.abs(lat) > 90 || Math.abs(lon) > 180) return null;
  return { lat, lon, ident: String(point?.ident ?? point?.Ident ?? '').trim() };
}
function currentPlannedRoute() {
  return (Array.isArray(flightPlan?.route_points) ? flightPlan.route_points : []).map(normalizedPlanPoint).filter(Boolean);
}
function currentMapFitPoints() {
  return [...currentPlannedRoute(), ...(Array.isArray(flightMapState.lastTrack) ? flightMapState.lastTrack.map(normalizedTrackPoint).filter(Boolean) : [])];
}
function plannedProgress(planned, current) {
  if (!current || planned.length < 2) return null;
  let best=0, bestDistance=Infinity;
  planned.forEach((point,index)=>{ const latScale=Math.cos(current.lat*Math.PI/180), dx=(point.lon-current.lon)*latScale, dy=point.lat-current.lat, d=dx*dx+dy*dy; if(d<bestDistance){bestDistance=d;best=index;} });
  return Math.max(0,Math.min(100,best/(planned.length-1)*100));
}

function mapScreenPoint(point, width, height) {
  const center = mapWorldPoint(flightMapState.centerLat, flightMapState.centerLon);
  const world = mapWorldPoint(point.lat, point.lon);
  return {
    x: width / 2 + world.x - center.x,
    y: height / 2 + world.y - center.y
  };
}

function fitFlightMap(points) {
  const map = $('#flightMap');
  if (!map || !points.length) return;
  const width = Math.max(320, map.clientWidth);
  const height = Math.max(300, map.clientHeight);
  if (points.length === 1) {
    flightMapState.centerLat = points[0].lat;
    flightMapState.centerLon = points[0].lon;
    flightMapState.zoom = 12;
    return;
  }

  const minLat = Math.min(...points.map(point => point.lat));
  const maxLat = Math.max(...points.map(point => point.lat));
  const minLon = Math.min(...points.map(point => point.lon));
  const maxLon = Math.max(...points.map(point => point.lon));
  const padding = 84;

  for (let zoom = 13; zoom >= 3; zoom -= 1) {
    const northWest = mapWorldPoint(maxLat, minLon, zoom);
    const southEast = mapWorldPoint(minLat, maxLon, zoom);
    if (Math.abs(southEast.x - northWest.x) <= width - padding * 2
      && Math.abs(southEast.y - northWest.y) <= height - padding * 2) {
      flightMapState.zoom = zoom;
      const center = mapGeoPoint((northWest.x + southEast.x) / 2, (northWest.y + southEast.y) / 2, zoom);
      flightMapState.centerLat = center.lat;
      flightMapState.centerLon = center.lon;
      return;
    }
  }

  flightMapState.zoom = 3;
  flightMapState.centerLat = (minLat + maxLat) / 2;
  flightMapState.centerLon = (minLon + maxLon) / 2;
}

function renderFlightMapTiles(width, height) {
  const layer = $('#flightMapTiles');
  if (!layer) return;
  const zoom = flightMapState.zoom;
  const center = mapWorldPoint(flightMapState.centerLat, flightMapState.centerLon, zoom);
  const tileSize = 256;
  const tilesAcross = Math.pow(2, zoom);
  const left = center.x - width / 2;
  const top = center.y - height / 2;
  const firstX = Math.floor(left / tileSize) - 1;
  const lastX = Math.floor((left + width) / tileSize) + 1;
  const firstY = Math.floor(top / tileSize) - 1;
  const lastY = Math.floor((top + height) / tileSize) + 1;
  const wanted = new Set();

  for (let tileY = firstY; tileY <= lastY; tileY += 1) {
    if (tileY < 0 || tileY >= tilesAcross) continue;
    for (let tileX = firstX; tileX <= lastX; tileX += 1) {
      const wrappedX = ((tileX % tilesAcross) + tilesAcross) % tilesAcross;
      const key = zoom + '/' + wrappedX + '/' + tileY + '@' + tileX;
      wanted.add(key);
      let tile = layer.querySelector('[data-map-tile="' + key + '"]');
      if (!tile) {
        tile = document.createElement('img');
        tile.className = 'flight-map-tile';
        tile.alt = '';
        tile.draggable = false;
        tile.dataset.mapTile = key;
        tile.src = 'https://tile.openstreetmap.org/' + zoom + '/' + wrappedX + '/' + tileY + '.png';
        layer.append(tile);
      }
      tile.style.left = (tileX * tileSize - left) + 'px';
      tile.style.top = (tileY * tileSize - top) + 'px';
    }
  }

  layer.querySelectorAll('[data-map-tile]').forEach(tile => {
    if (!wanted.has(tile.dataset.mapTile)) tile.remove();
  });
}

function resizeFlightMapCanvas(canvas, width, height) {
  const ratio = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
  const pixelWidth = Math.round(width * ratio);
  const pixelHeight = Math.round(height * ratio);
  if (canvas.width !== pixelWidth || canvas.height !== pixelHeight) {
    canvas.width = pixelWidth;
    canvas.height = pixelHeight;
  }
  canvas.style.width = width + 'px';
  canvas.style.height = height + 'px';
  const context = canvas.getContext('2d');
  context.setTransform(ratio, 0, 0, ratio, 0, 0);
  return context;
}

function approximateTrackHeading(points, latest) {
  const direct = Number(latest?.headingDegrees ?? latest?.HeadingDegrees ?? latest?.heading ?? latest?.Heading);
  if (Number.isFinite(direct)) return direct;
  if (points.length < 2) return 0;
  const a = points[points.length - 2];
  const b = points[points.length - 1];
  const lat1 = a.lat * Math.PI / 180;
  const lat2 = b.lat * Math.PI / 180;
  const deltaLon = (b.lon - a.lon) * Math.PI / 180;
  const y = Math.sin(deltaLon) * Math.cos(lat2);
  const x = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(deltaLon);
  return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
}

function updateFlightMapControls() {
  const follow = $('#mapFollowBtn');
  if (follow) {
    follow.classList.toggle('active', flightMapState.autoFit);
    follow.setAttribute('aria-pressed', flightMapState.autoFit ? 'true' : 'false');
    follow.textContent = flightMapState.autoFit ? 'Suivi auto ✓' : 'Suivi auto';
  }
  setText($('#flightMapZoom'), 'Z' + flightMapState.zoom);
}

function drawMap(track, latest = {}) {
  const map=$('#flightMap'), canvas=$('#flightMapOverlay'), empty=$('#flightMapEmpty'), aircraft=$('#flightMapAircraft');
  if(!map||!canvas)return;
  const width=Math.max(1,map.clientWidth), height=Math.max(1,map.clientHeight), context=resizeFlightMapCanvas(canvas,width,height);
  const points=(Array.isArray(track)?track:[]).map(normalizedTrackPoint).filter(Boolean), planned=currentPlannedRoute();
  flightMapState.lastTrack=track||[];
  if(!points.length&&!planned.length){
    if(flightMapState.hadTrack){flightMapState.centerLat=46.5;flightMapState.centerLon=2.5;flightMapState.zoom=5;flightMapState.autoFit=true;}
    flightMapState.hadTrack=false;renderFlightMapTiles(width,height);context.clearRect(0,0,width,height);
    if(aircraft)aircraft.hidden=true;if(empty){empty.hidden=false;empty.textContent='En attente de la télémétrie…';}
    setText($('#flightMapState'),'STANDBY');setText($('#flightMapFlight'),selectedOperation?displayFlightIdent(normalizeFlight(selectedOperation.flight||selectedOperation)):'—');
    ['#flightMapPosition','#flightMapAltitude','#flightMapSpeed','#flightMapProgress','#flightMapEta'].forEach(id=>setText($(id),'—'));
    const flight=selectedOperation?normalizeFlight(selectedOperation.flight||selectedOperation):null;
    setText($('#flightMapRoute'),flight?(flight.departure||'—')+' → '+(flight.arrival||'—'):'La carte se mettra à jour dès le premier point ACARS.');updateFlightMapControls();return;
  }
  flightMapState.hadTrack=points.length>0;const fitPoints=[...planned,...points];if(flightMapState.autoFit&&!flightMapState.dragging&&fitPoints.length)fitFlightMap(fitPoints);
  renderFlightMapTiles(width,height);context.clearRect(0,0,width,height);
  const bodyStyle=getComputedStyle(document.body), plannedColor=bodyStyle.getPropertyValue('--red').trim()||'#c43a42', actualColor=bodyStyle.getPropertyValue('--blue').trim()||'#2f8fd8';
  if(planned.length>1){
    const screen=planned.map(p=>mapScreenPoint(p,width,height));context.save();context.lineJoin='round';context.lineCap='round';context.strokeStyle=plannedColor;context.globalAlpha=.78;context.lineWidth=2.4;context.setLineDash([8,7]);context.beginPath();
    screen.forEach((p,i)=>i?context.lineTo(p.x,p.y):context.moveTo(p.x,p.y));context.stroke();context.setLineDash([]);context.globalAlpha=.95;
    const every=Math.max(1,Math.ceil(planned.length/8));screen.forEach((p,i)=>{const label=i===0||i===planned.length-1||i%every===0;context.beginPath();context.fillStyle='#fff';context.strokeStyle=plannedColor;context.lineWidth=2;context.arc(p.x,p.y,label?4:2.5,0,Math.PI*2);context.fill();context.stroke();if(label&&planned[i].ident){context.fillStyle=bodyStyle.getPropertyValue('--navy').trim()||'#173b59';context.font='700 10px system-ui, sans-serif';context.fillText(planned[i].ident,p.x+6,p.y-6);}});context.restore();
  }
  const screenPoints=points.map(p=>mapScreenPoint(p,width,height));
  if(screenPoints.length>1){context.save();context.lineJoin='round';context.lineCap='round';context.strokeStyle='rgba(5, 29, 52, .45)';context.lineWidth=7;context.beginPath();screenPoints.forEach((p,i)=>i?context.lineTo(p.x,p.y):context.moveTo(p.x,p.y));context.stroke();context.strokeStyle=actualColor;context.lineWidth=3.5;context.beginPath();screenPoints.forEach((p,i)=>i?context.lineTo(p.x,p.y):context.moveTo(p.x,p.y));context.stroke();context.restore();}
  if(screenPoints.length){const first=screenPoints[0];context.save();context.fillStyle='#fff';context.strokeStyle=actualColor;context.lineWidth=3;context.beginPath();context.arc(first.x,first.y,7,0,Math.PI*2);context.fill();context.stroke();context.restore();}
  const currentPoint=points.length?points[points.length-1]:null,current=screenPoints.length?screenPoints[screenPoints.length-1]:null;
  if(aircraft){aircraft.hidden=!current;if(current){aircraft.style.left=current.x+'px';aircraft.style.top=current.y+'px';aircraft.style.setProperty('--aircraft-heading',(approximateTrackHeading(points,latest)-45)+'deg');}}
  if(empty){empty.hidden=points.length>0;if(!points.length)empty.textContent='Route prévue chargée · en attente de la télémétrie…';}
  const flight=selectedOperation?normalizeFlight(selectedOperation.flight||selectedOperation):null, altitude=Number(latest?.altitudeMslFeet??latest?.AltitudeMslFeet??latest?.altitude??latest?.Altitude??currentPoint?.altitude), speed=Number(latest?.groundSpeedKnots??latest?.GroundSpeedKnots??latest?.gs??latest?.Gs);
  const recording=Boolean(lastStatus?.flight?.recording??lastStatus?.flight?.Recording);setText($('#flightMapState'),recording?'LIVE':(points.length?'TRACK':'PLANNED'));setText($('#flightMapFlight'),flight?displayFlightIdent(flight):'Vol en cours');setText($('#flightMapRoute'),flight?(flight.departure||'—')+' → '+(flight.arrival||'—'):'Trajet ACARS enregistré');
  setText($('#flightMapPosition'),currentPoint?currentPoint.lat.toFixed(4)+', '+currentPoint.lon.toFixed(4):'—');setText($('#flightMapAltitude'),Number.isFinite(altitude)?Math.round(altitude).toLocaleString('fr-FR')+' ft':'—');setText($('#flightMapSpeed'),Number.isFinite(speed)?Math.round(speed)+' kt':'—');
  const progress=plannedProgress(planned,currentPoint);setText($('#flightMapProgress'),progress==null?(planned.length?'0 %':'—'):Math.round(progress)+' %');
  const estimated=Number(flightPlan?.estimated_time_enroute||0), activeFlight=lastStatus?.flight||lastStatus?.Flight||{}, elapsed=Number(activeFlight.airborneSeconds??activeFlight.AirborneSeconds??0), remaining=estimated>0?Math.max(0,estimated-elapsed):null;setText($('#flightMapEta'),remaining==null?'—':Math.ceil(remaining/60)+' min');updateFlightMapControls();
}

  Object.assign(window, {
    flightMapState,
    clampMapLatitude,
    mapWorldPoint,
    mapGeoPoint,
    normalizedTrackPoint,
    normalizedPlanPoint,
    currentPlannedRoute,
    currentMapFitPoints,
    plannedProgress,
    mapScreenPoint,
    fitFlightMap,
    renderFlightMapTiles,
    resizeFlightMapCanvas,
    approximateTrackHeading,
    updateFlightMapControls,
    drawMap
  });
})();
