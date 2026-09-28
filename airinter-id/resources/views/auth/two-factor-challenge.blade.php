@extends('layout')
@section('title', 'Double authentification · Argos')
@section('content')
<section class="card auth-card">
    <span class="kicker">SÉCURITÉ ARGOS</span>
    <h1>Double authentification</h1>
    <p>Saisissez le code à 6 chiffres de votre application d’authentification ou un code de récupération.</p>
    <form method="post" action="{{ route('two-factor.login.store') }}">
        @csrf
        <label>Code
            <input name="code" autocomplete="one-time-code" inputmode="numeric" required autofocus>
        </label>
        @error('code')<p class="error">{{ $message }}</p>@enderror
        <label class="check">
            <input type="checkbox" name="trust_device" value="1">
            Faire confiance à cet appareil pendant {{ config('argos-security.trusted_device_days', 30) }} jours
        </label>
        <button class="button primary">Continuer</button>
    </form>
</section>
@endsection
