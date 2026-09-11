<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ config('app.name') }} · Connexion</title>
    <script>try{document.documentElement.dataset.loginEra=localStorage.getItem('promethee-era')||'modern'}catch(e){document.documentElement.dataset.loginEra='modern'}</script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ public_asset('/promethee-assets/login.css') }}">
</head>
<body class="promethee-auth">
<main class="login-shell">
    <header class="login-topbar"><span class="login-wordmark">AIR INTER · PROMÉTHÉE</span><label class="login-era">ÉCRAN <select id="login-era" aria-label="Style d'affichage"><option value="modern">Moderne</option><option value="2000">Années 2000</option><option value="minitel">Minitel</option></select></label></header>
    <div class="login-layout">
        <aside class="login-intro"><div><small>CENTRE D'EXPLOITATION AÉRIENNE</small><h1>Bienvenue à bord.</h1><p>Accédez au réseau Air Inter et préparez votre prochaine rotation.</p></div><div class="login-route"><b>AI</b><span>Pourquoi vivre sans ailes !</span></div></aside>
        <section class="login-panel">@include('flash.message')@yield('content')</section>
    </div>
    <footer class="login-footer">AIR INTER · PROMÉTHÉE · RÉSEAU INTÉRIEUR FRANÇAIS</footer>
</main>
<script src="{{ public_asset('/promethee-assets/login.js') }}" defer></script>
@yield('scripts')
</body>
</html>
