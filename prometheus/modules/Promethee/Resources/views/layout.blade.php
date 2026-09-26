<!doctype html>
<html lang="{{ app()->getLocale() }}" data-era="modern" data-appearance="light" data-minitel-runtime="m2" @auth data-minitel-bootstrap="{{ route('promethee.minitel.bootstrap') }}" @endauth>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', __('promethee.operations_centre')) · Prométhée · Air Inter</title>
<script>(() => { try { const era=localStorage.getItem('promethee-era'), appearance=localStorage.getItem('promethee-appearance'), minitelSessionDisabled=sessionStorage.getItem('promethee-minitel-session-disabled')==='1'; const selectedEra=['modern','2000','minitel'].includes(era)?era:'modern'; document.documentElement.dataset.era=(selectedEra==='minitel'&&minitelSessionDisabled)?'modern':selectedEra; document.documentElement.dataset.appearance=['light','dark'].includes(appearance)?appearance:(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'); } catch (_) {} })();</script>
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee.css') }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-v2.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-v2.css')) }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-distinction.css') }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-community.css') }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/airinter-eras.css') }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-appearance.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-appearance.css')) }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/minitel/minitel-runtime.css') }}?v={{ filemtime(public_path('promethee-assets/minitel/minitel-runtime.css')) }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/minitel/minitel-shell.css') }}?v={{ filemtime(public_path('promethee-assets/minitel/minitel-shell.css')) }}">
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-minitel.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-minitel.css')) }}">
<script src="{{ asset('promethee-assets/minitel/runtime.js') }}?v={{ filemtime(public_path('promethee-assets/minitel/runtime.js')) }}" defer></script>
<script src="{{ asset('promethee-assets/minitel/renderer.js') }}?v={{ filemtime(public_path('promethee-assets/minitel/renderer.js')) }}" defer></script>
<script src="{{ asset('promethee-assets/minitel/shell.js') }}?v={{ filemtime(public_path('promethee-assets/minitel/shell.js')) }}" defer></script>
<script src="{{ asset('promethee-assets/promethee.js') }}?v={{ filemtime(public_path('promethee-assets/promethee.js')) }}" defer></script>
<script src="{{ asset('promethee-assets/promethee-minitel.js') }}?v={{ filemtime(public_path('promethee-assets/promethee-minitel.js')) }}" defer></script>
<script src="{{ asset('promethee-assets/navigation-groups.js') }}" defer></script>
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
</head>
<body>
<a class="skip" href="#main">{{ __('promethee.skip_to_content') }}</a>
<aside class="sidebar">
<a class="brand" href="{{ route('promethee.dashboard') }}"><span class="brand-logo-shell"><img class="brand-logo" src="{{ $branding['url'] }}" alt="Air Inter"><img class="brand-logo-minitel" src="{{ asset('promethee-assets/logos/air-inter-minitel.png') }}" alt="Air Inter"></span><span class="brand-caption">{{ __('promethee.virtual_airline') }}<br>{{ __('promethee.french_domestic_network') }}</span></a>
<div class="system-name"><span class="eyebrow">{{ __('promethee.operations_centre') }}</span><strong>Prométhée<span class="cursor">_</span></strong><small>{{ __('promethee.airline_slogan') }}</small></div>
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
<span class="pilot-identity"><strong>{{ $sidebarPilot->name }}</strong><small>{{ $sidebarPilot->pilot_id ?: 'ITF---' }}</small><em>{{ $sidebarPilot->rank?->name ?? 'Pilote Air Inter' }}@if($sidebarPilot->home_airport_id) · {{ $sidebarPilot->home_airport_id }}@endif</em></span>
<b class="pilot-open">↗</b>
</a>
<div class="pilot-space-actions"><a href="{{ route('promethee.profile') }}">{{ __('promethee.view_my_profile') }}</a><a href="{{ url('/logout') }}">{{ __('promethee.logout') }}</a></div>
</section>
@else
<section class="pilot-space" aria-label="{{ __('promethee.visitor_access') }}"><span class="pilot-space-label"><i class="status-dot"></i> {{ __('promethee.visitor_access') }}</span><span class="pilot-identity"><strong>{{ __('promethee.public_report') }}</strong><small>Air Inter VA</small><em>{{ __('promethee.read_only') }}</em></span><div class="pilot-space-actions"><a href="{{ route('login') }}">{{ __('promethee.login') }}</a><a href="{{ route('register') }}">{{ __('promethee.register') }}</a></div></section>
@endauth
<nav class="is-grouped" aria-label="{{ __('promethee.navigation') }}">
@auth
@php
    // Each entry uses a registered, server-side route. Optional legacy modules
    // are intentionally absent: their module manifests currently mark them inactive.
    $navigationGroups = [
        'navigation_welcome' => [
            ['route' => 'promethee.dashboard', 'label' => 'dashboard', 'active' => 'promethee.dashboard'],
            ['route' => 'promethee.occ', 'label' => 'public_home', 'active' => 'promethee.occ'],
            ['route' => 'promethee.pilots', 'label' => 'community', 'active' => 'promethee.pilots*'],
            ['route' => 'promethee.calendar', 'label' => 'calendar', 'active' => 'promethee.calendar*'],
        ],
        'navigation_pilot' => [
            ['route' => 'promethee.profile', 'label' => 'open_profile', 'active' => 'promethee.profile'],
            ['route' => 'promethee.profile', 'label' => 'my_missions', 'active' => 'promethee.profile', 'fragment' => 'my-missions'],
            ['route' => 'promethee.passport', 'label' => 'passport', 'active' => 'promethee.passport'],
            ['route' => 'promethee.assignments', 'label' => 'assignments', 'active' => 'promethee.assignments'],
            ['route' => 'promethee.bookings', 'label' => 'navigation_menu.bookings', 'active' => 'promethee.bookings'],
            ['route' => 'promethee.public.pireps.mine', 'label' => 'navigation_menu.my_reports', 'active' => 'promethee.public.pireps.mine', 'emphasis' => true],
            ['route' => 'promethee.public.pireps', 'label' => 'navigation_menu.all_reports', 'active' => 'promethee.public.pireps|promethee.pireps.*'],
            ['route' => 'promethee.shop', 'label' => 'shop', 'active' => 'promethee.shop*'],
            ['route' => 'promethee.jumpseat', 'label' => 'jumpseat', 'active' => 'promethee.jumpseat*'],
            ['route' => 'promethee.acars', 'label' => 'acars', 'active' => 'promethee.acars'],
        ],
        'navigation_company' => [
            ['route' => 'promethee.finances', 'label' => 'company_finances', 'active' => 'promethee.finances'],
            ['route' => 'promethee.airlines', 'label' => 'airlines', 'active' => 'promethee.airlines'],
            ['route' => 'promethee.fleet', 'label' => 'fleet', 'active' => 'promethee.fleet'],
            ['route' => 'promethee.maintenance', 'label' => 'maintenance', 'active' => 'promethee.maintenance'],
            ['route' => 'promethee.downloads', 'label' => 'navigation_menu.downloads', 'active' => 'promethee.downloads*'],
        ],
        'navigation_operations' => [
            ['route' => 'promethee.flights', 'label' => 'flight_schedule', 'active' => 'promethee.flights*'],
            ['route' => 'promethee.operations', 'label' => 'operations', 'active' => 'promethee.operations'],
            ['route' => 'admin.promethee.dispatch', 'label' => 'dispatch_desk', 'active' => 'admin.promethee.dispatch*'],
            ['route' => 'promethee.missions', 'label' => 'missions_circuits', 'active' => 'promethee.missions'],
            ['route' => 'promethee.live', 'label' => 'navigation_menu.live_flights', 'active' => 'promethee.live'],
            ['route' => 'promethee.safety', 'label' => 'flight_safety', 'active' => 'promethee.safety*'],
        ],
    ];
@endphp
@foreach($navigationGroups as $groupKey => $links)
    @php($groupActive = collect($links)->contains(fn ($link) => request()->routeIs(...explode('|', $link['active']))))
    <details @class(['nav-group', 'selected' => $groupActive])>
        <summary>{{ __('promethee.'.$groupKey) }}<b aria-hidden="true">⌄</b></summary>
        <div class="nav-menu">
            @foreach($links as $link)
                @if(\Illuminate\Support\Facades\Route::has($link['route']))
                <a @class(['selected' => request()->routeIs(...explode('|', $link['active'])), 'nav-emphasis' => ($link['emphasis'] ?? false)]) href="{{ route($link['route']).(isset($link['fragment']) ? '#'.$link['fragment'] : '') }}">{{ __('promethee.'.$link['label']) }}</a>
                @endif
            @endforeach
        </div>
    </details>
@endforeach
<details @class(['nav-group', 'selected' => !request()->routeIs('admin.promethee.dispatch*') && request()->routeIs('admin.promethee.*', 'admin.users.*', 'admin.ranks.*')])>
    <summary>{{ __('promethee.navigation_private') }}<b aria-hidden="true">⌄</b></summary>
    <div class="nav-menu">
        @ability('admin','admin-access')
        <a @class(['selected' => !request()->routeIs('admin.promethee.dispatch*') && request()->routeIs('admin.promethee.*')]) href="{{ route('admin.promethee.dashboard') }}">{{ __('promethee.administration') }}</a>
        @if(\Illuminate\Support\Facades\Route::has('admin.users.index'))<a @class(['selected' => request()->routeIs('admin.users.*')]) href="{{ route('admin.users.index') }}">{{ __('promethee.admin_pilots') }}</a>@endif
        @if(\Illuminate\Support\Facades\Route::has('admin.ranks.index'))<a @class(['selected' => request()->routeIs('admin.ranks.*')]) href="{{ route('admin.ranks.index') }}">{{ __('promethee.admin_ranks') }}</a>@endif
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
<label class="theme-control">{{ __('promethee.language') }} <select aria-label="{{ __('promethee.language') }}" onchange="if(this.value) window.location=this.value">@foreach(config('languages') as $code=>$language)<option value="{{ route('promethee.language',$code) }}" @selected(app()->getLocale() === $code)>{{ $language['display'] }}</option>@endforeach</select></label>
<label class="theme-control">{{ __('promethee.display') }} <select id="era" aria-label="{{ __('promethee.display_style') }}"><option value="modern">{{ __('promethee.modern') }}</option><option value="2000">{{ __('promethee.year_2000') }}</option><option value="minitel">{{ __('promethee.minitel') }}</option></select></label>
<label class="theme-control appearance-control">{{ __('promethee.appearance') }} <select id="appearance" aria-label="{{ __('promethee.appearance_style') }}"><option value="light">{{ __('promethee.appearance_light') }}</option><option value="dark">{{ __('promethee.appearance_dark') }}</option></select></label>
<time id="utc-clock">UTC</time></header>
<main id="main">
@if(session('success'))<div class="notice success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="notice error" role="alert"><strong>{{ __('promethee.input_error') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main>
<footer class="footer"><span>AIR INTER · PROMÉTHÉE</span><span>{{ __('promethee.airline_simulation') }} · {{ date('Y') }}</span><span class="tricolor"><i></i><i></i><i></i></span></footer>
</div>
@stack('scripts')
</body></html>
