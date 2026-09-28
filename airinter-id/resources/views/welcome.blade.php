@extends('layout')
@section('title', 'Argos · Air Inter Identity')
@section('content')
<section class="hero hero-premium">
    <div class="hero-copy">
        <span class="eyebrow">AIR INTER IDENTITY SERVICES</span>
        <h1>Un compte.<br><em>Toute la compagnie.</em></h1>
        <p>Argos protège votre identité pilote et relie les services Air Inter VA sans déplacer votre historique opérationnel.</p>
        <div class="hero-actions">
            @auth
                <a class="button primary" href="{{ route('account') }}">Ouvrir mon espace</a>
            @else
                <a class="button primary" href="{{ route('login') }}">Se connecter</a>
            @endauth
            <a class="button ghost" href="{{ config('airinter-id.public_url') }}">Retour au site Air Inter VA</a>
        </div>
        <div class="trust-row light">
            <span>Passkeys</span><span>MFA</span><span>OpenID Connect</span><span>PKCE S256</span>
        </div>
    </div>
    <div class="systems">
        <article><span>01</span><strong>Air Inter VA</strong><small>Communauté & espace membre</small><b>WEB</b></article>
        <article><span>02</span><strong>Prométhée</strong><small>Opérations & carrière pilote</small><b>OPS</b></article>
        <article><span>03</span><strong>Hermès</strong><small>Poste équipage & ACARS</small><b>DESKTOP</b></article>
    </div>
</section>

<section class="feature-grid">
    <article class="feature-card"><span>IDENTITÉ</span><h3>Un identifiant stable</h3><p>Votre UUID Argos reste la référence entre les applications, indépendamment de votre adresse e-mail.</p></article>
    <article class="feature-card"><span>SÉCURITÉ</span><h3>Contrôle de vos accès</h3><p>Sessions, passkeys, MFA, appareils de confiance et applications autorisées au même endroit.</p></article>
    <article class="feature-card"><span>CONFIDENTIALITÉ</span><h3>Le minimum nécessaire</h3><p>Chaque service reçoit uniquement les informations couvertes par les scopes que vous autorisez.</p></article>
</section>
@endsection