<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ config('app.name') }} · {{ __('promethee.login') }}</title>
    @php
        // Blade cannot safely parse nested translation calls inside @json().
        $prometheeLoginI18n = [
            'minitelLocked' => __('promethee_javascript.minitel.login_locked'),
            'minitelOpen' => __('promethee_javascript.minitel.login_open'),
            'appearance' => __('promethee.appearance'),
            'appearanceLight' => __('promethee.appearance_light'),
            'appearanceDark' => __('promethee.appearance_dark'),
        ];
    @endphp
    <script>window.prometheeLoginI18n=@json($prometheeLoginI18n);</script>
    <script>try{document.documentElement.dataset.loginEra=localStorage.getItem('promethee-era')||'modern';const appearance=localStorage.getItem('promethee-appearance');document.documentElement.dataset.appearance=['light','dark'].includes(appearance)?appearance:(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light')}catch(e){document.documentElement.dataset.loginEra='modern'}</script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ public_asset('/promethee-assets/login.css') }}">
    <link rel="stylesheet" href="{{ public_asset('/promethee-assets/airinter-auth-eras.css') }}">
</head>
<body class="promethee-auth">
<main class="login-shell">
    <header class="login-topbar"><span class="login-wordmark">AIR INTER · PROMÉTHÉE</span><label class="login-era">{{ __('promethee.display') }} <select id="login-era" aria-label="{{ __('promethee.display_style') }}"><option value="modern">{{ __('promethee.modern') }}</option><option value="2000">{{ __('promethee.year_2000') }}</option><option value="minitel">{{ __('promethee.minitel') }}</option></select></label></header>
    <div class="login-layout">
        <aside class="login-intro"><div><small>{{ __('promethee.operations_centre') }}</small><h1>{{ __('promethee.welcome_aboard') }}</h1><p>{{ __('promethee.login_welcome') }}</p></div><div class="login-route"><span>{{ __('promethee.airline_slogan') }}</span></div></aside>
        <section class="login-panel">@include('flash.message')@yield('content')</section>
    </div>
    <footer class="login-footer">AIR INTER · PROMÉTHÉE · {{ __('promethee.french_domestic_network') }}</footer>
</main>
<script src="{{ public_asset('/promethee-assets/login.js') }}" defer></script>
@yield('scripts')
</body>
</html>
