<?php

namespace Tests;

use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Pirep;
use Illuminate\Support\Facades\DB;
use Modules\Promethee\Services\AirframeMaintenanceService;

final class PrometheeAirframeMaintenanceTest extends TestCase
{
    public function test_a_b_c_time_cycle_and_duration_limits_are_persisted(): void
    {
        /** @var AirframeMaintenanceService $service */
        $service = app(AirframeMaintenanceService::class);
        $service->saveSettings([
            'a_time_limit_hours' => 20,
            'a_cycle_limit' => 20,
            'a_duration_hours' => 20,
            'b_time_limit_hours' => 60,
            'b_cycle_limit' => 60,
            'b_duration_hours' => 96,
            'c_time_limit_hours' => 180,
            'c_cycle_limit' => 180,
            'c_duration_hours' => 120,
            'warning_percent' => 10,
        ]);

        $this->assertSame('20', DB::table('promethee_settings')->where('key', 'maintenance.airframe.a.cycle_limit')->value('value'));
        $this->assertSame('60', DB::table('promethee_settings')->where('key', 'maintenance.airframe.b.time_limit_hours')->value('value'));
        $this->assertSame('120', DB::table('promethee_settings')->where('key', 'maintenance.airframe.c.duration_hours')->value('value'));
    }

    public function test_accepted_pirep_increments_airframe_hours_and_cycles_once(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1, 'LFPO');
        $aircraft = $fleet['aircraft']->first();
        $pirep = Pirep::factory()->create([
            'aircraft_id' => $aircraft->id,
            'flight_time' => 90,
        ]);

        /** @var AirframeMaintenanceService $service */
        $service = app(AirframeMaintenanceService::class);
        $service->recordAcceptedPirep($pirep);
        $service->recordAcceptedPirep($pirep);

        $state = DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)->first();
        $this->assertSame(90, (int) $state->a_minutes);
        $this->assertSame(1, (int) $state->a_cycles);
        $this->assertSame(90, (int) $state->b_minutes);
        $this->assertSame(1, (int) $state->b_cycles);
        $this->assertSame(90, (int) $state->c_minutes);
        $this->assertSame(1, (int) $state->c_cycles);
    }

    public function test_b_check_uses_duration_and_resets_a_and_b_without_resetting_c(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1, 'LFPO');
        $aircraft = $fleet['aircraft']->first();
        $aircraft->update(['status' => AircraftStatus::ACTIVE, 'state' => AircraftState::PARKED]);

        DB::table('promethee_operational_bases')->updateOrInsert(
            ['airport_id' => 'LFPO'],
            [
                'kind' => 'hub',
                'is_hub' => true,
                'is_regional_platform' => false,
                'is_technical_stop' => false,
                'check_a' => true,
                'check_b' => true,
                'check_c' => true,
                'small_maintenance' => true,
                'heavy_maintenance' => true,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        /** @var AirframeMaintenanceService $service */
        $service = app(AirframeMaintenanceService::class);
        $service->syncFleet();
        $service->saveSettings([
            'a_time_limit_hours' => 20,
            'a_cycle_limit' => 20,
            'a_duration_hours' => 20,
            'b_time_limit_hours' => 60,
            'b_cycle_limit' => 60,
            'b_duration_hours' => 0,
            'c_time_limit_hours' => 180,
            'c_cycle_limit' => 180,
            'c_duration_hours' => 120,
            'warning_percent' => 10,
        ]);

        DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)->update([
            'a_minutes' => 1200,
            'a_cycles' => 20,
            'b_minutes' => 3600,
            'b_cycles' => 60,
            'c_minutes' => 3600,
            'c_cycles' => 60,
            'updated_at' => now(),
        ]);

        $service->startCheck((int) $aircraft->id, 'b', null);
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);

        $this->assertSame(1, $service->releaseCompletedChecks());
        $state = DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)->first();

        $this->assertSame(0, (int) $state->a_minutes);
        $this->assertSame(0, (int) $state->a_cycles);
        $this->assertSame(0, (int) $state->b_minutes);
        $this->assertSame(0, (int) $state->b_cycles);
        $this->assertSame(3600, (int) $state->c_minutes);
        $this->assertSame(60, (int) $state->c_cycles);
        $this->assertNull($state->active_check);
        $this->assertSame(AircraftStatus::ACTIVE, $aircraft->fresh()->status);
    }

    public function test_rotation_priority_uses_cycle_limit_as_well_as_time(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1, 'LFPO');
        $aircraft = $fleet['aircraft']->first();

        /** @var AirframeMaintenanceService $service */
        $service = app(AirframeMaintenanceService::class);
        $service->syncFleet();
        $service->saveSettings([
            'a_time_limit_hours' => 1000,
            'a_cycle_limit' => 20,
            'a_duration_hours' => 20,
            'b_time_limit_hours' => 2000,
            'b_cycle_limit' => 200,
            'b_duration_hours' => 96,
            'c_time_limit_hours' => 4000,
            'c_cycle_limit' => 400,
            'c_duration_hours' => 120,
            'warning_percent' => 10,
        ]);

        DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)->update([
            'a_minutes' => 60,
            'a_cycles' => 19,
            'updated_at' => now(),
        ]);

        $priorities = $service->rotationPriorities(10);
        $this->assertTrue($priorities->has((int) $aircraft->id));
        $this->assertSame('a', $priorities->get((int) $aircraft->id)->check);
    }
}
