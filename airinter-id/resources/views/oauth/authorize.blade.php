@extends('layout')
@section('title', 'Autorisation · Air Inter ID')
@section('content')
<section class="card auth-card">
    <span class="kicker">AUTORISATION</span>
    <h1>{{ $client->name }} souhaite accéder à Air Inter ID</h1>
    <p>Connecté en tant que <strong>{{ $user->display_name }}</strong>.</p>

    @if(count($scopes))
        <ul class="scope-list">
            @foreach($scopes as $scope)
                <li>{{ $scope->description }}</li>
            @endforeach
        </ul>
    @endif

    <div class="actions">
        <form method="post" action="{{ route('passport.authorizations.approve') }}">
            @csrf
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button class="button primary">Autoriser</button>
        </form>
        <form method="post" action="{{ route('passport.authorizations.deny') }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button class="button">Refuser</button>
        </form>
    </div>
</section>
@endsection
