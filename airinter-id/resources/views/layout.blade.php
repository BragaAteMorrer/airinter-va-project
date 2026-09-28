<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('airinter-id.name', 'Argos'))</title>
    <link rel="stylesheet" href="/id.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ route('home') }}"><span>AIR INTER</span><strong>ARGOS</strong></a>
    <nav>
        <a href="{{ config('airinter-id.public_url') }}">Air Inter VA</a>
        <a href="{{ config('airinter-id.promethee_url') }}">Prométhée</a>
        @auth<a href="{{ route('account') }}">Mon compte</a>@endauth
    </nav>
</header>
<main>
    @if(session('status'))<div class="notice">{{ session('status') }}</div>@endif
    @yield('content')
</main>
<footer><span>Air Inter Virtual Airlines · Argos Identity Services</span><span>id.airinter-va.org</span></footer>
    <script src="/passkeys.js" defer></script>
</body>
</html>
