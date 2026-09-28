@extends('layout')
@section('title', 'Nouveau mot de passe · Argos')
@section('content')
<section class="card auth-card">
    <span class="kicker">RÉCUPÉRATION</span>
    <h1>Nouveau mot de passe</h1>
    <form method="post" action="{{ route('password.update.reset') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label>E-mail
            <input type="email" name="email" value="{{ old('email', $email) }}" required>
        </label>
        <label>Nouveau mot de passe
            <input type="password" name="password" autocomplete="new-password" required>
        </label>
        <label>Confirmation
            <input type="password" name="password_confirmation" autocomplete="new-password" required>
        </label>
        @error('email')<p class="error">{{ $message }}</p>@enderror
        @error('password')<p class="error">{{ $message }}</p>@enderror
        <button class="button primary">Réinitialiser</button>
    </form>
</section>
@endsection
