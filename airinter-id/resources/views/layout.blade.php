<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Air Inter ID')</title>
    <link rel="stylesheet" href="/id.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ route('home') }}"><span>AIR INTER</span><strong>ID</strong></a>
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
<footer><span>Air Inter Virtual Airlines · Identity Services</span><span>id.airinter-va.org</span></footer>
</body>
</html>
