<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') · {{ config('app.name') }}</title>
  <script>(() => { try { const era=localStorage.getItem('promethee-era')||'modern'; document.documentElement.dataset.loginEra=['modern','2000','minitel'].includes(era)?era:'modern'; } catch { document.documentElement.dataset.loginEra='modern'; } })();</script>
  <link rel="shortcut icon" type="image/png" href="{{ public_asset('/assets/img/favicon.png') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="{{ public_asset('/promethee-assets/login.css') }}">
  <link rel="stylesheet" href="{{ public_asset('/promethee-assets/airinter-auth-eras.css') }}">
  @yield('css')
</head>
<body class="promethee-auth">
  <main class="login-shell">
    <header class="login-topbar"><span class="login-wordmark">AIR INTER · PROMÉTHÉE</span><label class="login-era">{{ __('promethee.display') }} <select id="login-era" aria-label="{{ __('promethee.display_style') }}"><option value="modern">{{ __('promethee.modern') }}</option><option value="2000">{{ __('promethee.year_2000') }}</option><option value="minitel">{{ __('promethee.minitel') }}</option></select></label></header>
    <div class="login-layout">
      <aside class="login-intro"><div><small>{{ __('promethee.company_virtual') }}</small><h1>Pourquoi vivre sans ailes !</h1><p>{{ __('promethee.login_welcome') }}</p></div><div class="login-route"><span>{{ __('promethee.french_domestic_network') }}</span></div></aside>
      <section class="login-panel">@include('flash.message')@yield('content')</section>
    </div>
    <footer class="login-footer">© {{ date('Y') }} {{ config('app.name') }} · {{ __('promethee.powered_by') }}</footer>
  </main>
  <script src="{{ public_asset('/promethee-assets/login.js') }}" defer></script>
  @yield('scripts')
</body>
</html>
