@extends('auth.onboarding_layout')
@section('title', 'Accès pilote suspendu')
@section('hero-title', 'Votre accès est temporairement suspendu.')
@section('hero-copy', 'Prométhée conserve votre dossier pilote, mais l’accès opérationnel nécessite une régularisation auprès de l’équipe Air Inter.')

@section('content')
  <div class="airinter-status">
    <div class="airinter-status-icon">!</div>
    <span class="airinter-kicker" style="color:#e8384f">ACCÈS SUSPENDU</span>
    <h2>Compte temporairement indisponible</h2>
    <p>Votre compte existe toujours, mais la connexion opérationnelle est suspendue. Contactez l’administration Air Inter VA pour connaître la marche à suivre.</p>
    <div class="airinter-status-steps">
      <div class="airinter-status-step"><b>1</b><span>Dossier pilote conservé</span></div>
      <div class="airinter-status-step"><b>2</b><span>Accès Prométhée et Hermès suspendu</span></div>
      <div class="airinter-status-step"><b>3</b><span>Réactivation par l’administration</span></div>
    </div>
    <a class="airinter-login-link" href="{{ url('/login') }}">Retour à la connexion</a>
  </div>
@endsection
