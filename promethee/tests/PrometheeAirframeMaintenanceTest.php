<?php

namespace Tests;

use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Pirep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        if (Schema::hasTable('disposable_settings')) {
            $this->assertSame('20', DB::table('disposable_settings')->where('key', 'turksim.maint_lim_ac')->value('value'));
            $this->assertSame('60', DB::table('disposable_settings')->where('key', 'turksim.maint_lim_bt')->value('value'));
            $this->assertSame('120', DB::table('disposable_settings')->where('key', 'turksim.maint_hours_c')->value('value'));
            $this->assertSame('disposable_settings', $service->settings()['source']);
        }
    }

    public function test_admin_fleet_status_uses_the_same_aircraft_maintenance_record(): void
    {
        if (!Schema::hasTable('disposable_maintenance')) {
            $this->markTestSkipped('Disposable maintenance table is not installed in this test environment.');
        }

        $fleet = $this->createSubfleetWithAircraft(1, 'LFPO');
        $aircraft = $fleet['aircraft']->first();
        $aircraft->update(['icao' => 'ZZZZ']);

        /** @var AirframeMaintenanceService $service */
        $service = app(AirframeMaintenanceService::class);
        $service->saveSettings([
            'a_time_limit_hours' => 20,
            'a_cycle_limit' => 20,
            'a_duration_hours' => 10,
            'b_time_limit_hours' => 60,
            'b_cycle_limit' => 60,
            'b_duration_hours' => 48,
            'c_time_limit_hours' => 180,
            'c_cycle_limit' => 180,
            'c_duration_hours' => 120,
            'warning_percent' => 10,
        ]);
        $service->syncFleet();

        DB::table('disposable_maintenance')->updateOrInsert(
            ['aircraft_id' => $aircraft->id],
            [
                'curr_state' => 99,
                'time_a' => 600,
                'time_b' => 1200,
                'time_c' => 1800,
                'cycle_a' => 8,
                'cycle_b' => 20,
                'cycle_c' => 20,
                'rem_ta' => 600,
                'rem_tb' => 2400,
                'rem_tc' => 9000,
                'rem_ca' => 12,
                'rem_cb' => 40,
                'rem_cc' => 160,
                'last_a' => '2026-09-07 16:02:14',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $state = $service->fleetStatus()->firstWhere('aircraft_id', $aircraft->id);

        $this->assertNotNull($state);
        $this->assertSame('disposable_maintenance', $state->maintenance_source);
        $this->assertSame(99.0, $state->current_state_percent);
        $this->assertSame(10.0, $state->checks['a']['remaining_hours']);
        $this->assertSame(12, $state->checks['a']['remaining_cycles']);
        $this->assertSame(40.0, $state->checks['b']['remaining_hours']);
        $this->assertSame(160, $state->checks['c']['remaining_cycles']);
        $this->assertSame('2026-09-07 16:02:14', (string) $state->checks['a']['last_check_at']);
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


    public function test_structural_gforce_places_and_releases_an_airframe_safety_hold(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1, 'LFPO');
        $aircraft = $fleet['aircraft']->first();
        $aircraft->update(['status' => AircraftStatus::ACTIVE, 'state' => AircraftState::PARKED]);
        $pirep = Pirep::factory()->create(['aircraft_id' => $aircraft->id]);

        /** @var AirframeMaintenanceService $service */
        $service = app(AirframeMaintenanceService::class);
        $service->syncFleet();

        $placed = $service->placeSafetyHold($pirep, 3.05, '2026-10-05T09:30:00Z');
        $this->assertSame(1, $placed['updated']);
        $this->assertSame('SAFETY_HOLD', $placed['reason']);
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);

        $state = DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)->first();
        $this->assertNotNull($state->safety_hold_at);
        $this->assertSame((string) $pirep->id, (string) $state->safety_hold_pirep_id);
        $this->assertSame(AircraftStatus::ACTIVE, (string) $state->safety_hold_previous_status);

        $secondPirep = Pirep::factory()->create(['aircraft_id' => $aircraft->id]);
        $duplicate = $service->placeSafetyHold($secondPirep, 3.10, '2026-10-05T09:30:01Z');
        $this->assertSame(0, $duplicate['updated']);
        $this->assertSame('ALREADY_HELD', $duplicate['reason']);

        $state = DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)->first();
        $this->assertSame((string) $pirep->id, (string) $state->safety_hold_pirep_id);
        $this->assertSame(AircraftStatus::ACTIVE, (string) $state->safety_hold_previous_status);

        $this->assertTrue($service->releaseSafetyHold((int) $aircraft->id, null, 'Inspection visuelle OK.'));
        $state = DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)->first();
        $this->assertNull($state->safety_hold_at);
        $this->assertNull($state->safety_hold_pirep_id);
        $this->assertSame(AircraftStatus::ACTIVE, $aircraft->fresh()->status);

        $this->assertDatabaseHas('promethee_airframe_maintenance_events', [
            'aircraft_id' => $aircraft->id,
            'check_type' => 'g',
            'event_type' => 'safety_hold',
        ]);
        $this->assertDatabaseHas('promethee_airframe_maintenance_events', [
            'aircraft_id' => $aircraft->id,
            'check_type' => 'g',
            'event_type' => 'safety_release',
        ]);
    }

    public function test_runway_overrun_places_aircraft_on_mandatory_maintenance_hold(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1, 'LFPO');
        $aircraft = $fleet['aircraft']->first();
        $aircraft->update(['status' => AircraftStatus::ACTIVE, 'state' => AircraftState::PARKED]);
        $pirep = Pirep::factory()->create(['aircraft_id' => $aircraft->id]);

        /** @var AirframeMaintenanceService $service */
        $service = app(AirframeMaintenanceService::class);
        $service->syncFleet();

        $placed = $service->placeRunwayOverrunHold($pirep, 143, '36', '2026-10-05T20:00:00Z');

        $this->assertSame(1, $placed['updated']);
        $this->assertSame('SAFETY_HOLD', $placed['reason']);
        $this->assertSame('r', $placed['trigger']);
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);

        $state = DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)->first();
        $this->assertNotNull($state->safety_hold_at);
        $this->assertSame((string) $pirep->id, (string) $state->safety_hold_pirep_id);
        $this->assertStringContainsString('Runway overrun', (string) $state->safety_hold_reason);
        $this->assertStringContainsString('piste 36', (string) $state->safety_hold_reason);
        $this->assertStringContainsString('143 m', (string) $state->safety_hold_reason);

        $this->assertDatabaseHas('promethee_airframe_maintenance_events', [
            'aircraft_id' => $aircraft->id,
            'check_type' => 'r',
            'event_type' => 'safety_hold',
        ]);

        $this->assertTrue($service->releaseSafetyHold(
            (int) $aircraft->id,
            null,
            'Inspection après sortie de piste OK.'
        ));
        $this->assertSame(AircraftStatus::ACTIVE, $aircraft->fresh()->status);
        $this->assertDatabaseHas('promethee_airframe_maintenance_events', [
            'aircraft_id' => $aircraft->id,
            'check_type' => 'r',
            'event_type' => 'safety_release',
        ]);
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
