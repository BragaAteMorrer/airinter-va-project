@extends('layout')
@section('title', 'Autorisation · Argos')
@section('content')
<section class="consent-stage">
    <section class="card consent-card">
        <div class="consent-app">
            <span class="product-mark">A</span>
            <span class="consent-arrow">→</span>
            <span class="product-mark app">{{ strtoupper(substr($client->name, 0, 1)) }}</span>
        </div>
        <span class="kicker">AUTORISATION ARGOS</span>
        <h1>{{ $client->name }} souhaite accéder à votre compte</h1>
        <p class="muted">Vous êtes connecté en tant que <strong>{{ $user->display_name }}</strong>.</p>

        @if(count($scopes))
            <div class="scope-panel">
                <strong>Cette application demande :</strong>
                <ul class="scope-list">
                    @foreach($scopes as $scope)
                        <li><span>✓</span>{{ $scope->description }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="privacy-note">Votre mot de passe Argos n’est jamais transmis à {{ $client->name }}.</p>

        <div class="actions consent-actions">
            <form method="post" action="{{ route('passport.authorizations.approve', [], false) }}">
                @csrf
                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button class="button primary">Autoriser</button>
            </form>
            <form method="post" action="{{ route('passport.authorizations.deny', [], false) }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button class="button secondary">Refuser</button>
            </form>
        </div>
    </section>
</section>
@endsection