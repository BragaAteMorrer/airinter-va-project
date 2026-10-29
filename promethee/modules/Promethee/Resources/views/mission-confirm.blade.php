@extends('promethee::layout')
@section('title', 'Confirmer un rapatriement')
@section('content')
<div class="ops-header compact">
  <div>
    <span class="eyebrow">RAPATRIEMENT · CONFIRMATION</span>
    <h1>Vérifier avant de réserver.</h1>
    <p>Aucune position pilote ni aucune écriture financière n'est modifiée avant votre confirmation.</p>
  </div>
</div>

<section class="panel">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">MISSION</span>
      <h2>{{ $mission->title }}</h2>
      <p>{{ $mission->description }}</p>
    </div>
    <span class="tag">{{ $mission->dpt_airport_id }} → {{ $mission->arr_airport_id }}</span>
  </div>
  <div class="control-strip">
    <article>
      <span>Position actuelle du pilote</span>
      <strong>{{ $quote['origin_airport_id'] }}</strong>
    </article>
    <article>
      <span>Position après éventuel jumpseat</span>
      <strong>{{ $quote['destination_airport_id'] }}</strong>
    </article>
    <article>
      <span>Coût du déplacement</span>
      <strong>{{ $quote['needs_jumpseat'] ? (string) $quote['jumpseat_amount'] : 'Gratuit' }}</strong>
      <small>{{ $quote['needs_jumpseat'] ? 'Débité uniquement après confirmation' : 'Vous êtes déjà au départ de la mission' }}</small>
    </article>
  </div>
  <p class="hint">
    @if($quote['needs_jumpseat'])
      En confirmant, vous réservez la mission et acceptez le débit d'un jumpseat de
      <strong>{{ $quote['origin_airport_id'] }} à {{ $quote['destination_airport_id'] }}</strong>
      pour <strong>{{ $quote['jumpseat_amount'] }}</strong>. Votre position pilote changera immédiatement.
    @else
      Votre position ne changera pas : aucun jumpseat n'est nécessaire.
    @endif
  </p>
  <form method="post" action="{{ route('promethee.missions.reserve', $mission->id) }}">
    @csrf
    <input type="hidden" name="confirmed" value="1">
    <input type="hidden" name="origin_airport_id" value="{{ $quote['origin_airport_id'] }}">
    <input type="hidden" name="jumpseat_amount_minor" value="{{ $quote['jumpseat_amount']->getAmount() }}">
    <button type="submit">Confirmer la réservation{{ $quote['needs_jumpseat'] ? ' et le jumpseat' : '' }}</button>
    <a class="button outline" href="{{ route('promethee.missions') }}#rapatriements">Annuler et revenir aux missions</a>
  </form>
</section>
@endsection
