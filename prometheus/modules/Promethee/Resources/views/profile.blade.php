@extends('promethee::layout')
@section('title','Profil pilote')
@section('content')
@if(auth()->id() === $pilot->id)
<div class="profile-page-actions"><a class="button outline" href="{{ route('promethee.profile.edit') }}">Modifier mon profil</a></div>
@endif
<div class="ops-header compact"><div><span class="eyebrow">{{ $pilot->ident }}</span><h1>{{ $pilot->name }}</h1><div class="rank-block">@if($pilot->rank?->image_url)<img class="distinction-image rank-image" src="{{ $pilot->rank->image_url }}" alt="{{ $pilot->rank?->name ?? 'Grade' }}">@endif<p>{{ $pilot->rank?->name ?? 'Sans grade' }} · {{ $pilot->airline?->name ?? 'Air Inter' }}</p></div></div><div class="ops-clock"><span>Solde phpVMS</span><strong>{{ $wallet }}</strong><small>Base {{ $pilot->home_airport_id ?: '—' }} · Position {{ $pilot->curr_airport_id ?: '—' }}</small></div></div>
<section class="control-strip"><article><span>Vols acceptés</span><strong>{{ $pilot->accepted_pireps_count }}</strong><small>Carnet validé</small></article><article><span>Total vols</span><strong>{{ $pilot->flights }}</strong><small>Compteur phpVMS</small></article><article><span>Heures</span><strong>{{ round(($pilot->flight_time ?? 0)/60) }}</strong><small>Temps total</small></article><article><span>Réservations</span><strong>{{ $pilot->bids_count }}</strong><small>Bids actifs</small></article></section>
<div class="two-columns"><section class="panel"><div class="panel-heading"><div><span class="eyebrow">CARNET PROMÉTHÉE</span><h2>Derniers vols acceptés</h2></div></div><div class="table-wrap"><table><thead><tr><th>Vol</th><th>Ligne</th><th>Avion</th><th>Durée</th><th>Date</th></tr></thead><tbody>@forelse($pireps as $pirep)<tr><td><strong>{{ $pirep->ident }}</strong></td><td>{{ $pirep->dpt_airport_id }} → {{ $pirep->arr_airport_id }}</td><td>{{ $pirep->aircraft?->registration ?? '—' }}</td><td>{{ $pirep->flight_time ? floor($pirep->flight_time / 60).'h '.str_pad($pirep->flight_time % 60,2,'0',STR_PAD_LEFT) : '—' }}</td><td>{{ optional($pirep->submitted_at)->setTimezone('Europe/Paris')->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="5">Aucun vol accepté.</td></tr>@endforelse</tbody></table></div>{{ $pireps->links() }}</section><section class="panel"><div class="panel-heading"><div><span class="eyebrow">LIGNES FRÉQUENTES</span><h2>Routes préférées</h2></div></div><div class="route-board">@forelse($routes as $route)<article><b>{{ $route->dpt_airport_id }}</b><i></i><b>{{ $route->arr_airport_id }}</b><span>{{ $route->total }} vols</span></article>@empty<p class="empty">Les routes apparaîtront après acceptation des PIREP.</p>@endforelse</div></section></div>
@if(auth()->id() === $pilot->id)
<section class="panel" id="my-missions">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">MON ESPACE</span>
      <h2>Mes missions</h2>
      <p>Retrouvez ici les missions que vous avez réservées.</p>
    </div>
    <span class="tag">{{ $myMissions->count() }} active(s)</span>
  </div>

  <div class="profile-page-actions">
    <a class="button" href="{{ route('promethee.missions') }}#missions">Voir les missions</a>
    <a class="button outline" href="{{ route('promethee.missions') }}#circuits">Voir les circuits</a>
  </div>

  <div class="flight-cards">
    @forelse($myMissions as $mission)
      <article class="panel line-card">
        <div class="line-card-head">
          <div>
            <span class="eyebrow">{{ $mission->mission_type === 'repatriation' ? 'RAPATRIEMENT' : 'MISSION' }}</span>
            <h3>{{ $mission->title }}</h3>
          </div>
          <span class="tag">{{ $mission->dpt_airport_id ?: 'Libre' }} → {{ $mission->arr_airport_id ?: 'Libre' }}</span>
        </div>

        @if($mission->description)<p>{{ $mission->description }}</p>@endif

        <div class="control-strip">
          <article>
            <span>Appareil</span>
            <strong>{{ $mission->aircraft_registration ?: 'Libre' }}</strong>
            <small>{{ $mission->mission_type === 'repatriation' ? 'Appareil imposé' : 'Selon la mission' }}</small>
          </article>
          <article>
            <span>Échéance</span>
            <strong>{{ $mission->ends_on ? CarbonCarbonImmutable::parse($mission->ends_on)->format('d/m/Y') : 'Aucune' }}</strong>
            <small>Mission active</small>
          </article>
        </div>

        <form method="post" action="{{ route('promethee.missions.cancel', $mission->id) }}" onsubmit="return confirm('Abandonner cette mission ? Elle redeviendra disponible pour les autres pilotes.');">
          @csrf
          @method('DELETE')
          <button class="button outline" type="submit">Abandonner la mission</button>
        </form>
      </article>
    @empty
      <div class="empty">
        <p>Vous n’avez aucune mission réservée actuellement.</p>
        <a class="button" href="{{ route('promethee.missions') }}#missions">Choisir une mission</a>
      </div>
    @endforelse
  </div>
</section>
@endif

<section class="panel"><div class="panel-heading"><div><span class="eyebrow">DISTINCTIONS</span><h2>Badges obtenus</h2></div><span class="tag">{{ $badges->count() }} badge(s)</span></div><div class="distinctions">@forelse($badges as $badge)<article class="distinction">@if($badge->image_url)<img class="distinction-image" src="{{ $badge->image_url }}" alt="">@endif<div><strong>{{ $badge->name }}</strong><p>{{ $badge->description }}</p></div></article>@empty<p class="muted">Aucun badge obtenu pour le moment.</p>@endforelse</div></section>
@endsection
