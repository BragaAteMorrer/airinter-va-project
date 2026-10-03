@extends('promethee::layout')
@section('title','Audit financier')
@section('content')
@php
  $periodLabels = ['month' => 'Mois', '6months' => '6 mois', 'year' => 'Année'];
  $riskClass = fn($risk) => match($risk) { 'élevé' => 'risk-high', 'modéré' => 'risk-medium', default => 'risk-low' };
@endphp

<div class="finance-hero">
  <div>
    <span class="eyebrow">DIRECTION FINANCIÈRE · PROMÉTHÉE</span>
    <h1>Audit financier multi-compagnies.</h1>
    <p>Lecture consolidée et détaillée des journaux comptables phpVMS, avec analyse par période, évolution mensuelle et indicateurs de performance.</p>
  </div>
  <div class="finance-hero-stamp">
    <span>PERFORMANCE</span><span>CONTRÔLE</span><span>VISION</span>
    <small>Situation au {{ $parisNow->format('d/m/Y H:i') }}</small>
  </div>
</div>

<section class="finance-toolbar panel">
  <form method="get" class="finance-filter-grid">
    <div class="filter-group">
      <span>Période</span>
      <div class="segmented">
        @foreach($periodLabels as $key => $label)
          <a href="{{ request()->fullUrlWithQuery(['period' => $key]) }}" class="{{ $period === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
      </div>
    </div>
    <label>Compagnie
      <select name="airline">
        <option value="">Toutes les compagnies</option>
        @foreach($allAirlines as $airline)
          <option value="{{ $airline->id }}" @selected($selectedAirlineId === (int) $airline->id)>{{ $airline->icao }} · {{ $airline->name }}</option>
        @endforeach
      </select>
    </label>
    <input type="hidden" name="period" value="{{ $period }}">
    <button class="finance-refresh">Actualiser l’audit</button>
  </form>
</section>

<section class="finance-kpis">
  <article>
    <span class="kpi-icon">€</span><div><small>Chiffre d’affaires / recettes</small><strong>{{ $consolidated['credits'] }}</strong><em>Consolidé · {{ $periodLabels[$period] }}</em></div>
  </article>
  <article>
    <span class="kpi-icon">↘</span><div><small>Charges d’exploitation</small><strong>{{ $consolidated['debits'] }}</strong><em>Décaissements comptables</em></div>
  </article>
  <article>
    <span class="kpi-icon">Σ</span><div><small>Résultat net</small><strong class="{{ $consolidated['net']->getAmount() < 0 ? 'negative' : 'positive' }}">{{ $consolidated['net'] }}</strong><em>Recettes − charges</em></div>
  </article>
  <article>
    <span class="kpi-icon">%</span><div><small>Marge opérationnelle</small><strong>{{ number_format($consolidated['margin'],1,',',' ') }} %</strong><em>Rentabilité consolidée</em></div>
  </article>
  <article>
    <span class="kpi-icon">⌂</span><div><small>Trésorerie consolidée</small><strong>{{ $consolidated['balance'] }}</strong><em>Solde actuel des journaux</em></div>
  </article>
</section>

@forelse($financeRows as $index => $row)
<section class="company-audit panel" id="company-{{ $row['airline']->id }}">
  <header class="company-audit-header">
    <div class="company-identity">
      <div class="company-mark">{{ strtoupper(substr($row['airline']->icao ?: $row['airline']->name,0,3)) }}</div>
      <div>
        <span class="eyebrow">{{ $row['airline']->icao ?: 'AIR INTER VA' }}</span>
        <h2>{{ $row['airline']->name }}</h2>
        <p>Compte d’exploitation · analyse {{ strtolower($periodLabels[$period]) }}</p>
      </div>
    </div>
    <div class="company-head-metrics">
      <div><small>Recettes</small><strong>{{ $row['selected']['credits'] }}</strong>
        @if($row['selected']['credit_change'] !== null)<span class="{{ $row['selected']['credit_change'] < 0 ? 'negative' : 'positive' }}">{{ $row['selected']['credit_change'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($row['selected']['credit_change']),1,',',' ') }}%</span>@endif
      </div>
      <div><small>Marge</small><strong>{{ number_format($row['selected']['margin'],1,',',' ') }}%</strong></div>
      <div><small>Résultat net</small><strong>{{ $row['selected']['net'] }}</strong>
        @if($row['selected']['net_change'] !== null)<span class="{{ $row['selected']['net_change'] < 0 ? 'negative' : 'positive' }}">{{ $row['selected']['net_change'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($row['selected']['net_change']),1,',',' ') }}%</span>@endif
      </div>
      <div><small>Trésorerie</small><strong>{{ $row['balance'] }}</strong></div>
    </div>
  </header>

  <div class="period-summary-grid">
    @foreach(['month','6months','year'] as $key)
      @php($p = $row['periods'][$key])
      <article class="{{ $period === $key ? 'active' : '' }}">
        <div class="period-summary-title"><strong>{{ $p['label'] }}</strong><span>{{ $p['transactions'] }} écritures</span></div>
        <div class="period-summary-values">
          <span><small>CA / recettes</small><b>{{ $p['credits'] }}</b></span>
          <span><small>Charges</small><b>{{ $p['debits'] }}</b></span>
          <span><small>Net</small><b class="{{ $p['net_raw'] < 0 ? 'negative' : 'positive' }}">{{ $p['net'] }}</b></span>
          <span><small>Marge</small><b>{{ number_format($p['margin'],1,',',' ') }}%</b></span>
        </div>
      </article>
    @endforeach
  </div>

  <div class="finance-visual-grid">
    <article class="chart-card chart-wide">
      <div class="chart-title">
        <div><span class="eyebrow">PERFORMANCE</span><h3>Chiffre d’affaires & marge</h3></div>
        <div class="chart-legend"><span><i class="legend-bar"></i>Recettes</span><span><i class="legend-line"></i>Marge</span></div>
      </div>
      <canvas id="revenue-chart-{{ $row['airline']->id }}" height="260" aria-label="Recettes et marge {{ $row['airline']->name }}"></canvas>
    </article>

    <article class="chart-card">
      <div class="chart-title"><div><span class="eyebrow">STRUCTURE</span><h3>Charges & recettes</h3></div></div>
      <canvas id="cost-chart-{{ $row['airline']->id }}" height="260" aria-label="Charges {{ $row['airline']->name }}"></canvas>
    </article>

    <article class="chart-card">
      <div class="chart-title"><div><span class="eyebrow">RENTABILITÉ</span><h3>Résultat net mensuel</h3></div></div>
      <canvas id="net-chart-{{ $row['airline']->id }}" height="260" aria-label="Résultat net {{ $row['airline']->name }}"></canvas>
    </article>
  </div>

  <div class="audit-bottom-grid">
    <article class="audit-table-card">
      <div class="chart-title"><div><span class="eyebrow">DÉTAIL MENSUEL</span><h3>Compte d’exploitation</h3></div></div>
      <div class="table-wrap finance-table-wrap">
        <table class="finance-table">
          <thead><tr><th>Période</th><th>CA / recettes</th><th>Charges</th><th>Résultat net</th><th>Marge</th><th>Écritures</th><th>Lecture</th></tr></thead>
          <tbody>
            @foreach($row['monthly'] as $point)
            <tr>
              <td><strong>{{ $point['full_label'] }}</strong></td>
              <td>{{ $point['credits_money'] }}</td>
              <td>{{ $point['debits_money'] }}</td>
              <td class="{{ $point['net'] < 0 ? 'negative' : 'positive' }}">{{ $point['net_money'] }}</td>
              <td>{{ number_format($point['margin'],1,',',' ') }}%</td>
              <td>{{ $point['transactions'] }}</td>
              <td>
                @if($point['credits'] === 0 && $point['debits'] === 0)
                  <span class="audit-note muted">Aucune activité</span>
                @elseif($point['net'] < 0)
                  <span class="audit-note risk">Déficitaire</span>
                @elseif($point['margin'] >= 15)
                  <span class="audit-note good">Rentabilité forte</span>
                @else
                  <span class="audit-note watch">À surveiller</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </article>

    <aside class="audit-insights">
      <div class="audit-insights-head">
        <div><span class="eyebrow">OBSERVATIONS D’AUDIT</span><h3>Lecture exécutive</h3></div>
        <span class="risk-badge {{ $riskClass($row['risk']) }}">Risque {{ $row['risk'] }}</span>
      </div>
      <ul class="audit-list">
        @foreach($row['observations'] as $observation)
          <li><span>✓</span>{{ $observation }}</li>
        @endforeach
      </ul>
      <div class="audit-recommendations">
        <h4>Recommandations</h4>
        <ul>
          @if($row['selected']['margin'] < 10)<li>Revoir les postes de charges les plus consommateurs de trésorerie.</li>@endif
          @if($row['selected']['credit_change'] !== null && $row['selected']['credit_change'] < 0)<li>Identifier les lignes ou activités responsables du recul des recettes.</li>@endif
          <li>Comparer les écritures atypiques avec les opérations et PIREP de la période.</li>
          <li>Suivre mensuellement la marge et la trésorerie avant arbitrage flotte/réseau.</li>
        </ul>
      </div>
    </aside>
  </div>
</section>

<script type="application/json" id="finance-data-{{ $row['airline']->id }}">@json($row['monthly'])</script>
@empty
<section class="panel"><p>Aucune compagnie active pour le filtre sélectionné.</p></section>
@endforelse

<section class="panel finance-methodology">
  <div><span class="eyebrow">MÉTHODOLOGIE</span><h2>Base de l’audit.</h2></div>
  <p>Les indicateurs présentés sont calculés à partir des journaux financiers phpVMS : crédits assimilés aux recettes comptables, débits aux charges et différence aux résultats. Cette vue est un outil de pilotage interne et ne remplace pas une comptabilité légale.</p>
</section>
@endsection

@push('styles')
<style>
.finance-hero{display:flex;justify-content:space-between;gap:2rem;align-items:end;padding:1.6rem 1.8rem;margin-bottom:1rem;border-radius:22px;background:linear-gradient(120deg,#0a2342 0%,#123e70 62%,#1f5d91 100%);color:#fff;box-shadow:0 18px 45px rgba(9,35,67,.18)}
.finance-hero h1{font-size:clamp(2rem,4vw,3.4rem);margin:.2rem 0 .5rem;font-family:Georgia,serif}.finance-hero p{max-width:760px;color:#dceafb;margin:0}.finance-hero .eyebrow{color:#efbd4c}
.finance-hero-stamp{display:grid;gap:.1rem;text-align:right;font-size:.78rem;letter-spacing:.14em;font-weight:800;color:#edf6ff}.finance-hero-stamp small{letter-spacing:0;color:#b9d3eb;margin-top:.6rem}
.finance-toolbar{padding:1rem 1.2rem}.finance-filter-grid{display:grid;grid-template-columns:minmax(260px,1fr) minmax(240px,.7fr) auto;gap:1rem;align-items:end}.filter-group>span,.finance-filter-grid label{font-size:.78rem;font-weight:800;color:#526274;text-transform:uppercase;letter-spacing:.06em}
.segmented{display:flex;background:#edf2f7;border-radius:10px;padding:4px;margin-top:.35rem}.segmented a{flex:1;text-align:center;padding:.65rem .8rem;border-radius:8px;color:#33475b;font-weight:800;text-decoration:none}.segmented a.active{background:#0d3158;color:#fff;box-shadow:0 5px 12px rgba(13,49,88,.2)}
.finance-filter-grid select{width:100%;margin-top:.35rem}.finance-refresh{height:44px;background:#0d3158;color:#fff;border:0;border-radius:9px;padding:0 1.25rem;font-weight:800}
.finance-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.9rem;margin:1rem 0 1.2rem}.finance-kpis article{display:flex;gap:.8rem;align-items:center;background:linear-gradient(180deg,#fff,#f8fbfd);border:1px solid #dce6ee;border-radius:14px;padding:1rem;box-shadow:0 8px 24px rgba(20,45,70,.06)}.kpi-icon{width:36px;height:36px;border-radius:10px;background:#fff4d8;color:#b57c00;display:grid;place-items:center;font-weight:900;font-size:1.1rem}.finance-kpis small{display:block;color:#657789;font-weight:700}.finance-kpis strong{display:block;font-size:1.35rem;color:#0f2f52;margin:.2rem 0}.finance-kpis em{font-size:.74rem;color:#83919f;font-style:normal}
.company-audit{padding:0;overflow:hidden;margin-bottom:1.25rem;border:1px solid #d8e4ed;box-shadow:0 14px 34px rgba(17,44,71,.08)}
.company-audit-header{display:flex;justify-content:space-between;gap:1.4rem;align-items:center;padding:1.2rem 1.35rem;background:linear-gradient(100deg,#f6fbff,#fff 55%,#eef6fc)}.company-identity{display:flex;align-items:center;gap:1rem}.company-mark{min-width:58px;height:58px;border-radius:14px;background:linear-gradient(135deg,#0b2c52,#276aa0);color:#fff;display:grid;place-items:center;font-weight:900;letter-spacing:.08em;box-shadow:0 8px 20px rgba(10,44,82,.22)}.company-identity h2{font-family:Georgia,serif;font-size:2rem;margin:.05rem 0}.company-identity p{margin:0;color:#697a8b}.company-head-metrics{display:grid;grid-template-columns:repeat(4,minmax(110px,1fr));gap:0}.company-head-metrics>div{padding:.25rem .9rem;border-left:1px solid #d8e3ec}.company-head-metrics small,.company-head-metrics span{display:block}.company-head-metrics small{color:#66798c}.company-head-metrics strong{display:block;font-size:1.05rem;color:#102f51;margin:.15rem 0}.company-head-metrics span{font-size:.73rem;font-weight:800}
.period-summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;padding:1rem 1.2rem;background:#f7fafc;border-top:1px solid #e3ebf1;border-bottom:1px solid #e3ebf1}.period-summary-grid article{background:#fff;border:1px solid #dfe8ef;border-radius:12px;padding:.9rem}.period-summary-grid article.active{border-color:#d9a62a;box-shadow:inset 0 3px #d9a62a}.period-summary-title{display:flex;justify-content:space-between;gap:1rem;margin-bottom:.7rem}.period-summary-title span{color:#83919f;font-size:.76rem}.period-summary-values{display:grid;grid-template-columns:repeat(4,1fr);gap:.7rem}.period-summary-values small{display:block;color:#788898}.period-summary-values b{font-size:.9rem}
.finance-visual-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:1rem;padding:1rem 1.2rem}.chart-card{border:1px solid #dde7ee;border-radius:12px;padding:1rem;background:#fff;min-width:0}.chart-title{display:flex;justify-content:space-between;align-items:start;gap:1rem;margin-bottom:.8rem}.chart-title h3{margin:.1rem 0;font-size:1rem;color:#173a5e}.chart-legend{display:flex;gap:.8rem;font-size:.75rem;color:#617486}.legend-bar,.legend-line{display:inline-block;width:16px;height:8px;margin-right:.25rem}.legend-bar{background:#2e72d6}.legend-line{height:2px;background:#d6a11c;vertical-align:middle}
.chart-card canvas{width:100%;height:260px;display:block}
.audit-bottom-grid{display:grid;grid-template-columns:minmax(0,2.2fr) minmax(280px,.8fr);gap:1rem;padding:0 1.2rem 1.2rem}.audit-table-card,.audit-insights{border:1px solid #dde7ee;border-radius:12px;background:#fff;padding:1rem}.finance-table{width:100%;border-collapse:collapse;font-size:.78rem}.finance-table th{background:#edf3f7;color:#43576c;text-align:left;padding:.55rem;border-bottom:1px solid #d7e1e9}.finance-table td{padding:.55rem;border-bottom:1px solid #edf2f5;white-space:nowrap}.audit-note{display:inline-block;border-radius:999px;padding:.25rem .5rem;font-size:.7rem;font-weight:800}.audit-note.good{background:#e9f8ef;color:#1b7d43}.audit-note.watch{background:#fff6dc;color:#966a00}.audit-note.risk{background:#fdeaea;color:#a02b2b}.audit-note.muted{background:#eef2f5;color:#68798a}
.audit-insights-head{display:flex;justify-content:space-between;gap:.6rem;align-items:flex-start}.audit-insights h3{margin:.1rem 0}.risk-badge{border-radius:999px;padding:.35rem .55rem;font-size:.7rem;font-weight:900;white-space:nowrap}.risk-low{background:#e9f8ef;color:#197b42}.risk-medium{background:#fff2cf;color:#9a6a00}.risk-high{background:#fde7e7;color:#a82929}.audit-list{padding:0;margin:1rem 0;list-style:none;display:grid;gap:.65rem}.audit-list li{display:flex;gap:.55rem;color:#405467;line-height:1.35}.audit-list li span{color:#17904d;font-weight:900}.audit-recommendations{border-top:1px solid #e4ebf0;padding-top:.8rem}.audit-recommendations h4{color:#bd8600;margin:.1rem 0 .5rem}.audit-recommendations ul{padding-left:1.1rem;margin:0;color:#4e6072}.positive{color:#18884a!important}.negative{color:#c03939!important}
.finance-methodology{display:grid;grid-template-columns:220px 1fr;gap:1.4rem;align-items:center}.finance-methodology h2{margin:.1rem 0}.finance-methodology p{margin:0;color:#667789}
@media(max-width:1200px){.finance-kpis{grid-template-columns:repeat(3,1fr)}.company-audit-header{align-items:flex-start;flex-direction:column}.company-head-metrics{width:100%}.finance-visual-grid{grid-template-columns:1fr 1fr}.chart-wide{grid-column:1/-1}.audit-bottom-grid{grid-template-columns:1fr}}
@media(max-width:760px){.finance-hero{align-items:flex-start;flex-direction:column}.finance-hero-stamp{text-align:left}.finance-filter-grid{grid-template-columns:1fr}.finance-kpis{grid-template-columns:1fr}.period-summary-grid{grid-template-columns:1fr}.period-summary-values{grid-template-columns:1fr 1fr}.finance-visual-grid{grid-template-columns:1fr}.chart-wide{grid-column:auto}.company-head-metrics{grid-template-columns:1fr 1fr}.company-head-metrics>div{border-left:0;border-top:1px solid #dbe5ed;padding:.65rem}.finance-methodology{grid-template-columns:1fr}}
</style>
@endpush

@push('scripts')
<script>
(() => {
  const euro = new Intl.NumberFormat('fr-FR',{style:'currency',currency:'EUR',maximumFractionDigits:0});
  const compact = value => {
    const euros = Number(value || 0) / 100;
    const abs = Math.abs(euros);
    if(abs >= 1000000) return (euros/1000000).toLocaleString('fr-FR',{maximumFractionDigits:1})+' M€';
    if(abs >= 1000) return (euros/1000).toLocaleString('fr-FR',{maximumFractionDigits:1})+' k€';
    return euro.format(euros);
  };
  const css = name => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  const palette = { blue:'#2f73d6', blue2:'#9cc2f4', gold:'#d7a21d', green:'#2b9a57', red:'#d34b4b', grid:'#dce5ec', text:'#53687d' };

  function canvasSize(canvas){
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    const w = Math.max(320, Math.floor(rect.width));
    const h = Number(canvas.getAttribute('height')) || 260;
    canvas.width = w*dpr; canvas.height = h*dpr;
    const ctx = canvas.getContext('2d'); ctx.setTransform(dpr,0,0,dpr,0,0);
    return {ctx,w,h};
  }
  function axes(ctx,w,h,max,min=0){
    const l=50,r=16,t=18,b=34,ph=h-t-b,pw=w-l-r;
    ctx.strokeStyle=palette.grid;ctx.lineWidth=1;ctx.fillStyle=palette.text;ctx.font='11px system-ui';
    for(let i=0;i<=4;i++){const y=t+ph*(i/4);ctx.beginPath();ctx.moveTo(l,y);ctx.lineTo(w-r,y);ctx.stroke();const v=max-(max-min)*(i/4);ctx.fillText(compact(v),4,y+4);}
    return {l,r,t,b,ph,pw};
  }
  function revenueChart(canvas,data){
    const {ctx,w,h}=canvasSize(canvas);ctx.clearRect(0,0,w,h);
    const max=Math.max(1,...data.map(x=>x.credits))*1.15, a=axes(ctx,w,h,max,0), n=data.length, step=a.pw/n, bw=Math.max(8,step*.52);
    data.forEach((p,i)=>{const x=a.l+i*step+(step-bw)/2;const bh=(p.credits/max)*a.ph;ctx.fillStyle=palette.blue;ctx.fillRect(x,a.t+a.ph-bh,bw,bh);ctx.fillStyle=palette.text;ctx.fillText(p.label,x,a.t+a.ph+18);});
    const margins=data.map(x=>Number(x.margin||0)), mMax=Math.max(20,...margins.map(Math.abs))*1.25;
    const points=margins.map((m,i)=>({x:a.l+i*step+step/2,y:a.t+a.ph-(Math.max(0,m)/mMax)*a.ph}));
    ctx.strokeStyle=palette.gold;ctx.lineWidth=2;ctx.beginPath();
    points.forEach((p,i)=>i?ctx.lineTo(p.x,p.y):ctx.moveTo(p.x,p.y));
    ctx.stroke();
    ctx.fillStyle=palette.gold;
    points.forEach(p=>{ctx.beginPath();ctx.arc(p.x,p.y,2.8,0,Math.PI*2);ctx.fill();});
  }
  function costChart(canvas,data){
    const {ctx,w,h}=canvasSize(canvas);ctx.clearRect(0,0,w,h);
    const max=Math.max(1,...data.flatMap(x=>[x.credits,x.debits]))*1.15,a=axes(ctx,w,h,max,0),n=data.length,step=a.pw/n,bw=Math.max(4,step*.3);
    data.forEach((p,i)=>{const x=a.l+i*step+step*.17;const ch=(p.credits/max)*a.ph,dh=(p.debits/max)*a.ph;ctx.fillStyle=palette.blue2;ctx.fillRect(x,a.t+a.ph-ch,bw,ch);ctx.fillStyle='#66788a';ctx.fillRect(x+bw+2,a.t+a.ph-dh,bw,dh);ctx.fillStyle=palette.text;ctx.fillText(p.label,x-2,a.t+a.ph+18);});
  }
  function netChart(canvas,data){
    const {ctx,w,h}=canvasSize(canvas);ctx.clearRect(0,0,w,h);
    const vals=data.map(x=>Number(x.net||0)), max=Math.max(1,...vals.map(Math.abs))*1.2, mid=(h-34+18)/2, l=42,r=12,t=18,b=34,pw=w-l-r,ph=h-t-b,n=data.length,step=pw/n,bw=Math.max(6,step*.55);
    ctx.strokeStyle=palette.grid;ctx.fillStyle=palette.text;ctx.font='11px system-ui';ctx.beginPath();ctx.moveTo(l,mid);ctx.lineTo(w-r,mid);ctx.stroke();
    data.forEach((p,i)=>{const v=Number(p.net||0),height=(Math.abs(v)/max)*(ph/2),x=l+i*step+(step-bw)/2;ctx.fillStyle=v<0?palette.red:palette.green;ctx.fillRect(x,v>=0?mid-height:mid,bw,height);ctx.fillStyle=palette.text;ctx.fillText(p.label,x, h-12);});
  }
  function drawAll(){
    document.querySelectorAll('[id^="finance-data-"]').forEach(node=>{
      const id=node.id.replace('finance-data-','');let data=[];try{data=JSON.parse(node.textContent)}catch(e){return}
      const r=document.getElementById('revenue-chart-'+id),c=document.getElementById('cost-chart-'+id),n=document.getElementById('net-chart-'+id);
      if(r) revenueChart(r,data); if(c) costChart(c,data); if(n) netChart(n,data);
    });
  }
  let timer; window.addEventListener('resize',()=>{clearTimeout(timer);timer=setTimeout(drawAll,120)}); drawAll();
})();
</script>
@endpush
