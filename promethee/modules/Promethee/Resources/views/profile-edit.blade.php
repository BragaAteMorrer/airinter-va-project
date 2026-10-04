@extends('promethee::layout')
@section('title','Modifier mon profil')
@section('content')
<div class="page-heading"><div><span class="eyebrow">ESPACE PILOTE</span><h1>Modifier mon profil.</h1><p>Ces informations sont utilisées pour votre compte et votre carnet de vol.</p></div><a class="button outline" href="{{ route('promethee.profile') }}">Annuler</a></div>
<form class="panel profile-form" method="post" action="{{ route('promethee.profile.update') }}" enctype="multipart/form-data">
@csrf @method('PATCH')
<div class="panel-heading"><div><span class="eyebrow">IDENTITÉ</span><h2>Informations personnelles</h2></div>@if(config('services.airinter_id.enabled'))<a class="button outline" href="{{ rtrim(config('services.airinter_id.base_url'), '/') }}/account">Sécurité & compte Air Inter</a>@endif</div>
@if(config('services.airinter_id.enabled'))<p class="notice">Argos est l’identité Air Inter commune à Prométhée et Hermès. Utilisez le compte Air Inter pour gérer mot de passe, MFA, passkeys et sessions. Les champs ci-dessous restent les données opérationnelles de votre profil Prométhée.</p>@endif
<div class="form-grid">
<label>Nom affiché<input name="name" value="{{ old('name', $pilot->name) }}" required autocomplete="name"></label>
<label>E-mail<input type="email" name="email" value="{{ old('email', $pilot->email) }}" required autocomplete="email"><small>Une modification demandera une nouvelle vérification.</small></label>
<label>Compagnie<select name="airline_id" required>@foreach($airlines as $airline)<option value="{{ $airline->id }}" @selected((string)old('airline_id', $pilot->airline_id) === (string)$airline->id)>{{ $airline->icao }} · {{ $airline->name }}</option>@endforeach</select></label>
<label>Base d'attache<select name="home_airport_id"><option value="">Aucune base</option>@foreach($airports as $airport)<option value="{{ $airport->id }}" @selected(old('home_airport_id', $pilot->home_airport_id) === $airport->id)>{{ $airport->icao ?: $airport->id }} · {{ $airport->name }}@if($airport->location) ({{ $airport->location }})@endif</option>@endforeach</select></label>
<label>Pays<select name="country"><option value="">Non renseigné</option>@foreach($countries as $code => $name)<option value="{{ $code }}" @selected(old('country', $pilot->country) === $code)>{{ $name }}</option>@endforeach</select></label>
@php($selectedTimezone = in_array(old('timezone', $pilot->timezone), $timezones, true) ? old('timezone', $pilot->timezone) : 'Europe/Paris')
<label>Fuseau horaire<select name="timezone" required>@foreach($timezones as $timezone)<option value="{{ $timezone }}" @selected($selectedTimezone === $timezone)>{{ $timezone }}</option>@endforeach</select><small>Format IANA, par exemple Europe/Paris.</small></label>
<label>Identifiant VATSIM<input name="vatsim_id" value="{{ old('vatsim_id', $pilot->vatsim_id) }}"></label>
<label>Identifiant IVAO<input name="ivao_id" value="{{ old('ivao_id', $pilot->ivao_id) }}"></label>
<label class="full">Photo de profil<input type="file" name="avatar" accept="image/jpeg,image/png,image/webp"><small>JPEG, PNG ou WebP, 2 Mo maximum.</small></label>
</div>
<div class="panel-heading profile-password-heading"><div><span class="eyebrow">CONNEXION DE SECOURS</span><h2>Mot de passe Prométhée</h2><p>@if(config('services.airinter_id.enabled'))Ce mot de passe sert uniquement à la connexion Prométhée de secours. Le mot de passe principal Air Inter se gère dans Argos.@else Laissez ces deux champs vides pour conserver votre mot de passe actuel.@endif</p></div></div>
<div class="form-grid"><label>Nouveau mot de passe<input type="password" name="password" autocomplete="new-password"></label><label>Confirmer le mot de passe<input type="password" name="password_confirmation" autocomplete="new-password"></label></div>
<div class="profile-form-actions"><button type="submit">Enregistrer les modifications</button></div>
</form>
@endsection
