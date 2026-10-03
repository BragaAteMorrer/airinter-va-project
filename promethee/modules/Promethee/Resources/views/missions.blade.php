@extends('promethee::layout')
@section('title','Missions et circuits')
@section('content')
<div class="ops-header compact">
  <div>
    <span class="eyebrow">OPÉRATIONS SPÉCIALES</span>
    <h1>Missions et circuits.</h1>
    <p>Les missions de rapatriement remettent automatiquement les appareils sur leur plateforme attitrée.</p>
  </div>
</div>
@php
  $myMissions = $missions->filter(fn ($mission) => $mission->booking && $mission->booking->status === 'reserved');
  $otherMissions = $missions->reject(fn ($mission) => $mission->booking && $mission->booking->status === 'reserved');
@endphp

<nav class="pilot-hub-nav" aria-label="Navigation missions">
  <a href="#my-missions">Mes missions <span>{{ $myMissions->count() }}</span></a>
  <a href="#missions">Missions disponibles <span>{{ $otherMissions->count() }}</span></a>
  <a href="#circuits">Circuits <span>{{ $circuits->count() }}</span></a>
</nav>

<section class="panel mission-personal" id="my-missions">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">MES MISSIONS</span>
      <h2>Réservées pour moi</h2>
      <p>Vos missions actives sont regroupées ici. Vous pouvez les abandonner avant le vol si nécessaire.</p>
    </div>
    <span class="tag">{{ $myMissions->count() }} active(s)</span>
  </div>
  <div class="mission-owned-list">
    @forelse($myMissions as $mission)
      <article class="mission-owned-row">
        <div>
          <span class="eyebrow">{{ $mission->mission_type === 'repatriation' ? 'RAPATRIEMENT' : 'MISSION' }}</span>
          <strong>{{ $mission->mission_type === 'repatriation' && $mission->aircraft_registration ? $mission->aircraft_registration : $mission->title }}</strong>
          <small>{{ $mission->dpt_airport_id ?: 'Libre' }} → {{ $mission->arr_airport_id ?: 'Libre' }}@if($mission->ends_on) · avant le {{ $mission->ends_on }}@endif</small>
        </div>
        <div class="mission-owned-actions">
          @if($mission->mission_type === 'repatriation' && $mission->reward_multiplier)
            <span class="tag">PRIME ×{{ number_format((float) $mission->reward_multiplier, 1, ',', ' ') }}</span>
          @endif
          <form method="post" action="{{ route('promethee.missions.cancel', $mission->id) }}" onsubmit="return confirm('Abandonner cette mission ? Elle redeviendra disponible pour les autres pilotes.');">
            @csrf
            @method('DELETE')
            <button class="button outline" type="submit">Abandonner</button>
          </form>
        </div>
      </article>
    @empty
      <p class="empty">Vous n’avez aucune mission réservée. Les missions disponibles sont juste en dessous.</p>
    @endforelse
  </div>
</section>


<section class="panel" id="missions">
  <div class="panel-heading"><div><span class="eyebrow">MISSIONS</span><h2>Disponibles et en cours</h2><p>Les missions déjà réservées pour vous sont retirées de cette liste et affichées dans “Mes missions”.</p></div><span class="tag">{{ $otherMissions->count() }}</span></div>
  <div class="flight-cards">
    @forelse($otherMissions as $mission)
      <article class="panel line-card">
        <div class="line-card-head">
          <div>
            <span class="eyebrow">
              @if($mission->mission_type === 'repatriation')
                RAPATRIEMENT · PRIME ×{{ number_format((float) $mission->reward_multiplier, 1, ',', ' ') }}
              @else
                {{ $mission->completion ? 'VALIDÉE' : 'EN COURS' }}
              @endif
            </span>
            <h2>
              @if($mission->mission_type === 'repatriation' && $mission->aircraft_registration)
                Rapatriement <span class="aircraft-registration">{{ $mission->aircraft_registration }}</span> vers {{ $mission->arr_airport_id }}
              @else
                {{ $mission->title }}
              @endif
            </h2>
          </div>
          <span class="tag">{{ $mission->dpt_airport_id ?: 'Libre' }} → {{ $mission->arr_airport_id ?: 'Libre' }}</span>
        </div>

        <p>{{ $mission->description }}</p>

        @if($mission->mission_type === 'repatriation')
          <div class="control-strip">
            <article>
              <span>Appareil imposé</span>
              <strong class="aircraft-registration">{{ $mission->aircraft_registration ?: '—' }}</strong>
              <small>Retour vers sa base attitrée</small>
            </article>
            <article>
              <span>Rémunération</span>
              <strong>×{{ number_format((float) $mission->reward_multiplier, 1, ',', ' ') }}</strong>
              <small>par rapport au vol normal</small>
            </article>
          </div>

          @if($mission->reserved_by_other)
            <span class="tag">DÉJÀ PRISE</span>
          @else
            <form method="post" action="{{ route('promethee.missions.reserve', $mission->id) }}">
              @csrf
              <button>Réserver la mission</button>
              <small>Si nécessaire, votre jumpseat jusqu'à {{ $mission->dpt_airport_id }} sera débité automatiquement.</small>
            </form>
          @endif
        @endif

        <small>{{ $mission->starts_on ?: 'Sans début' }} · {{ $mission->ends_on ?: 'Sans échéance' }}</small>
      </article>
    @empty
      <p class="empty">Aucune mission active.</p>
    @endforelse
  </div>
</section>

<section class="panel" id="circuits">
  <div class="panel-heading"><div><span class="eyebrow">CIRCUITS</span><h2>Tours en plusieurs étapes</h2></div></div>
  @forelse($circuits as $circuit)
    <article class="panel">
      <div class="panel-heading">
        <div>
          <span class="eyebrow">{{ $circuit->completed ? 'CIRCUIT TERMINÉ' : 'PROGRESSION' }}</span>
          <h2>{{ $circuit->title }}</h2>
          <p>{{ $circuit->description }}</p>
        </div>
        <span class="tag">{{ $circuit->legs->where('completion')->count() }}/{{ $circuit->legs->count() }}</span>
      </div>
      <div class="route-board">
        @foreach($circuit->legs as $leg)
          <article><b>{{ $leg->dpt_airport_id }}</b><i></i><b>{{ $leg->arr_airport_id }}</b><span>{{ $leg->completion ? 'Validée' : 'À voler' }}</span></article>
        @endforeach
      </div>
    </article>
  @empty
    <p class="empty">Aucun circuit actif.</p>
  @endforelse
</section>
@endsection
