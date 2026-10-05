@extends('layout')
@section('title', 'Journal du compte · Argos')
@section('content')
<section class="account-hero">
    <div>
        <span class="eyebrow">ARGOS · AUDIT D’IDENTITÉ</span>
        <h1>Journal du compte Air Inter</h1>
        <p class="muted">Connexions, autorisations, changements de sécurité et actions sensibles de tout l’écosystème.</p>
    </div>
    <a class="button secondary" href="{{ route('account') }}#activity">← Mon espace</a>
</section>

@if(session('status'))
    <div class="card" role="status"><strong>{{ session('status') }}</strong></div>
@endif

<section class="section-block">
    <div class="section-head">
        <div><span class="kicker">HISTORIQUE GLOBAL</span><h2>{{ $events->total() }} événement(s)</h2></div>
        <p>Une activité inconnue peut être signalée. Argos coupera alors les autres sessions, accès OAuth et appareils de confiance.</p>
    </div>

    <article class="card timeline-card">
        <div class="timeline">
            @forelse($events as $event)
                @php($metadata = (array) $event->metadata)
                <div class="timeline-item">
                    <span class="timeline-dot {{ $event->reported_at || $event->severity === 'high' ? 'danger' : '' }}"></span>
                    <div style="width:100%">
                        <div style="display:flex;gap:.75rem;justify-content:space-between;align-items:flex-start;flex-wrap:wrap">
                            <div>
                                <strong>{{ $event->display_label }}</strong>
                                <div><span class="badge neutral">{{ $event->application_name }}</span>
                                    @if($event->reported_at)<span class="badge warn">SIGNALÉ</span>@endif
                                </div>
                            </div>
                            @unless($event->reported_at || $event->type === 'account.incident.reported')
                                <form method="post" action="{{ route('account.activity.report', $event) }}" onsubmit="return confirm('Confirmer que cette activité n’était pas la vôtre ? Argos révoquera immédiatement les autres sessions et accès OAuth.');">
                                    @csrf
                                    <button class="button danger small">Ce n’était pas moi</button>
                                </form>
                            @endunless
                        </div>
                        <small>
                            {{ $event->created_at->format('d/m/Y H:i:s') }}
                            @if($event->ip_address) · {{ $event->ip_address }} @endif
                            @if(isset($metadata['risk_level'])) · risque {{ $metadata['risk_level'] }} @endif
                        </small>
                    </div>
                </div>
            @empty
                <div class="empty-state">Aucune activité enregistrée.</div>
            @endforelse
        </div>
    </article>

    <div style="margin-top:1rem">{{ $events->links() }}</div>
</section>
@endsection
