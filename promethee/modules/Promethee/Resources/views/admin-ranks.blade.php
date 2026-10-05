@extends('promethee::layout')
@section('title','Grades pilotes')
@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush
@section('content')
<div class="admin-workspace-page rank-admin">
  <div class="ops-header compact">
    <div><span class="eyebrow">PROMÉTHÉE · CARRIÈRES PILOTES</span><h1>Grades & progression.</h1><p>Pilotez les seuils, l’automatisation des promotions et la couverture flotte de chaque grade.</p></div>
    <div class="admin-workspace-form-action"><a class="button outline" href="{{ route('admin.promethee.users') }}">Voir les pilotes</a><a class="button" href="{{ route('admin.promethee.ranks.create') }}">Créer un grade</a></div>
  </div>

  <section class="admin-kpi-grid">
    <article><span>Grades</span><strong>{{ $metrics['total'] }}</strong><small>niveaux de carrière</small></article>
    <article><span>Promotions auto</span><strong>{{ $metrics['automatic'] }}</strong><small>grades automatisés</small></article>
    <article><span>Pilotes classés</span><strong>{{ $metrics['pilots'] }}</strong><small>avec un grade attribué</small></article>
    <article class="{{ $metrics['without_subfleets'] ? 'is-warning' : '' }}"><span>Sans flotte</span><strong>{{ $metrics['without_subfleets'] }}</strong><small>grade(s) sans sous-flotte</small></article>
  </section>

  <section class="panel admin-filter-panel">
    <form method="get" class="admin-filter-grid">
      <label class="wide">Recherche<input name="q" value="{{ request('q') }}" placeholder="Nom du grade"></label>
      <label>Promotion
        <select name="automation"><option value="">Toutes</option><option value="automatic" @selected(request('automation')==='automatic')>Automatique</option><option value="manual" @selected(request('automation')==='manual')>Manuelle</option></select>
      </label>
      <label>Couverture flotte
        <select name="coverage"><option value="">Toutes</option><option value="with_subfleets" @selected(request('coverage')==='with_subfleets')>Avec sous-flottes</option><option value="without_subfleets" @selected(request('coverage')==='without_subfleets')>Sans sous-flotte</option></select>
      </label>
      <div class="admin-workspace-form-action"><button class="button">Filtrer</button><a class="button outline" href="{{ route('admin.promethee.ranks') }}">Réinitialiser</a></div>
    </form>
  </section>

  <div class="rank-admin-grid">
  @forelse($ranks as $rank)
    <article class="panel rank-card">
      <div class="rank-card-head">
        <div><span class="eyebrow">{{ $rank->auto_promote ? 'PROGRESSION AUTOMATIQUE' : 'PROGRESSION MANUELLE' }}</span><h2>{{ $rank->name }}</h2></div>
        <strong class="rank-hours">{{ number_format((float)$rank->hours,0,',',' ') }} h</strong>
      </div>
      <div class="admin-detail-metrics">
        <div><span>Pilotes</span><strong>{{ $rank->users_count }}</strong><small>{{ $rank->active_users_count }} actif(s)</small></div>
        <div><span>Sous-flottes</span><strong>{{ $rank->subfleets_count }}</strong><small>{{ $rank->coverage_airlines_count }} compagnie(s)</small></div>
        <div><span>Prêts promotion</span><strong>{{ $rank->promotion_ready_count }}</strong><small>@if($rank->next_rank) vers {{ $rank->next_rank->name }} @else grade terminal @endif</small></div>
      </div>
      <div class="rank-policy">
        <span class="admin-status-badge {{ $rank->auto_approve_acars ? 'success':'muted' }}">ACARS {{ $rank->auto_approve_acars ? 'AUTO':'MANUEL' }}</span>
        <span class="admin-status-badge {{ $rank->auto_approve_manual ? 'success':'muted' }}">MANUEL {{ $rank->auto_approve_manual ? 'AUTO':'MANUEL' }}</span>
      </div>
      <div class="rank-fleet-list">
        @forelse($rank->subfleets->take(7) as $subfleet)<span>{{ $subfleet->airline?->icao }} · {{ $subfleet->name }}</span>@empty<em>Aucune sous-flotte autorisée</em>@endforelse
        @if($rank->subfleets_count>7)<span>+ {{ $rank->subfleets_count-7 }} autre(s)</span>@endif
      </div>
      <div class="rank-card-footer">
        @if($rank->next_rank)<small>Prochain seuil : <strong>{{ $rank->next_rank->name }}</strong> à {{ number_format((float)$rank->next_rank->hours,0,',',' ') }} h</small>@else<small>Sommet de la grille de progression</small>@endif
        <a class="button outline" href="{{ route('admin.promethee.ranks.edit',$rank) }}">Configurer</a>
      </div>
    </article>
  @empty
    <section class="panel admin-empty-state"><strong>Aucun grade</strong><span>Aucun résultat pour ces filtres.</span></section>
  @endforelse
  </div>
</div>
@endsection
