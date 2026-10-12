import { greatCircleNm, todEstimateNm, estimateArrivalFuel, nearestRouteOffsetNm, validPoint, type GeoPoint } from './HermesNavigationMath';
/** Read-only advisory assistant. No flight-control, ATC or FDM commands. */
export interface AssistantSnapshot {
 context?: {departure?:string|null;arrival?:string|null;alternate?:string|null;route?:string|null;flightLevel?:number|null;blockFuel?:number|null;destinationPoint?:(GeoPoint&{elevation_ft?:number|null})|null;routePoints?:Array<GeoPoint&{ident?:string;altitude?:number|null}>|null}|null;
 state?: {hermesConnected?:boolean;flight?:{phase?:string|null;recording?:boolean;distance?:number;fuelUsed?:number}|null;telemetry?:{recordedAt?:string|null;latitude?:number|null;longitude?:number|null;altitude?:number|null;agl?:number|null;ias?:number|null;gs?:number|null;vs?:number|null;fuel?:number|null;onGround?:boolean|null;paused?:boolean|null}|null;warning?:string|null}|null;
}
type Level='normal'|'caution';
type Advice={key:string;title:string;detail:string;level:Level};
const phases=['BOARDING','PUSHBACK','TAXI_OUT','TAKEOFF','CLIMB','CRUISE','DESCENT','APPROACH','FINAL','LANDING','TAXI_IN','IN'] as const;
const followups:Record<string,string>={BOARDING:'Préparation et départ',PUSHBACK:'Roulage',TAXI_OUT:'Décollage',TAKEOFF:'Montée',CLIMB:'Croisière',CRUISE:'Descente',DESCENT:'Approche',APPROACH:'Finale',FINAL:'Atterrissage',LANDING:'Roulage arrivée',TAXI_IN:'Stationnement',IN:'Fin de vol'};
const make=(tag:string,klass:string,text:string)=>{const node=document.createElement(tag);node.className=klass;node.textContent=text;return node;};
export class HermesFlightAssistant {
 private readonly area:HTMLElement;
 private lastSnapshotAt=0;
 private lastFlightKey='';
 private lastAdvisories:Advice[]=[];
 private fuelBaseline:{at:number;fuel:number}|null=null;
 private fuelRate:number|null=null;
 constructor(host:HTMLElement){
  this.area=make('section','efb-assistant','');
  this.area.setAttribute('aria-label','Assistant de vol consultatif');
  host.append(this.area);
  this.showStatus('En attente des données de vol Hermès.');
 }
 update(snapshot:AssistantSnapshot){
  this.lastSnapshotAt=Date.now();
  const c=snapshot.context||{},s=snapshot.state||{},t=s.telemetry||{},f=s.flight||{};
  const current=(f.phase||'STANDBY').toUpperCase();
  const op=[c.departure||'',c.arrival||'',c.route||''].join('|');
  if(op!==this.lastFlightKey){this.lastFlightKey=op;this.lastAdvisories=[];this.fuelBaseline=null;this.fuelRate=null;}
  const alerts:Advice[]=[];
  if(!s.hermesConnected)alerts.push({key:'offline',title:'Hermès non connecté',detail:'Aucune assistance dynamique avant la reprise de la liaison.',level:'caution'});
  if(t.paused)alerts.push({key:'pause',title:'Simulateur en pause',detail:'Données et conseils suspendus pendant la pause.',level:'caution'});
  if(s.warning)alerts.push({key:'native',title:'Information Hermès',detail:s.warning,level:'caution'});
  if(s.hermesConnected&&!t.paused){
   if(current==='DESCENT'&&typeof t.vs==='number'&&t.vs>100)
    alerts.push({key:'vertical',title:'Descente interrompue',detail:'L’appareil remonte alors que la phase déclarée est DESCENT. Vérifiez votre plan et les instructions ATC.',level:'normal'});
   if(['APPROACH','FINAL'].includes(current)&&typeof t.agl==='number'&&t.agl<1000&&typeof t.vs==='number'&&t.vs< -1000)
    alerts.push({key:'approach-rate',title:'Taux de descente à surveiller',detail:'VS sous -1 000 ft/min à basse hauteur. Vérifiez la stabilité selon les SOP applicables.',level:'caution'});
   if(typeof t.fuel==='number'&&t.fuel<=0)
    alerts.push({key:'fuel',title:'Carburant télémétrique nul',detail:'Vérifiez immédiatement la lecture carburant et les instruments de bord ; cette donnée peut être indisponible ou erronée.',level:'caution'});
  }
  // Only calculate navigation with actual GPS and a verified airport location.
  const position:GeoPoint|null=typeof t.latitude==='number'&&typeof t.longitude==='number'?{lat:t.latitude,lon:t.longitude}:null;
  const destination=c.destinationPoint;
  const distance=validPoint(position)&&validPoint(destination)?greatCircleNm(position,destination):null;
  const stamped=t.recordedAt?Date.parse(t.recordedAt):NaN;
  if(s.hermesConnected&&!t.paused&&Number.isFinite(stamped)&&typeof t.fuel==='number'&&t.fuel>0){
   if(this.fuelBaseline&&stamped>this.fuelBaseline.at){
    const hours=(stamped-this.fuelBaseline.at)/3600000,used=this.fuelBaseline.fuel-t.fuel;
    if(hours>=5/60&&hours<=2&&used>0){
     const rate=used/hours;
     if(rate>=100&&rate<=100000)this.fuelRate=rate;
     this.fuelBaseline={at:stamped,fuel:t.fuel};
    } else if(hours>=5/60||used< -10)this.fuelBaseline={at:stamped,fuel:t.fuel};
   }else if(!this.fuelBaseline)this.fuelBaseline={at:stamped,fuel:t.fuel};
  }
  const route: Array<GeoPoint & {ident?:string;altitude?:number|null}> = Array.isArray(c.routePoints)
   ? c.routePoints.filter(point=>validPoint(point)) : [];
  const routeOffset=validPoint(position)&&route.length>=2?nearestRouteOffsetNm(position,route):null;
  if(routeOffset!==null&&s.hermesConnected&&!t.paused&&typeof t.gs==='number'&&t.gs>80){
   alerts.push({key:'route-offset',title:'Écart géographique à l’OFP',detail:routeOffset.toFixed(1)+' NM du segment SimBrief le plus proche (géométrie du plan, non trajectoire ATC).',level:routeOffset>=5?'caution':'normal'});
  }
  const nextPoint=validPoint(position)?route
   .map((point,index)=>({point,index,distance:greatCircleNm(position,point)}))
   .filter(x=>x.distance<30)
   .sort((a,b)=>a.distance-b.distance)[0]:undefined;
  if(nextPoint&&typeof nextPoint.point.altitude==='number'&&nextPoint.point.altitude>0)
   alerts.push({key:'nav-altitude',title:'Altitude prévue au navlog',detail:'Point '+(nextPoint.point.ident||'#'+(nextPoint.index+1))+' à '+nextPoint.distance.toFixed(0)+' NM : '+Math.round(nextPoint.point.altitude)+' ft planifiés (pas une contrainte publiée ni une clairance).',level:'normal'});
  const tod=distance!==null&&typeof t.altitude==='number'&&validPoint(destination)?todEstimateNm(t.altitude,destination.elevation_ft??destination.elevationFt??0):null;
  const arrivalFuel=distance!==null&&typeof t.fuel==='number'&&typeof t.gs==='number'&&this.fuelRate!==null
   ?estimateArrivalFuel(t.fuel,t.gs,distance,this.fuelRate):null;
  if(distance!==null&&s.hermesConnected){
   alerts.push({key:'direct-distance',title:'Distance directe destination',detail:distance.toFixed(0)+' NM en orthodromie, sans tenir compte des voies aériennes ni de l’approche.',level:'normal'});
   if(tod!==null&&['CRUISE','CLIMB'].includes(current))
    alerts.push({key:'tod',title:'Repère indicatif de descente',detail:'Distance directe '+distance.toFixed(0)+' NM ; repère 3° ~'+tod.toFixed(0)+' NM avant destination (marge incluse). Planifiez selon STAR, ATC et performances avion.',level:distance<=tod?'caution':'normal'});
   if(arrivalFuel!==null)
    alerts.push({key:'estimated-fuel',title:'Projection carburant indicative',detail:Math.max(0,arrivalFuel).toFixed(0)+' lb estimées à destination en projection linéaire. Basée sur '+this.fuelRate!.toFixed(0)+' lb/h observés ; réserves réglementaires non vérifiées.',level:arrivalFuel<0?'caution':'normal'});
  }
  if(!alerts.length)alerts.push({key:'normal',title:'Aucune alerte calculable',detail:'Les données actuellement disponibles ne signalent aucune situation surveillée par cet assistant.',level:'normal'});
  this.lastAdvisories=alerts;
  this.area.classList.remove('efb-assistant-offline');
  this.area.replaceChildren();
  const header=make('header','efb-assistant-header','');
  header.append(make('span','efb-assistant-kicker','HERMÈS · ASSISTANCE DE VOL'),make('strong','',current));
  this.area.append(header);
  const next=followups[current]||'Étape suivante non déterminée';
  this.area.append(make('div','efb-assistant-next','PROCHAINE ÉTAPE · '+next));
  if(c.flightLevel&&['CLIMB','CRUISE'].includes(current))this.area.append(make('p','efb-assistant-hint','Niveau OFP prévu : FL'+c.flightLevel+'. Confirmez toujours l’autorisation ATC.'));
  const list=make('div','efb-assistant-alerts','');
  for(const alert of alerts){const item=make('article','efb-assistant-alert '+alert.level,'');item.append(make('strong','',alert.title),make('p','',alert.detail));list.append(item);}
  this.area.append(list);
  this.area.append(make('p','efb-assistant-limit','Les écarts de route ne sont calculés que si le navlog géoréférencé est présent. Les vitesses/altitudes réglementaires et restrictions ATC ne sont pas connues ; TOD et carburant restent indicatifs.'));
  this.area.append(make('small','efb-assistant-disclaimer','Aide indicative uniquement · instruments de bord, SOP et instructions ATC prioritaires.'));
 }
 checkStale(){
  if(this.lastSnapshotAt&&Date.now()-this.lastSnapshotAt>6500)this.showStatus('Connexion interrompue : dernières données périmées. Aucune nouvelle recommandation calculée.');
 }
 private showStatus(message:string){this.area.replaceChildren(make('strong','','ASSISTANCE DE VOL'),make('p','',message));this.area.classList.add('efb-assistant-offline');}
}
