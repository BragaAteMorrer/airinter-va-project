@extends('layout')
@section('title', 'Air Inter ID')
@section('content')
<section class="hero">
    <div>
        <span class="kicker">IDENTITÉ AIR INTER</span>
        <h1>Un compte.<br>Toute la compagnie.</h1>
        <p>Air Inter ID unifie progressivement le site Air Inter VA, Prométhée et Hermès sans déplacer votre historique de pilote.</p>
        @auth
            <a class="button primary" href="{{ route('account') }}">Ouvrir mon compte</a>
        @else
            <a class="button primary" href="{{ route('login') }}">Se connecter</a>
        @endauth
    </div>
    <div class="systems">
        <article><span>01</span><strong>Air Inter VA</strong><small>Patrimoine & communauté</small></article>
        <article><span>02</span><strong>Prométhée</strong><small>Opérations & carrière</small></article>
        <article><span>03</span><strong>Hermès</strong><small>Poste équipage & ACARS</small></article>
    </div>
</section>
@endsection
