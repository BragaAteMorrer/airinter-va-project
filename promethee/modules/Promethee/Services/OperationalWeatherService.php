<?php

namespace Modules\Promethee\Services;

use App\Contracts\Unit;
use App\Models\Airport;
use App\Models\Flight;
use App\Services\AirportService;
use App\Support\HttpClient;
use App\Support\Metar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * One operational weather truth for Prométhée, Hermès, Dispatch and the MSFS 2024 EFB.
 *
 * METAR/TAF remain backed by phpVMS' configured AviationWeather provider. SIGMETs
 * are fetched as GeoJSON from AviationWeather.gov and filtered locally against a
 * deliberately broad route corridor. Runway advice is wind-only and informational:
 * it is never a take-off/landing clearance and never blocks Hermès readiness.
 */
class OperationalWeatherService
{
    public const CONTRACT_VERSION = '1.0';
    private const SIGMET_URL = 'https://aviationweather.gov/api/data/sigmet?format=geojson';
    private const SIGMET_CACHE_KEY = 'promethee.weather.sigmet.geojson.v1';
    private const ROUTE_MARGIN_DEGREES = 4.0;

    public function __construct(
        private readonly AirportService $airports,
        private readonly HttpClient $http
    ) {}

    public function forFlight(Flight $flight, ?string $alternate = null): array
    {
        $flight->loadMissing(['dpt_airport', 'arr_airport', 'alt_airport']);

        $departure = strtoupper(trim((string) $flight->dpt_airport_id));
        $arrival = strtoupper(trim((string) $flight->arr_airport_id));
        $alternate = strtoupper(trim((string) ($alternate ?: $flight->alt_airport_id)));

        return $this->forAirports($departure, $arrival, $alternate ?: null, [
            $flight->dpt_airport,
            $flight->arr_airport,
            $alternate && $alternate !== strtoupper((string) $flight->alt_airport_id)
                ? Airport::find($alternate)
                : $flight->alt_airport,
        ]);
    }

    /**
     * @param array<int, Airport|null> $routeAirports
     */
    public function forAirports(
        string $departure,
        string $arrival,
        ?string $alternate = null,
        array $routeAirports = []
    ): array {
        $departure = strtoupper(trim($departure));
        $arrival = strtoupper(trim($arrival));
        $alternate = $alternate ? strtoupper(trim($alternate)) : null;

        $stations = [
            'departure' => $this->station($departure),
            'arrival' => $this->station($arrival),
            'alternate' => $alternate ? $this->station($alternate) : null,
        ];

        if ($routeAirports === []) {
            $routeAirports = collect([$departure, $arrival, $alternate])
                ->filter()
                ->unique()
                ->map(fn (string $icao) => Airport::find($icao))
                ->all();
        }

        [$sigmets, $sigmetStatus] = $this->routeSigmets($routeAirports);
        $availableStations = collect($stations)->filter(fn ($station) => is_array($station) && !empty($station['metar']['raw']));
        $categories = $availableStations->pluck('metar.category')->filter()->map(fn ($value) => strtoupper((string) $value));
        $worstCategory = $this->worstCategory($categories->all());

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'provider' => 'AviationWeather.gov',
            'status' => $availableStations->isNotEmpty() ? 'AVAILABLE' : 'DEGRADED',
            'generated_at' => now()->toIso8601String(),
            'stations' => $stations,
            'sigmet_status' => $sigmetStatus,
            'sigmets' => $sigmets,
            'summary' => [
                'worst_category' => $worstCategory,
                'sigmet_count' => count($sigmets),
                'departure_runway' => $stations['departure']['recommended_runway']['ident'] ?? null,
                'arrival_runway' => $stations['arrival']['recommended_runway']['ident'] ?? null,
                'alternate_runway' => $stations['alternate']['recommended_runway']['ident'] ?? null,
            ],
            'note' => 'Météo opérationnelle indicative. Les pistes proposées sont calculées uniquement à partir du vent METAR et ne remplacent ni ATIS, ni NOTAM, ni instruction ATC.',
        ];
    }

    public function station(string $icao): ?array
    {
        $icao = strtoupper(trim($icao));
        if ($icao === '') return null;

        $metar = null;
        $taf = null;
        try { $metar = $this->airports->getMetar($icao); } catch (Throwable) {}
        try { $taf = $this->airports->getTaf($icao); } catch (Throwable) {}

        $windDirection = $this->number($metar?->offsetExists('wind_direction') ? $metar['wind_direction'] : null);
        $windVariable = (bool) ($metar?->offsetExists('wind_direction_varies') ? $metar['wind_direction_varies'] : false);
        $windSpeed = $this->unit($metar?->offsetExists('wind_speed') ? $metar['wind_speed'] : null, 'knots');
        $windGust = $this->unit($metar?->offsetExists('wind_gust_speed') ? $metar['wind_gust_speed'] : null, 'knots');

        return [
            'icao' => $icao,
            'available' => $metar !== null || $taf !== null,
            'metar' => [
                'raw' => $metar?->raw,
                'category' => $this->text($metar, 'category'),
                'observed_time' => $this->text($metar, 'observed_time'),
                'observed_age' => $this->text($metar, 'observed_age'),
                'wind' => [
                    'direction' => $windDirection,
                    'variable' => $windVariable,
                    'speed_kt' => $windSpeed,
                    'gust_kt' => $windGust,
                ],
                'visibility_km' => $this->unit($metar?->offsetExists('visibility') ? $metar['visibility'] : null, 'km'),
                'ceiling_ft' => $this->unit($metar?->offsetExists('cloud_height') ? $metar['cloud_height'] : null, 'ft'),
                'temperature_c' => $this->unit($metar?->offsetExists('temperature') ? $metar['temperature'] : null, 'C'),
                'dewpoint_c' => $this->unit($metar?->offsetExists('dew_point') ? $metar['dew_point'] : null, 'C'),
                'qnh_hpa' => $this->unit($metar?->offsetExists('barometer') ? $metar['barometer'] : null, 'hPa'),
                'phenomena' => $this->text($metar, 'present_weather_report'),
                'clouds' => $this->text($metar, 'clouds_report_ft'),
                'rvr' => $metar?->offsetExists('runways_visual_range') ? ($metar['runways_visual_range'] ?: []) : [],
                'wind_shear_all_runways' => (bool) ($metar?->offsetExists('wind_shear_all_runways') ? $metar['wind_shear_all_runways'] : false),
                'wind_shear_runways' => $metar?->offsetExists('wind_shear_runways') ? ($metar['wind_shear_runways'] ?: []) : [],
            ],
            'taf' => [
                'raw' => $taf?->raw,
            ],
            'recommended_runway' => $this->recommendedRunway($icao, $windDirection, $windSpeed, $windGust, $windVariable),
            'atis' => null,
        ];
    }

    public function recommendedRunway(
        string $icao,
        ?float $windDirection,
        ?float $windSpeed,
        ?float $windGust = null,
        bool $variable = false
    ): ?array {
        if ($variable || $windDirection === null || $windSpeed === null || $windSpeed < 2) return null;
        if (!Schema::hasTable('disposable_runways')) return null;

        $runways = DB::table('disposable_runways')
            ->whereRaw('UPPER(airport_id) = ?', [strtoupper($icao)])
            ->get(['runway_ident', 'heading', 'length', 'ils_freq', 'loc_course']);

        if ($runways->isEmpty()) return null;

        $best = $runways
            ->map(function ($runway) use ($windDirection, $windSpeed, $windGust) {
                if (!is_numeric($runway->heading)) return null;
                $heading = fmod(((float) $runway->heading + 360.0), 360.0);
                $angle = abs(fmod(($heading - $windDirection + 540.0), 360.0) - 180.0);
                $radians = deg2rad($angle);
                $headwind = $windSpeed * cos($radians);
                $crosswind = abs($windSpeed * sin($radians));
                $gustHeadwind = $windGust !== null ? $windGust * cos($radians) : null;

                return [
                    'ident' => (string) $runway->runway_ident,
                    'heading' => round($heading),
                    'length_m' => is_numeric($runway->length) ? (int) $runway->length : null,
                    'ils_freq' => $runway->ils_freq ?: null,
                    'loc_course' => is_numeric($runway->loc_course) ? (int) $runway->loc_course : null,
                    'angle_to_wind' => round($angle, 1),
                    'headwind_kt' => round($headwind, 1),
                    'crosswind_kt' => round($crosswind, 1),
                    'gust_headwind_kt' => $gustHeadwind === null ? null : round($gustHeadwind, 1),
                ];
            })
            ->filter()
            ->sortBy([
                ['angle_to_wind', 'asc'],
                ['crosswind_kt', 'asc'],
            ])
            ->first();

        if (!$best) return null;

        return $best + [
            'confidence' => 'WIND_ONLY',
            'advisory' => true,
            'label' => 'Piste probable selon le vent METAR',
        ];
    }

    /**
     * @param array<int, Airport|null> $airports
     * @return array{0: array<int, array<string, mixed>>, 1: string}
     */
    private function routeSigmets(array $airports): array
    {
        $points = collect($airports)
            ->filter(fn ($airport) => $airport instanceof Airport && is_numeric($airport->lat) && is_numeric($airport->lon))
            ->map(fn (Airport $airport) => [(float) $airport->lat, (float) $airport->lon])
            ->values();

        if ($points->count() < 2) return [[], 'UNAVAILABLE'];

        $routeBounds = [
            'min_lat' => $points->min(fn ($p) => $p[0]) - self::ROUTE_MARGIN_DEGREES,
            'max_lat' => $points->max(fn ($p) => $p[0]) + self::ROUTE_MARGIN_DEGREES,
            'min_lon' => $points->min(fn ($p) => $p[1]) - self::ROUTE_MARGIN_DEGREES,
            'max_lon' => $points->max(fn ($p) => $p[1]) + self::ROUTE_MARGIN_DEGREES,
        ];

        try {
            $feed = Cache::remember(self::SIGMET_CACHE_KEY, now()->addMinutes(5), function () {
                $response = $this->http->get(self::SIGMET_URL, [
                    'timeout' => 4,
                    'headers' => ['User-Agent' => 'AirInterVA-Promethee/1.0 weather-ops'],
                ]);
                if (is_string($response)) {
                    $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
                }
                return is_array($response) ? $response : [];
            });
        } catch (Throwable) {
            return [[], 'UNAVAILABLE'];
        }

        $features = is_array($feed['features'] ?? null) ? $feed['features'] : [];
        $result = [];
        foreach ($features as $feature) {
            if (!is_array($feature)) continue;
            $bounds = $this->geometryBounds($feature['geometry']['coordinates'] ?? null);
            if ($bounds === null || !$this->boundsIntersect($routeBounds, $bounds)) continue;

            $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            $raw = $this->firstText($properties, ['rawSigmet', 'rawText', 'raw', 'text', 'sigmet']);
            $hazard = $this->firstText($properties, ['hazard', 'hazardType', 'phenomenon', 'phenom']);
            $result[] = [
                'id' => $this->firstText($properties, ['id', 'seriesId', 'sigmetId']) ?: ($feature['id'] ?? null),
                'fir' => $this->firstText($properties, ['firId', 'fir', 'firName']),
                'hazard' => $hazard,
                'valid_from' => $this->firstText($properties, ['validTimeFrom', 'valid_from', 'validFrom']),
                'valid_to' => $this->firstText($properties, ['validTimeTo', 'valid_to', 'validTo']),
                'raw' => $raw,
            ];
            if (count($result) >= 12) break;
        }

        return [$result, 'AVAILABLE'];
    }

    private function geometryBounds(mixed $coordinates): ?array
    {
        $points = [];
        $this->collectCoordinates($coordinates, $points);
        if ($points === []) return null;

        $lats = array_column($points, 1);
        $lons = array_column($points, 0);

        return [
            'min_lat' => min($lats),
            'max_lat' => max($lats),
            'min_lon' => min($lons),
            'max_lon' => max($lons),
        ];
    }

    private function collectCoordinates(mixed $value, array &$points): void
    {
        if (!is_array($value)) return;
        if (count($value) >= 2 && is_numeric($value[0]) && is_numeric($value[1])) {
            $points[] = [(float) $value[0], (float) $value[1]];
            return;
        }
        foreach ($value as $child) $this->collectCoordinates($child, $points);
    }

    private function boundsIntersect(array $a, array $b): bool
    {
        return !(
            $a['max_lat'] < $b['min_lat']
            || $a['min_lat'] > $b['max_lat']
            || $a['max_lon'] < $b['min_lon']
            || $a['min_lon'] > $b['max_lon']
        );
    }

    private function worstCategory(array $categories): ?string
    {
        $rank = ['VFR' => 1, 'MVFR' => 2, 'IFR' => 3, 'LIFR' => 4];
        $worst = null;
        $score = 0;
        foreach ($categories as $category) {
            $category = strtoupper((string) $category);
            $value = $rank[$category] ?? 0;
            if ($value > $score) {
                $worst = $category;
                $score = $value;
            }
        }
        return $worst;
    }

    private function text(?Metar $metar, string $key): ?string
    {
        if (!$metar || !$metar->offsetExists($key)) return null;
        $value = $metar[$key];
        if (!is_scalar($value) || trim((string) $value) === '') return null;
        return trim((string) $value);
    }

    private function unit(mixed $value, string $unit): ?float
    {
        if ($value instanceof Unit) {
            try { return round((float) $value->toUnit($unit), 2); } catch (Throwable) { return null; }
        }
        if (is_array($value) && array_key_exists($unit, $value) && is_numeric($value[$unit])) {
            return round((float) $value[$unit], 2);
        }
        return is_numeric($value) ? round((float) $value, 2) : null;
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function firstText(array $values, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $values[$key] ?? null;
            if (is_scalar($value) && trim((string) $value) !== '') return trim((string) $value);
        }
        return null;
    }
}
