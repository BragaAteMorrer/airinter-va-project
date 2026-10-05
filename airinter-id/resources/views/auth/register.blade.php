@extends('layout')
@section('title', 'Rejoindre Air Inter · Argos')
@section('content')
<section class="auth-stage">
    <div class="auth-story">
        <span class="eyebrow">ARGOS · AIR INTER IDENTITY</span>
        <h1>Créer votre identité <em>Air Inter.</em></h1>
        <p>Un seul compte pour Prométhée, Hermès et les services Air Inter. Votre candidature pilote sera créée automatiquement dans Prométhée et restera en attente de validation du staff.</p>
        <div class="trust-row">
            <span>IDENTITÉ UNIQUE</span>
            <span>SSO OIDC</span>
            <span>PROFIL CENTRALISÉ</span>
            <span>SÉCURITÉ ARGOS</span>
        </div>
    </div>

    <article class="card auth-card">
        <div class="auth-card-head">
            <span class="product-mark">A</span>
            <div><span class="kicker">NOUVELLE IDENTITÉ</span><h2>Rejoindre Air Inter</h2></div>
        </div>

        <form method="post" action="{{ route('register.store') }}">
            @csrf

            <label>Nom affiché
                <input name="display_name" value="{{ old('display_name') }}" autocomplete="name" required maxlength="191">
            </label>
            @error('display_name')<p class="error">{{ $message }}</p>@enderror

            <label>Adresse e-mail
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="191">
            </label>
            @error('email')<p class="error">{{ $message }}</p>@enderror

            <label>Fuseau horaire
                <input name="timezone" value="{{ old('timezone', 'Europe/Paris') }}" required autocomplete="off">
            </label>
            @error('timezone')<p class="error">{{ $message }}</p>@enderror

            <div class="form-row registration-identifiers">
                <label>VATSIM
                    <input name="vatsim_id" value="{{ old('vatsim_id') }}" inputmode="numeric" maxlength="32">
                </label>
                <label>IVAO
                    <input name="ivao_id" value="{{ old('ivao_id') }}" inputmode="numeric" maxlength="32">
                </label>
            </div>
            @error('vatsim_id')<p class="error">{{ $message }}</p>@enderror
            @error('ivao_id')<p class="error">{{ $message }}</p>@enderror

            <label>Mot de passe
                <input type="password" name="password" autocomplete="new-password" required minlength="10">
            </label>
            @error('password')<p class="error">{{ $message }}</p>@enderror

            <label>Confirmation
                <input type="password" name="password_confirmation" autocomplete="new-password" required minlength="10">
            </label>

            <label class="check">
                <input type="checkbox" name="toc_accepted" value="1" @checked(old('toc_accepted')) required>
                <span>J’accepte les conditions et règles Air Inter VA.</span>
            </label>
            @error('toc_accepted')<p class="error">{{ $message }}</p>@enderror

            <label class="check">
                <input type="hidden" name="opt_in" value="0">
                <input type="checkbox" name="opt_in" value="1" @checked(old('opt_in'))>
                <span>J’accepte de recevoir les communications non essentielles de la compagnie.</span>
            </label>

            <div class="registration-staff-note">
                <strong>Affectations opérationnelles</strong>
                <span>La base d’attache, le pays opérationnel, le grade et les qualifications seront attribués par le staff dans Prométhée. Ils ne sont pas choisis ici.</span>
            </div>

            <button class="button primary wide">CRÉER MON IDENTITÉ ARGOS</button>
        </form>

        <p class="privacy-note">Déjà pilote ? <a href="{{ route('login') }}">Connectez-vous à Argos</a>.</p>
    </article>
</section>
@endsection
