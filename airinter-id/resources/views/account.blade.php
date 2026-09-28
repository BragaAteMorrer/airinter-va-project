@extends('layout')
@section('title', 'Mon compte · Argos')
@section('content')
<section class="account-grid">
    <article class="card">
        <span class="kicker">ARGOS</span>
        <h1>{{ $user->display_name }}</h1>
        <p class="subject">SUB · {{ $user->subject }}</p>
        <dl>
            <div><dt>E-mail</dt><dd>{{ $user->email }}</dd></div>
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
</section>
@endsection
