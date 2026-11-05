@extends('layout')
@section('title', 'Double authentification · Argos')
@section('content')
<section class="card auth-card">
    <span class="kicker">MFA</span>
    @if($enabled)
        <h1>Double authentification activée</h1>
        <p>Votre compte Argos exige actuellement un second facteur.</p>
        <form method="post" action="{{ route('account.mfa.recovery.regenerate') }}" onsubmit="return confirm('Générer de nouveaux codes ? Tous les anciens codes de secours seront invalidés.');">
            @csrf
            <button class="button">Générer de nouveaux codes de secours</button>
        </form>
        <form method="post" action="{{ route('account.mfa.destroy') }}">
            @csrf @method('DELETE')
            <button class="button">Désactiver le MFA</button>
        </form>
    @else
        <h1>Activer le MFA</h1>
        <p>Scannez ce QR code avec 2FAS, Aegis, Google Authenticator ou une application compatible TOTP. Le QR est généré localement, sans transmission de votre secret à un service externe.</p>
        <div id="argos-totp-qr" data-totp-uri="{{ $provisioningUri }}" role="img" aria-label="QR code à scanner pour activer la double authentification" style="padding:12px;background:#fff;display:inline-block;border-radius:12px;min-width:220px;min-height:220px"></div>
        <p class="muted" id="argos-totp-qr-help">Si le QR code ne s'affiche pas, recopiez la clé manuellement :</p>
        <p class="subject" id="argos-totp-secret">{{ $secret }}</p>
        <button type="button" class="button secondary" data-copy-totp-secret>Copier la clé secrète</button>
        <p><a href="{{ $provisioningUri }}">Ouvrir dans une application compatible</a></p>
        <form method="post" action="{{ route('account.mfa.confirm') }}">
            @csrf
            <label>Code à 6 chiffres
                <input name="code" inputmode="numeric" autocomplete="one-time-code" required>
            </label>
            @error('code')<p class="error">{{ $message }}</p>@enderror
            <button class="button primary">Activer</button>
        </form>
    @endif
</section>
@endsection
