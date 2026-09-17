@extends('promethee::layout')
@section('title','La communauté')
@section('content')
<div class="page-heading"><div><span class="eyebrow">UNE HISTOIRE D’ÉQUIPAGE</span><h1>Ceux qui font Air Inter.</h1><p>Les pilotes d’aujourd’hui et ceux qui ont écrit les chapitres précédents.</p></div></div>
@php($canManage = auth()->user()?->ability('admin','admin-access') ?? false)
<div class="tabs">@foreach(['actif'=>'Pilotes actifs','ancien'=>'Les anciens','retraite'=>'Les retraités'] as $key=>$label)<a @class(['active'=>$status===$key]) href="{{ route('promethee.pilots',['status'=>$key]) }}">{{ $label }}</a>@endforeach</div>
<form class="panel filters directory-filter" method="get">
    <input type="hidden" name="status" value="{{ $status }}">
    <label>Rechercher<input name="q" value="{{ request('q') }}" placeholder="Nom, matricule, e-mail"></label>
    <label>Compagnie<select name="airline_id"><option value="">Toutes les compagnies</option>@foreach($airlines as $airline)<option value="{{ $airline->id }}" @selected((string) request('airline_id') === (string) $airline->id)>{{ $airline->icao }} · {{ $airline->name }}</option>@endforeach</select></label>
    <label>Grade<select name="rank_id"><option value="">Tous les grades</option>@foreach($ranks as $rank)<option value="{{ $rank->id }}" @selected((string) request('rank_id') === (string) $rank->id)>{{ $rank->name }}</option>@endforeach</select></label>
    <button>Filtrer</button><a href="{{ route('promethee.pilots',['status'=>$status]) }}">Réinitialiser</a>
</form>
<div class="pilot-grid">@forelse($pilots as $pilot)<article class="panel pilot-card"><div class="avatar-text">{{ mb_substr($pilot->name,0,1) }}</div><span class="eyebrow">{{ $pilot->ident }}</span><h2>{{ $pilot->name }}</h2><p>{{ $pilot->rank?->name ?? 'Sans grade' }}</p><div class="pilot-numbers"><span><b>{{ $pilot->flights }}</b> vols</span><span><b>{{ round(($pilot->flight_time ?? 0)/60) }}</b> heures</span></div><a href="{{ route('promethee.pilots.show',$pilot->id) }}">Fiche Prométhée →</a>
@if($canManage)<form class="member-form" method="post" action="{{ route('admin.promethee.pilots.save',$pilot->id) }}">@csrf<label>Classement<select name="status"><option value="actif" @selected($status==='actif')>Actif</option><option value="ancien" @selected($status==='ancien')>Ancien</option><option value="retraite" @selected($status==='retraite')>Retraité</option></select></label><button>Enregistrer</button></form>@endif</article>@empty<div class="panel empty"><h2>Cette page reste à écrire.</h2><p>Aucun pilote dans cette catégorie.</p></div>@endforelse</div>
{{ $pilots->links('pagination::bootstrap-4') }}
@endsection
