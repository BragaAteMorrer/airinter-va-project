<?php

namespace Tests;

use App\Models\Airport;
use App\Services\AirportService;
use App\Support\HttpClient;
use App\Support\Metar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery;
use Modules\Promethee\Services\OperationalWeatherService;

final class OperationalWeatherServiceTest extends TestCase
{
    public function test_recommended_runway_prefers_the_runway_facing_the_wind(): void
    {
        DB::table('disposable_runways')->where('airport_id', 'TST1')->delete();
        DB::table('disposable_runways')->insert([
            [
                'airport_id' => 'TST1',
                'runway_ident' => '24',
                'lat' => '48.0000',
                'lon' => '2.0000',
                'heading' => '240',
                'length' => '2800',
                'ils_freq' => '110.30',
                'loc_course' => '240',
                'airac' => '2610',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'airport_id' => 'TST1',
                'runway_ident' => '06',
                'lat' => '48.0000',
                'lon' => '2.0000',
                'heading' => '060',
                'length' => '2800',
                'ils_freq' => null,
                'loc_course' => null,
                'airac' => '2610',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $service = new OperationalWeatherService(
            Mockery::mock(AirportService::class),
            Mockery::mock(HttpClient::class)
        );

        $runway = $service->recommendedRunway('TST1', 250, 16, 24, false);

        $this->assertNotNull($runway);
        $this->assertSame('24', $runway['ident']);
        $this->assertGreaterThan(15, $runway['headwind_kt']);
        $this->assertLessThan(4, $runway['crosswind_kt']);
        $this->assertSame('WIND_ONLY', $runway['confidence']);
        $this->assertTrue($runway['advisory']);
    }

    public function test_variable_or_calm_wind_never_invents_a_runway(): void
    {
        $service = new OperationalWeatherService(
            Mockery::mock(AirportService::class),
            Mockery::mock(HttpClient::class)
        );

        $this->assertNull($service->recommendedRunway('TST1', 250, 1, null, false));
        $this->assertNull($service->recommendedRunway('TST1', 250, 16, null, true));
        $this->assertNull($service->recommendedRunway('TST1', null, 16, null, false));
    }

    public function test_route_weather_combines_metar_taf_and_only_nearby_sigmet_features(): void
    {
        Cache::forget('promethee.weather.sigmet.geojson.v1');

        $airports = Mockery::mock(AirportService::class);
        $airports->shouldReceive('getMetar')->twice()->andReturnUsing(
            fn (string $icao) => new Metar($icao.' 051900Z 25012G20KT 9999 SCT020 16/10 Q1015')
        );
        $airports->shouldReceive('getTaf')->twice()->andReturnUsing(
            fn (string $icao) => new Metar('TAF '.$icao.' 051700Z 0518/0624 25012KT 9999 SCT020', true)
        );

        $http = Mockery::mock(HttpClient::class);
        $http->shouldReceive('get')->once()->andReturn([
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'seriesId' => 'A1',
                        'firId' => 'LFFF',
                        'hazard' => 'TURB',
                        'validTimeFrom' => '2026-10-05T18:00:00Z',
                        'validTimeTo' => '2026-10-05T22:00:00Z',
                        'rawSigmet' => 'LFFF SIGMET 1 VALID 051800/052200 SEV TURB',
                    ],
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [[[1.0, 47.0], [4.0, 47.0], [4.0, 50.0], [1.0, 50.0], [1.0, 47.0]]],
                    ],
                ],
                [
                    'type' => 'Feature',
                    'properties' => ['seriesId' => 'FAR', 'hazard' => 'TS'],
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [[[-110.0, 35.0], [-100.0, 35.0], [-100.0, 40.0], [-110.0, 40.0], [-110.0, 35.0]]],
                    ],
                ],
            ],
        ]);

        $departure = new Airport();
        $departure->lat = 48.72;
        $departure->lon = 2.36;
        $arrival = new Airport();
        $arrival->lat = 50.56;
        $arrival->lon = 3.09;

        $weather = (new OperationalWeatherService($airports, $http))
            ->forAirports('LFPO', 'LFQQ', null, [$departure, $arrival]);

        $this->assertSame('AVAILABLE', $weather['status']);
        $this->assertNotEmpty($weather['stations']['departure']['metar']['category']);
        $this->assertSame(250.0, (float) $weather['stations']['departure']['metar']['wind']['direction']);
        $this->assertSame('LFQQ', $weather['stations']['arrival']['icao']);
        $this->assertSame('AVAILABLE', $weather['sigmet_status']);
        $this->assertCount(1, $weather['sigmets']);
        $this->assertSame('TURB', $weather['sigmets'][0]['hazard']);
        $this->assertSame(1, $weather['summary']['sigmet_count']);
    }
}
