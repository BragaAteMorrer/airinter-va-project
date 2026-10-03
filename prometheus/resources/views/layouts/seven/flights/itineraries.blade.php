@if (!empty($itinerary_search_requested) && $flights->total() === 0)
    <div class="mb-4">
        @if ($itineraries->isNotEmpty())
            <div class="alert alert-info">
                <strong>@lang('flights.no_direct_flight')</strong>
                {{ __('flights.alternative_explanation', ['count' => $max_itinerary_stops]) }}
            </div>

            <h4 class="mb-3">@lang('flights.alternative_itineraries')</h4>

            @foreach ($itineraries as $itinerary)
                <div class="card mb-3">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <strong>{{ implode(' → ', $itinerary['airports']) }}</strong>
                        <span class="badge bg-secondary">
                            {{ trans_choice('flights.stopover', $itinerary['stops'], ['count' => $itinerary['stops']]) }}
                        </span>
                    </div>
                    <div class="card-body">
                        @foreach ($itinerary['legs'] as $index => $leg)
                            <div class="d-flex flex-wrap justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="me-3">
                                    <div class="fw-semibold">
                                        {{ __('flights.segment', ['count' => $index + 1]) }}
                                        · {{ $leg->ident }}
                                    </div>
                                    <div>
                                        <strong>{{ $leg->dpt_airport_id }}</strong>
                                        → <strong>{{ $leg->arr_airport_id }}</strong>
                                        @if ($leg->dpt_time || $leg->arr_time)
                                            <span class="text-muted">
                                                · {{ $leg->dpt_time ?: '—' }} / {{ $leg->arr_time ?: '—' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if ($leg->flight_time)
                                        <span>@minutestotime($leg->flight_time)</span>
                                    @endif
                                    @if ($leg->flight_time && $leg->distance)
                                        <span> · </span>
                                    @endif
                                    @if ($leg->distance)
                                        <span>{{ $leg->distance }} nm</span>
                                    @endif
                                    @if (isset($saved[$leg->id]))
                                        <div><span class="badge bg-success">@lang('flights.already_reserved')</span></div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                            <div class="text-muted">
                                @if ($itinerary['flight_time'] > 0)
                                    @minutestotime($itinerary['flight_time'])
                                @endif
                                @if ($itinerary['flight_time'] > 0 && $itinerary['distance'] > 0)
                                    ·
                                @endif
                                @if ($itinerary['distance'] > 0)
                                    {{ round($itinerary['distance']) }} nm
                                @endif
                            </div>

                            @if (setting('bids.allow_multiple_bids') === false)
                                <span class="text-warning">@lang('flights.multiple_bids_required')</span>
                            @else
                                <form method="post" action="{{ route('promethee.flights.itineraries.reserve') }}" class="mb-0">
                                    @csrf
                                    <input type="hidden" name="dep_icao" value="{{ request()->get('dep_icao') }}">
                                    <input type="hidden" name="arr_icao" value="{{ request()->get('arr_icao') }}">
                                    @foreach ($itinerary['legs'] as $leg)
                                        <input type="hidden" name="flight_ids[]" value="{{ $leg->id }}">
                                    @endforeach
                                    <button type="submit" class="btn btn-success">
                                        {{ trans_choice('flights.reserve_itinerary', count($itinerary['legs']), ['count' => count($itinerary['legs'])]) }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-warning">
                <strong>@lang('flights.no_direct_flight')</strong>
                {{ __('flights.no_alternative_itinerary', ['count' => $max_itinerary_stops]) }}
            </div>
        @endif
    </div>
@endif
