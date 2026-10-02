<?php

namespace Tests;

use Illuminate\Support\Facades\DB;
use Modules\Promethee\Services\AircraftConfigurationResolver;

final class PrometheeAircraftConfigurationTest extends TestCase
{
    public function test_resolver_applies_type_variant_configuration_and_registration_precedence(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1);
        $aircraft = $fleet['aircraft']->first();
        $aircraft->update(['icao' => 'A300', 'name' => 'Airbus A300']);

        $variantId = DB::table('promethee_aircraft_variants')->insertGetId([
            'aircraft_type_key' => 'A300',
            'name' => 'A300B2',
            'short_name' => 'A300B2',
            'icao_type' => 'A300',
            'max_pax' => 260,
            'mtow' => 150000,
            'active' => true,
            'historical_confidence' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $configurationId = DB::table('promethee_airframe_configurations')->insertGetId([
            'variant_id' => $variantId,
            'code' => 'A300_ITF_HD',
            'name' => 'Air Inter haute densité',
            'kind' => 'historical',
            'max_pax' => 280,
            'active' => true,
            'historical_confidence' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('promethee_aircraft_configuration_assignments')->insert([
            'aircraft_id' => $aircraft->id,
            'variant_id' => $variantId,
            'configuration_id' => $configurationId,
            'active_for_va' => true,
            'overrides' => json_encode(['max_pax' => 275]),
            'historical_confidence' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resolved = app(AircraftConfigurationResolver::class)->resolve($aircraft->fresh('subfleet'));

        $this->assertSame('A300B2', $resolved['variant']['code']);
        $this->assertSame('A300_ITF_HD', $resolved['configuration']['code']);
        $this->assertSame(275, (int) $resolved['resolved']['max_pax']);
        $this->assertSame('registration_override', $resolved['provenance']['max_pax']);
    }

    public function test_resolver_selects_historical_configuration_by_date(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1);
        $aircraft = $fleet['aircraft']->first();
        $aircraft->update(['icao' => 'N262', 'name' => 'Nord 262']);

        $variantId = DB::table('promethee_aircraft_variants')->insertGetId([
            'aircraft_type_key' => 'N262',
            'name' => 'Nord 262A',
            'short_name' => 'N262A',
            'icao_type' => 'N262',
            'active' => true,
            'historical_confidence' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $originalId = DB::table('promethee_airframe_configurations')->insertGetId([
            'variant_id' => $variantId,
            'code' => 'N262_ORIGINAL',
            'name' => 'Configuration originale',
            'kind' => 'historical',
            'max_pax' => 24,
            'active' => true,
            'historical_confidence' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $modernId = DB::table('promethee_airframe_configurations')->insertGetId([
            'variant_id' => $variantId,
            'code' => 'N262_MODERN',
            'name' => 'Configuration modernisée',
            'kind' => 'historical',
            'max_pax' => 26,
            'simbrief_strategy' => 'proxy',
            'simbrief_proxy_type' => 'SH33',
            'active' => true,
            'historical_confidence' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('promethee_aircraft_configuration_assignments')->insert([
            [
                'aircraft_id' => $aircraft->id,
                'variant_id' => $variantId,
                'configuration_id' => $originalId,
                'valid_from' => '1969-01-01',
                'valid_until' => '1975-12-31',
                'active_for_va' => false,
                'historical_confidence' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'aircraft_id' => $aircraft->id,
                'variant_id' => $variantId,
                'configuration_id' => $modernId,
                'valid_from' => '1976-01-01',
                'valid_until' => null,
                'active_for_va' => true,
                'historical_confidence' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $resolver = app(AircraftConfigurationResolver::class);
        $old = $resolver->resolve($aircraft->fresh('subfleet'), '1974-06-12', false);
        $new = $resolver->resolve($aircraft->fresh('subfleet'), '1978-06-12', false);

        $this->assertSame('N262_ORIGINAL', $old['configuration']['code']);
        $this->assertSame(24, (int) $old['resolved']['max_pax']);
        $this->assertSame('N262_MODERN', $new['configuration']['code']);
        $this->assertSame(26, (int) $new['resolved']['max_pax']);
        $this->assertSame('proxy', $new['simbrief']['strategy']);
        $this->assertSame('SH33', $new['simbrief']['effective_type']);
    }

    public function test_real_variant_can_expose_multiple_simulator_profiles_without_changing_identity(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1);
        $aircraft = $fleet['aircraft']->first();
        $aircraft->update(['icao' => 'A319', 'name' => 'Airbus A319']);

        $variantId = DB::table('promethee_aircraft_variants')->insertGetId([
            'aircraft_type_key' => 'A319',
            'name' => 'A319-111',
            'short_name' => 'A319-111',
            'icao_type' => 'A319',
            'active' => true,
            'historical_confidence' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('promethee_aircraft_configuration_assignments')->insert([
            'aircraft_id' => $aircraft->id,
            'variant_id' => $variantId,
            'active_for_va' => true,
            'historical_confidence' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach (['Fenix A319', 'Addon X'] as $addon) {
            DB::table('promethee_aircraft_simulator_profiles')->insert([
                'variant_id' => $variantId,
                'simulator' => 'msfs2024',
                'addon_name' => $addon,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $resolved = app(AircraftConfigurationResolver::class)->resolve($aircraft->fresh('subfleet'));

        $this->assertSame('A319-111', $resolved['variant']['code']);
        $this->assertCount(2, $resolved['simulator_profiles']);
    }
}
