@extends('auth.onboarding_layout')
@section('title', 'Candidature non retenue')
@section('hero-title', 'Votre dossier a été examiné.')
@section('hero-copy', 'La Direction de l’exploitation vous informe du statut de votre candidature pilote Air Inter.')

@section('content')
  <div class="airinter-status">
    <div class="airinter-status-icon">×</div>
    <span class="airinter-kicker" style="color:#e8384f">DOSSIER CLÔTURÉ</span>
    <h2>Candidature non retenue</h2>
    <p>Votre candidature pilote n’a pas été retenue à ce stade. Si vous souhaitez obtenir davantage d’informations, contactez l’équipe Air Inter VA en indiquant l’adresse e-mail utilisée lors de l’inscription.</p>
    <div class="airinter-status-steps">
      <div class="airinter-status-step"><b>1</b><span>Dossier examiné par l’équipe</span></div>
      <div class="airinter-status-step"><b>2</b><span>Accès Prométhée non ouvert</span></div>
      <div class="airinter-status-step"><b>3</b><span>Contact possible avec l’administration</span></div>
    </div>
    <a class="airinter-login-link" href="{{ url('/') }}">Retour au site Air Inter</a>
  </div>
@endsection
