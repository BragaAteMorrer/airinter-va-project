@extends('promethee::layout')
@section('title','Profil pilote')
@section('content')
@if(auth()->id() === $pilot->id)
<div class="profile-page-actions">
  <a class="button outline" href="{{ route('promethee.profile.edit') }}">Modifier mon profil</a>
  @if(config('services.airinter_id.enabled'))
    <a class="button" href="{{ rtrim(config('services.airinter_id.base_url'), '/') }}/account">Compte Air Inter</a>
  @endif
</div>
@endif
<div class="ops-header compact"><div class="pilot-profile-heading"><img class="pilot-profile-avatar" src="{{ $pilot->admin_portrait_url ?: ($pilot->avatar?->url ?: $pilot->gravatar(160)) }}" alt="Portrait de {{ $pilot->name }}"><div><span class="eyebrow">{{ $pilot->ident }}</span><h1>{{ $pilot->name }}</h1><div class="rank-block">@if($pilot->rank?->image_url)<img class="distinction-image rank-image" src="{{ $pilot->rank->image_url }}" alt="{{ $pilot->rank?->name ?? 'Grade' }}">@endif<p>{{ $pilot->rank?->name ?? 'Sans grade' }} · {{ $pilot->airline?->name ?? 'Air Inter' }}</p></div></div></div><div class="ops-clock"><span>Solde phpVMS</span><strong>{{ $wallet }}</strong><small>Base {{ $pilot->home_airport_id ?: '—' }} · Position {{ $pilot->curr_airport_id ?: '—' }}</small></div></div>
<section class="control-strip"><article><span>Vols acceptés</span><strong>{{ $pilot->accepted_pireps_count }}</strong><small>Carnet validé</small></article><article><span>Total vols</span><strong>{{ $pilot->flights }}</strong><small>Compteur phpVMS</small></article><article><span>Heures</span><strong>{{ round(($pilot->flight_time ?? 0)/60) }}</strong><small>Temps total</small></article><article><span>Réservations</span><strong>{{ $pilot->bids_count }}</strong><small>Bids actifs</small></article></section>
<div class="two-columns"><section class="panel"><div class="panel-heading"><div><span class="eyebrow">CARNET PROMÉTHÉE</span><h2>Derniers vols acceptés</h2></div></div><div class="table-wrap"><table><thead><tr><th>Vol</th><th>Ligne</th><th>Avion</th><th>Durée</th><th>Date</th></tr></thead><tbody>@forelse($pireps as $pirep)<tr><td><strong>{{ $pirep->ident }}</strong></td><td>{{ $pirep->dpt_airport_id }} → {{ $pirep->arr_airport_id }}</td><td>{{ $pirep->aircraft?->registration ?? '—' }}</td><td>{{ $pirep->flight_time ? floor($pirep->flight_time / 60).'h '.str_pad($pirep->flight_time % 60,2,'0',STR_PAD_LEFT) : '—' }}</td><td>{{ optional($pirep->submitted_at)->setTimezone('Europe/Paris')->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="5">Aucun vol accepté.</td></tr>@endforelse</tbody></table></div>{{ $pireps->links() }}</section><section class="panel"><div class="panel-heading"><div><span class="eyebrow">LIGNES FRÉQUENTES</span><h2>Routes préférées</h2></div></div><div class="route-board">@forelse($routes as $route)<article><b>{{ $route->dpt_airport_id }}</b><i></i><b>{{ $route->arr_airport_id }}</b><span>{{ $route->total }} vols</span></article>@empty<p class="empty">Les routes apparaîtront après acceptation des PIREP.</p>@endforelse</div></section></div>
@if(auth()->id() === $pilot->id && config('services.airinter_id.enabled'))
<section class="panel profile-identity-link">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">IDENTITÉ AIR INTER</span>
      <h2>Compte Argos</h2>
      <p>Votre connexion, votre sécurité et vos applications Air Inter sont regroupées dans Argos. Prométhée conserve ici uniquement les données opérationnelles de votre profil pilote.</p>
    </div>
    <span class="tag">SSO · OIDC</span>
  </div>
  <div class="profile-page-actions">
    <a class="button" href="{{ rtrim(config('services.airinter_id.base_url'), '/') }}/account">Gérer mon compte Air Inter</a>
    <span class="muted">Prométhée · Hermès · services Air Inter</span>
  </div>
</section>
@endif
@if(auth()->id() === $pilot->id)
<section class="panel profile-mission-link">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">OPÉRATIONS SPÉCIALES</span>
      <h2>Missions & circuits</h2>
      <p>Vos missions réservées et les missions disponibles sont maintenant regroupées dans un seul espace opérationnel.</p>
    </div>
    <span class="tag">{{ $myMissions->count() }} réservée(s)</span>
  </div>
  <div class="profile-page-actions">
    <a class="button" href="{{ route('promethee.missions') }}#my-missions">Ouvrir mes missions</a>
    <a class="button outline" href="{{ route('promethee.missions') }}#missions">Voir les missions disponibles</a>
  </div>
</section>
@endif

<section class="panel">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">FORMATION SUR SIMULATEUR RÉEL</span>
      <h2>Certifications vérifiées</h2>
      <p>Sessions et certifications documentées par le staff Air Inter VA. Elles ne remplacent aucune licence ou qualification réglementaire.</p>
    </div>
    <span class="tag">{{ $realSimulatorCertifications->count() }} certification(s)</span>
  </div>
  <div class="distinctions">
    @forelse($realSimulatorCertifications as $certification)
      <article class="distinction">
        <div>
          <strong>{{ $certification->certificate_name }}</strong>
          <p>
            @if($certification->aircraft_type){{ $certification->aircraft_type }} · @endif
            {{ str_replace('_', ' ', $certification->simulator_level) }} · {{ $certification->device_name }}
          </p>
          <p>{{ $certification->organisation }}@if($certification->location) · {{ $certification->location }}@endif</p>
          <small>
            Réalisée le {{ optional($certification->completed_on)->format('d/m/Y') }}
            @if($certification->valid_until)
              · {{ $certification->isExpired() ? 'Échue le' : 'Valide jusqu’au' }} {{ $certification->valid_until->format('d/m/Y') }}
            @else
              · Sans échéance enregistrée
            @endif
          </small>
        </div>
      </article>
    @empty
      <p class="muted">Aucune certification sur simulateur réel vérifiée pour ce pilote.</p>
    @endforelse
  </div>
</section>

<section class="panel"><div class="panel-heading"><div><span class="eyebrow">DISTINCTIONS</span><h2>Badges obtenus</h2></div><span class="tag">{{ $badges->count() }} badge(s)</span></div><div class="distinctions">@forelse($badges as $badge)<article class="distinction">@if($badge->image_url)<img class="distinction-image" src="{{ $badge->image_url }}" alt="">@endif<div><strong>{{ $badge->name }}</strong><p>{{ $badge->description }}</p></div></article>@empty<p class="muted">Aucun badge obtenu pour le moment.</p>@endforelse</div></section>
@endsection
