@extends('app')

@section('title', trans_choice('common.flight', 1) . ' ' . $flight->ident)

@section('css')
<style>
  .flight-briefing { --navy:#102d50; --blue:#0b67bd; --red:#e3404d; --line:#d9e4f1; color:var(--navy); }
  .brief-card { background:#fff; border:1px solid var(--line); border-radius:14px; box-shadow:0 10px 26px rgba(18,54,91,.07); overflow:hidden; margin-bottom:20px; }
  .brief-eyebrow { color:#527197; font-size:.67rem; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }
  .brief-title { color:var(--navy); font-size:1.2rem; font-weight:800; margin:.25rem 0 0; }
  .brief-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin:0 0 22px; }
  .brief-hero h1 { color:var(--navy); font-size:2.25rem; font-weight:800; letter-spacing:-.06em; margin:.2rem 0; }
  .brief-route { color:#5c7698; font-weight:600; } .brief-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:9px; }
  .brief-actions .btn { border-radius:9px; font-weight:700; padding:.62rem .9rem; }
  .brief-strip { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:24px; }
  .brief-kpi { min-height:96px; padding:15px 16px; border:1px solid var(--line); border-left:4px solid var(--blue); border-radius:12px; background:#fff; }
  .brief-kpi:nth-child(2) { border-left-color:var(--red); } .brief-kpi:nth-child(4) { border-left-color:#eaa800; }
  .brief-kpi span { display:block; color:#6a819d; font-size:.68rem; } .brief-kpi strong { display:block; color:var(--navy); font-size:1.55rem; line-height:1.15; } .brief-kpi small { color:#587494; font-size:.72rem; }
  .brief-grid { display:grid; grid-template-columns:minmax(0,1.7fr) minmax(290px,.95fr); gap:20px; align-items:start; } .brief-card-head { padding:20px 23px 13px; }
  .route-line { display:flex; align-items:center; gap:12px; color:var(--navy); font-size:1.2rem; font-weight:800; padding:0 23px 14px; } .route-line i { flex:1; height:2px; background:linear-gradient(90deg,var(--blue) 0 48%,var(--red) 52%); }
  .airport-cards { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; padding:0 23px 23px; } .airport-cards div { min-width:0; padding:10px; border:1px solid var(--line); border-radius:7px; background:#f9fbfe; }
  .airport-cards span { display:block; color:#7489a2; font-size:.62rem; letter-spacing:.1em; text-transform:uppercase; } .airport-cards strong { display:block; color:var(--navy); font-size:.92rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .brief-table { margin:0; } .brief-table td { padding:.75rem 1.15rem; border-color:var(--line); color:#45617f; } .brief-table td:first-child { color:var(--navy); font-weight:700; width:42%; }
  .route-map #map { height:430px !important; } .metar { padding:0 23px 20px; } .metar + .metar { border-top:1px solid var(--line); padding-top:16px; }
  .metar-name { color:var(--navy); font-weight:800; margin-bottom:8px; } .metar-raw { color:#426587; font:.72rem/1.6 Consolas,monospace; overflow-wrap:anywhere; }
  .metar-data { display:grid; grid-template-columns:1fr 1fr; margin-top:11px; border-top:1px solid var(--line); } .metar-data div { padding:7px 0; border-bottom:1px solid var(--line); font-size:.78rem; } .metar-data b { color:var(--navy); } .metar-label { display:block; color:#6a819d; font-size:.64rem; font-weight:800; letter-spacing:.09em; margin:12px 0 3px; text-transform:uppercase; } .metar-empty { color:#6a819d; font-size:.78rem; } .weather-source { border-top:1px solid var(--line); color:#6a819d; display:block; font-size:.68rem; padding:12px 23px 18px; } .notes { padding:0 23px 22px; color:#46617e; }
  @media (max-width:1100px) { .brief-strip { grid-template-columns:repeat(3,1fr); } .brief-grid { grid-template-columns:1fr; } }
  @media (max-width:650px) { .brief-hero { display:block; } .brief-actions { justify-content:flex-start; margin-top:15px; } .brief-strip { grid-template-columns:repeat(2,1fr); } .airport-cards { grid-template-columns:1fr; } .route-map #map { height:280px !important; } }
</style>
@endsection

@section('content')
@php
  $aircraft = $flight->subfleets->pluck('type')->filter()->unique()->implode(', ') ?: 'Flotte non précisée';
  $load = $flight->load_factor !== null ? round($flight->load_factor) . '%' : '—';
@endphp
<main class="flight-briefing">
  <header class="brief-hero">
    <div><div class="brief-eyebrow">{{ optional($flight->airline)->name ?: 'Compagnie' }} · fiche opérationnelle</div><h1>{{ $flight->ident }}</h1><div class="brief-route">{{ $flight->dpt_airport_id }} → {{ $flight->arr_airport_id }} · {{ \App\Models\Enums\FlightType::label($flight->flight_type) }}</div></div>
    <div class="brief-actions"><a class="btn btn-outline-primary" href="{{ route('frontend.pireps.create') }}?flight_id={{ $flight->id }}">Remplir un PIREP</a><button class="btn {{ $bid ? 'btn-outline-danger' : 'btn-primary' }} save_flight" x-id="{{ $flight->id }}" x-saved-class="btn-outline-danger" type="button">{{ $bid ? __('flights.removebid') : __('flights.addbid') }}</button></div>
  </header>
  <section class="brief-strip" aria-label="Résumé du vol">
    <article class="brief-kpi"><span>Départ publié</span><strong>{{ $flight->dpt_time ?: '—' }}</strong><small>{{ optional($flight->dpt_airport)->name ?: $flight->dpt_airport_id }}</small></article>
    <article class="brief-kpi"><span>Arrivée publiée</span><strong>{{ $flight->arr_time ?: '—' }}</strong><small>{{ optional($flight->arr_airport)->name ?: $flight->arr_airport_id }}</small></article>
    <article class="brief-kpi"><span>Temps de vol · distance</span><strong>@if($flight->flight_time) @minutestotime($flight->flight_time) @else — @endif</strong><small>{{ $flight->distance ? $flight->distance . ' nm' : 'Distance non renseignée' }}</small></article>
    <article class="brief-kpi"><span>Remplissage</span><strong>{{ $load }}</strong><small>Facteur de charge prévu</small></article>
    <article class="brief-kpi"><span>Niveau de croisière</span><strong>{{ $flight->level ? 'FL' . $flight->level : '—' }}</strong><small>{{ $aircraft }}</small></article>
  </section>
  <div class="brief-grid">
    <div>
      <section class="brief-card">
        <div class="brief-card-head"><div class="brief-eyebrow">Préparation</div><h2 class="brief-title">Dossier de vol</h2></div>
        <div class="route-line"><a href="{{ route('frontend.airports.show', $flight->dpt_airport_id) }}">{{ $flight->dpt_airport_id }}</a><i></i><a href="{{ route('frontend.airports.show', $flight->arr_airport_id) }}">{{ $flight->arr_airport_id }}</a></div>
        <div class="airport-cards"><div><span>Départ</span><strong>{{ optional($flight->dpt_airport)->location ?: optional($flight->dpt_airport)->name ?: $flight->dpt_airport_id }}</strong></div><div><span>Arrivée</span><strong>{{ optional($flight->arr_airport)->location ?: optional($flight->arr_airport)->name ?: $flight->arr_airport_id }}</strong></div><div><span>Distance</span><strong>{{ $flight->distance ? $flight->distance . ' nm' : '—' }}</strong></div></div>
        <table class="table brief-table">
          @if($flight->alt_airport_id)<tr><td>Aéroport de dégagement</td><td>{{ $flight->alt_airport_id }} · {{ optional($flight->alt_airport)->name }}</td></tr>@endif
          @if($flight->route)<tr><td>Route planifiée</td><td>{{ $flight->route }}</td></tr>@endif
          <tr><td>Indicatif ATC</td><td>{{ $flight->atc }}</td></tr><tr><td>Appareils autorisés</td><td>{{ $aircraft }}</td></tr>
          @if($flight->fares->isNotEmpty())<tr><td>Tarifs et capacités</td><td>{{ $flight->fares->map(fn($fare) => $fare->name . ' · ' . ($fare->pivot->capacity ?? $fare->capacity) . ' places')->implode(', ') }}</td></tr>@endif
          @foreach($flight->field_values as $field)<tr><td>{{ $field->name }}</td><td>{{ $field->value }}</td></tr>@endforeach
        </table>
      </section>
      <section class="brief-card route-map"><div class="brief-card-head"><div class="brief-eyebrow">Navigation</div><h2 class="brief-title">Carte et route prévue</h2></div>@include('flights.map')</section>
      <section class="brief-card"><div class="brief-card-head"><div class="brief-eyebrow">Consignes</div><h2 class="brief-title">Notes de l’exploitation</h2></div><div class="notes">{!! $flight->notes ?: 'Aucune consigne particulière pour ce vol.' !!}</div></section>
    </div>
    <aside><section class="brief-card"><div class="brief-card-head"><div class="brief-eyebrow">Météo préparation</div><h2 class="brief-title">METAR &amp; TAF</h2></div>
      @foreach($weather as $key => $report)
        @php($metar = $report['metar'])
        @php($taf = $report['taf'])
        <div class="metar"><div class="metar-name">{{ $report['icao'] }} · {{ $key === 'departure' ? 'Départ' : ($key === 'arrival' ? 'Arrivée' : 'Dégagement') }}</div>
          <span class="metar-label">METAR</span>
          @if($metar)<div class="metar-raw">{{ $metar['raw'] }}</div><div class="metar-data">
            <div><b>Conditions</b><br>{{ $metar['category'] ?: '—' }}</div><div><b>Vent</b><br>{{ $metar['wind_speed'] !== null ? $metar['wind_speed'] . ' kt ' . ($metar['wind_direction_label'] ?: '') : '—' }}</div>
            <div><b>Visibilité</b><br>{{ !empty($metar['visibility']) ? $metar['visibility']['km'] . ' km' : '—' }}</div><div><b>Nuages</b><br>{{ $metar['clouds_report_ft'] ?: ($metar['cavok'] ? 'CAVOK' : '—') }}</div>
            <div><b>Température</b><br>{{ !empty($metar['temperature']) ? $metar['temperature']['c'] . ' °C' : '—' }}</div><div><b>Pression</b><br>{{ !empty($metar['barometer']) ? round($metar['barometer']['hPa']) . ' hPa' : '—' }}</div>
          </div>@else<div class="metar-empty">Aucune observation METAR actuellement disponible.</div>@endif
          <span class="metar-label">TAF</span>
          @if($taf)<div class="metar-raw">{{ $taf['raw'] }}</div>@else<div class="metar-empty">Aucune prévision TAF actuellement disponible.</div>@endif
        </div>
      @endforeach
      <span class="weather-source">Données aéronautiques : Aviation Weather Center · mise en cache pendant 1 heure.</span>
    </section></aside>
  </div>
</main>
@if(setting('bids.block_aircraft', false)) @include('flights.bids_aircraft') @endif
@endsection

@section('scripts')
  @parent
  @include('flights.scripts')
@endsection
