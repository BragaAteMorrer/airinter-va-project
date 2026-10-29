<?php

namespace Tests;

use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Pirep;
use Illuminate\Support\Facades\DB;
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

    public function test_wrong_arrival_keeps_aircraft_immobilized(): void
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
        try {
            $service->complete($pirep);
            $this->fail('Wrong destination should be rejected.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('incohérent', $exception->getMessage());
        }
        $this->assertSame('authorized', $service->state((int) $aircraft->id)->ferry_status);
        $this->assertSame('LFMN', $aircraft->fresh()->airport_id);
        $this->assertSame(AircraftStatus::MAINTENANCE, $aircraft->fresh()->status);
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
