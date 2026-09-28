@extends('layout')
@section('title', 'Vérifier l’e-mail · Argos')
@section('content')
<section class="card auth-card">
    <span class="kicker">E-MAIL</span>
    <h1>Vérifiez votre adresse e-mail</h1>
    <p>Argos doit confirmer que cette adresse vous appartient.</p>
    <form method="post" action="{{ route('verification.send') }}">
        @csrf
        <button class="button primary">Renvoyer le lien</button>
    </form>
</section>
@endsection
