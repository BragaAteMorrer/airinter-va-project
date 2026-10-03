@extends('promethee::layout')
@section('title','Paramètres du passeport')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">ADMINISTRATION</span><h1>Passeport pilote.</h1><p>Les cachets restent calculés à partir des PIREP acceptés.</p></div></div>
<section class="panel"><form method="post" action="{{ route('admin.promethee.passport.save') }}" class="form-grid">@csrf<label><input type="checkbox" name="enabled" value="1" @checked($enabled)> Activer le passeport</label><label><input type="checkbox" name="map_enabled" value="1" @checked($showMap)> Afficher la carte des pays validés</label><button>Enregistrer</button></form></section>
@endsection
