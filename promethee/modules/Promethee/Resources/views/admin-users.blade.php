@extends('promethee::layout')
@section('title','Utilisateurs')
@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush
@section('content')
<div class="admin-workspace-page">
<div class="ops-header compact"><div><span class="eyebrow">PROMÉTHÉE · ADMINISTRATION</span><h1>Pilotes & utilisateurs.</h1><p>Gestion opérationnelle des pilotes dans Prométhée. L’identité et les accès restent centralisés dans Argos.</p></div><a class="button" href="{{ rtrim((string) config('services.airinter_id.base_url'), '/') }}/register">Créer via Argos</a></div>
<section class="panel"><form method="get" class="flight-filter"><label class="filter-wide">Recherche<input name="q" value="{{ request('q') }}" placeholder="Nom, identifiant pilote ou e-mail"></label><label>Grade<select name="rank"><option value="">Tous</option>@foreach($ranks as $rank)<option value="{{ $rank->id }}" @selected((string)request('rank')===(string)$rank->id)>{{ $rank->name }}</option>@endforeach</select></label><label>Compagnie<select name="airline"><option value="">Toutes</option>@foreach($airlines as $airline)<option value="{{ $airline->id }}" @selected((string)request('airline')===(string)$airline->id)>{{ $airline->icao }} · {{ $airline->name }}</option>@endforeach</select></label><button>Filtrer</button><a class="button outline" href="{{ route('admin.promethee.users') }}">Réinitialiser</a></form></section>
<section class="panel table-wrap"><table><thead><tr><th>Pilote</th><th>Compagnie</th><th>Grade</th><th>Base</th><th>E-mail</th><th></th></tr></thead><tbody>
@forelse($users as $pilot)<tr><td><strong>{{ $pilot->pilot_id ?: '—' }}</strong><br><small>{{ $pilot->name }}</small></td><td>{{ $pilot->airline?->icao ?: '—' }}</td><td>{{ $pilot->rank?->name ?: '—' }}</td><td>{{ $pilot->home_airport_id ?: '—' }}</td><td>{{ $pilot->email }}</td><td><a class="button outline" href="{{ route('admin.promethee.users.edit',$pilot->id) }}">Ouvrir la fiche</a></td></tr>@empty<tr><td colspan="6">Aucun utilisateur.</td></tr>@endforelse
</tbody></table><div class="pagination">{{ $users->links() }}</div></section>
</div>
@endsection