<?php

namespace Tests;

use Illuminate\Support\Facades\DB;
use Modules\Promethee\Services\EngineMaintenanceService;

final class PrometheeEngineMaintenanceTest extends TestCase
{
    public function test_reference_profiles_use_itva_categories_instead_of_real_tbo(): void
    {
        $references = (array) config('promethee.engine-profiles', []);

        $this->assertSame('ITVA', $references['__meta']['policy']);
        $this->assertContains(800, $references['__meta']['itva_categories']);

        $this->assertSame(650, $references['ACF|A300-B4']['tbo_hours']);
        $this->assertSame(750, $references['ACF|A310']['tbo_hours']);
        $this->assertSame(800, $references['ITF|A320-211']['tbo_hours']);
        $this->assertSame(850, $references['ACF|B777']['tbo_hours']);
        $this->assertSame(170, $references['ITF|L-1049G']['tbo_hours']);
        $this->assertSame(1095000, $references['ACF|A300-B4']['itva_tbo_cost']);
        $this->assertSame(1593750, $references['ACF|A310']['itva_tbo_cost']);

        $this->assertNotSame(20000, $references['ITF|A320-211']['tbo_hours']);
        $this->assertNotSame(25000, $references['ACF|B777']['tbo_hours']);
    }

    public function test_sync_replaces_real_tbo_on_existing_serial_with_itva_potential(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1, 'LFPO');
        $subfleet = $fleet['subfleet'];
        $aircraft = $fleet['aircraft']->first();

        $profileId = DB::table('promethee_engine_profiles')->insertGetId([
            'subfleet_id' => $subfleet->id,
            'engine_type' => 'CFM International CFM56-5A1',
            'engine_count' => 2,
            'tbo_hours' => 800,
            'tbo_cycles' => null,
            'warning_hours' => 80,
            'warning_cycles' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $engineId = DB::table('promethee_engines')->insertGetId([
            'engine_profile_id' => $profileId,
            'serial_number' => 'REAL-CFM56-TEST-001',
            'engine_type' => 'CFM International CFM56-5A1',
            'tbo_hours' => 20000,
            'tbo_cycles' => null,
            'hours_since_overhaul' => 760,
            'cycles_since_overhaul' => 0,
            'status' => 'serviceable',
            'last_overhaul_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('promethee_aircraft_engines')->insert([
            'aircraft_id' => $aircraft->id,
            'engine_id' => $engineId,
            'position' => 1,
            'installed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /** @var EngineMaintenanceService $service */
        $service = app(EngineMaintenanceService::class);
        $service->syncAircraft($aircraft);

        $engine = DB::table('promethee_engines')->where('id', $engineId)->first();

        $this->assertSame(800.0, (float) $engine->tbo_hours);
        $this->assertSame(760.0, (float) $engine->hours_since_overhaul);
        $this->assertSame('warning', $engine->status);
    }

    public function test_data_migration_replaces_existing_real_tbo_with_itva_values(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1, 'LFML');
        $subfleet = $fleet['subfleet'];

        $subfleet->update(['type' => 'A300-B4']);
        DB::table('airlines')->where('id', $subfleet->airline_id)->update([
            'icao' => 'ACF',
            'updated_at' => now(),
        ]);

        $profileId = DB::table('promethee_engine_profiles')->insertGetId([
            'subfleet_id' => $subfleet->id,
            'engine_type' => 'General Electric CF6-50C2',
            'engine_count' => 2,
            'tbo_hours' => 12000,
            'tbo_cycles' => null,
            'warning_hours' => 1200,
            'warning_cycles' => null,
            'itva_overhaul_cost' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $engineId = DB::table('promethee_engines')->insertGetId([
            'engine_profile_id' => $profileId,
            'serial_number' => 'AUTO-ITVA-MIGRATION-1',
            'engine_type' => 'General Electric CF6-50C2',
            'tbo_hours' => 12000,
            'tbo_cycles' => null,
            'hours_since_overhaul' => 600,
            'cycles_since_overhaul' => 12,
            'status' => 'serviceable',
            'last_overhaul_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require base_path('modules/Promethee/Database/migrations/2026_10_05_000200_apply_itva_engine_reference_data.php');
        $migration->up();

        $profile = DB::table('promethee_engine_profiles')->where('id', $profileId)->first();
        $engine = DB::table('promethee_engines')->where('id', $engineId)->first();

        $this->assertSame(650.0, (float) $profile->tbo_hours);
        $this->assertSame(65.0, (float) $profile->warning_hours);
        $this->assertSame(1095000.0, (float) $profile->itva_overhaul_cost);

        $this->assertSame(650.0, (float) $engine->tbo_hours);
        $this->assertSame(600.0, (float) $engine->hours_since_overhaul);
        $this->assertSame(12, (int) $engine->cycles_since_overhaul);
        $this->assertSame('warning', $engine->status);
    }

}
