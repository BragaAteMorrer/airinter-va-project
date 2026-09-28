@extends('layout')
@section('title', 'Double authentification · Argos')
@section('content')
<section class="card auth-card">
    <span class="kicker">MFA</span>
    @if($enabled)
        <h1>Double authentification activée</h1>
        <p>Votre compte Argos exige actuellement un second facteur.</p>
        <form method="post" action="{{ route('account.mfa.recovery.regenerate') }}">
            @csrf
            <button class="button">Régénérer les codes de récupération</button>
        </form>
        <form method="post" action="{{ route('account.mfa.destroy') }}">
            @csrf @method('DELETE')
            <button class="button">Désactiver le MFA</button>
        </form>
    @else
        <h1>Activer le MFA</h1>
        <p>Ajoutez ce secret dans 2FAS, Aegis, Google Authenticator ou une application compatible TOTP.</p>
        <p class="subject">{{ $secret }}</p>
        <p><a href="{{ $provisioningUri }}">Ouvrir dans une application compatible</a></p>
        <form method="post" action="{{ route('account.mfa.confirm') }}">
            @csrf
            <label>Code à 6 chiffres
                <input name="code" inputmode="numeric" autocomplete="one-time-code" required>
            </label>
            @error('code')<p class="error">{{ $message }}</p>@enderror
            <button class="button primary">Activer</button>
        </form>
    @endif
</section>
@endsection
