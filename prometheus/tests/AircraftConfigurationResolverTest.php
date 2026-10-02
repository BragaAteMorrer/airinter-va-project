<?php

namespace Tests;

use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use Modules\Promethee\Models\AircraftConfigurationAssignment;
use Modules\Promethee\Models\AircraftHistoricalVariant;
use Modules\Promethee\Models\AircraftTypeProfile;
use Modules\Promethee\Models\AirframeConfiguration;
use Modules\Promethee\Services\AircraftConfigurationResolver;

final class AircraftConfigurationResolverTest extends TestCase
{
    public function test_registration_override_wins_and_proxy_keeps_real_identity(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1);
        $aircraft = $fleet['aircraft']->first();
        $aircraft->registration = 'F-N262';
        $aircraft->icao = 'N262';
        $aircraft->simbrief_type = null;
        $aircraft->status = AircraftStatus::ACTIVE;
        $aircraft->state = AircraftState::PARKED;
        $aircraft->save();

        AircraftTypeProfile::query()->create([
            'type_key' => 'N262',
            'name' => 'Nord 262',
            'data' => ['max_pax' => 20, 'mtow' => 10000],
            'historical_confidence' => 'confirmed',
        ]);

        $variant = AircraftHistoricalVariant::query()->create([
            'type_key' => 'N262',
            'code' => 'N262A',
            'name' => 'Nord 262A',
            'data' => ['max_pax' => 24],
            'historical_confidence' => 'confirmed',
            'active' => true,
        ]);

        $configuration = AirframeConfiguration::query()->create([
            'variant_id' => $variant->id,
            'code' => 'ITF_STD_2',
            'name' => 'Air Inter standard 2',
            'configuration_kind' => 'VA_operational',
            'data' => ['max_pax' => 26, 'seat_configuration' => '2+2'],
            'simbrief_strategy' => 'proxy',
            'simbrief_proxy_type' => 'SH33',
            'historical_confidence' => 'VA_configuration',
            'active' => true,
        ]);

        AircraftConfigurationAssignment::query()->create([
            'aircraft_id' => $aircraft->id,
            'variant_id' => $variant->id,
            'configuration_id' => $configuration->id,
            'valid_from' => '1975-01-01',
            'active' => true,
            'overrides' => ['max_pax' => 27, 'oew' => 6500],
            'historical_confidence' => 'VA_configuration',
        ]);

        /** @var AircraftConfigurationResolver $resolver */
        $resolver = app(AircraftConfigurationResolver::class);
        $resolved = $resolver->resolveAircraft($aircraft, '1978-06-12');

        $this->assertSame('N262A', $resolved['variant']['code']);
        $this->assertSame('ITF_STD_2', $resolved['configuration']['code']);
        $this->assertSame(27, $resolved['effective']['max_pax']);
        $this->assertSame(6500, $resolved['effective']['oew']);
        $this->assertSame('proxy', $resolved['simbrief']['strategy']);
        $this->assertSame('SH33', $resolved['simbrief']['value']);
        $this->assertSame('N262', $resolved['simbrief']['actual_aircraft']);
        $this->assertSame('N262A', $resolved['simbrief']['actual_variant']);
    }

    public function test_date_selects_the_configuration_that_was_valid_then(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1);
        $aircraft = $fleet['aircraft']->first();
        $aircraft->registration = 'F-HIST';
        $aircraft->icao = 'A300';
        $aircraft->save();

        AircraftTypeProfile::query()->create([
            'type_key' => 'A300',
            'name' => 'Airbus A300',
            'data' => ['max_pax' => 250],
            'historical_confidence' => 'confirmed',
        ]);

        $variant = AircraftHistoricalVariant::query()->create([
            'type_key' => 'A300',
            'code' => 'A300B2',
            'name' => 'A300B2',
            'data' => ['max_pax' => 260],
            'historical_confidence' => 'confirmed',
            'active' => true,
        ]);

        $phase1 = AirframeConfiguration::query()->create([
            'variant_id' => $variant->id,
            'code' => 'PHASE_1',
            'name' => 'Air Inter Phase 1',
            'configuration_kind' => 'historical',
            'data' => ['max_pax' => 270],
            'historical_confidence' => 'confirmed',
            'active' => true,
        ]);
        $phase2 = AirframeConfiguration::query()->create([
            'variant_id' => $variant->id,
            'code' => 'PHASE_2',
            'name' => 'Air Inter Phase 2',
            'configuration_kind' => 'historical',
            'data' => ['max_pax' => 280],
            'historical_confidence' => 'confirmed',
            'active' => true,
        ]);

        AircraftConfigurationAssignment::query()->create([
            'aircraft_id' => $aircraft->id,
            'variant_id' => $variant->id,
            'configuration_id' => $phase1->id,
            'valid_from' => '1970-01-01',
            'valid_until' => '1975-12-31',
            'active' => true,
            'historical_confidence' => 'confirmed',
        ]);
        AircraftConfigurationAssignment::query()->create([
            'aircraft_id' => $aircraft->id,
            'variant_id' => $variant->id,
            'configuration_id' => $phase2->id,
            'valid_from' => '1976-01-01',
            'active' => true,
            'historical_confidence' => 'confirmed',
        ]);

        /** @var AircraftConfigurationResolver $resolver */
        $resolver = app(AircraftConfigurationResolver::class);

        $old = $resolver->resolveAircraft($aircraft, '1974-06-01');
        $new = $resolver->resolveAircraft($aircraft, '1978-06-01');

        $this->assertSame('PHASE_1', $old['configuration']['code']);
        $this->assertSame(270, $old['effective']['max_pax']);
        $this->assertSame('PHASE_2', $new['configuration']['code']);
        $this->assertSame(280, $new['effective']['max_pax']);
    }

    public function test_unconfigured_aircraft_falls_back_to_phpvms_without_regression(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1);
        $aircraft = $fleet['aircraft']->first();
        $aircraft->registration = 'F-BASE';
        $aircraft->icao = 'A319';
        $aircraft->simbrief_type = 'A319';
        $aircraft->save();

        /** @var AircraftConfigurationResolver $resolver */
        $resolver = app(AircraftConfigurationResolver::class);
        $resolved = $resolver->resolveAircraft($aircraft);

        $this->assertNull($resolved['variant']);
        $this->assertNull($resolved['configuration']);
        $this->assertSame('A319', $resolved['simbrief']['value']);
        $this->assertSame('phpvms', $resolved['simbrief']['source']);
    }
}
