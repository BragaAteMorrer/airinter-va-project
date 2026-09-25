<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="base-url" content="{{ url('') }}">
  <title>@yield('title') · Air Inter VA</title>
  <link rel="shortcut icon" type="image/png" href="{{ public_asset('/assets/img/favicon.png') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link href="{{ public_asset('/assets/vendor/tomselect/tom-select.bootstrap5.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ public_asset('/promethee-assets/onboarding.css') }}">
  @yield('css')
</head>
<body class="airinter-onboarding">
  <div class="airinter-stripes" aria-hidden="true"><i></i><b></b><i></i></div>
  <header class="airinter-onboarding-header">
    <a href="{{ url('/') }}" class="airinter-onboarding-brand" aria-label="Air Inter Virtual Airlines">
      <img src="{{ public_asset('/promethee-assets/logos/air-inter-1980.png') }}" alt="Air Inter">
      <span><strong>AIR INTER</strong><small>VIRTUAL AIRLINES · PROMÉTHÉE</small></span>
    </a>
    <a href="{{ url('/login') }}" class="airinter-login-link">Déjà pilote ? Se connecter</a>
  </header>

  <main class="airinter-onboarding-main">
    <section class="airinter-onboarding-intro">
      <span class="airinter-kicker">DIRECTION DE L’EXPLOITATION AÉRIENNE</span>
      <h1>@yield('hero-title', 'Bienvenue à bord.')</h1>
      <p>@yield('hero-copy', 'Rejoignez Air Inter VA et retrouvez une exploitation inspirée de la compagnie française, adaptée à la simulation moderne.')</p>
      <div class="airinter-route-line"><span>PARIS</span><em></em><span>FRANCE · EUROPE</span></div>
    </section>

    <section class="airinter-onboarding-panel">
      @include('flash.message')
      @yield('content')
    </section>
  </main>

  <footer class="airinter-onboarding-footer">
    <span>© {{ date('Y') }} Air Inter Virtual Airlines</span>
    <span>Pourquoi vivre sans ailes !</span>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
  <script src="{{ public_mix('/assets/global/js/vendor.js') }}"></script>
  <script src="{{ public_mix('/assets/frontend/js/app.js') }}"></script>
  @yield('scripts')
</body>
</html>
