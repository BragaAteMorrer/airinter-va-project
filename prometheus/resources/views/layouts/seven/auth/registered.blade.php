@extends('auth.onboarding_layout')
@section('title', 'Bienvenue chez Air Inter')
@section('hero-title', 'Bienvenue chez Air Inter.')
@section('hero-copy', 'Votre accès pilote est désormais ouvert. Prométhée et Hermès peuvent maintenant vous accompagner de la préparation au débriefing.')

@section('content')
  <div class="airinter-status">
    <div class="airinter-status-icon">→</div>
    <span class="airinter-kicker" style="color:#1765e9">ACCÈS PILOTE OUVERT</span>
    <h2>Votre compte est prêt</h2>
    <p>Votre inscription Air Inter VA est confirmée. Connectez-vous à Prométhée pour choisir votre vol, préparer votre OFP et utiliser Hermès.</p>
    <div class="airinter-status-steps">
      <div class="airinter-status-step"><b>1</b><span>Connexion Prométhée</span></div>
      <div class="airinter-status-step"><b>2</b><span>Réservation d’un vol Air Inter</span></div>
      <div class="airinter-status-step"><b>3</b><span>Préparation OFP puis suivi avec Hermès</span></div>
    </div>
    <a class="airinter-login-link" href="{{ url('/login') }}">Se connecter à Prométhée</a>
  </div>
@endsection
