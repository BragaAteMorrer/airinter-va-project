@extends('layout')
@section('title', 'Connexion · Air Inter ID')
@section('content')
<section class="card auth-card">
    <span class="kicker">AIR INTER ID</span>
    <h1>Connexion équipage</h1>
    <p>Utilisez votre adresse e-mail ou votre identifiant pilote Air Inter existant.</p>
    <form method="post" action="{{ route('login') }}">
        @csrf
        <label>Identifiant pilote ou e-mail
            <input name="login" value="{{ old('login') }}" autocomplete="username" required autofocus>
        </label>
        @error('login')<p class="error">{{ $message }}</p>@enderror
        <label>Mot de passe
            <input name="password" type="password" autocomplete="current-password" required>
        </label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Rester connecté</label>
        <button class="button primary">Se connecter avec Air Inter ID</button>
    </form>
</section>
@endsection
