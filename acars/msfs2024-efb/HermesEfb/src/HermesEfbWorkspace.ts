/* Read-only EFB workspace. Data comes exclusively from Hermès CommBus snapshots. */
type Station = { icao?: string|null; metar?: {category?: string|null; qnh_hpa?: number|null; wind?: {direction?:number|null;speed_kt?:number|null;gust_kt?:number|null;variable?:boolean}|null}|null;recommended_runway?:{ident?:string|null}|null };
export type EfbSnapshot = {
 generatedAt?: string;
 context?: {operationId?:string|null; flightIdent?:string|null;departure?:string|null;arrival?:string|null;alternate?:string|null; route?:string|null;aircraftRegistration?:string|null;aircraftIcao?:string|null;passengers?:number|null;flightLevel?:number|null;costIndex?:string|null;blockFuel?:number|null;estimatedTimeEnroute?:number|null;ofpSource?:string|null;dispatchStatus?:string|null;weather?:{status?:string|null;sigmet_status?:string|null;stations?:{departure?:Station|null;arrival?:Station|null;alternate?:Station|null}|null;summary?:{sigmet_count?:number|null;worst_category?:string|null;arrival_runway?:string|null}|null;sigmets?:Array<{hazard?:string|null;fir?:string|null}>|null}|null}|null;
 state?:{hermesConnected?:boolean;flight?:{phase?:string|null;recording?:boolean;distance?:number;fuelUsed?:number;pausedSeconds?:number}|null;telemetry?:{altitude?:number|null;agl?:number|null;ias?:number|null;gs?:number|null;heading?:number|null;vs?:number|null;fuel?:number|null;grossWeight?:number|null;onGround?:boolean|null;paused?:boolean|null}|null;review?:{airborneMinutes?:number;blockMinutes?:number;fuelUsed?:number;landingRate?:number|null;pauseCount?:number;readyToFile?:boolean}|null;pending?:number;recoveryAvailable?:boolean;warning?:string|null}|null;
};
const dash = (v:unknown, unit='') => typeof v === 'number' && Number.isFinite(v) ? Math.round(v).toLocaleString('fr-FR') + unit : '—';
const txt = (v:unknown) => v === null || v === undefined || v === '' ? '—' : String(v);
const el = (tag:string, cls='', value='') => { const x=document.createElement(tag); if(cls)x.className=cls;if(value)x.textContent=value;return x; };
const row = (label:string,value:string) => {const x=el('div','efb-data-row');x.append(el('span','',label),el('strong','',value));return x;};
const section = (heading:string) => {const x=el('section','efb-page-section');x.append(el('h2','',heading));return x;};
const pages = ['Vue générale','Navigation','OFP','Météo','Carburant','Performances','Procédures','Journal'] as const;
type Tab=typeof pages[number];
export class HermesEfbWorkspace {
 private readonly nav=el('nav','efb-tabs');
 private readonly details=el('div','efb-workspace-details');
 private readonly buttons=new Map<Tab,HTMLButtonElement>();
 private readonly legacy=new Map<Tab,HTMLElement>();
 private readonly history:Array<string>=[];
 private readonly checklist=new Map<string,boolean>();
 private selected:Tab='Vue générale';
 private snapshot:EfbSnapshot|null=null;
 private previousPhase='';
 private previousOperation='';
 constructor(private readonly root:HTMLElement,host:HTMLElement) {
  this.nav.setAttribute('aria-label','Sections de l’EFB');
  host.append(this.nav,this.details);
  for(const name of pages){
   const button=el('button','efb-tab',name) as HTMLButtonElement;
   button.type='button';button.addEventListener('click',()=>this.select(name));
   this.nav.append(button);this.buttons.set(name,button);
  }
  for(const [tab,selector] of [['Vue générale','.metric-grid'],['OFP','.briefing-panel'],['Météo','.weather-panel']] as const){
   const panel=root.querySelector<HTMLElement>(selector);if(panel)this.legacy.set(tab,panel);
  }
  this.select('Vue générale');
 }
 private select(tab:Tab){
  this.selected=tab;
  for(const [name,button] of this.buttons){button.classList.toggle('active',name===tab);button.setAttribute('aria-current',name===tab?'page':'false');}
  for(const [name,node] of this.legacy){node.hidden=name!==tab;}
  this.details.hidden=tab==='Vue générale';
  this.paint();
 }
 public update(value:EfbSnapshot){
  const op=value.context?.operationId||value.context?.flightIdent||'';
  const phase=value.state?.flight?.phase||'';
  if(op!==this.previousOperation){this.history.length=0;this.checklist.clear();this.previousPhase='';this.previousOperation=op;if(op)this.note('Opération chargée : '+txt(value.context?.flightIdent||op));}
  if(phase&&phase!==this.previousPhase){this.note('Phase : '+phase);this.previousPhase=phase;}
  if(value.state?.warning && value.state.warning!==this.snapshot?.state?.warning)this.note('Information Hermès : '+value.state.warning);
  this.snapshot=value;this.paint();
 }
 public stale(){this.details.classList.add('efb-stale');}
 public fresh(){this.details.classList.remove('efb-stale');}
 private note(s:string){this.history.unshift(new Date().toLocaleTimeString('fr-FR')+' · '+s);if(this.history.length>30)this.history.pop();}
 private paint(){
  this.details.replaceChildren();
  const c=this.snapshot?.context||{},s=this.snapshot?.state||{},t=s.telemetry||{},f=s.flight||{},r=s.review||{},wx=c.weather||{};
  const panel=section(this.selected);this.details.append(panel);
  const add=(label:string,value:string)=>panel.append(row(label,value));
  switch(this.selected){
   case 'Navigation':{
    add('Route',c.departure&&c.arrival?c.departure+' → '+c.arrival:'Aucune route active');
    add('Dégagement',txt(c.alternate));
    add('Route OFP',txt(c.route));
    add('Altitude',dash(t.altitude,' ft'));
    add('Altitude sol',dash(t.agl,' ft AGL'));
    add('Cap',dash(t.heading,'°'));
    add('Vitesse sol',dash(t.gs,' kt'));
    add('Distance parcourue',dash(f.distance,' NM'));
    panel.append(el('p','efb-note','Carte, position GPS et prochain waypoint : données non disponibles sur le pont actuel. Aucun guidage fictif.'));
    break;
   }
   case 'OFP':
    add('Vol',txt(c.flightIdent));add('Niveau',c.flightLevel?'FL'+c.flightLevel:'—');
    add('Cost Index',txt(c.costIndex));add('PAX',dash(c.passengers));
    add('Carburant block',dash(c.blockFuel,' (unité de l’OFP)'));
    add('EET',typeof c.estimatedTimeEnroute==='number'?Math.round(c.estimatedTimeEnroute/60)+' min':'—');
    add('Source',txt(c.ofpSource));add('Dispatch',txt(c.dispatchStatus));
    break;
   case 'Météo':
    add('Disponibilité',txt(wx.status));add('Catégorie la plus restrictive',txt(wx.summary?.worst_category));
    for(const [name,station] of [['Départ',wx.stations?.departure],['Arrivée',wx.stations?.arrival],['Dégagement',wx.stations?.alternate]] as const){
     add(name,txt(station?.icao)+' · '+txt(station?.metar?.category)+' · '+dash(station?.metar?.qnh_hpa,' hPa'));
     add('Vent '+name.toLowerCase(),station?.metar?.wind?txt(station.metar.wind.variable?'VRB':dash(station.metar.wind.direction,'°'))+' / '+dash(station.metar.wind.speed_kt,' kt'):'—');
    }
    add('SIGMET',wx.sigmet_status==='AVAILABLE'?String(wx.summary?.sigmet_count??wx.sigmets?.length??0):'Indisponible');
    panel.append(el('p','efb-note','Les prévisions peuvent dater du dernier briefing Hermès. Vérifier ATIS et informations ATC.'));
    break;
   case 'Carburant':
    add('Carburant actuel',dash(t.fuel,' lb (télémétrie)'));
    add('Block planifié',dash(c.blockFuel,' (unité OFP)'));
    add('Consommation enregistrée',dash(f.fuelUsed,' (unité Hermès)'));
    add('Masse actuelle',dash(t.grossWeight,' lb'));
    panel.append(el('p','efb-note','Pas de calcul automatique du carburant à destination ni des réserves : unités OFP et débit instantané non vérifiés. Ne pas utiliser pour les décisions opérationnelles.'));
    break;
   case 'Performances':
    add('IAS',dash(t.ias,' kt'));add('GS',dash(t.gs,' kt'));add('Vitesse verticale',dash(t.vs,' ft/min'));
    add('Altitude',dash(t.altitude,' ft'));add('AGL',dash(t.agl,' ft'));
    add('Masse brute',dash(t.grossWeight,' lb'));add('Taux atterrissage',dash(r.landingRate,' ft/min'));
    add('État simulateur',t.paused?'PAUSE':t.onGround===true?'AU SOL':t.onGround===false?'EN VOL':'Non renseigné');
    panel.append(el('p','efb-note','Calculs de décollage/atterrissage (V1, VR, V2, pistes, configurations) à connecter à des données avion certifiées ; aucune valeur simulée.'));
    break;
   case 'Procédures':{
    panel.append(el('p','efb-note','Aide-mémoire générique, non certifié et non synchronisé avec les checklists de l’appareil. Les procédures du constructeur priment.'));
    for(const [key,label] of [['preparation','Préparation du vol vérifiée'],['ofp','OFP et route vérifiés'],['weather','Météo et dégagement examinés'],['fuel','Carburant et réserves vérifiés'],['arrival','Briefing arrivée effectué']] as const){
     const holder=el('label','efb-check');const box=document.createElement('input');box.type='checkbox';box.checked=this.checklist.get(key)===true;box.addEventListener('change',()=>this.checklist.set(key,box.checked));holder.append(box,el('span','',label));panel.append(holder);
    }
    break;
   }
   case 'Journal':
    add('Enregistrement ACARS',f.recording?'Actif':'Inactif');
    add('PIREP prêt',r.readyToFile?'Oui':'Non');
    add('Événements en attente',dash(s.pending));
    add('Récupération',s.recoveryAvailable?'Disponible':'—');
    panel.append(el('p','efb-note','Journal local des changements reçus par l’EFB pendant cette session. Le journal ACARS complet reste dans Hermès/Prométhée.'));
    for(const event of this.history)panel.append(el('div','efb-journal-event',event));
    break;
  }
 }
}
