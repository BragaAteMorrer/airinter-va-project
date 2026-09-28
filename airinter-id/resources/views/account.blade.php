@extends('layout')
@section('title', 'Mon espace · Argos')
@section('content')
<section class="account-hero">
    <div>
        <span class="eyebrow">MON ESPACE ARGOS</span>
        <h1>{{ $user->display_name }}</h1>
        <p class="muted">Identité Air Inter · <span class="mono">{{ $user->subject }}</span></p>
    </div>
    <div class="security-meter" aria-label="Niveau de sécurité {{ $securityScore }}%">
        <div class="security-ring" style="--score:{{ $securityScore }}"><strong>{{ $securityScore }}</strong><span>/100</span></div>
        <div><small>SÉCURITÉ DU COMPTE</small><strong>{{ $securityLevel }}</strong><span>{{ $user->two_factor_confirmed_at ? 'MFA actif' : 'MFA à activer' }}</span></div>
    </div>
</section>

<nav class="account-tabs" aria-label="Sections du compte">
    <a href="#overview">Vue d’ensemble</a>
    <a href="#apps">Applications</a>
    <a href="#security">Sécurité</a>
    <a href="#activity">Activité</a>
    <a href="#preferences">Préférences</a>
</nav>

<section id="overview" class="account-grid">
    <article class="card profile-card">
        <span class="kicker">IDENTITÉ</span>
        <h2>Profil Argos</h2>
        <dl>
            <div><dt>E-mail</dt><dd>{{ $user->email }} <span class="status-dot {{ $user->hasVerifiedEmail() ? 'ok' : 'warn' }}"></span></dd></div>
            <div><dt>État</dt><dd><span class="badge">{{ strtoupper($user->state) }}</span></dd></div>
            <div><dt>Dernière connexion</dt><dd>{{ $user->last_login_at?->diffForHumans() ?: 'Non renseignée' }}</dd></div>
            <div><dt>Langue</dt><dd>{{ strtoupper($user->preferred_locale) }}</dd></div>
            <div><dt>Fuseau</dt><dd>{{ $user->timezone }}</dd></div>
        </dl>
        @unless($user->hasVerifiedEmail())
            <form method="post" action="{{ route('verification.send') }}">@csrf<button class="button secondary">Vérifier mon e-mail</button></form>
        @endunless
    </article>

    <article class="card">
        <span class="kicker">COMPTES LIÉS</span>
        <h2>Identités historiques</h2>
        @forelse($user->identities as $identity)
            <div class="identity">
                <div><strong>{{ ucfirst($identity->provider) }}</strong><span>{{ $identity->external_ident ?: '#'.$identity->external_user_id }}</span></div>
                <span class="badge ok">LIÉ</span>
                <small>Depuis {{ optional($identity->linked_at)->format('d/m/Y') ?: 'l’import' }}</small>
            </div>
        @empty
            <div class="empty-state">Aucune identité historique liée.</div>
        @endforelse
    </article>
</section>

<section id="apps" class="section-block">
    <div class="section-head"><div><span class="kicker">ÉCOSYSTÈME</span><h2>Mes applications</h2></div><p>Les services Air Inter reliés à votre identité Argos.</p></div>
    <div class="product-grid">
        @foreach($products as $product)
            <article class="product-card">
                <div class="product-icon">{{ substr($product['name'], 0, 1) }}</div>
                <div><span class="kicker">{{ $product['code'] }}</span><h3>{{ $product['name'] }}</h3><p>{{ $product['description'] }}</p></div>
                <span class="app-status {{ $product['connected'] ? 'connected' : '' }}">{{ $product['connected'] ? 'Connecté' : 'Disponible' }}</span>
                @if($product['url'])<a href="{{ $product['url'] }}" class="text-link">Ouvrir ↗</a>@endif
            </article>
        @endforeach
    </div>

    <article class="card compact-card">
        <div class="section-head small"><div><span class="kicker">AUTORISATIONS OAUTH</span><h3>Accès accordés</h3></div></div>
        @forelse($applications as $application)
            <div class="identity">
                <div><strong>{{ $application['name'] }}</strong><span>{{ $application['active'] }} session(s) OAuth active(s)</span></div>
                @if($application['active'] > 0)
                    <form method="post" action="{{ route('account.applications.revoke', $application['client_id']) }}">@csrf @method('DELETE')<button class="button danger small">Révoquer</button></form>
                @endif
                <small>Client {{ $application['client_id'] }} @if($application['last_used_at']) · utilisé {{ IlluminateSupportCarbon::parse($application['last_used_at'])->diffForHumans() }} @endif</small>
            </div>
        @empty
            <div class="empty-state">Aucune autorisation OAuth active.</div>
        @endforelse
    </article>
</section>

<section id="security" class="section-block">
    <div class="section-head"><div><span class="kicker">PROTECTION</span><h2>Sécurité du compte</h2></div><p>Les méthodes qui protègent votre identité Air Inter.</p></div>
    <div class="security-grid">
        <article class="card security-card">
            <div class="security-icon">✦</div><div><h3>Authentification multifacteur</h3><p>{{ $user->two_factor_confirmed_at ? 'Activée sur votre compte.' : 'Ajoutez une seconde preuve lors de la connexion.' }}</p></div>
            <span class="badge {{ $user->two_factor_confirmed_at ? 'ok' : 'warn' }}">{{ $user->two_factor_confirmed_at ? 'ACTIF' : 'À ACTIVER' }}</span>
            <a class="button secondary" href="{{ route('account.mfa') }}">{{ $user->two_factor_confirmed_at ? 'Gérer' : 'Activer' }}</a>
        </article>

        <article class="card security-card">
            <div class="security-icon">⌁</div><div><h3>Passkeys</h3><p>Windows Hello, Touch ID, Face ID ou clé de sécurité.</p></div>
            <span class="badge {{ $passkeys->isNotEmpty() ? 'ok' : 'neutral' }}">{{ $passkeys->count() }} ENREGISTRÉE(S)</span>
            <button type="button" class="button secondary" data-passkey-register>Ajouter</button>
            <p class="error" data-passkey-error></p>
        </article>

        <article class="card security-card">
            <div class="security-icon">◉</div><div><h3>Appareils de confiance</h3><p>{{ $trustedDevices->count() }} appareil(s) peuvent mémoriser votre MFA.</p></div>
            <span class="badge neutral">{{ $trustedDevices->count() }} ACTIF(S)</span>
        </article>
    </div>

    <div class="account-grid">
        <article class="card">
            <span class="kicker">PASSKEYS</span><h3>Clés enregistrées</h3>
            @forelse($passkeys as $passkey)
                <div class="identity"><div><strong>{{ $passkey->name }}</strong><span>{{ $passkey->last_used_at ? 'Utilisée '.$passkey->last_used_at->diffForHumans() : 'Jamais utilisée' }}</span></div>
                    <form method="post" action="/user/passkeys/{{ $passkey->id }}">@csrf @method('DELETE')<button class="button danger small">Supprimer</button></form>
                    <small>Ajoutée {{ $passkey->created_at->format('d/m/Y H:i') }}</small>
                </div>
            @empty<div class="empty-state">Aucune passkey enregistrée.</div>@endforelse
        </article>

        <article class="card">
            <span class="kicker">APPAREILS DE CONFIANCE</span><h3>Confiance MFA</h3>
            @forelse($trustedDevices as $device)
                <div class="identity"><div><strong>{{ $device->name ?: 'Appareil' }}</strong><span>Expire {{ $device->expires_at->diffForHumans() }}</span></div>
                    <form method="post" action="{{ route('account.trusted-devices.revoke', $device) }}">@csrf @method('DELETE')<button class="button danger small">Révoquer</button></form>
                    <small>{{ $device->last_ip_address ?: 'IP inconnue' }} · {{ $device->last_used_at?->diffForHumans() ?: 'jamais utilisé' }}</small>
                </div>
            @empty<div class="empty-state">Aucun appareil de confiance actif.</div>@endforelse
        </article>
    </div>
</section>

<section id="activity" class="section-block">
    <div class="section-head"><div><span class="kicker">SESSIONS & AUDIT</span><h2>Activité récente</h2></div></div>
    <div class="account-grid">
        <article class="card">
            <span class="kicker">SESSIONS ARGOS</span><h3>Appareils connectés</h3>
            @forelse($sessions as $session)
                @php
                    $ua = strtolower((string) $session->user_agent);
                    $browser = str_contains($ua, 'edg/') ? 'Microsoft Edge' : (str_contains($ua, 'chrome/') ? 'Google Chrome' : (str_contains($ua, 'firefox/') ? 'Firefox' : (str_contains($ua, 'safari/') ? 'Safari' : 'Navigateur')));
                    $os = str_contains($ua, 'windows') ? 'Windows' : (str_contains($ua, 'mac os') ? 'macOS' : (str_contains($ua, 'android') ? 'Android' : (str_contains($ua, 'iphone') ? 'iPhone' : 'Appareil')));
                @endphp
                <div class="identity">
                    <div><strong>{{ $session->id === $currentSessionId ? 'Cet appareil' : $os.' · '.$browser }}</strong><span>{{ $session->ip_address ?: 'IP inconnue' }}</span></div>
                    @if($session->id !== $currentSessionId)<form method="post" action="{{ route('account.sessions.revoke', $session->id) }}">@csrf @method('DELETE')<button class="button danger small">Déconnecter</button></form>@else<span class="badge ok">ACTUELLE</span>@endif
                    <small>Dernière activité {{ IlluminateSupportCarbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</small>
                </div>
            @empty<div class="empty-state">Aucune session active.</div>@endforelse
            <form method="post" action="{{ route('account.sessions.revoke-others') }}">@csrf @method('DELETE')<button class="button secondary">Déconnecter les autres sessions</button></form>
        </article>

        <article class="card timeline-card">
            <span class="kicker">JOURNAL DE SÉCURITÉ</span><h3>30 derniers événements</h3>
            <div class="timeline">
                @forelse($securityEvents as $event)
                    <div class="timeline-item"><span class="timeline-dot {{ $event->severity === 'high' ? 'danger' : '' }}"></span><div><strong>{{ str_replace('.', ' · ', $event->type) }}</strong><small>{{ $event->created_at->format('d/m/Y H:i') }} @if($event->ip_address) · {{ $event->ip_address }} @endif</small></div></div>
                @empty<div class="empty-state">Aucun événement récent.</div>@endforelse
            </div>
        </article>
    </div>
</section>

<section id="preferences" class="section-block">
    <div class="account-grid">
        <article class="card">
            <span class="kicker">PRÉFÉRENCES</span><h2>Langue & fuseau</h2>
            <form method="post" action="{{ route('account.preferences.update') }}" class="form-stack">
                @csrf @method('PUT')
                <label>Langue
                    <select name="preferred_locale"><option value="fr" @selected($user->preferred_locale === 'fr')>Français</option><option value="en" @selected($user->preferred_locale === 'en')>English</option></select>
                </label>
                <label>Fuseau horaire<input name="timezone" value="{{ $user->timezone }}" required></label>
                @error('timezone')<p class="error">{{ $message }}</p>@enderror
                <button class="button primary">Enregistrer</button>
            </form>
        </article>

        <article class="card">
            <span class="kicker">MOT DE PASSE</span><h2>Modifier le secret</h2>
            <form method="post" action="{{ route('account.password.update') }}" class="form-stack">
                @csrf @method('PUT')
                <label>Nouveau mot de passe<input type="password" name="password" autocomplete="new-password" required></label>
                <label>Confirmation<input type="password" name="password_confirmation" autocomplete="new-password" required></label>
                @error('password')<p class="error">{{ $message }}</p>@enderror
                <button class="button secondary">Changer le mot de passe</button>
            </form>
        </article>
    </div>
</section>

<div class="account-footer-actions">
    <form method="post" action="{{ route('logout') }}">@csrf<button class="button ghost-dark">Déconnexion d’Argos</button></form>
</div>
@endsection