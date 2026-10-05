@extends('promethee::layout')
@section('title','Pilotes & utilisateurs')
@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush
@section('content')
<div class="admin-workspace-page pilot-admin">
  <div class="ops-header compact">
    <div>
      <span class="eyebrow">PROMÉTHÉE · PERSONNEL NAVIGANT</span>
      <h1>Pilotes & utilisateurs.</h1>
      <p>Vue opérationnelle de l’effectif Air Inter : statut, rattachement, activité, qualifications et synchronisation Argos.</p>
    </div>
    <div class="admin-workspace-form-action">
      <a class="button outline" href="{{ route('admin.promethee.ranks') }}">Gérer les grades</a>
      <a class="button" href="{{ rtrim((string) config('services.airinter_id.base_url'), '/') }}/register">Créer via Argos</a>
    </div>
  </div>

  <section class="admin-kpi-grid" aria-label="Indicateurs pilotes">
    <article><span>Effectif</span><strong>{{ $metrics['total'] }}</strong><small>comptes pilotes</small></article>
    <article><span>Actifs</span><strong>{{ $metrics['active'] }}</strong><small>{{ $metrics['recent'] }} actifs en vol sur 30 j</small></article>
    <article class="is-warning"><span>En attente</span><strong>{{ $metrics['pending'] }}</strong><small>dossiers à traiter</small></article>
    <article><span>Congé</span><strong>{{ $metrics['on_leave'] }}</strong><small>indisponibilité déclarée</small></article>
    <article class="is-danger"><span>Suspendus</span><strong>{{ $metrics['suspended'] }}</strong><small>accès opérationnel limité</small></article>
    @if($hasArgosSubject)
    <article><span>Reliés Argos</span><strong>{{ $metrics['argos'] }}</strong><small>{{ $metrics['total'] ? round(($metrics['argos']/$metrics['total'])*100) : 0 }} % de l’effectif</small></article>
    @endif
  </section>

  <section class="panel admin-filter-panel">
    <div class="panel-heading">
      <div><span class="eyebrow">RECHERCHE & SEGMENTATION</span><h2>Filtrer l’effectif</h2></div>
      <a class="button outline" href="{{ route('admin.promethee.users') }}">Tout réinitialiser</a>
    </div>
    <form method="get" class="admin-filter-grid">
      <label class="wide">Recherche
        <input name="q" value="{{ request('q') }}" placeholder="Nom, ITF, callsign, e-mail, VATSIM ou IVAO">
      </label>
      <label>État
        <select name="state"><option value="">Tous</option>@foreach($states as $value=>$label)<option value="{{ $value }}" @selected((string)request('state')===(string)$value)>{{ $label }}</option>@endforeach</select>
      </label>
      <label>Grade
        <select name="rank"><option value="">Tous</option>@foreach($ranks as $rank)<option value="{{ $rank->id }}" @selected((string)request('rank')===(string)$rank->id)>{{ $rank->name }}</option>@endforeach</select>
      </label>
      <label>Compagnie
        <select name="airline"><option value="">Toutes</option>@foreach($airlines as $airline)<option value="{{ $airline->id }}" @selected((string)request('airline')===(string)$airline->id)>{{ $airline->icao }} · {{ $airline->name }}</option>@endforeach</select>
      </label>
      <label>Base
        <select name="base"><option value="">Toutes</option>@foreach($bases as $base)<option value="{{ $base->id }}" @selected(request('base')===$base->id)>{{ $base->icao ?: $base->iata ?: $base->id }} · {{ $base->name }}</option>@endforeach</select>
      </label>
      <label>Activité
        <select name="activity">
          <option value="">Toutes</option>
          <option value="30d" @selected(request('activity')==='30d')>Vol dans les 30 jours</option>
          <option value="90d" @selected(request('activity')==='90d')>Vol dans les 90 jours</option>
          <option value="no_flight" @selected(request('activity')==='no_flight')>Aucun vol</option>
        </select>
      </label>
      @if($hasArgosSubject)
      <label>Identité
        <select name="identity">
          <option value="">Toutes</option>
          <option value="argos" @selected(request('identity')==='argos')>Relié à Argos</option>
          <option value="unlinked" @selected(request('identity')==='unlinked')>Non relié</option>
        </select>
      </label>
      @endif
      <label>Tri
        <select name="sort">
          <option value="pilot_id" @selected(request('sort','pilot_id')==='pilot_id')>Identifiant pilote</option>
          <option value="name" @selected(request('sort')==='name')>Nom</option>
          <option value="flight_time" @selected(request('sort')==='flight_time')>Heures de vol</option>
          <option value="flights" @selected(request('sort')==='flights')>Nombre de vols</option>
          <option value="lastlogin_at" @selected(request('sort')==='lastlogin_at')>Dernière connexion</option>
          <option value="created_at" @selected(request('sort')==='created_at')>Ancienneté</option>
        </select>
      </label>
      <label>Ordre
        <select name="direction"><option value="asc" @selected(request('direction','asc')==='asc')>Croissant</option><option value="desc" @selected(request('direction')==='desc')>Décroissant</option></select>
      </label>
      <div class="admin-workspace-form-action"><button class="button">Appliquer</button></div>
    </form>
  </section>

  <section class="panel">
    <div class="panel-heading">
      <div><span class="eyebrow">EFFECTIF</span><h2>{{ $users->total() }} pilote(s) correspondant aux critères</h2></div>
      <small>Page {{ $users->currentPage() }} / {{ max(1,$users->lastPage()) }}</small>
    </div>
    <div class="admin-table-scroll">
      <table class="pilot-admin-table">
        <thead><tr><th>Pilote</th><th>État</th><th>Affectation</th><th>Activité</th><th>Qualifications</th><th>Identité</th><th></th></tr></thead>
        <tbody>
        @forelse($users as $pilot)
          @php
            $stateLabels=[0=>'En attente',1=>'Actif',2=>'Refusé',3=>'En congé',4=>'Suspendu',5=>'Supprimé'];
            $stateTones=[0=>'warning',1=>'success',2=>'danger',3=>'muted',4=>'danger',5=>'muted'];
            $minutes=(int)$pilot->flight_time+(int)$pilot->transfer_time;
            $lastActivity=$pilot->last_pirep?->submitted_at ?: $pilot->lastlogin_at;
          @endphp
          <tr>
            <td>
              <div class="pilot-cell">
                <span class="pilot-avatar-sm"><img src="{{ $pilot->avatar?->url ?: $pilot->gravatar(64) }}" alt=""></span>
                <span><strong>{{ $pilot->ident }}</strong><b>{{ $pilot->name }}</b><small>{{ $pilot->email }}</small></span>
              </div>
            </td>
            <td><span class="admin-status-badge {{ $stateTones[$pilot->state] ?? 'muted' }}">{{ $stateLabels[$pilot->state] ?? 'Inconnu' }}</span>@if(!$pilot->email_verified_at)<small class="admin-inline-alert">e-mail non vérifié</small>@endif</td>
            <td><strong>{{ $pilot->airline?->icao ?: '—' }} · {{ $pilot->rank?->name ?: 'Sans grade' }}</strong><small>{{ $pilot->home_airport_id ?: 'Sans base' }}@if($pilot->country) · {{ strtoupper($pilot->country) }}@endif</small></td>
            <td><strong>{{ number_format($minutes/60,1,',',' ') }} h · {{ $pilot->flights ?: $pilot->pireps_count }} vols</strong><small>@if($lastActivity) Dernière activité {{ $lastActivity->diffForHumans() }} @else Aucune activité enregistrée @endif</small></td>
            <td><strong>{{ $pilot->typeratings_count }} qualification(s)</strong><small>{{ $pilot->awards_count }} badge(s) · {{ $pilot->pireps_count }} PIREP(s)</small></td>
            <td>
              @if($hasArgosSubject)
                <span class="admin-status-badge {{ $pilot->argos_subject ? 'success' : 'warning' }}">{{ $pilot->argos_subject ? 'ARGOS LIÉ' : 'À RELIER' }}</span>
              @endif
              <small>VATSIM {{ $pilot->vatsim_id ?: '—' }} · IVAO {{ $pilot->ivao_id ?: '—' }}</small>
            </td>
            <td class="table-actions"><a class="button outline" href="{{ route('admin.promethee.users.edit',$pilot) }}">Ouvrir</a></td>
          </tr>
        @empty
          <tr><td colspan="7"><div class="admin-empty-state"><strong>Aucun pilote</strong><span>Ajustez les filtres ou réinitialisez la recherche.</span></div></td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="pagination">{{ $users->links() }}</div>
  </section>
</div>
@endsection
