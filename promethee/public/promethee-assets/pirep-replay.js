/* Hermès / Prométhée PIREP replay. Read-only: no ACARS or scoring writes. */
(() => {
  'use strict';
  const root = document.querySelector('[data-replay]');
  const payload = document.getElementById('replay-data');
  if (!root || !payload) return;
  let source;
  try { source = JSON.parse(payload.textContent); }
  catch { root.querySelector('#replay-detail').textContent = 'Données replay illisibles.'; return; }
  const $ = id => root.querySelector('#' + id);
  const finite = value => value !== null && value !== undefined && value !== '' && Number.isFinite(Number(value)) ? Number(value) : null;
  const utc = value => {
    if (!value) return null;
    const str = String(value).trim();
    const normalized = str.includes('T') ? str : str.replace(' ', 'T');
    const time = Date.parse(/[zZ]|[+-]\d\d:\d\d$/.test(normalized) ? normalized : normalized + 'Z');
    return Number.isFinite(time) ? time : null;
  };
  const clock = value => value === null ? '—' : new Date(value).toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit',second:'2-digit',timeZone:'UTC'}) + ' UTC';
  const fmt = (value, unit='', digits=0) => value === null ? '—' : value.toLocaleString('fr-FR',{maximumFractionDigits:digits}) + unit;
  const raw = Array.isArray(source.samples) ? source.samples : [];
  const samples = raw.map((point, index) => ({
    index, at:utc(point.recorded_at), lat:finite(point.lat), lon:finite(point.lon),
    altitude:finite(point.altitude_msl), ias:finite(point.ias), gs:finite(point.gs),
    vs:finite(point.vs), fuel:finite(point.fuel), phase:String(point.phase||'—'),
    paused:point.paused===true || point.paused===1
  })).filter(sample => sample.at !== null).sort((a,b)=>a.at-b.at);
  const events = (Array.isArray(source.journal) ? source.journal : []).map((event,index)=>({
    index, at:utc(event.at), code:String(event.code||'ACARS'),
    message:String(event.message||event.code||'Événement'), source:String(event.source||'PIREP'),
    detail:event.detail==null?'':String(event.detail)
  })).filter(event=>event.at!==null).sort((a,b)=>a.at-b.at);
  const planned = (Array.isArray(source.planned) ? source.planned : [])
    .filter(p=>Array.isArray(p)&&finite(p[0])!==null&&finite(p[1])!==null)
    .map(p=>[Number(p[0]),Number(p[1])]);
  const gps = samples.filter(s=>s.lat!==null&&s.lon!==null&&Math.abs(s.lat)<=90&&Math.abs(s.lon)<=180);
  const start = samples[0]?.at ?? events[0]?.at ?? null, end=samples[samples.length-1]?.at ?? null;
  let index=0, playing=false, speed=60, baseClock=0, baseFlight=0, raf=null;
  let map=null, marker=null, travelled=null;
  const range=$('replay-range'), play=$('replay-play'), velocity=$('replay-speed'), detail=$('replay-detail');
  const chooseTime=target=>{
    let lo=0, hi=samples.length-1;
    while(lo<hi){const mid=(lo+hi)>>1;if(samples[mid].at<target)lo=mid+1;else hi=mid;}
    return lo>0 && Math.abs(samples[lo-1].at-target)<Math.abs(samples[lo].at-target)?lo-1:lo;
  };
  const charts=['altitude','speed','fuel'].map(kind=>({kind,node:$('replay-'+kind)}));
  const svgNS='http://www.w3.org/2000/svg';
  const svg=(tag,attrs={})=>{
    const node=document.createElementNS(svgNS,tag);
    Object.entries(attrs).forEach(([k,v])=>node.setAttribute(k,String(v)));
    return node;
  };
  const chartX=time => 38+((time-start)/Math.max(1,end-start))*840;
  const renderCharts=()=>{
    if(start===null||end===null) return;
    charts.forEach(chart=>{
      const holder=chart.node;if(!holder)return;
      holder.replaceChildren();
      holder.setAttribute('viewBox','0 0 900 174');
      const definitions=chart.kind==='altitude'
        ? [{key:'altitude',name:'Altitude',color:'#1374aa'}]
        : chart.kind==='speed'
          ? [{key:'ias',name:'IAS',color:'#1f70a3'},{key:'gs',name:'GS',color:'#c8841c'}]
          : [{key:'fuel',name:'Carburant',color:'#16876f'}];
      const all=definitions.flatMap(d=>samples.map(s=>s[d.key]).filter(v=>v!==null));
      if(!all.length){const message=svg('text',{x:450,y:85,'text-anchor':'middle'});message.textContent='Aucune donnée disponible';holder.append(message);return;}
      const low=Math.min(...all),high=Math.max(...all),rangeY=Math.max(1,high-low),min=low-rangeY*.08,max=high+rangeY*.08;
      for(let n=0;n<4;n++){const y=12+n*44;holder.append(svg('line',{x1:38,x2:878,y1:y,y2:y,stroke:'#d8e3ea','stroke-width':1}));}
      for(const definition of definitions){
        const coords=samples.filter(s=>s[definition.key]!==null).map(s=>chartX(s.at).toFixed(1)+','+(155-(s[definition.key]-min)/(max-min)*136).toFixed(1));
        if(coords.length>1)holder.append(svg('polyline',{points:coords.join(' '),fill:'none',stroke:definition.color,'stroke-width':2.5,'stroke-linejoin':'round'}));
      }
      const legend=svg('text',{x:44,y:168,'font-size':11,fill:'#536b80'});legend.textContent=definitions.map(d=>d.name).join(' / ')+' · '+fmt(low)+' — '+fmt(high);holder.append(legend);
      holder.append(svg('line',{x1:chartX(samples[index].at),x2:chartX(samples[index].at),y1:6,y2:155,stroke:'#a52c3c','stroke-width':2,'data-cursor':'1'}));
      holder.addEventListener('click',event=>{
        const bounds=holder.getBoundingClientRect();
        const fraction=Math.max(0,Math.min(1,(event.clientX-bounds.left)/Math.max(1,bounds.width)));
        seek(chooseTime(start+fraction*(end-start)),true);
      });
    });
  };
  const markEvents=()=>{
    const current=samples[index]?.at;
    root.querySelectorAll('[data-event-index]').forEach(button=>{
      const ev=events[Number(button.dataset.eventIndex)];
      const active=!!ev && current!==undefined && Math.abs(ev.at-current)<=30000;
      button.classList.toggle('is-current',active);
      button.setAttribute('aria-current',active?'true':'false');
    });
  };
  const draw=()=>{
    if(!samples.length){detail.textContent='Aucune télémétrie enregistrée. La route prévue et les événements restent consultables.';return;}
    const s=samples[index];
    range.value=String(index);$('replay-time').textContent=clock(s.at);
    $('replay-elapsed').textContent=Math.floor((s.at-start)/60000)+' min / '+Math.ceil((end-start)/60000)+' min';
    detail.textContent=[clock(s.at),'PHASE '+s.phase,'ALT '+fmt(s.altitude,' ft'),
      'IAS '+fmt(s.ias,' kt'),'GS '+fmt(s.gs,' kt'),'VS '+fmt(s.vs,' ft/min'),
      'FUEL '+fmt(s.fuel,' lb'),s.paused?'SIM EN PAUSE':null].filter(Boolean).join(' · ');
    for(const {node} of charts){
      const cursor=node?.querySelector('[data-cursor]');
      if(cursor){cursor.setAttribute('x1',String(chartX(s.at)));cursor.setAttribute('x2',String(chartX(s.at)));}
    }
    if(map && travelled){
      const path=gps.filter(x=>x.at<=s.at).map(x=>[x.lat,x.lon]);
      travelled.setLatLngs(path);
      const current=gps.findLast?gps.findLast(x=>x.at<=s.at):gps.slice().reverse().find(x=>x.at<=s.at);
      if(current && marker) marker.setLatLng([current.lat,current.lon]);
    }
    markEvents();
  };
  const stop=()=>{playing=false;play.textContent='▶ Lecture';play.setAttribute('aria-pressed','false');if(raf!==null)cancelAnimationFrame(raf);raf=null;};
  const tick=()=>{
    if(!playing)return;
    const target=Math.min(end,baseFlight+(performance.now()-baseClock)*speed);
    seek(chooseTime(target),false);
    if(target>=end){stop();return;}
    raf=requestAnimationFrame(tick);
  };
  const run=()=>{
    if(!samples.length)return;
    if(playing){stop();return;}
    if(index>=samples.length-1)seek(0,true);
    playing=true;play.textContent='⏸ Pause';play.setAttribute('aria-pressed','true');
    baseFlight=samples[index].at;baseClock=performance.now();raf=requestAnimationFrame(tick);
  };
  function seek(next,userInput){
    index=Math.max(0,Math.min(samples.length-1,Math.round(next)||0));
    if(userInput && playing){baseFlight=samples[index].at;baseClock=performance.now();}
    draw();
  }
  const eventList=$('replay-events');
  if(!events.length)eventList.appendChild(Object.assign(document.createElement('p'),{className:'empty',textContent:'Aucun événement ACARS ou FDM archivé pour ce PIREP.'}));
  events.forEach((event,i)=>{
    const button=document.createElement('button');
    button.type='button';button.className='replay-event';button.dataset.eventIndex=String(i);
    const top=document.createElement('span');top.className='replay-event-heading';top.textContent=clock(event.at)+' · '+event.source+' · '+event.code;
    const title=document.createElement('strong');title.textContent=event.message;
    button.append(top,title);
    if(event.detail){const description=document.createElement('small');description.textContent=event.detail;button.append(description);}
    button.addEventListener('click',()=>{stop();if(samples.length)seek(chooseTime(event.at),true);button.scrollIntoView({block:'nearest',behavior:'smooth'});});
    eventList.append(button);
  });
  if(!samples.length){range.disabled=true;play.disabled=true;velocity.disabled=true;}
  else{
    range.max=String(samples.length-1);
    range.addEventListener('input',()=>{stop();seek(Number(range.value),true);});
    play.addEventListener('click',run);
    velocity.addEventListener('change',()=>{speed=Number(velocity.value)||60;if(playing){baseFlight=samples[index].at;baseClock=performance.now();}});
    $('replay-back').addEventListener('click',()=>{stop();seek(index-1,true);});
    $('replay-next').addEventListener('click',()=>{stop();seek(index+1,true);});
  }
  if(window.L){
    const points=[...gps.map(s=>[s.lat,s.lon]),...planned];
    map=L.map($('replay-map'),{scrollWheelZoom:true,minZoom:2,maxZoom:14});
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);
    if(gps.length)L.polyline(gps.map(s=>[s.lat,s.lon]),{color:'#12558b',weight:3,opacity:.24}).addTo(map);
    if(planned.length)L.polyline(planned,{color:'#e0a900',weight:4,dashArray:'9 7'}).bindTooltip('Route prévue').addTo(map);
    travelled=L.polyline([],{color:'#1266ae',weight:4}).addTo(map);
    if(gps.length)marker=L.marker([gps[0].lat,gps[0].lon],{icon:L.divIcon({className:'aircraft-marker',html:'✈',iconSize:[24,24]})}).addTo(map);
    if(points.length>1)map.fitBounds(points,{padding:[24,24],maxZoom:9});
    else map.setView(points[0]||[46.6,2.5],points.length?7:5);
    setTimeout(()=>map.invalidateSize(),100);
  }else $('replay-map').textContent='Fond cartographique indisponible ; chronologie et profils accessibles.';
  window.addEventListener('pagehide',stop,{once:true});
  renderCharts();draw();
})();