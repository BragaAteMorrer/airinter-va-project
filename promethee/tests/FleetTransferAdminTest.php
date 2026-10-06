<?php

namespace Tests;

use App\Models\Airport;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Promethee\Http\FleetTransferAdminController;

final class FleetTransferAdminTest extends TestCase
{
    public function test_available_aircraft_can_be_transferred_without_moving_the_pilot(): void
    {
        $origin = Airport::factory()->create(['id' => 'T001', 'icao' => 'T001', 'iata' => 'T01']);
        $destination = Airport::factory()->create(['id' => 'T002', 'icao' => 'T002', 'iata' => 'T02']);
        $fleet = $this->createSubfleetWithAircraft(1, $origin->id);
        $aircraft = $fleet['aircraft']->first();
        $aircraft->update([
            'status' => AircraftStatus::ACTIVE,
            'state' => AircraftState::PARKED,
            'airport_id' => $origin->id,
            'hub_id' => $origin->id,
        ]);

        $admin = $this->createAdminUser([
            'curr_airport_id' => $origin->id,
            'home_airport_id' => $origin->id,
        ]);

        $this->actingAs($admin, 'web');
        $view = app(FleetTransferAdminController::class)->index();
        $this->assertSame('promethee::admin-fleet-transfers', $view->name());
        $rendered = $view->render();
        $this->assertStringContainsString('Repositionnement des appareils.', $rendered);
        $this->assertStringNotContainsString('Changer aussi la base de rattachement', $rendered);

        $request = Request::create('/admin/promethee/fleet-transfers', 'POST', [
            'aircraft_ids' => [$aircraft->id],
            'destination_airport_id' => $destination->id,
            'reason' => 'Test de repositionnement administratif',
            // Even if a stale/malicious client still sends the old field,
            // this endpoint must never alter the base.
            'change_base' => '1',
        ]);
        $request->setUserResolver(fn () => $admin);
        app(FleetTransferAdminController::class)->transfer($request);

        $aircraft->refresh();
        $admin->refresh();

        $this->assertSame($destination->id, $aircraft->airport_id);
        $this->assertSame($origin->id, $aircraft->hub_id);
        $this->assertSame($origin->id, $admin->curr_airport_id);
        $this->assertDatabaseHas('promethee_aircraft_bases', [
            'aircraft_id' => $aircraft->id,
            'base_airport_id' => $origin->id,
        ]);
        $this->assertNotNull(
            DB::table('promethee_aircraft_bases')
                ->where('aircraft_id', $aircraft->id)
                ->value('away_since')
        );
        $this->assertDatabaseHas('promethee_audit_logs', [
            'action' => 'aircraft.transfer',
            'subject_type' => 'aircraft',
            'subject_id' => (string) $aircraft->id,
        ]);

        $audit = DB::table('promethee_audit_logs')
            ->where('action', 'aircraft.transfer')
            ->where('subject_id', (string) $aircraft->id)
            ->latest('id')
            ->first();

        $context = json_decode((string) $audit->context, true);
        $this->assertSame($origin->id, $context['from_airport']);
        $this->assertSame($destination->id, $context['to_airport']);
        $this->assertSame($origin->id, $context['base_unchanged']);
        $this->assertArrayNotHasKey('change_base', $context);
    }

    public function test_aircraft_in_flight_cannot_be_transferred(): void
    {
        $origin = Airport::factory()->create(['id' => 'T011', 'icao' => 'T011', 'iata' => 'T11']);
        $destination = Airport::factory()->create(['id' => 'T012', 'icao' => 'T012', 'iata' => 'T12']);
        $fleet = $this->createSubfleetWithAircraft(1, $origin->id);
        $aircraft = $fleet['aircraft']->first();
        $aircraft->update([
            'status' => AircraftStatus::ACTIVE,
            'state' => AircraftState::IN_AIR,
            'airport_id' => $origin->id,
        ]);

        $admin = $this->createAdminUser();

        $request = Request::create('/admin/promethee/fleet-transfers', 'POST', [
            'aircraft_ids' => [$aircraft->id],
            'destination_airport_id' => $destination->id,
        ]);
        $request->setUserResolver(fn () => $admin);

        try {
            app(FleetTransferAdminController::class)->transfer($request);
            $this->fail('Un appareil en vol ne doit jamais pouvoir être transféré administrativement.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString(
                'actuellement en vol',
                implode(' ', $exception->errors()['aircraft_ids'] ?? [])
            );
        }

        $aircraft->refresh();
        $this->assertSame($origin->id, $aircraft->airport_id);
        $this->assertDatabaseMissing('promethee_audit_logs', [
            'action' => 'aircraft.transfer',
            'subject_id' => (string) $aircraft->id,
        ]);
    }

    public function test_bulk_transfer_is_atomic_when_one_aircraft_becomes_unavailable(): void
    {
        $origin = Airport::factory()->create(['id' => 'T021', 'icao' => 'T021', 'iata' => 'T21']);
        $destination = Airport::factory()->create(['id' => 'T022', 'icao' => 'T022', 'iata' => 'T22']);
        $fleet = $this->createSubfleetWithAircraft(2, $origin->id);
        $first = $fleet['aircraft']->values()->get(0);
        $second = $fleet['aircraft']->values()->get(1);

        foreach ([$first, $second] as $plane) {
            $plane->update([
                'status' => AircraftStatus::ACTIVE,
                'state' => AircraftState::PARKED,
                'airport_id' => $origin->id,
            ]);
        }
        $second->update(['state' => AircraftState::IN_USE]);

        $admin = $this->createAdminUser();

        $request = Request::create('/admin/promethee/fleet-transfers', 'POST', [
            'aircraft_ids' => [$first->id, $second->id],
            'destination_airport_id' => $destination->id,
        ]);
        $request->setUserResolver(fn () => $admin);

        try {
            app(FleetTransferAdminController::class)->transfer($request);
            $this->fail('Le transfert groupé doit être atomique si un appareil devient indisponible.');
        } catch (ValidationException) {
            // Expected: the transaction must leave every selected aircraft untouched.
        }

        $first->refresh();
        $second->refresh();
        $this->assertSame($origin->id, $first->airport_id);
        $this->assertSame($origin->id, $second->airport_id);
    }
}
