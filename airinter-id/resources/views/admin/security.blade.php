@extends('layout')
@section('title', 'Centre de sécurité · Argos')
@section('content')
<section class="admin-hero">
    <div><span class="eyebrow">ARGOS SECURITY OPERATIONS</span><h1>Centre de sécurité</h1><p>Recherche de comptes, supervision des signaux et révocation des contextes d’accès.</p></div>
    <span class="badge ok">ADMIN</span>
</section>

<section class="security-stats">
    <div><strong>{{ $stats['users'] }}</strong><span>comptes Argos</span></div>
    <div><strong>{{ $stats['events_24h'] }}</strong><span>événements / 24 h</span></div>
    <div><strong>{{ $stats['high_24h'] }}</strong><span>risque élevé / 24 h</span></div>
    <div><strong>{{ $stats['mfa_users'] }}</strong><span>comptes avec MFA</span></div>
    <div><strong>{{ $stats['passkey_users'] }}</strong><span>comptes avec passkey</span></div>
    <div><strong>{{ $stats['trusted_devices'] }}</strong><span>appareils de confiance</span></div>
</section>

<section class="card admin-search">
    <form method="get" action="{{ route('admin.security') }}">
        <label>Rechercher un pilote, un e-mail, un SUB, une IP ou un événement
            <input name="q" value="{{ $query }}" placeholder="IT199, nom, e-mail, UUID, adresse IP…">
        </label>
        <label>Sévérité
            <select name="severity">
                <option value="">Toutes</option>
                <option value="medium" @selected($severity === 'medium')>Medium</option>
                <option value="high" @selected($severity === 'high')>High</option>
            </select>
        </label>
        <button class="button primary">Rechercher</button>
    </form>
</section>

@if($query !== '')
<section class="section-block">
    <div class="section-head"><div><span class="kicker">COMPTES</span><h2>Résultats utilisateurs</h2></div></div>
    <div class="admin-user-grid">
        @forelse($users as $candidate)
            <article class="card user-result">
                <div><h3>{{ $candidate->display_name }}</h3><p>{{ $candidate->email }}</p><small class="mono">{{ $candidate->subject }}</small></div>
                <div class="mini-stats"><span>{{ $candidate->passkeys_count }} passkey(s)</span><span>{{ $candidate->trusted_devices_count }} appareil(s)</span><span>{{ $candidate->security_events_count }} événements</span></div>
                <form method="post" action="{{ route('admin.security.users.revoke', $candidate) }}">@csrf<button class="button danger">Révoquer tous les contextes</button></form>
            </article>
        @empty
            <div class="empty-state">Aucun compte correspondant.</div>
        @endforelse
    </div>
</section>
@endif

<section class="card section-block">
    <div class="section-head small"><div><span class="kicker">AUDIT</span><h2>Événements sensibles récents</h2></div></div>
    <div class="event-table">
        @forelse($events as $event)
            <div class="event-row">
                <span class="severity-pill {{ $event->severity }}">{{ strtoupper($event->severity ?: 'INFO') }}</span>
                <div><strong>{{ $event->type }}</strong><small>{{ $event->created_at->format('d/m/Y H:i') }} @if($event->ip_address) · {{ $event->ip_address }} @endif</small></div>
                <div>@if($event->user)<strong>{{ $event->user->display_name }}</strong><small>{{ $event->user->email }}</small>@else<span class="muted">Système</span>@endif</div>
                @if($event->user)<form method="post" action="{{ route('admin.security.users.revoke', $event->user) }}">@csrf<button class="button danger small">Révoquer</button></form>@endif
            </div>
        @empty
            <div class="empty-state">Aucun événement sensible récent.</div>
        @endforelse
    </div>
</section>
@endsection
