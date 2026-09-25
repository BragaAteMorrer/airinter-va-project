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

<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">MISSIONS</span><h2>À accomplir</h2></div></div>
  <div class="flight-cards">
    @forelse($missions as $mission)
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
            <h2>{{ $mission->title }}</h2>
          </div>
          <span class="tag">{{ $mission->dpt_airport_id ?: 'Libre' }} → {{ $mission->arr_airport_id ?: 'Libre' }}</span>
        </div>

        <p>{{ $mission->description }}</p>

        @if($mission->mission_type === 'repatriation')
          <div class="control-strip">
            <article>
              <span>Appareil imposé</span>
              <strong>{{ $mission->aircraft_registration ?: '—' }}</strong>
              <small>Retour vers sa base attitrée</small>
            </article>
            <article>
              <span>Rémunération</span>
              <strong>×{{ number_format((float) $mission->reward_multiplier, 1, ',', ' ') }}</strong>
              <small>par rapport au vol normal</small>
            </article>
          </div>

          @if($mission->booking)
            <span class="tag">MISSION RÉSERVÉE</span>
          @elseif($mission->reserved_by_other)
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

<section class="panel">
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
