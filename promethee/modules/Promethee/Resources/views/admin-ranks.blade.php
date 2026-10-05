@extends('promethee::layout')
@section('title','Grades')
@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush
@section('content')
<div class="admin-workspace-page">
<div class="ops-header compact"><div><span class="eyebrow">PROMÉTHÉE · ADMINISTRATION</span><h1>Grades pilotes.</h1><p>Grades, seuils, promotions et sous-flottes administrés directement dans Prométhée.</p></div><a class="button" href="{{ route('admin.promethee.ranks.create') }}">Créer un grade</a></div>
<section class="panel"><form method="get" class="flight-filter"><label class="filter-wide">Recherche<input name="q" value="{{ request('q') }}" placeholder="Nom du grade"></label><button>Filtrer</button><a class="button outline" href="{{ route('admin.promethee.ranks') }}">Réinitialiser</a></form></section>
<section class="panel table-wrap"><table><thead><tr><th>Grade</th><th>Heures requises</th><th>Pilotes</th><th>Sous-flottes</th><th>Actions</th></tr></thead><tbody>
@forelse($ranks as $rank)<tr><td><strong>{{ $rank->name }}</strong></td><td>{{ number_format((float)$rank->hours,1,',',' ') }} h</td><td>{{ $rank->users_count }}</td><td>{{ $rank->subfleets_count }}</td><td><a class="button outline" href="{{ route('admin.promethee.ranks.edit',$rank->id) }}">Modifier</a></td></tr>@empty<tr><td colspan="5">Aucun grade.</td></tr>@endforelse
</tbody></table></section>
</div>
@endsection