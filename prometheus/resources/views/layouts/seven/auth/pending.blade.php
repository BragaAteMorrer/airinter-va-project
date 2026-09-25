@extends('auth.onboarding_layout')
@section('title', 'Candidature en attente')
@section('hero-title', 'Votre dossier est arrivé.')
@section('hero-copy', 'L’équipe Air Inter VA vérifie votre candidature avant l’ouverture complète de votre accès pilote.')

@section('content')
  <div class="airinter-status">
    <div class="airinter-status-icon">✓</div>
    <span class="airinter-kicker" style="color:#1765e9">CANDIDATURE TRANSMISE</span>
    <h2>En attente de validation</h2>
    <p>Votre compte a bien été créé. Une validation de l’équipe Air Inter est encore nécessaire avant votre première connexion opérationnelle.</p>
    <div class="airinter-status-steps">
      <div class="airinter-status-step"><b>1</b><span>Dossier reçu par Prométhée</span></div>
      <div class="airinter-status-step"><b>2</b><span>Contrôle du profil pilote par l’équipe</span></div>
      <div class="airinter-status-step"><b>3</b><span>Notification par e-mail dès que l’accès est ouvert</span></div>
    </div>
    <p>Vous n’avez rien d’autre à faire pour le moment. Vérifiez simplement votre boîte mail, y compris les courriers indésirables.</p>
    <a class="airinter-login-link" href="{{ url('/login') }}">Retour à la connexion</a>
  </div>
@endsection
