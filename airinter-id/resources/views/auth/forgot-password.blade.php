@extends('layout')
@section('title', 'Mot de passe oublié · Argos')
@section('content')
<section class="card auth-card">
    <span class="kicker">RÉCUPÉRATION</span>
    <h1>Mot de passe oublié</h1>
    <p>Entrez l’adresse e-mail de votre compte Argos.</p>
    <form method="post" action="{{ route('password.email') }}">
        @csrf
        <label>E-mail
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
        </label>
        @error('email')<p class="error">{{ $message }}</p>@enderror
        <button class="button primary">Envoyer le lien</button>
    </form>
</section>
@endsection
