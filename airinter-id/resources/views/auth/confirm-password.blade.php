@extends('layout')
@section('title', 'Confirmer le mot de passe · Argos')
@section('content')
<section class="card auth-card">
    <span class="kicker">ACTION SENSIBLE</span>
    <h1>Confirmez votre mot de passe</h1>
    <form method="post" action="{{ route('password.confirm.store') }}">
        @csrf
        <label>Mot de passe
            <input type="password" name="password" autocomplete="current-password" required autofocus>
        </label>
        @error('password')<p class="error">{{ $message }}</p>@enderror
        <button class="button primary">Confirmer</button>
    </form>
</section>
@endsection
