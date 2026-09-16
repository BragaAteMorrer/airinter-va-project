<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ config('app.name') }} · Connexion</title>
    <script>try{document.documentElement.dataset.loginEra=localStorage.getItem('promethee-era')||'modern'}catch(e){document.documentElement.dataset.loginEra='modern'}</script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ public_asset('/promethee-assets/login.css') }}">
</head>
<body class="promethee-auth">
<main class="login-shell">
    <header class="login-topbar"><span class="login-wordmark">AIR INTER · PROMÉTHÉE</span><label class="login-era">{{ __('promethee.display') }} <select id="login-era" aria-label="{{ __('promethee.display_style') }}"><option value="modern">{{ __('promethee.modern') }}</option><option value="2000">{{ __('promethee.year_2000') }}</option><option value="minitel">{{ __('promethee.minitel') }}</option></select></label></header>
    <div class="login-layout">
        <aside class="login-intro"><div><small>{{ __('promethee.operations_centre') }}</small><h1>{{ __('promethee.welcome_aboard') }}</h1><p>{{ __('promethee.login_welcome') }}</p></div><div class="login-route"><b>AI</b><span>Pourquoi vivre sans ailes !</span></div></aside>
        <section class="login-panel">@include('flash.message')@yield('content')</section>
    </div>
    <footer class="login-footer">AIR INTER · PROMÉTHÉE · {{ __('promethee.french_domestic_network') }}</footer>
</main>
<script src="{{ public_asset('/promethee-assets/login.js') }}" defer></script>
@yield('scripts')
</body>
</html>
