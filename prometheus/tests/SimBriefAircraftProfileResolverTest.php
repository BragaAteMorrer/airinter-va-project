<?php

namespace Tests;

use App\Models\Aircraft;
use Modules\Promethee\Services\SimBriefAircraftProfileResolver;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class SimBriefAircraftProfileResolverTest extends TestCase
{
    public function test_proxy_without_base_type_is_rejected_cleanly(): void
    {
        $aircraft = new Aircraft();
        $aircraft->icao = 'N262';
        $aircraft->name = 'Nord 262';
        $aircraft->registration = 'F-BNAA';

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('aucun type proxy SimBrief');

        app(SimBriefAircraftProfileResolver::class)->resolve($aircraft, [
            'simbrief_profile' => [
                'strategy' => 'proxy',
                'icao' => 'N262',
                'name' => 'NORD 262',
                'proxy_type' => null,
            ],
        ]);
    }

    public function test_custom_airframe_requires_an_internal_id(): void
    {
        $aircraft = new Aircraft();
        $aircraft->icao = 'A319';
        $aircraft->name = 'Airbus A319';
        $aircraft->registration = 'F-GPMA';

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Internal ID SimBrief est vide');

        app(SimBriefAircraftProfileResolver::class)->resolve($aircraft, [
            'simbrief_profile' => [
                'strategy' => 'custom_airframe',
                'icao' => 'A319',
                'name' => 'Fenix A319',
                'internal_id' => null,
            ],
        ]);
    }
}
