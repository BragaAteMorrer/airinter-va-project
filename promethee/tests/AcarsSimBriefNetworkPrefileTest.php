<?php

namespace Tests;

use Modules\Promethee\Http\Api\AcarsSimBriefController;
use ReflectionMethod;

final class AcarsSimBriefNetworkPrefileTest extends TestCase
{
    public function test_vatsim_prefile_carries_simbrief_fuel_endurance(): void
    {
        $xml = simplexml_load_file(base_path('tests/data/simbrief/acars_briefing.xml'));
        $this->assertNotFalse($xml);

        $controller = app(AcarsSimBriefController::class);
        $method = new ReflectionMethod($controller, 'networkPrefiles');
        $method->setAccessible(true);

        $prefiles = $method->invoke($controller, $xml);

        // Fixture endurance is 10,496 seconds => 02:54. myVATSIM needs this
        // separately because SimBrief flightplan_text does not include item 19.
        $this->assertSame('0254', $prefiles['vatsim']['fuel_time']);
        $this->assertStringContainsString('fuel_time=0254', $prefiles['vatsim']['url']);
        $this->assertStringContainsString('raw=', $prefiles['vatsim']['url']);
    }
}
