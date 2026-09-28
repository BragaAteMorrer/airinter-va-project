@extends('layout')
@section('title', 'Sécurité · Argos')
@section('content')
<section class="card">
    <span class="kicker">ARGOS SECURITY</span>
    <h1>Centre de sécurité</h1>
    <div class="security-stats">
        <div><strong>{{ $stats['events_24h'] }}</strong><span>événements / 24 h</span></div>
        <div><strong>{{ $stats['high_24h'] }}</strong><span>risque élevé / 24 h</span></div>
        <div><strong>{{ $stats['trusted_devices'] }}</strong><span>appareils de confiance</span></div>
        <div><strong>{{ $stats['mfa_users'] }}</strong><span>comptes avec MFA</span></div>
        <div><strong>{{ $stats['passkey_users'] }}</strong><span>comptes avec passkey</span></div>
    </div>
</section>

<section class="card" style="margin-top:24px">
    <span class="kicker">AUDIT</span>
    <h2>Événements sensibles récents</h2>
    @forelse($events as $event)
        <div class="identity">
            <strong>{{ $event->type }}</strong>
            <span>{{ strtoupper($event->severity) }} · score {{ $event->risk_score }}</span>
            <small>
                {{ $event->created_at->format('d/m/Y H:i') }}
                @if($event->user) · {{ $event->user->display_name }} · {{ $event->user->email }} @endif
                @if($event->ip_address) · {{ $event->ip_address }} @endif
            </small>
            @if($event->user)
                <form method="post" action="{{ route('admin.security.users.revoke', $event->user) }}">
                    @csrf
                    <button class="button">Révoquer tous les contextes</button>
                </form>
            @endif
        </div>
    @empty
        <p>Aucun événement sensible récent.</p>
    @endforelse
</section>
@endsection
