@extends('auth.onboarding_layout')
@section('title', 'Vérification de votre adresse e-mail')
@section('hero-title', 'Confirmez votre adresse e-mail.')
@section('hero-copy', 'Cette étape permet de sécuriser votre dossier pilote avant l’ouverture de votre accès Air Inter.')

@section('content')
  <div class="airinter-status">
    <div class="airinter-status-icon">@</div>
    <span class="airinter-kicker" style="color:#1765e9">VÉRIFICATION E-MAIL</span>
    <h2>Consultez votre boîte de réception</h2>

    @if (session('resent'))
      <div class="alert alert-success">Un nouveau lien de vérification vient de vous être envoyé.</div>
    @endif

    <p>Un lien de vérification a été envoyé à votre adresse e-mail. Cliquez dessus pour poursuivre votre inscription Air Inter.</p>
    <p>Vous n’avez rien reçu ? Vérifiez les courriers indésirables ou demandez un nouvel envoi.</p>

    <form method="POST" action="{{ route('verification.resend') }}">
      @csrf
      <button type="submit" class="airinter-submit">RENVOYER LE LIEN DE VÉRIFICATION</button>
    </form>
  </div>
@endsection
