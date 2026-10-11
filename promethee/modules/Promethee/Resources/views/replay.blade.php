@extends('promethee::layout')
@section('title', 'Replay de vol · Hermès')
@section('content')
<div class="ops-header compact">
  <div>
    <span class="eyebrow">HERMÈS · REPLAY SYNCHRONISÉ AVEC LE PIREP</span>
    <h1>{{ $pirep->ident }}</h1>
    <p>{{ $pirep->dpt_airport_id }} → {{ $pirep->arr_airport_id }} · {{ $pirep->user?->name }}</p>
  </div>
  <div class="toolbar">
    <a class="button outline" href="{{ route('promethee.pireps.show', $pirep->id) }}">← Voir le PIREP</a>
    <span class="tag">Score {{ $score['score'] ?? '—' }}/100</span>
  </div>
</div>
<section class="control-strip">
  <article><span>Télémétrie</span><strong>{{ $samples->count() }}</strong><small>Échantillons archivés</small></article>
  <article><span>Événements</span><strong>{{ $journal->count() }}</strong><small>Journal ACARS, SOP et PIREP</small></article>
  <article><span>Atterrissage</span><strong>{{ $pirep->landing_rate === null ? '—' : number_format($pirep->landing_rate, 0, ',', ' ') }}</strong><small>ft/min</small></article>
  <article><span>Approches</span><strong>{{ count($analysis['approaches'] ?? []) }}</strong><small>Points de contrôle</small></article>
</section>
<div data-replay class="pirep-replay">
  <section class="panel replay-flight-map">
    <div class="panel-heading">
      <div>
        <span class="eyebrow">TRAJECTOIRE</span>
        <h2>Replay géographique</h2>
        <div class="replay-legend"><span><i class="actual"></i> Trace Hermès</span><span><i class="planned"></i> Trajet PIREP prévu</span></div>
      </div>
      <span id="replay-time" class="replay-clock">—</span>
    </div>
    <div id="replay-map" class="ops-leaflet-map" role="img" aria-label="Route parcourue et route prévue"></div>
    <div class="replay-transport" aria-label="Commandes de relecture">
      <button id="replay-back" type="button" aria-label="Échantillon précédent">◀◀</button>
      <button id="replay-play" type="button" aria-pressed="false">▶ Lecture</button>
      <button id="replay-next" type="button" aria-label="Échantillon suivant">▶▶</button>
      <label>Vitesse
        <select id="replay-speed" aria-label="Vitesse de relecture">
          <option value="1">x1 réel</option><option value="10">x10</option>
          <option value="60" selected>x60</option><option value="240">x240</option>
          <option value="600">x600</option>
        </select>
      </label>
      <span id="replay-elapsed" class="replay-elapsed">—</span>
    </div>
    <label class="replay-range-label" for="replay-range">Position dans le vol</label>
    <input id="replay-range" type="range" min="0" max="{{ max(0, $samples->count()-1) }}" value="0" aria-label="Déplacer le curseur de vol">
    <p id="replay-detail" class="replay-detail" role="status" aria-live="off">En attente de données…</p>
  </section>
  <div class="replay-main-grid">
    <section class="panel replay-chart-panel">
      <div class="panel-heading"><div><span class="eyebrow">PROFIL VERTICAL</span><h2>Altitude (ft)</h2></div></div>
      <svg id="replay-altitude" class="replay-chart" aria-label="Altitude synchronisée au curseur" role="img"></svg>
      <div class="panel-heading"><div><span class="eyebrow">VITESSES</span><h2>IAS et GS (kt)</h2></div></div>
      <svg id="replay-speed" class="replay-chart" aria-label="Vitesses synchronisées au curseur" role="img"></svg>
      <div class="panel-heading"><div><span class="eyebrow">CARBURANT</span><h2>Carburant restant (lb)</h2></div></div>
      <svg id="replay-fuel" class="replay-chart" aria-label="Carburant synchronisé au curseur" role="img"></svg>
      <p class="replay-note">Les graphiques sont basés uniquement sur la télémétrie archivée. Les périodes sans échantillon ne sont pas reconstituées.</p>
    </section>
    <section class="panel replay-timeline-panel">
      <div class="panel-heading"><div><span class="eyebrow">JOURNAL CHRONOLOGIQUE</span><h2>Événements Hermès / PIREP</h2></div></div>
      <p class="replay-note">Sélectionner un événement déplace le curseur sur sa date et synchronise carte et profils.</p>
      <div id="replay-events" class="replay-events" aria-label="Chronologie des événements"></div>
    </section>
  </div>
  <section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">NOTATION</span><h2>Événements et score du PIREP</h2></div></div>
    <div class="replay-score-grid">
      @forelse(($score['items'] ?? []) as $item)
        <article class="replay-score"><strong>{{ $item['label'] ?? 'Indicateur' }} · {{ $item['score'] ?? '—' }}/{{ $item['max'] ?? '—' }}</strong><p>{{ $item['detail'] ?? '' }}</p></article>
      @empty
        <p class="empty">Aucun indicateur de notation calculable.</p>
      @endforelse
    </div>
  </section>
</div>
<script type="application/json" id="replay-data">@json(['samples'=>$samples,'planned'=>$plannedPath,'journal'=>$journal])</script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="{{ asset('promethee-assets/pirep-replay.css') }}">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('promethee-assets/pirep-replay.js') }}"></script>
@endsection
