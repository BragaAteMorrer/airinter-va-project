@php
    $wxStations = collect($weather['stations'] ?? [])->filter(fn ($station) => is_array($station));
    $wxLabels = ['departure' => 'Départ', 'arrival' => 'Destination', 'alternate' => 'Dégagement'];
    $wxSummary = $weather['summary'] ?? [];
    $wxSigmets = $weather['sigmets'] ?? [];
@endphp

@if($wxStations->isEmpty())
    <p class="empty">Aucune donnée météo opérationnelle disponible pour le moment.</p>
@else
    <div class="control-strip">
        <article><span>Situation la plus restrictive</span><strong>{{ $wxSummary['worst_category'] ?? '—' }}</strong><small>selon les METAR disponibles</small></article>
        <article><span>SIGMET corridor</span><strong>{{ (int) ($wxSummary['sigmet_count'] ?? count($wxSigmets)) }}</strong><small>{{ ($weather['sigmet_status'] ?? 'UNAVAILABLE') === 'AVAILABLE' ? 'flux AviationWeather.gov' : 'flux temporairement indisponible' }}</small></article>
        <article><span>Piste probable arrivée</span><strong>{{ $wxSummary['arrival_runway'] ?? '—' }}</strong><small>vent METAR uniquement</small></article>
    </div>

    <div class="route-list">
        @foreach($wxStations as $role => $station)
            @php
                $metar = $station['metar'] ?? [];
                $wind = $metar['wind'] ?? [];
                $runway = $station['recommended_runway'] ?? null;
                $windDirection = ($wind['variable'] ?? false)
                    ? 'VRB'
                    : (($wind['direction'] ?? null) !== null ? sprintf('%03d°', (int) round($wind['direction'])) : '—');
                $windSpeed = ($wind['speed_kt'] ?? null) !== null ? number_format((float) $wind['speed_kt'], 0, ',', ' ').' kt' : '—';
                $windGust = ($wind['gust_kt'] ?? null) !== null ? ' G'.number_format((float) $wind['gust_kt'], 0, ',', ' ').' kt' : '';
            @endphp
            <article class="event">
                <h3>{{ $wxLabels[$role] ?? ucfirst((string) $role) }} · {{ $station['icao'] ?? '—' }}</h3>
                <p><strong>{{ $metar['category'] ?? 'N/D' }}</strong> · Vent {{ $windDirection }} {{ $windSpeed }}{{ $windGust }}</p>
                <p>
                    Visibilité {{ ($metar['visibility_km'] ?? null) !== null ? number_format((float) $metar['visibility_km'], 1, ',', ' ').' km' : '—' }}
                    · plafond {{ ($metar['ceiling_ft'] ?? null) !== null ? number_format((float) $metar['ceiling_ft'], 0, ',', ' ').' ft' : '—' }}
                    · QNH {{ ($metar['qnh_hpa'] ?? null) !== null ? number_format((float) $metar['qnh_hpa'], 0, ',', ' ').' hPa' : '—' }}
                </p>
                <p>
                    <strong>Piste probable :</strong>
                    @if($runway)
                        {{ $runway['ident'] }} · cap {{ sprintf('%03d°', (int) $runway['heading']) }}
                        · vent de face {{ number_format((float) $runway['headwind_kt'], 1, ',', ' ') }} kt
                        · travers {{ number_format((float) $runway['crosswind_kt'], 1, ',', ' ') }} kt
                    @else
                        non déterminée (vent calme/variable ou données piste absentes)
                    @endif
                </p>
                @if(!empty($metar['phenomena']) || !empty($metar['wind_shear_all_runways']) || !empty($metar['wind_shear_runways']))
                    <p><strong>Attention :</strong> {{ $metar['phenomena'] ?: 'cisaillement signalé' }}
                        @if(!empty($metar['wind_shear_all_runways'])) · WS toutes pistes @endif
                    </p>
                @endif
                <details>
                    <summary>METAR / TAF bruts</summary>
                    <p><strong>METAR</strong></p>
                    <p class="mono preserve">{{ $metar['raw'] ?? 'METAR indisponible.' }}</p>
                    <p><strong>TAF</strong></p>
                    <p class="mono preserve">{{ $station['taf']['raw'] ?? 'TAF indisponible.' }}</p>
                </details>
            </article>
        @endforeach
    </div>

    @if($wxSigmets)
        <h3>SIGMET recoupant le corridor</h3>
        <div class="route-list">
            @foreach($wxSigmets as $sigmet)
                <article class="event">
                    <h3>{{ $sigmet['hazard'] ?: 'SIGMET' }}{{ $sigmet['fir'] ? ' · '.$sigmet['fir'] : '' }}</h3>
                    <p>{{ $sigmet['valid_from'] ?: 'Validité non renseignée' }} → {{ $sigmet['valid_to'] ?: '—' }}</p>
                    @if($sigmet['raw'])<p class="mono preserve">{{ $sigmet['raw'] }}</p>@endif
                </article>
            @endforeach
        </div>
    @elseif(($weather['sigmet_status'] ?? 'UNAVAILABLE') === 'AVAILABLE')
        <p class="muted">Aucun SIGMET du flux courant ne recoupe le corridor élargi de la liaison.</p>
    @endif

    <p class="muted">{{ $weather['note'] ?? 'Information météo indicative.' }}</p>
@endif
