@extends('promethee::layout')
@section('title', __('promethee.dashboard_page.title'))
@section('content')
<div class="ops-header">
    <div>
        <span class="eyebrow occ-title">OPERATION CONTROL CENTER<br><small>CENTRE DES OPÉRATIONS AÉRIENNES</small></span>
        <h1>{{ __('promethee.dashboard_page.heading') }}</h1>
        <p>{{ __('promethee.dashboard_page.intro') }}</p>
    </div>
    <div class="ops-clock">
        <span>{{ now('Europe/Paris')->locale(app()->getLocale())->isoFormat('DD MMMM YYYY') }}</span>
        <strong>{{ now('Europe/Paris')->format('H:i') }}</strong>
        <small>{{ __('promethee.dashboard_page.paris_time') }}</small>
    </div>
</div>

<section class="control-strip">
    <article><span>{{ __('promethee.dashboard_page.active_flights') }}</span><strong>{{ $activeFlights }}</strong><small>{{ __('promethee.dashboard_page.open_pireps') }}</small></article>
    <article><span>{{ __('promethee.dashboard_page.pending') }}</span><strong>{{ $pendingPireps }}</strong><small>{{ __('promethee.dashboard_page.admin_queue') }}</small></article>
    <article><span>{{ __('promethee.dashboard_page.today') }}</span><strong>{{ $acceptedToday }}</strong><small>{{ __('promethee.dashboard_page.accepted_flights') }}</small></article>
    <article><span>{{ __('promethee.dashboard_page.telemetry') }}</span><strong>{{ number_format($telemetrySamples,0,',',' ') }}</strong><small>{{ __('promethee.dashboard_page.daily_samples') }}</small></article>
    <article><span>{{ __('promethee.dashboard_page.your_logbook') }}</span><strong>{{ $personal }}</strong><small>{{ __('promethee.dashboard_page.accepted_flights') }}</small></article>
</section>

<section class="panel world-clocks" aria-label="{{ __('promethee.dashboard_page.world_clocks') }}">
    <div class="panel-heading"><div><span class="eyebrow">{{ __('promethee.dashboard_page.international_network') }}</span><h2>{{ __('promethee.dashboard_page.world_clocks') }}</h2></div></div>
    @php($worldClocks = [
        ['city'=>'Paris', 'zone'=>'Europe/Paris', 'flag'=>'fr'],
        ['city'=>'Lisbonne', 'zone'=>'Europe/Lisbon', 'flag'=>'pt'],
        ['city'=>'Charlotte', 'zone'=>'America/New_York', 'flag'=>'us'],
        ['city'=>'Londres', 'zone'=>'Europe/London', 'flag'=>'gb'],
        ['city'=>'Berlin', 'zone'=>'Europe/Berlin', 'flag'=>'de'],
        ['city'=>'Antalya', 'zone'=>'Europe/Istanbul', 'flag'=>'tr'],
        ['city'=>'Tokyo', 'zone'=>'Asia/Tokyo', 'flag'=>'jp'],
    ])
    <div class="control-strip world-clock-grid">@foreach($worldClocks as $clock)<article class="world-clock" data-world-clock="{{ $clock['zone'] }}"><span><img class="world-clock-flag" src="{{ asset('SPTheme/images/flags/4x3/'.$clock['flag'].'.svg') }}" alt=""><b>{{ $clock['city'] }}</b></span><strong>--:--</strong><small>{{ $clock['zone'] }}</small></article>@endforeach</div>
</section>

<div class="dispatch-grid">
    <section class="panel board-main departure-board">
        <div class="panel-heading">
            <div><span class="eyebrow">{{ __('promethee.dashboard_page.network_schedule') }}</span><h2>{{ __('promethee_board.movements') }}</h2></div>
            <a href="{{ route('promethee.flights') }}">{{ __('promethee.dashboard_page.full_schedule') }} ↗</a>
        </div>
        <div class="departure-board-scroll" tabindex="0" aria-label="{{ __('promethee_board.movements') }}">
        <div class="flight-stack" data-split-flap-board data-board-url="{{ route('promethee.departure-board.data') }}" data-board-refresh="{{ config('departure-board.refresh_seconds') }}" data-empty-text="{{ __('promethee_board.no_movements') }}">
            <div class="split-flap-grid split-flap-head" aria-hidden="true">
                <span>{{ __('promethee_board.airline') }}</span><span>{{ __('promethee_board.flight') }}</span><span>{{ __('promethee_board.departure') }}</span><span>{{ __('promethee_board.departure_time') }}</span><span>{{ __('promethee_board.destination') }}</span><span>{{ __('promethee_board.arrival_time') }}</span><span>{{ __('promethee_board.status') }}</span>
            </div>
            @forelse($departureBoard as $flight)
                <article class="split-flap-grid dispatch-flight" data-board-flight="{{ $flight['id'] }}" style="--board-row: {{ $loop->index }}">
                    <span class="board-cell split-flap-logo airline-logo-cell" aria-label="{{ $flight['airline_code'] }}">
                        @if($flight['logo_url'])<img src="{{ $flight['logo_url'] }}" alt="{{ $flight['airline_code'] }}">@else<span class="airline-logo-fallback" data-flap-width="4">{{ $flight['airline_code'] }}</span>@endif
                    </span>
                    <a class="board-cell flight-cell flight-ident" href="{{ $flight['url'] }}" data-flap-width="9">{{ $flight['flight'] }}</a>
                    <span class="board-cell departure-cell" data-flap-width="24">{{ $flight['departure'] }}</span>
                    <time class="board-cell departure-time-cell" data-flap-width="5">{{ $flight['departure_time'] }}</time>
                    <span class="board-cell destination-cell destination" data-flap-width="28">{{ $flight['destination'] }}</span>
                    <time class="board-cell arrival-time-cell" data-flap-width="5">{{ $flight['arrival_time'] }}</time>
                    <span class="board-cell status-cell status" data-flap-width="16">{{ $flight['status_label'] }}</span>
                </article>
            @empty
                <p class="empty" data-board-empty>{{ __('promethee_board.no_movements') }}</p>
            @endforelse
        </div>
        </div>
    </section>

    <aside class="panel signal-panel">
        <span class="eyebrow">{{ __('promethee.dashboard_page.control_post') }}</span>
        <h2>{{ __('promethee.dashboard_page.quick_actions') }}</h2>
        <a class="signal" href="{{ route('promethee.operations') }}"><b>OPS</b><span>{{ __('promethee.dashboard_page.operations_room') }}</span></a>
        <a class="signal" href="{{ route('promethee.acars') }}"><b>ACR</b><span>Hermès (ACARS)</span></a>
        <a class="signal" href="{{ route('promethee.safety') }}"><b>SV</b><span>{{ __('promethee.dashboard_page.safety_bulletin') }}</span></a>
    </aside>
</div>

<div class="three-columns">
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">{{ __('promethee.dashboard_page.monthly_network') }}</span><h2>{{ __('promethee.dashboard_page.most_flown_routes') }}</h2></div></div>
        <div class="route-list">
            @forelse($topRoutes as $route)
                <div><strong>{{ $route->dpt_airport_id }} → {{ $route->arr_airport_id }}</strong><span>{{ __('promethee.dashboard_page.flights_count', ['count' => $route->total]) }}</span></div>
            @empty
                <p class="empty">{{ __('promethee.dashboard_page.no_monthly_activity') }}</p>
            @endforelse
        </div>
    </section>
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">{{ __('promethee.dashboard_page.safety') }}</span><h2>{{ __('promethee.dashboard_page.current_bulletin') }}</h2></div></div>
        @if($lastBulletin)
            <div class="mini-bulletin"><strong>{{ $lastBulletin->month }}</strong><span>{{ __('promethee.dashboard_page.last_bulletin') }}</span><a href="{{ route('promethee.safety',['month'=>$lastBulletin->month]) }}">{{ __('promethee.dashboard_page.open') }} ↗</a></div>
        @else
            <div class="mini-bulletin"><strong>{{ __('promethee.dashboard_page.draft') }}</strong><span>{{ __('promethee.dashboard_page.no_bulletin') }}</span><a href="{{ route('promethee.safety') }}">Aperçu calculé ↗</a></div>
        @endif
    </section>
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">{{ __('promethee.dashboard_page.your_progress') }}</span><h2>{{ __('promethee.dashboard_page.pilot_logbook') }}</h2></div></div>
        <div class="mini-bulletin"><strong>{{ $personal }}</strong><span>{{ __('promethee.dashboard_page.validated_flights') }}</span><a href="{{ route('promethee.profile') }}">{{ __('promethee.view_my_profile') }} ↗</a></div>
    </section>
</div>

<section class="panel">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">CLASSEMENTS · {{ mb_strtoupper($monthLabel) }}</span>
            <h2>Meilleurs pilotes du mois</h2>
        </div>
        <span class="tag">PIREP acceptés</span>
    </div>

    <div class="three-columns">
        @foreach($leaderboards as $board)
            <article class="panel">
                <div class="panel-heading"><div><span class="eyebrow">MEILLEURS PILOTES · {{ mb_strtoupper($monthLabel) }}</span><h3>{{ $board['title'] }}</h3></div></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Pilote</th><th>Valeur</th></tr></thead>
                        <tbody>
                        @forelse($board['rows'] as $row)
                            <tr>
                                <td><strong>{{ $loop->iteration }}</strong></td>
                                <td><a href="{{ route('promethee.pilots.show', $row->user_id) }}">{{ $row->name }}</a>@if($row->pilot_id)<small style="display:block">{{ $row->pilot_id }}</small>@endif</td>
                                <td><strong>{{ $row->display_value }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="3">Aucune donnée ce mois-ci.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        @endforeach
    </div>
</section>

<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">{{ __('promethee.dashboard_page.latest_accepted_pireps') }}</span><h2>{{ __('promethee.dashboard_page.recent_activity') }}</h2></div><a href="{{ route('promethee.operations') }}">{{ __('promethee.dashboard_page.view_operations_room') }} ↗</a></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>{{ __('promethee.flight') }}</th><th>{{ __('promethee.route') }}</th><th>{{ __('promethee.aircraft') }}</th><th>{{ __('promethee.dashboard_page.time') }}</th><th>Pilote</th><th>Date</th></tr></thead>
            <tbody>
            @forelse($recentPireps as $pirep)
                <tr>
                    <td><strong>{{ $pirep->ident }}</strong></td>
                    <td>{{ $pirep->dpt_airport_id }} → {{ $pirep->arr_airport_id }}</td>
                    <td>{{ $pirep->aircraft?->registration ?? '—' }}</td>
                    <td>{{ $pirep->flight_time ? floor($pirep->flight_time / 60).'h '.str_pad($pirep->flight_time % 60,2,'0',STR_PAD_LEFT) : '—' }}</td>
                    <td>{{ $pirep->user?->pilot_id ?: '—' }}@if($pirep->user?->name) · {{ $pirep->user->name }}@endif</td>
                    <td>{{ optional($pirep->submitted_at)->setTimezone('Europe/Paris')->format('d/m H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">{{ __('promethee.dashboard_page.no_accepted_pireps') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
