@extends('promethee::layout')

@section('title', __('promethee_briefing.title'))

@section('content')
<div class="ops-header compact">
    <div>
        <span class="eyebrow">{{ __('promethee_briefing.eyebrow') }}</span>
        <h1>{{ $flight->ident }}</h1>
        <p>{{ $flight->dpt_airport_id }} → {{ $flight->arr_airport_id }} · {{ number_format($flight->distance->toUnit('nmi'), 0, ',', ' ') }} NM</p>
    </div>
    <a class="button outline" href="{{ route('promethee.flights.show', $flight->id) }}">{{ __('promethee_briefing.route_sheet') }}</a>
</div>

<section class="control-strip">
    <article><span>{{ __('promethee_briefing.suggested_fuel') }}</span><strong>{{ number_format($suggestedFuel, 0, ',', ' ') }} {{ strtoupper($fuelUnit) }}</strong><small>{{ __('promethee_briefing.fuel_hint') }}</small></article>
    <article><span>{{ __('promethee_briefing.published_alternate') }}</span><strong>{{ $flight->alt_airport_id ?: '—' }}</strong><small>{{ __('promethee_briefing.weather_check') }}</small></article>
    <article><span>{{ __('promethee_briefing.compatible_fleets') }}</span><strong>{{ $compatibleSubfleetCount }}</strong><small>{{ $lineFleetRestricted ? __('promethee_briefing.aircraft_required') : 'Aucune restriction de sous-flotte publiée' }} · {{ $compatibleAircraftCount }} appareil(s) actif(s)</small></article>
    <article><span>{{ __('promethee_briefing.weather') }}</span><strong>{{ count(array_filter($weather, fn ($report) => !empty($report['metar']))) }}</strong><small>{{ __('promethee_briefing.metar_received') }}</small></article>
</section>

<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">{{ __('promethee_briefing.continuity') }}</span><h2>{{ __('promethee_briefing.ready_to_fly') }}</h2></div></div>
    <div class="control-strip">
        <article>
            <span>{{ __('promethee_briefing.booking') }}</span>
            <strong>{{ $bid ? __('promethee_briefing.booked') : __('promethee_briefing.not_booked') }}</strong>
            <small>{{ $bid?->aircraft?->registration ?: __('promethee_briefing.aircraft_pending') }}</small>
        </article>
        <article>
            <span>SimBrief</span>
            <strong>{{ $simbrief ? __('promethee_briefing.ofp_available') : __('promethee_briefing.ofp_missing') }}</strong>
            <small>{{ $simbrief ? optional($simbrief->updated_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') : __('promethee_briefing.ofp_in_hermes') }}</small>
        </article>
        <article>
            <span>Hermès</span>
            <strong>{{ __('promethee_briefing.flight_companion') }}</strong>
            <small>{{ __('promethee_briefing.hermes_hint') }}</small>
        </article>
    </div>
    <div class="toolbar">
        @unless($bid)
            <form method="post" action="{{ route('promethee.flights.reserve', $flight->id) }}">@csrf<button>{{ __('promethee_briefing.reserve') }}</button></form>
        @endunless
        @if($simbrief)
            <a class="button outline" href="{{ route('frontend.simbrief.briefing', $simbrief->id) }}">{{ __('promethee_briefing.open_ofp') }}</a>
        @endif
        <a class="button" href="{{ route('promethee.downloads.category', 'acars') }}">{{ __('promethee_briefing.open_hermes') }}</a>
    </div>
</section>

<div class="two-columns">
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">{{ __('promethee_briefing.weather_route') }}</span><h2>{{ __('promethee_briefing.available_information') }}</h2></div></div>
        @foreach($weather as $airport => $report)
            <article class="event">
                <h3>{{ $airport }}</h3>
                <p><strong>METAR</strong></p>
                <p class="mono preserve">{{ is_string($report['metar'] ?? null) ? $report['metar'] : __('promethee_briefing.metar_unavailable') }}</p>
                <p><strong>TAF</strong></p>
                <p class="mono preserve">{{ is_string($report['taf'] ?? null) ? $report['taf'] : __('promethee_briefing.taf_unavailable') }}</p>
            </article>
        @endforeach
        @if($weather === [])
            <p class="empty">{{ __('promethee_briefing.weather_unavailable') }}</p>
        @endif
        @if($flight->route)
            <article class="event"><h3>{{ __('promethee_briefing.published_route') }}</h3><p class="mono preserve">{{ $flight->route }}</p></article>
        @endif
        <p class="muted">{{ __('promethee_briefing.external_services') }}</p>
    </section>

    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">{{ __('promethee_briefing.my_folder') }}</span><h2>{{ __('promethee_briefing.ofp_decisions') }}</h2></div></div>
        <form method="post" action="{{ route('promethee.flights.briefing.save', $flight->id) }}" class="form-grid">
            @csrf
            <label>{{ __('promethee_briefing.ofp_reference') }}<input name="ofp_reference" value="{{ old('ofp_reference', $briefing->ofp_reference ?? $simbrief?->id ?? '') }}" placeholder="SB-…"></label>
            <label>{{ __('promethee_briefing.selected_alternate') }}<input name="alternate" maxlength="8" value="{{ old('alternate', $briefing->alternate ?? $flight->alt_airport_id) }}"></label>
            <label>{{ __('promethee_briefing.planned_fuel', ['unit' => strtoupper($fuelUnit)]) }}<input name="planned_fuel" type="number" min="0" value="{{ old('planned_fuel', $briefing->planned_fuel ?? $suggestedFuel) }}"></label>
            <label class="full">{{ __('promethee_briefing.pilot_notes') }}<textarea name="notes" rows="7" placeholder="{{ __('promethee_briefing.notes_placeholder') }}">{{ old('notes', $briefing->notes ?? '') }}</textarea></label>
            <button>{{ __('promethee_briefing.save') }}</button>
        </form>
    </section>
</div>
@endsection
