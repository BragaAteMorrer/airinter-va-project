import './HermesFlightAssistant.scss';
/** Read-only advisory assistant. No flight-control, ATC or FDM commands. */
export interface AssistantSnapshot {
 context?: {departure?:string|null;arrival?:string|null;alternate?:string|null;route?:string|null;flightLevel?:number|null;blockFuel?:number|null}|null;
 state?: {hermesConnected?:boolean;flight?:{phase?:string|null;recording?:boolean;distance?:number;fuelUsed?:number}|null;telemetry?:{altitude?:number|null;agl?:number|null;ias?:number|null;gs?:number|null;vs?:number|null;fuel?:number|null;onGround?:boolean|null;paused?:boolean|null}|null;warning?:string|null}|null;
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
  if(op!==this.lastFlightKey){this.lastFlightKey=op;this.lastAdvisories=[];}
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
  if(!alerts.length)alerts.push({key:'normal',title:'Aucune alerte calculable',detail:'Les données actuellement disponibles ne signalent aucune situation surveillée par cet assistant.',level:'normal'});
  this.lastAdvisories=alerts;
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
  this.area.append(make('p','efb-assistant-limit','Route latérale, point de descente (TOD), carburant prévu à destination et contraintes publiées : données insuffisantes dans le flux actuel. Aucune alerte calculée sur ces critères.'));
  this.area.append(make('small','efb-assistant-disclaimer','Aide indicative uniquement · instruments de bord, SOP et instructions ATC prioritaires.'));
 }
 checkStale(){
  if(this.lastSnapshotAt&&Date.now()-this.lastSnapshotAt>6500)this.showStatus('Connexion interrompue : dernières données périmées. Aucune nouvelle recommandation calculée.');
 }
 private showStatus(message:string){this.area.replaceChildren(make('strong','','ASSISTANCE DE VOL'),make('p','',message));this.area.classList.add('efb-assistant-offline');}
}
