@extends('promethee::layout')
@section('title','Modifier un pilote')
@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush
@section('content')
<div class="admin-workspace-page">
<div class="ops-header compact"><div><span class="eyebrow">PROMÉTHÉE · ADMINISTRATION</span><h1>{{ $pilot->name }}</h1><p>{{ $pilot->pilot_id }} · fiche phpVMS éditée sans quitter Prométhée.</p></div><a class="button outline" href="{{ route('admin.promethee.users') }}">← Pilotes</a></div>
<form method="post" action="{{ route('admin.promethee.users.update',$pilot) }}" class="panel form-grid">@csrf @method('PUT')
<label>Nom<input name="name" required value="{{ old('name',$pilot->name) }}"></label><label>E-mail<input type="email" name="email" required value="{{ old('email',$pilot->email) }}"></label><label>ID pilote<input type="number" name="pilot_id" required value="{{ old('pilot_id',$pilot->pilot_id) }}"></label><label>Callsign<input maxlength="4" name="callsign" value="{{ old('callsign',$pilot->callsign) }}"></label>
<label>Compagnie<select name="airline_id" required>@foreach($airlines as $airline)<option value="{{ $airline->id }}" @selected((string)$pilot->airline_id===(string)$airline->id)>{{ $airline->icao }} · {{ $airline->name }}</option>@endforeach</select></label>
<label>Grade<select name="rank_id"><option value="">—</option>@foreach($ranks as $rank)<option value="{{ $rank->id }}" @selected((string)$pilot->rank_id===(string)$rank->id)>{{ $rank->name }}</option>@endforeach</select></label>
<label class="filter-wide">Base<select name="home_airport_id"><option value="">—</option>@foreach($airports as $airport)<option value="{{ $airport->id }}" @selected((string)$pilot->home_airport_id===(string)$airport->id)>{{ $airport->icao ?: $airport->iata }} · {{ $airport->name }}</option>@endforeach</select></label>
<div><button class="button">Enregistrer le pilote</button></div></form>
</div>
@endsection