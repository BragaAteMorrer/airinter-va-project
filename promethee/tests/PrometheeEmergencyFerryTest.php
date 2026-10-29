<?php

namespace Tests;

use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Enums\PirepState;
use App\Models\Pirep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\DisposableSpecial\Services\DS_MaintenanceServices;
use Modules\Promethee\Services\AirframeEmergencyFerryService;
use Modules\Promethee\Services\AirframeMaintenanceService;
use RuntimeException;

final class PrometheeEmergencyFerryTest extends TestCase
{
    private function heldAircraft(): array
    {
        $fleet = $this->createSubfleetWithAircraft(1, 'LFMN');
        $aircraft = $fleet['aircraft']->first();
        $aircraft->update([
            'status' => AircraftStatus::ACTIVE,
            'state' => AircraftState::PARKED,
            'airport_id' => 'LFMN',
        ]);
        $incident = Pirep::factory()->create(['aircraft_id' => $aircraft->id]);
        $maintenance = app(AirframeMaintenanceService::class);
        $maintenance->syncFleet();
        $placed = $maintenance->placeSafetyHold($incident, 3.15);
        $this->assertSame(1, $placed['updated']);

        DB::table('promethee_operational_bases')->updateOrInsert(
            ['airport_id' => 'LFPG'],
            [
                'kind' => 'regional',
                'is_hub' => false,
                'is_regional_platform' => true,
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
        return [$aircraft, $maintenance, app(AirframeEmergencyFerryService::class)];
    }

    public function test_authorization_creates_private_ferry_but_does_not_release_grounding(): void
    {
        [$aircraft, , $service] = $this->heldAircraft();
        $result = $service->authorize((int) $aircraft->id, 'LFPG', 4, null,
            'Inspection structurelle provisoire favorable au convoyage à vide.');

        $this->assertDatabaseHas('flights', [
            'id' => $result['flight_id'],
            'dpt_airport_id' => 'LFMN',
            'arr_airport_id' => 'LFPG',
            'visible' => 0,
            'active' => 1,
        ]);
        $this->assertDatabaseHas('promethee_missions', [
            'id' => $result['mission_id'],
            'aircraft_id' => $aircraft->id,
            'mission_type' => 'repatriation',
            'reward_multiplier' => 1,
        ]);
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);
        $this->assertFalse($service->isAuthorized((int) $aircraft->id, $result['flight_id']));
        $this->expectException(RuntimeException::class);
        $service->authorize((int) $aircraft->id, 'LFPG', 1, null, 'Tentative de deuxième autorisation interdite.');
    }

    public function test_exactly_one_ferry_leads_to_mandatory_final_inspection(): void
    {
        [$aircraft, , $service] = $this->heldAircraft();
        $result = $service->authorize((int) $aircraft->id, 'LFPG', 1, null,
            'Vérification des trains et de la structure avant vol technique.');

        DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)
            ->update(['ferry_ready_at' => now()->subMinute()]);
        $this->assertTrue($service->isAuthorized((int) $aircraft->id, $result['flight_id']));

        $pirep = Pirep::factory()->create([
            'aircraft_id' => $aircraft->id,
            'flight_id' => $result['flight_id'],
            'dpt_airport_id' => 'LFMN',
            'arr_airport_id' => 'LFPG',
        ]);
        $this->bookAndAcceptFerry($pirep, (int) $result['mission_id']);
        $this->assertTrue($service->complete($pirep));
        $this->assertFalse($service->complete($pirep));
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);
        $this->assertSame('LFPG', $aircraft->fresh()->airport_id);
        $this->assertSame('arrived', $service->state((int) $aircraft->id)->ferry_status);
        $this->assertDatabaseHas('flights', ['id' => $result['flight_id'], 'active' => 0]);

        $service->finalize((int) $aircraft->id, null,
            'Inspection lourde terminée, avion validé pour remise en service.');
        $this->assertNull($service->state((int) $aircraft->id)->ferry_status);
        $this->assertNull($service->state((int) $aircraft->id)->safety_hold_at);
        $this->assertSame(AircraftStatus::ACTIVE, $aircraft->fresh()->status);
    }

    public function test_cannot_finish_or_release_fdm_hold_while_ferry_is_authorized(): void
    {
        [$aircraft, $maintenance, $service] = $this->heldAircraft();
        $service->authorize((int) $aircraft->id, 'LFPG', 1, null,
            'Inspection provisoire favorable, rapatriement pour réparation lourde.');
        try {
            $maintenance->releaseSafetyHold((int) $aircraft->id);
            $this->fail('Safety hold must remain active.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Convoyage', $exception->getMessage());
        }

        $this->expectException(RuntimeException::class);
        $service->finalize((int) $aircraft->id, null,
            'Une fausse réparation finale avant convoyage ne doit pas passer.');
    }

    public function test_diversion_closes_first_mission_and_keeps_plane_grounded(): void
    {
        [$aircraft, , $service] = $this->heldAircraft();
        $result = $service->authorize((int) $aircraft->id, 'LFPG', 1, null,
            'Contrôle de sécurité provisoire et autorisation de rapatriement.');
        $pirep = Pirep::factory()->create([
            'aircraft_id' => $aircraft->id,
            'flight_id' => $result['flight_id'],
            'dpt_airport_id' => 'LFMN',
            'arr_airport_id' => 'LFPO',
        ]);
        $this->bookAndAcceptFerry($pirep, (int) $result['mission_id']);

        $this->assertTrue($service->complete($pirep));
        $this->assertFalse($service->complete($pirep));
        $this->assertSame('diverted', $service->state((int) $aircraft->id)->ferry_status);
        $this->assertSame('LFPO', $aircraft->fresh()->airport_id);
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);
        $this->assertDatabaseHas('flights', ['id' => $result['flight_id'], 'active' => 0]);
        $this->assertDatabaseHas('promethee_missions', ['id' => $result['mission_id'], 'active' => 0]);
        $this->assertDatabaseHas('promethee_airframe_maintenance_events', [
            'aircraft_id' => $aircraft->id,
            'event_type' => 'ferry_diverted',
            'airport_id' => 'LFPO',
        ]);
        $this->assertTrue($service->missionIsProtected((int) $result['mission_id']));
    }

    public function test_diversion_to_non_technical_airport_requires_new_ferry(): void
    {
        [$aircraft, , $service] = $this->heldAircraft();
        $old = $service->authorize((int) $aircraft->id, 'LFPG', 1, null,
            'Dépannage provisoire et transfert prévu vers la base technique.');
        $pirep = Pirep::factory()->create([
            'aircraft_id' => $aircraft->id,
            'flight_id' => $old['flight_id'],
            'dpt_airport_id' => 'LFMN',
            'arr_airport_id' => 'LFMN',
        ]);
        $this->bookAndAcceptFerry($pirep, (int) $old['mission_id']);
        $service->complete($pirep);
        $this->assertSame('diverted', $service->state((int) $aircraft->id)->ferry_status);

        try {
            $service->finalize((int) $aircraft->id, null,
                'Fausse remise en ligne depuis escale non technique.');
            $this->fail('A non-technical diversion cannot release the aircraft.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('base technique', $exception->getMessage());
        }

        $next = $service->authorize((int) $aircraft->id, 'LFPG', 2, null,
            'Nouvelle inspection après le déroutement et départ autorisé à vide.');
        $this->assertNotSame($old['flight_id'], $next['flight_id']);
        $this->assertSame('authorized', $service->state((int) $aircraft->id)->ferry_status);
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);
    }

    public function test_diversion_to_technical_base_can_be_repaired_on_site(): void
    {
        [$aircraft, , $service] = $this->heldAircraft();
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
        $old = $service->authorize((int) $aircraft->id, 'LFPG', 1, null,
            'Inspection favorable pour un retour à la base technique CDG.');
        $pirep = Pirep::factory()->create([
            'aircraft_id' => $aircraft->id,
            'flight_id' => $old['flight_id'],
            'dpt_airport_id' => 'LFMN',
            'arr_airport_id' => 'LFPO',
        ]);
        $this->bookAndAcceptFerry($pirep, (int) $old['mission_id']);
        $service->complete($pirep);
        $this->assertSame('diverted', $service->state((int) $aircraft->id)->ferry_status);

        $service->finalize((int) $aircraft->id, null,
            'Inspection structurelle définitive sur base Orly, appareil remis en service.');
        $this->assertSame(AircraftStatus::ACTIVE, $aircraft->fresh()->status);
        $this->assertSame('LFPO', $aircraft->fresh()->airport_id);
        $this->assertNull($service->state((int) $aircraft->id)->ferry_status);
    }

    public function test_diversion_cannot_be_created_from_wrong_departure(): void
    {
        [$aircraft, , $service] = $this->heldAircraft();
        $old = $service->authorize((int) $aircraft->id, 'LFPG', 1, null,
            'Inspection sécurité favorable avant le convoyage technique.');
        $pirep = Pirep::factory()->create([
            'aircraft_id' => $aircraft->id,
            'flight_id' => $old['flight_id'],
            'dpt_airport_id' => 'LFPO',
            'arr_airport_id' => 'LFMN',
        ]);
        $this->bookAndAcceptFerry($pirep, (int) $old['mission_id']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('PIREP de convoyage incohérent');
        $service->complete($pirep);
    }

    public function test_admin_can_cancel_before_departure_without_releasing_aircraft(): void
    {
        [$aircraft, , $ferry] = $this->heldAircraft();
        $first = $ferry->authorize((int) $aircraft->id, 'LFPG', 4, null,
            'Inspection provisoire confirmée avant transport technique à vide.');
        $this->assertTrue($ferry->missionIsProtected((int) $first['mission_id']));

        $ferry->cancel((int) $aircraft->id, null,
            'Dépannage provisoire suspendu, nouvelle inspection technique nécessaire.');

        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);
        $this->assertNotNull($ferry->state((int) $aircraft->id)->safety_hold_at);
        $this->assertNull($ferry->state((int) $aircraft->id)->ferry_status);
        $this->assertFalse($ferry->missionIsProtected((int) $first['mission_id']));
        $this->assertDatabaseHas('flights', ['id' => $first['flight_id'], 'active' => 0]);
        $this->assertDatabaseHas('promethee_missions', ['id' => $first['mission_id'], 'active' => 0]);
        $this->assertDatabaseHas('promethee_airframe_maintenance_events', [
            'aircraft_id' => $aircraft->id,
            'event_type' => 'ferry_cancelled',
            'check_type' => 'f',
        ]);

        $second = $ferry->authorize((int) $aircraft->id, 'LFPG', 2, null,
            'Inspection renouvelée après annulation du premier permis technique.');
        $this->assertNotSame($first['flight_id'], $second['flight_id']);
        $this->assertTrue($ferry->missionIsProtected((int) $second['mission_id']));
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);
    }

    public function test_cancel_is_refused_if_pilot_has_booked_the_ferry(): void
    {
        [$aircraft, , $ferry] = $this->heldAircraft();
        $permit = $ferry->authorize((int) $aircraft->id, 'LFPG', 1, null,
            'Inspection satisfaisante avant déplacement technique vers la base.');
        DB::table('promethee_mission_bookings')->insert([
            'mission_id' => $permit['mission_id'],
            'user_id' => $this->createUser()->id,
            'status' => 'reserved',
            'reserved_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $ferry->cancel((int) $aircraft->id, null,
                'Tentative interdite pendant la réservation active du pilote.');
            $this->fail('A booked ferry cannot be cancelled by administration.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('réservée', $exception->getMessage());
        }
        $this->assertSame('authorized', $ferry->state((int) $aircraft->id)->ferry_status);
        $this->assertDatabaseHas('flights', ['id' => $permit['flight_id'], 'active' => 1]);
    }

    public function test_cancel_is_refused_after_a_ferry_pirep_was_created(): void
    {
        [$aircraft, , $ferry] = $this->heldAircraft();
        $permit = $ferry->authorize((int) $aircraft->id, 'LFPG', 1, null,
            'Contrôle structurel validé avant un unique vol technique.');
        Pirep::factory()->create([
            'aircraft_id' => $aircraft->id,
            'flight_id' => $permit['flight_id'],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('PIREP existe déjà');
        $ferry->cancel((int) $aircraft->id, null,
            'Tentative interdite après création du rapport de convoyage.');
    }

    public function test_legacy_hard_landing_creates_persistent_safety_hold(): void
    {
        if (!Schema::hasTable('disposable_maintenance')) {
            $this->markTestSkipped('Maintenance historique indisponible.');
        }

        [$aircraft, , $ferry] = $this->heldAircraft();
        DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)
            ->update([
                'safety_hold_at' => null,
                'safety_hold_reason' => null,
                'safety_hold_previous_status' => null,
            ]);
        DB::table('disposable_maintenance')->updateOrInsert(
            ['aircraft_id' => $aircraft->id],
            [
                'curr_state' => 100,
                'time_a' => 0, 'time_b' => 0, 'time_c' => 0,
                'cycle_a' => 0, 'cycle_b' => 0, 'cycle_c' => 0,
                'act_note' => 'Hard Landing Check',
                'act_start' => now()->subHour(),
                'act_end' => now()->addHour(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $permit = $ferry->authorize((int) $aircraft->id, 'LFPG', 1, null,
            'Inspection provisoire du train et autorisation de convoyage à vide.');
        $this->assertNotNull($ferry->state((int) $aircraft->id)->safety_hold_at);
        $this->assertSame('authorized', $ferry->state((int) $aircraft->id)->ferry_status);
        $ferry->cancel((int) $aircraft->id, null,
            'Dépannage interrompu pour nouvelle inspection de la structure.');
        $this->assertNotNull($ferry->state((int) $aircraft->id)->safety_hold_at);
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);
        $this->assertDatabaseHas('flights', ['id' => $permit['flight_id'], 'active' => 0]);
    }

    public function test_legacy_maintenance_cron_cannot_release_authorized_ferry(): void
    {
        if (!Schema::hasTable('disposable_maintenance')) {
            $this->markTestSkipped('Legacy maintenance is unavailable.');
        }
        [$aircraft] = $this->heldAircraft();
        DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)
            ->update(['safety_hold_at' => null, 'ferry_status' => 'authorized']);
        DB::table('disposable_maintenance')->updateOrInsert(
            ['aircraft_id' => $aircraft->id],
            [
                'curr_state' => 100,
                'time_a' => 0, 'time_b' => 0, 'time_c' => 0,
                'cycle_a' => 0, 'cycle_b' => 0, 'cycle_c' => 0,
                'act_note' => 'Hard Landing Check',
                'act_start' => now()->subHours(2),
                'act_end' => now()->subHour(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        app(DS_MaintenanceServices::class)->ProcessMaintenance();
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);
        $this->assertNull(DB::table('disposable_maintenance')
            ->where('aircraft_id', $aircraft->id)->value('act_note'));
    }

    public function test_completed_cell_check_cannot_clear_legacy_ferry_grounding(): void
    {
        [$aircraft, $maintenance] = $this->heldAircraft();

        // Legacy-only hard landing recovery: no current FDM hold, but ferry
        // has arrived and cannot be cleared by completing an A check alone.
        DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)
            ->update([
                'safety_hold_at' => null,
                'ferry_status' => 'arrived',
                'ferry_destination_airport_id' => 'LFPG',
                'active_check' => 'a',
                'active_started_at' => now()->subHours(3),
                'active_due_at' => now()->subMinute(),
            ]);
        $aircraft->update([
            'airport_id' => 'LFPG',
            'status' => AircraftStatus::MAINTENANCE,
        ]);

        $this->assertTrue($maintenance->completeCheck((int) $aircraft->id));
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);
        $this->assertSame('arrived', DB::table('promethee_airframe_maintenance')
            ->where('aircraft_id', $aircraft->id)->value('ferry_status'));
    }

    public function test_rejected_or_deleted_ferry_report_never_releases_aircraft(): void
    {
        [$aircraft, , $service] = $this->heldAircraft();
        $result = $service->authorize((int) $aircraft->id, 'LFPG', 1, null,
            'Contrôle provisoire validé avant le convoyage vers la base technique.');
        DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraft->id)
            ->update(['ferry_ready_at' => now()->subMinute()]);

        $pirep = Pirep::factory()->create([
            'aircraft_id' => $aircraft->id,
            'flight_id' => $result['flight_id'],
            'dpt_airport_id' => 'LFMN',
            'arr_airport_id' => 'LFPG',
        ]);
        $this->bookAndAcceptFerry($pirep, (int) $result['mission_id']);
        $this->assertTrue($service->complete($pirep));

        // Simulate a later administrative rejection of the accepted flight.
        DB::table('pireps')->where('id', $pirep->id)->update(['state' => PirepState::REJECTED]);
        try {
            $service->finalize((int) $aircraft->id, null,
                'Fausse réparation après rejet du PIREP à ne pas accepter.');
            $this->fail('The flight report is rejected.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('PIREP', $exception->getMessage());
        }
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);
        $this->assertSame('arrived', $service->state((int) $aircraft->id)->ferry_status);
    }

    public function test_accepted_ferry_without_a_reserved_pilot_is_rejected(): void
    {
        [$aircraft, , $service] = $this->heldAircraft();
        $result = $service->authorize((int) $aircraft->id, 'LFPG', 1, null,
            'Inspection provisoire effectuée avant le déplacement technique.');
        $pirep = Pirep::factory()->create([
            'aircraft_id' => $aircraft->id,
            'flight_id' => $result['flight_id'],
            'dpt_airport_id' => 'LFMN',
            'arr_airport_id' => 'LFPG',
        ]);
        DB::table('pireps')->where('id', $pirep->id)
            ->update(['state' => PirepState::ACCEPTED]);
        $pirep->refresh();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('réservation pilote introuvable');
        $service->complete($pirep);
    }

    private function bookAndAcceptFerry(Pirep $pirep, int $missionId): void
    {
        DB::table('promethee_mission_bookings')->insert([
            'mission_id' => $missionId,
            'user_id' => $pirep->user_id,
            'status' => 'reserved',
            'reserved_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Don't fire phpVMS event listeners during this narrow service test.
        DB::table('pireps')->where('id', $pirep->id)
            ->update(['state' => PirepState::ACCEPTED]);
        $pirep->refresh();
    }

    public function test_no_hold_or_wrong_destination_cannot_request_emergency_repair(): void
    {
        [$aircraft, $maintenance, $service] = $this->heldAircraft();
        $maintenance->releaseSafetyHold((int) $aircraft->id);
        $aircraft->update(['status' => AircraftStatus::MAINTENANCE]);

        try {
            $service->authorize((int) $aircraft->id, 'LFPG', 1, null,
                'Demande de ferry sans incident nécessitant immobilisation.');
            $this->fail('No incident must reject ferry.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Aucun incident', $exception->getMessage());
        }
        $this->expectException(RuntimeException::class);
        $service->authorize((int) $aircraft->id, 'LFMN', 1, null,
            'Demande de convoyage vers un aéroport sans check B et C.');
    }
}
