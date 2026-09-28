@extends('layout')
@section('title', 'Mon compte · Argos')
@section('content')
<section class="account-grid">
    <article class="card">
        <span class="kicker">ARGOS</span>
        <h1>{{ $user->display_name }}</h1>
        <p class="subject">SUB · {{ $user->subject }}</p>
        <dl>
            <div><dt>E-mail</dt><dd>{{ $user->email }} · {{ $user->hasVerifiedEmail() ? 'vérifié' : 'non vérifié' }}</dd></div>
            <div><dt>État</dt><dd>{{ strtoupper($user->state) }}</dd></div>
            <div><dt>Langue</dt><dd>{{ $user->preferred_locale }}</dd></div>
            <div><dt>Fuseau</dt><dd>{{ $user->timezone }}</dd></div>
        </dl>
        <form method="post" action="{{ route('logout') }}">@csrf<button class="button">Déconnexion</button></form>
    </article>
    <article class="card">
        <span class="kicker">COMPTES LIÉS</span>
        <h2>Votre galaxie Air Inter</h2>
        @forelse($user->identities as $identity)
            <div class="identity">
                <strong>{{ ucfirst($identity->provider) }}</strong>
                <span>{{ $identity->external_ident ?: '#'.$identity->external_user_id }}</span>
                <small>lié {{ optional($identity->linked_at)->format('d/m/Y') }}</small>
            </div>
        @empty
            <p>Aucun système historique n'est encore lié.</p>
        @endforelse
    </article>
    <article class="card">
        <span class="kicker">APPLICATIONS AUTORISÉES</span>
        <h2>Accès OAuth</h2>
        @forelse($applications as $application)
            <div class="identity">
                <strong>{{ $application['name'] }}</strong>
                <span>{{ $application['active'] }} session(s) active(s)</span>
                <small>Client {{ $application['client_id'] }}</small>
                @if($application['active'] > 0)
                    <form method="post" action="{{ route('account.applications.revoke', $application['client_id']) }}">
                        @csrf @method('DELETE')
                        <button class="button">Révoquer l'accès</button>
                    </form>
                @endif
            </div>
        @empty
            <p>Aucune application Argos autorisée.</p>
        @endforelse
    </article>

    <article class="card">
        <span class="kicker">SESSIONS ARGOS</span>
        <h2>Appareils connectés</h2>
        @forelse($sessions as $session)
            <div class="identity">
                <strong>{{ $session->id === $currentSessionId ? 'Session actuelle' : 'Session web' }}</strong>
                <span>{{ $session->ip_address ?: 'IP inconnue' }}</span>
                <small>{{ \Illuminate\Support\Str::limit($session->user_agent ?: 'Navigateur inconnu', 90) }}</small>
                @if($session->id !== $currentSessionId)
                    <form method="post" action="{{ route('account.sessions.revoke', $session->id) }}">
                        @csrf @method('DELETE')
                        <button class="button">Déconnecter</button>
                    </form>
                @endif
            </div>
        @empty
            <p>Aucune session active.</p>
        @endforelse

        <form method="post" action="{{ route('account.sessions.revoke-others') }}">
            @csrf @method('DELETE')
            <button class="button">Déconnecter toutes les autres sessions</button>
        </form>
    </article>
    <article class="card">
        <span class="kicker">SÉCURITÉ DU COMPTE</span>
        <h2>Mot de passe & MFA</h2>

        @unless($user->hasVerifiedEmail())
            <p>Votre adresse e-mail n’est pas encore vérifiée.</p>
            <form method="post" action="{{ route('verification.send') }}">
                @csrf
                <button class="button">Envoyer le lien de vérification</button>
            </form>
        @endunless

        <p>
            Double authentification :
            <strong>{{ $user->two_factor_confirmed_at ? 'activée' : 'désactivée' }}</strong>
        </p>
        <a class="button" href="{{ route('account.mfa') }}">
            {{ $user->two_factor_confirmed_at ? 'Gérer le MFA' : 'Activer le MFA' }}
        </a>

        <form method="post" action="{{ route('account.password.update') }}" style="margin-top:20px">
            @csrf @method('PUT')
            <label>Nouveau mot de passe
                <input type="password" name="password" autocomplete="new-password" required>
            </label>
            <label>Confirmation
                <input type="password" name="password_confirmation" autocomplete="new-password" required>
            </label>
            @error('password')<p class="error">{{ $message }}</p>@enderror
            <button class="button">Changer le mot de passe</button>
        </form>
    </article>
    <article class="card">
        <span class="kicker">PASSKEYS</span>
        <h2>Clés d’accès</h2>
        <p>Utilisez Windows Hello, Touch ID, Face ID, votre téléphone ou une clé de sécurité compatible WebAuthn.</p>
        <button type="button" class="button primary" data-passkey-register>Ajouter une passkey</button>
        <p class="error" data-passkey-error></p>

        @forelse($passkeys as $passkey)
            <div class="identity">
                <strong>{{ $passkey->name }}</strong>
                <span>{{ $passkey->last_used_at ? 'utilisée '.$passkey->last_used_at->diffForHumans() : 'jamais utilisée' }}</span>
                <small>Ajoutée {{ $passkey->created_at->format('d/m/Y H:i') }}</small>
                <form method="post" action="/user/passkeys/{{ $passkey->id }}">
                    @csrf @method('DELETE')
                    <button class="button">Supprimer</button>
                </form>
            </div>
        @empty
            <p>Aucune passkey enregistrée.</p>
        @endforelse
    </article>

    <article class="card">
        <span class="kicker">HISTORIQUE DE SÉCURITÉ</span>
        <h2>Activité récente</h2>
        @forelse($securityEvents as $event)
            @php
                $highRisk = in_array($event->type, [
                    'password.changed',
                    'mfa.disabled',
                    'passkey.deleted',
                    'oauth.refresh.reuse_detected',
                    'login.new_context',
                ], true);
            @endphp
            <div class="identity">
                <strong>{{ $event->type }}</strong>
                <span>{{ $highRisk ? 'Sensible' : 'Information' }}</span>
                <small>
                    {{ $event->created_at->format('d/m/Y H:i') }}
                    @if($event->ip_address) · {{ $event->ip_address }} @endif
                </small>
            </div>
        @empty
            <p>Aucun événement de sécurité récent.</p>
        @endforelse
    </article>
</section>
@endsection
