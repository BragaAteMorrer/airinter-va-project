<!doctype html>
<html lang="{{ app()->getLocale() }}" data-era="modern" data-appearance="light" data-minitel-runtime="m2" @auth data-minitel-bootstrap="{{ route('promethee.minitel.bootstrap') }}" @endauth>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', __('promethee.operations_centre')) · Prométhée · Air Inter</title>
<link rel="icon" type="image/png" href="{{ asset('promethee-assets/favicon.png') }}">
<script>(() => { try { const era=localStorage.getItem('promethee-era'), appearance=localStorage.getItem('promethee-appearance'), minitelSessionDisabled=sessionStorage.getItem('promethee-minitel-session-disabled')==='1'; const selectedEra=['modern','2000','minitel'].includes(era)?era:'modern'; document.documentElement.dataset.era=(selectedEra==='minitel'&&minitelSessionDisabled)?'modern':selectedEra; document.documentElement.dataset.appearance=['light','dark'].includes(appearance)?appearance:(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'); } catch (_) {} })();</script>
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee.css') }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-v2.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-v2.css')) }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-distinction.css') }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-community.css') }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/airinter-eras.css') }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-appearance.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-appearance.css')) }}">
<script>
window.ensurePrometheeMinitelStyles = (() => {
  let loaded = false;
  const sources = [
    "{{ asset('promethee-assets/minitel/minitel-runtime.css') }}?v={{ filemtime(public_path('promethee-assets/minitel/minitel-runtime.css')) }}",
    "{{ asset('promethee-assets/minitel/minitel-shell.css') }}?v={{ filemtime(public_path('promethee-assets/minitel/minitel-shell.css')) }}",
    "{{ asset('promethee-assets/promethee-minitel.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-minitel.css')) }}"
  ];
  return () => {
    if (loaded) return;
    loaded = true;
    sources.forEach(href => {
      if (document.querySelector('link[data-promethee-minitel-style="' + href + '"]')) return;
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = href;
      link.dataset.prometheeMinitelStyle = href;
      document.head.append(link);
    });
  };
})();
window.ensurePrometheeMinitelRuntime = (() => {
  let promise;
  const sources = [
    "{{ asset('promethee-assets/minitel/runtime.js') }}?v={{ filemtime(public_path('promethee-assets/minitel/runtime.js')) }}",
    "{{ asset('promethee-assets/minitel/renderer.js') }}?v={{ filemtime(public_path('promethee-assets/minitel/renderer.js')) }}",
    "{{ asset('promethee-assets/minitel/shell.js') }}?v={{ filemtime(public_path('promethee-assets/minitel/shell.js')) }}",
    "{{ asset('promethee-assets/promethee-minitel.js') }}?v={{ filemtime(public_path('promethee-assets/promethee-minitel.js')) }}"
  ];
  const load = src => new Promise((resolve, reject) => {
    const existing = document.querySelector('script[data-promethee-minitel-src="' + src + '"]');
    if (existing?.dataset.loaded === 'true') return resolve();
    const script = existing || document.createElement('script');
    script.src = src;
    script.async = false;
    script.dataset.prometheeMinitelSrc = src;
    script.addEventListener('load', () => { script.dataset.loaded = 'true'; resolve(); }, { once: true });
    script.addEventListener('error', () => reject(new Error('Impossible de charger ' + src)), { once: true });
    if (!existing) document.head.append(script);
  });
  return () => {
    window.ensurePrometheeMinitelStyles?.();
    return promise ||= sources.reduce((chain, src) => chain.then(() => load(src)), Promise.resolve());
  };
})();
if (document.documentElement.dataset.era === 'minitel') window.ensurePrometheeMinitelStyles();
</script>
<script src="{{ asset('promethee-assets/promethee.js') }}?v={{ filemtime(public_path('promethee-assets/promethee.js')) }}" defer></script>
<script src="{{ asset('promethee-assets/navigation-groups.js') }}?v={{ filemtime(public_path('promethee-assets/navigation-groups.js')) }}" defer></script>
@php
    // Keeping the array out of the @json directive is deliberate: Blade's
    // directive parser stops at the first closing parenthesis it encounters
    // and cannot safely parse the nested array below.
    $prometheeI18n = [
        'locale' => app()->getLocale(),
        'dateLocale' => config('languages.' . app()->getLocale() . '.intl', app()->getLocale()),
        'minitelBoot' => [
            __('promethee_javascript.minitel.directory'),
            __('promethee_javascript.minitel.rule'),
            __('promethee_javascript.minitel.operations_centre'),
            '',
            __('promethee_javascript.minitel.network_link'),
            __('promethee_javascript.minitel.terminal'),
            '',
            __('promethee_javascript.minitel.loading'),
            __('promethee_javascript.minitel.wait'),
        ],
    ];
@endphp
<script>
window.prometheeI18n = @json($prometheeI18n);
</script>
@stack('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-era-components.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-era-components.css')) }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-accessibility.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-accessibility.css')) }}">
</head>
@php
    // Most Prométhée pages receive branding through PortalController::page().
    // Dedicated controllers may render the shared layout directly, so keep the
    // layout resilient instead of crashing on a missing view variable.
    $branding ??= app(\Modules\Promethee\Services\BrandingService::class)->active();
@endphp
<body>
<a class="skip" href="#main">{{ __('promethee.skip_to_content') }}</a>
<aside class="sidebar" data-promethee-shell>
<div class="shell-primary">
    <div class="shell-branding">
        <a class="brand" href="{{ route('promethee.dashboard') }}">
            <span class="brand-logo-shell">
                <img class="brand-logo" src="{{ $branding['url'] }}" alt="Air Inter">
                <img class="brand-logo-minitel" src="{{ asset('promethee-assets/logos/air-inter-minitel.png') }}" alt="Air Inter">
            </span>
            <span class="brand-caption">{{ __('promethee.virtual_airline') }}<br>{{ __('promethee.french_domestic_network') }}</span>
        </a>
        <div class="system-name">
            <span class="eyebrow">{{ __('promethee.operations_centre') }}</span>
            <strong>Prométhée<span class="cursor">_</span></strong>
            <small>{{ __('promethee.airline_slogan') }}</small>
        </div>
    </div>

    @auth
    <div class="workspace-switch" role="group" aria-label="Espace Prométhée">
        <button type="button" data-workspace-choice="pilot" aria-pressed="true"><span>PILOTE</span><small>Préparer & voler</small></button>
        @ability('admin','admin-access')
        <button type="button" data-workspace-choice="staff" aria-pressed="false"><span>OCC / HQ</span><small>Exploiter & administrer</small></button>
        @endability
    </div>
    @endauth

    <div class="shell-account">
        @auth
        @php
            $sidebarPilot = Auth::user() ?: new \App\Models\User([
                'name' => __('promethee.visitor_access'),
                'pilot_id' => 'AIR INTER',
            ]);
        @endphp
        <section class="pilot-space" aria-label="{{ __('promethee.pilot_area') }}">
            <span class="pilot-space-label"><i class="status-dot"></i> {{ __('promethee.pilot_area') }}</span>
            <a class="pilot-card-link" href="{{ route('promethee.profile') }}" aria-label="{{ __('promethee.open_profile') }}">
                <span class="pilot-avatar">
                    @if($sidebarPilot?->avatar)<img src="{{ $sidebarPilot->avatar->url }}" alt="{{ __('promethee_accessibility.photo_of', ['name' => $sidebarPilot->name]) }}">
                    @else<img src="{{ $sidebarPilot ? $sidebarPilot->gravatar(96) : asset('promethee-assets/logos/air-inter-1970s.png') }}" alt="{{ __('promethee_accessibility.air_inter_avatar') }}">
                    @endif
                </span>
                <span class="pilot-identity">
                    <strong>{{ $sidebarPilot->name }}</strong>
                    <small>{{ $sidebarPilot->pilot_id ?: 'ITF---' }}</small>
                    <em>{{ $sidebarPilot->rank?->name ?? 'Pilote Air Inter' }}@if($sidebarPilot->home_airport_id) · {{ $sidebarPilot->home_airport_id }}@endif</em>
                </span>
                <b class="pilot-open">↗</b>
            </a>
            <div class="pilot-space-actions">
                <a href="{{ route('promethee.profile') }}">{{ __('promethee.view_my_profile') }}</a>
                <a href="{{ url('/logout') }}">{{ __('promethee.logout') }}</a>
            </div>
        </section>
        @else
        <section class="pilot-space" aria-label="{{ __('promethee.visitor_access') }}">
            <span class="pilot-space-label"><i class="status-dot"></i> {{ __('promethee.visitor_access') }}</span>
            <span class="pilot-identity"><strong>{{ __('promethee.public_report') }}</strong><small>Air Inter VA</small><em>{{ __('promethee.read_only') }}</em></span>
            <div class="pilot-space-actions"><a href="{{ route('login') }}">{{ __('promethee.login') }}</a><a href="{{ route('register') }}">{{ __('promethee.register') }}</a></div>
        </section>
        @endauth
    </div>

    <button class="shell-menu-toggle" type="button" aria-expanded="false" aria-controls="promethee-navigation">
        <span>{{ __('promethee.navigation') }}</span><b aria-hidden="true">☰</b>
    </button>
</div>
<nav id="promethee-navigation" class="is-grouped" aria-label="{{ __('promethee.navigation') }}" data-default-workspace="{{ request()->routeIs('admin.promethee.*', 'admin.users.*', 'admin.ranks.*') ? 'staff' : 'pilot' }}">
@auth
@php
    // Each entry uses a registered, server-side route. Optional legacy modules
    // are intentionally absent: their module manifests currently mark them inactive.
    $navigationGroups = [
        'welcome' => [
            'title' => __('promethee.navigation_welcome'),
            'scope' => 'pilot',
            'links' => [
                ['route' => 'promethee.dashboard', 'label' => 'dashboard', 'active' => 'promethee.dashboard'],
                ['route' => 'promethee.pilots', 'label' => 'community', 'active' => 'promethee.pilots*'],
                ['route' => 'promethee.calendar', 'label' => 'calendar', 'active' => 'promethee.calendar*'],
            ],
        ],
        'pilot' => [
            'title' => __('promethee.navigation_pilot'),
            'scope' => 'pilot',
            'links' => [
                ['route' => 'promethee.profile', 'label' => 'open_profile', 'active' => 'promethee.profile'],
                ['route' => 'promethee.bookings', 'label' => 'navigation_menu.bookings', 'active' => 'promethee.bookings'],
                ['route' => 'promethee.public.pireps.mine', 'label' => 'navigation_menu.my_reports', 'active' => 'promethee.public.pireps.mine', 'emphasis' => true],
                ['route' => 'promethee.public.pireps', 'label' => 'navigation_menu.all_reports', 'active' => 'promethee.public.pireps|promethee.pireps.*'],
                ['route' => 'promethee.passport', 'label' => 'passport', 'active' => 'promethee.passport'],
                ['route' => 'promethee.assignments', 'label' => 'assignments', 'active' => 'promethee.assignments'],
                ['route' => 'promethee.shop', 'label' => 'shop', 'active' => 'promethee.shop*'],
                ['route' => 'promethee.jumpseat', 'label' => 'jumpseat', 'active' => 'promethee.jumpseat*'],
                ['route' => 'promethee.acars', 'label' => 'acars', 'active' => 'promethee.acars'],
            ],
        ],
        'operations' => [
            'title' => __('promethee.navigation_operations'),
            'scope' => 'pilot',
            'links' => [
                ['route' => 'promethee.flights', 'label' => 'flight_schedule', 'active' => 'promethee.flights*'],
                ['route' => 'promethee.operations', 'label' => 'operations', 'active' => 'promethee.operations'],
                ['route' => 'promethee.missions', 'label' => 'missions_circuits', 'active' => 'promethee.missions*'],
                ['route' => 'promethee.live', 'label' => 'navigation_menu.live_flights', 'active' => 'promethee.live'],
            ],
        ],
        'company' => [
            'title' => __('promethee.navigation_company'),
            'scope' => 'shared',
            'links' => [
                ['route' => 'promethee.airlines', 'label' => 'airlines', 'active' => 'promethee.airlines'],
                ['route' => 'promethee.fleet', 'label' => 'fleet', 'active' => 'promethee.fleet'],
                ['route' => 'promethee.maintenance', 'label' => 'maintenance', 'active' => 'promethee.maintenance'],
                ['route' => 'promethee.finances', 'label' => 'company_finances', 'active' => 'promethee.finances'],
                ['route' => 'promethee.documents.mine', 'text' => 'Documentation Air Inter', 'active' => 'promethee.documents.mine|promethee.documents|promethee.documents.show'],
                ['route' => 'promethee.downloads', 'text' => 'Ressources techniques', 'active' => 'promethee.downloads'],
                ['route' => 'promethee.safety', 'label' => 'flight_safety', 'active' => 'promethee.safety*'],
            ],
        ],
        'occ' => [
            'title' => 'OCC / EXPLOITATION',
            'scope' => 'staff',
            'links' => [
                ['route' => 'admin.promethee.dispatch', 'label' => 'dispatch_desk', 'active' => 'admin.promethee.dispatch*', 'emphasis' => true],
                ['route' => 'promethee.live', 'label' => 'navigation_menu.live_flights', 'active' => 'promethee.live'],
                ['route' => 'admin.promethee.network', 'text' => 'Air Inter Network', 'active' => 'admin.promethee.network*'],
                ['route' => 'admin.promethee.mailbox', 'text' => 'Boîte OCC', 'active' => 'admin.promethee.mailbox*'],
                ['route' => 'admin.promethee.health', 'text' => 'État des services', 'active' => 'admin.promethee.health'],
            ],
        ],
    ];
@endphp
@foreach($navigationGroups as $groupKey => $group)
    @php($links = $group['links'])
    @php($groupActive = collect($links)->contains(fn ($link) => request()->routeIs(...explode('|', $link['active']))))
    <details @class(['nav-group', 'selected' => $groupActive]) data-workspace-group="{{ $group['scope'] }}">
        <summary>{{ $group['title'] }}<b aria-hidden="true">⌄</b></summary>
        <div class="nav-menu">
            @foreach($links as $link)
                @if(\Illuminate\Support\Facades\Route::has($link['route']))
                @php($linkActive = request()->routeIs(...explode('|', $link['active'])))
                <a @class(['selected' => $linkActive, 'nav-emphasis' => ($link['emphasis'] ?? false)]) @if($linkActive) aria-current="page" @endif href="{{ route($link['route']).(isset($link['fragment']) ? '#'.$link['fragment'] : '') }}">{{ $link['text'] ?? __('promethee.'.$link['label']) }}</a>
                @endif
            @endforeach
        </div>
    </details>
@endforeach
<details @class(['nav-group', 'selected' => !request()->routeIs('admin.promethee.dispatch*', 'admin.promethee.network*', 'admin.promethee.mailbox*', 'admin.promethee.health') && request()->routeIs('admin.promethee.*', 'admin.users.*', 'admin.ranks.*')]) data-workspace-group="staff">
    <summary>{{ __('promethee.navigation_private') }}<b aria-hidden="true">⌄</b></summary>
    <div class="nav-menu">
        @ability('admin','admin-access')
        <a @class(['selected' => !request()->routeIs('admin.promethee.dispatch*', 'admin.promethee.network*', 'admin.promethee.mailbox*', 'admin.promethee.health') && request()->routeIs('admin.promethee.*')]) @if(!request()->routeIs('admin.promethee.dispatch*', 'admin.promethee.network*', 'admin.promethee.mailbox*', 'admin.promethee.health') && request()->routeIs('admin.promethee.*')) aria-current="page" @endif href="{{ route('admin.promethee.dashboard') }}">{{ __('promethee.administration') }}</a>
        @if(\Illuminate\Support\Facades\Route::has('admin.promethee.crm'))<a @class(['selected' => request()->routeIs('admin.promethee.crm*')]) @if(request()->routeIs('admin.promethee.crm*')) aria-current="page" @endif href="{{ route('admin.promethee.crm') }}">CRM & communications</a>@endif
        @if(\Illuminate\Support\Facades\Route::has('admin.users.index'))<a @class(['selected' => request()->routeIs('admin.users.*')]) @if(request()->routeIs('admin.users.*')) aria-current="page" @endif href="{{ route('admin.users.index') }}">{{ __('promethee.admin_pilots') }}</a>@endif
        @if(\Illuminate\Support\Facades\Route::has('admin.ranks.index'))<a @class(['selected' => request()->routeIs('admin.ranks.*')]) @if(request()->routeIs('admin.ranks.*')) aria-current="page" @endif href="{{ route('admin.ranks.index') }}">{{ __('promethee.admin_ranks') }}</a>@endif
        @endability
        <a href="{{ url('/logout') }}">{{ __('promethee.logout') }}</a>
    </div>
</details>
@else
<a class="nav-link" href="{{ route('promethee.occ') }}"><span>01</span>{{ __('promethee.public_home') }}<b>↗</b></a>
<a class="nav-link" href="{{ route('promethee.public.pilots') }}"><span>02</span>{{ __('promethee.community') }}<b>↗</b></a>
<a class="nav-link" href="{{ route('promethee.public.live') }}"><span>03</span>{{ __('promethee.navigation_menu.live_flights') }}<b>↗</b></a>
@endauth
</nav>
</aside>
<div class="workspace">
<header class="topbar"><span class="breadcrumb">AIR INTER <span>/</span> PROMÉTHÉE <span>/</span> @yield('title','EXPLOITATION')</span>
<div class="topbar-controls" aria-label="{{ __('promethee.display') }}">
<label class="theme-control"><span class="theme-control-label">{{ __('promethee.language') }}</span><select aria-label="{{ __('promethee.language') }}" onchange="if(this.value) window.location=this.value">@foreach(config('languages') as $code=>$language)<option value="{{ route('promethee.language',$code) }}" @selected(app()->getLocale() === $code)>{{ $language['display'] }}</option>@endforeach</select></label>
<label class="theme-control"><span class="theme-control-label">{{ __('promethee.display') }}</span><select id="era" aria-label="{{ __('promethee.display_style') }}"><option value="modern">{{ __('promethee.modern') }}</option><option value="2000">{{ __('promethee.year_2000') }}</option><option value="minitel">{{ __('promethee.minitel') }}</option></select></label>
<label class="theme-control appearance-control"><span class="theme-control-label">{{ __('promethee.appearance') }}</span><select id="appearance" aria-label="{{ __('promethee.appearance_style') }}"><option value="light">{{ __('promethee.appearance_light') }}</option><option value="dark">{{ __('promethee.appearance_dark') }}</option></select></label>
</div>
<time id="utc-clock">UTC</time></header>
<main id="main" tabindex="-1">
@if(session('success'))<div class="notice success" role="status" aria-live="polite">{{ session('success') }}</div>@endif
@if($errors->any())<div class="notice error" role="alert" aria-live="assertive"><strong>{{ __('promethee.input_error') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main>
<footer class="footer"><span>AIR INTER · PROMÉTHÉE</span><span>{{ __('promethee.airline_simulation') }} · {{ date('Y') }}</span><span class="tricolor"><i></i><i></i><i></i></span></footer>
</div>
@stack('scripts')
</body></html>
