@extends('promethee::layout')
@section('title','Modifier un grade')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">ADMINISTRATION · GRADES</span><h1>{{ $rank->name }}</h1><p>Configuration phpVMS éditée directement depuis Prométhée.</p></div><a class="button outline" href="{{ route('admin.promethee.ranks') }}">← Grades</a></div>
<form method="post" action="{{ route('admin.promethee.ranks.update',$rank) }}" class="panel form-grid">@csrf @method('PUT')
<label>Nom<input name="name" required value="{{ old('name',$rank->name) }}"></label><label>Heures requises<input type="number" min="0" name="hours" required value="{{ old('hours',$rank->hours) }}"></label>
<label>Taux ACARS<input type="number" step="0.01" min="0" name="acars_base_pay_rate" value="{{ old('acars_base_pay_rate',$rank->acars_base_pay_rate) }}"></label><label>Taux manuel<input type="number" step="0.01" min="0" name="manual_base_pay_rate" value="{{ old('manual_base_pay_rate',$rank->manual_base_pay_rate) }}"></label>
<label class="check"><input type="checkbox" name="auto_promote" value="1" @checked($rank->auto_promote)> Promotion automatique</label><label class="check"><input type="checkbox" name="auto_approve_acars" value="1" @checked($rank->auto_approve_acars)> Approuver ACARS automatiquement</label><label class="check"><input type="checkbox" name="auto_approve_manual" value="1" @checked($rank->auto_approve_manual)> Approuver manuel automatiquement</label>
<label class="filter-wide">Sous-flottes autorisées<select name="subfleet_ids[]" multiple size="12">@foreach($subfleets as $subfleet)<option value="{{ $subfleet->id }}" @selected($rank->subfleets->contains('id',$subfleet->id))>{{ $subfleet->name }} · {{ $subfleet->airline?->icao }}</option>@endforeach</select></label>
<div><button class="button">Enregistrer le grade</button></div></form>
@endsection