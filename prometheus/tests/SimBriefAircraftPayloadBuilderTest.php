<?php

namespace Tests;

use Modules\Promethee\Services\SimBriefAircraftPayloadBuilder;

final class SimBriefAircraftPayloadBuilderTest extends TestCase
{
    public function test_proxy_builds_simbrief_type_and_acdata_without_changing_real_identity(): void
    {
        $builder = app(SimBriefAircraftPayloadBuilder::class);

        $payload = $builder->build([
            'strategy' => 'proxy',
            'type' => 'SH33',
            'actual_icao' => 'N262',
            'actual_name' => 'NORD 262',
            'config' => [
                'engines' => 'BASTAN VIC',
                'maxpax' => 29,
                'ceiling' => 24000,
                'weights_kg' => [
                    'oew' => 6654,
                    'mtow' => 10300,
                ],
                'performance' => [
                    'fuelfactor' => 'P00',
                    'climb' => 'AUTO',
                ],
            ],
        ]);

        $this->assertSame('SH33', $payload['parameters']['type']);
        $this->assertSame('P00', $payload['parameters']['fuelfactor']);
        $this->assertSame('AUTO', $payload['parameters']['climb']);

        $acdata = json_decode($payload['parameters']['acdata'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('N262', $acdata['icao']);
        $this->assertSame('NORD 262', $acdata['name']);
        $this->assertSame('BASTAN VIC', $acdata['engines']);
        $this->assertSame(29, $acdata['maxpax']);
        $this->assertSame(14.67, $acdata['oew']);
        $this->assertSame(22.708, $acdata['mtow']);
    }

    public function test_native_profile_does_not_emit_acdata(): void
    {
        $builder = app(SimBriefAircraftPayloadBuilder::class);

        $payload = $builder->build([
            'strategy' => 'native',
            'type' => 'A319',
            'actual_icao' => 'A319',
            'actual_name' => 'Airbus A319',
            'config' => [],
        ]);

        $this->assertSame(['type' => 'A319'], $payload['parameters']);
        $this->assertSame([], $payload['acdata']);
    }

    public function test_partial_icao_equipment_group_is_rejected(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        app(SimBriefAircraftPayloadBuilder::class)->build([
            'strategy' => 'proxy',
            'type' => 'SH33',
            'actual_icao' => 'N262',
            'actual_name' => 'NORD 262',
            'config' => [
                'cat' => 'M',
                'equip' => 'S',
                'weights_kg' => [],
                'performance' => [],
            ],
        ]);
    }
}
