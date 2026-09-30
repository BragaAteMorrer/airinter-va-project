<!doctype html>
<html lang="{{ app()->getLocale() }}" data-theme="system">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#102d56">
    <title>@yield('title', config('airinter-id.name', 'Argos'))</title>
    <link rel="icon" href="{{ config('airinter-id.favicon_url') }}">
    <link rel="stylesheet" href="/id.css">
</head>
<body>
<div class="app-shell">
<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="Argos — Air Inter VA">
            <img src="{{ config('airinter-id.brand_logo_url') }}" alt="Air Inter">
            <span class="brand-copy"><small>Air Inter Identity</small><strong>ARGOS</strong></span>
        </a>
        <nav aria-label="Navigation principale">
            <a href="{{ config('airinter-id.public_url') }}">Air Inter VA</a>
            <a href="{{ config('airinter-id.promethee_url') }}">Prométhée</a>
            @auth
                <a href="{{ route('account') }}">Mon compte</a>
                @if(in_array(auth()->user()->subject, config('argos-security.admin_subjects', []), true))
                    <a href="{{ route('admin.security') }}">Sécurité</a>
                @endif
            @endauth
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Changer le thème">◐</button>
        </nav>
    </div>
</header>
<main>
    @if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
    @yield('content')
</main>
<footer>
    <div>
        <strong>ARGOS</strong>
        <span>Air Inter Virtual Airlines · Identity & Access</span>
    </div>
    <div class="footer-links">
        <a href="{{ route('oidc.discovery') }}">OpenID</a>
        <span>{{ config('airinter-id.release') ?: 'Production' }}</span>
    </div>
</footer>
</div>
<script src="/argos.js" defer></script>
<script src="/passkeys.js" defer></script>
</body>
</html>