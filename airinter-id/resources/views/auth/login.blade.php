@extends('layout')
@section('title', 'Connexion · Argos')
@section('content')
<section class="auth-stage">
    <div class="auth-story">
        <span class="eyebrow">AIR INTER IDENTITY</span>
        <h1>Votre identité.<br><em>Partout dans la compagnie.</em></h1>
        <p>Argos vous connecte à Prométhée, Hermès et aux services Air Inter VA avec un compte unique et sécurisé.</p>
        <div class="trust-row">
            <span>OIDC + PKCE</span>
            <span>Passkeys</span>
            <span>MFA</span>
        </div>
    </div>

    <section class="card auth-card">
        <div class="auth-card-head">
            <span class="product-mark">A</span>
            <div>
                <span class="kicker">ARGOS</span>
                <h2>Connexion équipage</h2>
            </div>
        </div>
        <p class="muted">Utilisez votre e-mail de pilote Air Inter.</p>

        <form method="post" action="{{ route('login') }}">
            @csrf
            <label>E-mail
                <input name="login" value="{{ old('login') }}" autocomplete="username webauthn" required autofocus placeholder="IT199 ou pilote@airinter-va.org">
            </label>
            @error('login')<p class="error">{{ $message }}</p>@enderror

            <label>Mot de passe
                <input name="password" type="password" autocomplete="current-password" required placeholder="Votre mot de passe">
            </label>

            <div class="form-row">
                <label class="check"><input type="checkbox" name="remember" value="1"> Rester connecté</label>
                <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
            </div>

            <button class="button primary wide">Se connecter</button>
        </form>

        <div class="divider"><span>ou</span></div>

        <button type="button" class="button secondary wide passkey-button" data-passkey-login>
            <span>⌁</span> Utiliser une passkey
        </button>
        <p class="error" data-passkey-error></p>
        <p class="privacy-note">Argos ne partage que les informations nécessaires à l’application demandée.</p>
    </section>
</section>
@endsection
