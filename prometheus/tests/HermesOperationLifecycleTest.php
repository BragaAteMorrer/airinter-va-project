<?php

namespace Tests;

use App\Models\Airport;
use App\Models\Bid;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Enums\PirepState;
use App\Models\Enums\PirepStatus;
use App\Models\Flight;
use App\Models\Pirep;
use App\Models\SimBrief;
use App\Models\User;
use App\Services\BidService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Promethee\Services\HermesPirepLifecycleService;

final class HermesOperationLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->updateSetting('pireps.restrict_aircraft_to_rank', '0');
        $this->updateSetting('pireps.only_aircraft_at_dpt_airport', '1');
        $this->updateSetting('pireps.only_user_at_dpt_airport', '0');
        $this->updateSetting('bids.block_aircraft', '1');
        $this->updateSetting('simbrief.block_aircraft', '1');
        $this->updateSetting('bids.allow_multiple_bids', '1');
        $this->updateSetting('bids.disable_flight_on_bid', '0');
    }

    public function test_01_reservation_is_never_completed(): void
    {
        $fx = $this->operationFixture(assignAircraft: false, createOfp: false);

        $operation = $this->get('/api/v1/operations/'.$fx['operation_id'], [], $fx['user'])
            ->assertOk()
            ->json('data');

        $this->assertSame('reserved', $operation['status']);
        $this->assertNull($operation['pirep_id']);
        $this->assertNotSame('completed', $operation['status']);

        $dispatch = $this->dispatch($fx);
        $this->assertSame('2.0', $dispatch['workflow_contract_version']);
        $this->assertSame('RESERVED', $dispatch['workflow_state']['state']);
        $this->assertSame('SELECT_AIRCRAFT', $dispatch['workflow_state']['next_action']['code']);
        $this->assertFalse((bool) $dispatch['workflow_state']['terminal']);
    }

    public function test_02_aircraft_selection_keeps_operation_active(): void
    {
        $fx = $this->operationFixture(assignAircraft: false, createOfp: false);

        $response = $this->put(
            '/api/v1/operations/'.$fx['operation_id'].'/aircraft',
            ['aircraft_id' => $fx['aircraft']->id],
            [],
            $fx['user']
        )->assertOk();

        $this->assertSame($fx['aircraft']->id, $response->json('data.aircraft.id'));
        $this->assertDatabaseHas('bids', [
            'id' => $fx['bid']->id,
            'aircraft_id' => $fx['aircraft']->id,
        ]);

        $operation = $this->get('/api/v1/operations/'.$fx['operation_id'], [], $fx['user'])
            ->assertOk()
            ->json('data');
        $this->assertNotSame('completed', $operation['status']);
    }

    public function test_03_ofp_creation_keeps_operation_active(): void
    {
        $fx = $this->operationFixture(assignAircraft: true, createOfp: true);

        $operation = $this->get('/api/v1/operations/'.$fx['operation_id'], [], $fx['user'])
            ->assertOk()
            ->json('data');

        $this->assertTrue((bool) $operation['simbrief']['available']);
        $this->assertSame('planned', $operation['status']);
        $this->assertNotSame('completed', $operation['status']);

        $dispatch = $this->dispatch($fx);
        $this->assertSame('PLANNING', $dispatch['workflow_state']['state']);
        $this->assertSame('FINALIZE_PREPARATION', $dispatch['workflow_state']['next_action']['code']);
    }

    public function test_04_prefile_creates_active_pirep_and_keeps_reservation_visible(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);

        $pirep = Pirep::findOrFail($pirepId);
        $this->assertSame(PirepState::IN_PROGRESS, (int) $pirep->state);
        $this->assertSame(PirepStatus::INITIATED, $pirep->status);
        $this->assertNull($pirep->submitted_at);
        $this->assertSame('Hermes ACARS ['.$fx['operation_id'].']', $pirep->source_name);

        $dispatch = $this->dispatch($fx);
        $this->assertSame('READY', $dispatch['status']);
        $this->assertSame('READY', $dispatch['workflow_state']['state']);
        $this->assertSame('START_RECORDING', $dispatch['workflow_state']['next_action']['code']);
        $this->assertSame(100, $dispatch['workflow_state']['preparation_progress']);
        $this->assertTrue((bool) $dispatch['can_start']);
        $this->assertNotSame('COMPLETED', $dispatch['status']);

        $operations = $this->operations($fx['user']);
        $this->assertContains($fx['operation_id'], array_column($operations, 'operation_id'));
        $this->assertDatabaseHas('bids', ['id' => $fx['bid']->id]);
        $this->assertDatabaseHas('aircraft', ['id' => $fx['aircraft']->id, 'airport_id' => $fx['origin']->id]);
    }

    public function test_05_prefile_is_idempotent_and_never_completes(): void
    {
        $fx = $this->operationFixture();
        $first = $this->prefile($fx);

        $second = $this->post(
            '/api/v1/operations/'.$fx['operation_id'].'/pirep',
            [],
            [],
            $fx['user']
        )->assertOk()->json('data');

        $this->assertSame($first, $second['pirep_id']);
        $this->assertTrue((bool) $second['already_prefiled']);
        $this->assertSame(
            1,
            Pirep::where('source_name', 'Hermes ACARS ['.$fx['operation_id'].']')->count()
        );
        $this->assertNotSame('COMPLETED', $this->dispatch($fx)['status']);
    }

    public function test_06_restart_rehydrates_same_active_pirep_aircraft_and_ofp(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);

        // These stateless reads model a fresh Hermès process selecting the same
        // operation again after local UI state has been lost.
        $operation = $this->get('/api/v1/operations/'.$fx['operation_id'], [], $fx['user'])
            ->assertOk()
            ->json('data');
        $pirep = $this->get('/api/v1/operations/'.$fx['operation_id'].'/pirep', [], $fx['user'])
            ->assertOk()
            ->json('data');

        $this->assertSame($pirepId, $operation['pirep_id']);
        $this->assertSame($pirepId, $pirep['pirep_id']);
        $this->assertSame($fx['aircraft']->id, $operation['aircraft']['id']);
        $this->assertTrue((bool) $operation['simbrief']['available']);
        $this->assertSame('prefiled', $operation['status']);
        $this->assertNotSame('COMPLETED', $this->dispatch($fx)['status']);
    }

    public function test_07_first_acars_telemetry_moves_dispatch_to_in_progress_not_completed(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);

        $this->telemetry($fx, 'BOARDING');

        $dispatch = $this->dispatch($fx);
        $this->assertSame('IN_PROGRESS', $dispatch['status']);
        $this->assertSame('IN_PROGRESS', $dispatch['workflow_state']['state']);
        $this->assertSame('TRACK_FLIGHT', $dispatch['workflow_state']['next_action']['code']);
        $this->assertFalse((bool) $dispatch['terminal']);
        $this->assertSame($pirepId, $dispatch['pirep']['id']);

        $pirep = Pirep::findOrFail($pirepId);
        $this->assertSame(PirepState::IN_PROGRESS, (int) $pirep->state);
        $this->assertNull($pirep->submitted_at);
    }

    public function test_08_partial_telemetry_cannot_complete_operation(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);

        foreach (['BOARDING', 'TAXI_OUT', 'TAKEOFF', 'CLIMB'] as $index => $phase) {
            $this->telemetry($fx, $phase, now()->addSeconds($index + 1));
        }

        $this->assertSame('IN_PROGRESS', $this->dispatch($fx)['status']);
        $this->assertNull(Pirep::findOrFail($pirepId)->submitted_at);
        $this->assertContains($fx['operation_id'], array_column($this->operations($fx['user']), 'operation_id'));
    }

    public function test_09_interruption_and_resume_keep_same_operation_and_pirep(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);

        $this->telemetry($fx, 'TAXI_OUT', now());
        $this->telemetry($fx, 'CLIMB', now()->addMinutes(3));

        $pirep = $this->get('/api/v1/operations/'.$fx['operation_id'].'/pirep', [], $fx['user'])
            ->assertOk()
            ->json('data');

        $this->assertSame($pirepId, $pirep['pirep_id']);
        $this->assertSame(
            1,
            Pirep::where('source_name', 'Hermes ACARS ['.$fx['operation_id'].']')->count()
        );
        $this->assertSame('IN_PROGRESS', $this->dispatch($fx)['status']);
    }

    public function test_10_final_file_is_impossible_before_acars_and_completes_only_after_in(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);

        $this->filePirep($fx['user'], $pirepId)
            ->assertStatus(409);

        $this->telemetry($fx, 'CLIMB');
        $this->filePirep($fx['user'], $pirepId)
            ->assertStatus(409);

        $this->telemetry($fx, 'IN', now()->addMinute());
        $this->filePirep($fx['user'], $pirepId)
            ->assertOk();

        $pirep = Pirep::findOrFail($pirepId);
        $this->assertNotNull($pirep->submitted_at);
        $this->assertContains((int) $pirep->state, [
            PirepState::PENDING,
            PirepState::ACCEPTED,
            PirepState::REJECTED,
        ]);
        $this->assertTrue(app(HermesPirepLifecycleService::class)->isFiled($pirep));

        // PirepFiled removes the bid. Therefore the completed operation leaves
        // the active reservation queue only after the real final filing.
        $this->assertDatabaseMissing('bids', ['id' => $fx['bid']->id]);
        $this->assertNotContains($fx['operation_id'], array_column($this->operations($fx['user']), 'operation_id'));
    }

    public function test_11_same_program_flight_can_create_a_new_operation_after_real_completion(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);
        $this->telemetry($fx, 'TAXI_OUT');
        $this->telemetry($fx, 'IN', now()->addMinute());
        $this->filePirep($fx['user'], $pirepId)->assertOk();

        /** @var BidService $bids */
        $bids = app(BidService::class);
        $newBid = $bids->addBid($fx['flight']->fresh(), $fx['user']->fresh());

        $this->assertNotSame((string) $fx['bid']->id, (string) $newBid->id);
        $this->assertSame((string) $fx['flight']->id, (string) $newBid->flight_id);
        $this->assertSame('op_'.$newBid->id, app(\Modules\Promethee\Services\OperationIdentityService::class)->id($newBid));
    }

    public function test_12_old_completed_pirep_same_flight_is_never_reused_by_new_operation(): void
    {
        $fx = $this->operationFixture();
        $oldPirepId = $this->prefile($fx);
        $this->telemetry($fx, 'TAXI_OUT');
        $this->telemetry($fx, 'IN', now()->addMinute());
        $this->filePirep($fx['user'], $oldPirepId)->assertOk();

        /** @var BidService $bids */
        $bids = app(BidService::class);
        $newBid = $bids->addBid($fx['flight']->fresh(), $fx['user']->fresh());

        // BidService::getBid() enriches the returned model with a reconciled
        // flight object. Persist only the actual column here so that synthetic
        // presentation attributes are never sent back to the bids table.
        Bid::query()->whereKey($newBid->id)->update([
            'aircraft_id' => $fx['second_aircraft']->id,
        ]);
        $newBid = Bid::query()->findOrFail($newBid->id);

        SimBrief::factory()->create([
            'user_id' => $fx['user']->id,
            'flight_id' => $fx['flight']->id,
            'aircraft_id' => $fx['second_aircraft']->id,
            'pirep_id' => null,
            'ofp_xml' => $this->minimalSimBriefXml(),
            'created_at' => now()->addSecond(),
            'updated_at' => now()->addSecond(),
        ]);

        $newFx = $fx;
        $newFx['bid'] = $newBid;
        $newFx['aircraft'] = $fx['second_aircraft'];
        $newFx['operation_id'] = 'op_'.$newBid->id;

        $newPirepId = $this->prefile($newFx);

        $this->assertNotSame($oldPirepId, $newPirepId);
        $this->assertSame(
            'Hermes ACARS ['.$newFx['operation_id'].']',
            Pirep::findOrFail($newPirepId)->source_name
        );
        $this->assertSame('READY', $this->dispatch($newFx)['status']);
    }

    public function test_13_current_operation_aircraft_remains_usable_after_prefile_with_blocking_enabled(): void
    {
        $fx = $this->operationFixture();
        $this->prefile($fx);

        $eligibility = $this->get(
            '/api/v1/operations/'.$fx['operation_id'].'/aircraft-eligibility',
            [],
            $fx['user']
        )->assertOk()->json('data');

        $availableIds = array_map(
            fn (array $aircraft) => (string) $aircraft['id'],
            $eligibility['available']
        );

        $this->assertContains((string) $fx['aircraft']->id, $availableIds);
        $this->assertDatabaseHas('bids', [
            'id' => $fx['bid']->id,
            'aircraft_id' => $fx['aircraft']->id,
        ]);
    }

    public function test_14_other_operation_cannot_take_same_aircraft_when_blocking_is_enabled(): void
    {
        $fx = $this->operationFixture();
        $this->prefile($fx);

        $otherFlight = $this->addFlight($fx['user'], [
            'dpt_airport_id' => $fx['origin']->id,
            'arr_airport_id' => $fx['destination']->id,
            'active' => true,
            'visible' => true,
        ], $fx['subfleet']->id);

        $otherBid = Bid::query()->create([
            'user_id' => $fx['user']->id,
            'flight_id' => $otherFlight->id,
            'aircraft_id' => null,
        ]);

        $eligibility = $this->get(
            '/api/v1/operations/op_'.$otherBid->id.'/aircraft-eligibility',
            [],
            $fx['user']
        )->assertOk()->json('data');

        $blocked = collect($eligibility['unavailable'])
            ->first(fn (array $aircraft) => (string) $aircraft['id'] === (string) $fx['aircraft']->id);

        $this->assertNotNull($blocked);
        $this->assertContains('ALREADY_BID', array_column($blocked['reasons'], 'code'));

        $this->put(
            '/api/v1/operations/op_'.$otherBid->id.'/aircraft',
            ['aircraft_id' => $fx['aircraft']->id],
            [],
            $fx['user']
        )->assertStatus(409);
    }

    public function test_arrived_without_final_filing_is_explicitly_non_terminal(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);
        $this->telemetry($fx, 'IN');

        $pirep = Pirep::findOrFail($pirepId);
        $pirep->status = PirepStatus::ARRIVED;
        $pirep->state = PirepState::IN_PROGRESS;
        $pirep->submitted_at = null;
        $pirep->save();

        $dispatch = $this->dispatch($fx);
        $this->assertSame('AWAITING_FILING', $dispatch['status']);
        $this->assertSame('AWAITING_FILING', $dispatch['workflow_state']['state']);
        $this->assertSame('REVIEW_AND_FILE', $dispatch['workflow_state']['next_action']['code']);
        $this->assertFalse((bool) $dispatch['terminal']);

        $operation = $this->get('/api/v1/operations/'.$fx['operation_id'], [], $fx['user'])
            ->assertOk()
            ->json('data');
        $this->assertSame('arrived', $operation['status']);
        $this->assertContains($fx['operation_id'], array_column($this->operations($fx['user']), 'operation_id'));
    }

    public function test_portal_projection_keeps_arrived_operation_awaiting_filing(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);
        $this->telemetry($fx, 'IN');

        $pirep = Pirep::findOrFail($pirepId);
        $pirep->status = PirepStatus::ARRIVED;
        $pirep->state = PirepState::IN_PROGRESS;
        $pirep->submitted_at = null;
        $pirep->save();

        $controller = app(\Modules\Promethee\Http\PortalController::class);
        $method = new \ReflectionMethod($controller, 'bookingOperation');
        $method->setAccessible(true);
        $booking = $method->invoke($controller, $fx['bid']->fresh(['flight', 'aircraft']));

        $this->assertSame('AWAITING_FILING', $booking->operation_status);
        $this->assertSame(95, $booking->operation_progress);
        $this->assertSame('Ouvrir le Flight Review', $booking->operation_next_action);
    }

    public function test_repeat_cleanup_removes_only_terminal_stale_bid_before_fresh_operation(): void
    {
        $fx = $this->operationFixture(assignAircraft: false, createOfp: false);
        $staleBid = $fx['bid'];

        $terminalPirep = Pirep::factory()->create([
            'user_id' => $fx['user']->id,
            'airline_id' => $fx['flight']->airline_id,
            'flight_id' => $fx['flight']->id,
            'flight_number' => $fx['flight']->flight_number,
            'dpt_airport_id' => $fx['origin']->id,
            'arr_airport_id' => $fx['destination']->id,
            'source_name' => 'Hermes ACARS [op_'.$staleBid->id.']',
            'state' => PirepState::ACCEPTED,
            'status' => PirepStatus::ARRIVED,
            'submitted_at' => now(),
        ]);
        // A real completed operation has simulator evidence. Without it this
        // fixture would intentionally be classified as a legacy zero-flight
        // ghost and the safety guard must refuse to delete its reservation.
        \App\Models\Acars::factory()->create([
            'pirep_id' => $terminalPirep->id,
            'type' => \App\Models\Enums\AcarsType::FLIGHT_PATH,
        ]);

        $controller = app(\Modules\Promethee\Http\PortalController::class);
        $method = new \ReflectionMethod($controller, 'releaseTerminalReservationsForFlight');
        $method->setAccessible(true);

        $active = $method->invoke($controller, $fx['flight']->fresh(), $fx['user']->fresh());

        $this->assertNull($active);
        $this->assertDatabaseMissing('bids', ['id' => $staleBid->id]);

        /** @var BidService $bids */
        $bids = app(BidService::class);
        $newBid = $bids->addBid($fx['flight']->fresh(), $fx['user']->fresh());

        $this->assertNotSame((string) $staleBid->id, (string) $newBid->id);
        $this->assertSame('op_'.$newBid->id, app(\Modules\Promethee\Services\OperationIdentityService::class)->id($newBid));
    }

    public function test_repeat_cleanup_never_deletes_non_terminal_operation(): void
    {
        $fx = $this->operationFixture(assignAircraft: false, createOfp: false);

        $controller = app(\Modules\Promethee\Http\PortalController::class);
        $method = new \ReflectionMethod($controller, 'releaseTerminalReservationsForFlight');
        $method->setAccessible(true);

        $active = $method->invoke($controller, $fx['flight']->fresh(), $fx['user']->fresh());

        $this->assertNotNull($active);
        $this->assertSame((string) $fx['bid']->id, (string) $active->id);
        $this->assertDatabaseHas('bids', ['id' => $fx['bid']->id]);
        $this->assertSame('AIRCRAFT_REQUIRED', $active->operation_status);
    }

    public function test_legacy_zero_flight_terminal_pirep_is_never_reopened_or_deleted_automatically(): void
    {
        $fx = $this->operationFixture();

        $ghost = Pirep::factory()->create([
            'user_id' => $fx['user']->id,
            'airline_id' => $fx['flight']->airline_id,
            'aircraft_id' => $fx['aircraft']->id,
            'flight_id' => $fx['flight']->id,
            'flight_number' => $fx['flight']->flight_number,
            'dpt_airport_id' => $fx['origin']->id,
            'arr_airport_id' => $fx['destination']->id,
            'source_name' => 'Hermes ACARS ['.$fx['operation_id'].']',
            'state' => PirepState::PENDING,
            'status' => PirepStatus::ARRIVED,
            'submitted_at' => now(),
            'flight_time' => 0,
            'block_off_time' => null,
            'block_on_time' => null,
            'landing_rate' => null,
        ]);

        $this->post(
            '/api/v1/operations/'.$fx['operation_id'].'/pirep',
            [],
            [],
            $fx['user']
        )->assertStatus(409);

        $this->assertDatabaseHas('pireps', ['id' => $ghost->id]);
        $ghost->refresh();
        $this->assertSame(PirepState::PENDING, (int) $ghost->state);
        $this->assertNotNull($ghost->submitted_at);

        $this->artisan('promethee:hermes-pirep-repair', [
            'operation' => $fx['operation_id'],
            '--pirep' => $ghost->id,
        ])->assertSuccessful();

        $this->assertDatabaseHas('pireps', ['id' => $ghost->id]);
    }

    private function operationFixture(bool $assignAircraft = true, bool $createOfp = true): array
    {
        $origin = Airport::factory()->create(['id' => 'H001', 'icao' => 'H001', 'iata' => 'H01']);
        $destination = Airport::factory()->create(['id' => 'H002', 'icao' => 'H002', 'iata' => 'H02']);

        $fleet = $this->createSubfleetWithAircraft(2, $origin->id);
        $subfleet = $fleet['subfleet'];
        $aircraft = $fleet['aircraft']->values()->get(0);
        $secondAircraft = $fleet['aircraft']->values()->get(1);

        foreach ([$aircraft, $secondAircraft] as $plane) {
            $plane->status = AircraftStatus::ACTIVE;
            $plane->state = AircraftState::PARKED;
            $plane->airport_id = $origin->id;
            $plane->save();
        }

        $rank = $this->createRank(2, [$subfleet->id]);
        $user = User::factory()->create([
            'airline_id' => $subfleet->airline_id,
            'rank_id' => $rank->id,
            'flight_time' => 1000,
            'state' => \App\Models\Enums\UserState::ACTIVE,
            'curr_airport_id' => $origin->id,
            'home_airport_id' => $origin->id,
        ]);

        $flight = $this->addFlight($user, [
            'dpt_airport_id' => $origin->id,
            'arr_airport_id' => $destination->id,
            'route' => 'DCT HERMES',
            'level' => 330,
            'active' => true,
            'visible' => true,
        ], $subfleet->id);

        $bid = Bid::query()->create([
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'aircraft_id' => $assignAircraft ? $aircraft->id : null,
        ]);

        if ($assignAircraft && $createOfp) {
            SimBrief::factory()->create([
                'user_id' => $user->id,
                'flight_id' => $flight->id,
                'aircraft_id' => $aircraft->id,
                'pirep_id' => null,
                'ofp_xml' => $this->minimalSimBriefXml(),
                'created_at' => now()->addSecond(),
                'updated_at' => now()->addSecond(),
            ]);
        }

        return [
            'origin' => $origin,
            'destination' => $destination,
            'subfleet' => $subfleet,
            'aircraft' => $aircraft,
            'second_aircraft' => $secondAircraft,
            'user' => $user,
            'flight' => $flight,
            'bid' => $bid,
            'operation_id' => 'op_'.$bid->id,
        ];
    }

    public function test_scoring_telemetry_fields_are_preserved_for_debrief(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);
        $at = now();

        $this->post(
            '/api/v1/operations/'.$fx['operation_id'].'/telemetry',
            ['samples' => [[
                'sample_id' => (string) Str::uuid(),
                'recorded_at' => $at->toIso8601String(),
                'phase' => 'LANDING',
                'lat' => 48.7,
                'lon' => 2.3,
                'altitude_msl' => 900,
                'agl' => 600,
                'ias' => 140,
                'gs' => 125,
                'vs' => -500,
                'heading' => 180,
                'fuel' => 5000,
                'bank' => 3,
                'pitch' => 2,
                'g_force' => 1.15,
                'on_ground' => false,
                'engines_running' => [true, true],
                'beacon_light' => true,
                'landing_light' => true,
                'slew_active' => false,
                'simulation_rate' => 1,
                'overspeed_warning' => false,
                'stall_warning' => false,
                'reverser_percent' => [0, 0],
            ]]],
            [],
            $fx['user']
        )->assertOk();

        $row = DB::table('promethee_telemetry')
            ->where('pirep_id', $pirepId)
            ->orderByDesc('recorded_at')
            ->first();

        $this->assertNotNull($row);
        $payload = json_decode((string) $row->payload, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame([true, true], $payload['engines_running']);
        $this->assertTrue($payload['beacon_light']);
        $this->assertTrue($payload['landing_light']);
        $this->assertSame(1.15, (float) $payload['g_force']);
        $this->assertFalse($payload['overspeed_warning']);
        $this->assertFalse($payload['stall_warning']);
        $this->assertSame([0, 0], $payload['reverser_percent']);
        $this->assertSame(1.0, (float) $payload['simulation_rate']);
    }

    public function test_scoring_tolerates_mixed_telemetry_schema_after_hermes_update(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);
        $at = now();

        // Legacy sample: recorded before the scoring telemetry fields existed.
        $this->post(
            '/api/v1/operations/'.$fx['operation_id'].'/telemetry',
            ['samples' => [[
                'sample_id' => (string) Str::uuid(),
                'recorded_at' => $at->toIso8601String(),
                'phase' => 'CRUISE',
                'lat' => 48.7,
                'lon' => 2.3,
                'altitude_msl' => 25000,
                'agl' => 24000,
                'ias' => 280,
                'gs' => 420,
                'vs' => 0,
                'heading' => 180,
                'fuel' => 5000,
                'on_ground' => false,
            ]]],
            [],
            $fx['user']
        )->assertOk();

        // Current sample: same PIREP after Hermès has been updated/restarted.
        $this->post(
            '/api/v1/operations/'.$fx['operation_id'].'/telemetry',
            ['samples' => [[
                'sample_id' => (string) Str::uuid(),
                'recorded_at' => $at->copy()->addSeconds(20)->toIso8601String(),
                'phase' => 'CRUISE',
                'lat' => 48.71,
                'lon' => 2.31,
                'altitude_msl' => 25000,
                'agl' => 24000,
                'ias' => 280,
                'gs' => 420,
                'vs' => 0,
                'heading' => 180,
                'fuel' => 4950,
                'on_ground' => false,
                'engines_running' => [true, true],
                'beacon_light' => true,
                'landing_light' => false,
                'g_force' => 1.0,
                'overspeed_warning' => false,
                'stall_warning' => false,
                'reverser_percent' => [0, 0],
                'slew_active' => false,
                'simulation_rate' => 1.0,
            ]]],
            [],
            $fx['user']
        )->assertOk();

        $score = app(\Modules\Promethee\Services\LegacyPirepScoringService::class)
            ->calculate(Pirep::findOrFail($pirepId), $fx['operation_id']);

        $this->assertTrue((bool) $score['available']);
        $this->assertArrayHasKey('score', $score);
        $this->assertIsInt($score['score']);
    }

    private function minimalSimBriefXml(): string
    {
        return <<<'XML'
<ofp>
  <general>
    <route>DCT HERMES</route>
    <initial_altitude>33000</initial_altitude>
  </general>
  <navlog>
    <fix>
      <ident>HERMES</ident>
      <type>wpt</type>
      <pos_lat>48.7000</pos_lat>
      <pos_long>2.3000</pos_long>
    </fix>
  </navlog>
</ofp>
XML;
    }

    private function prefile(array $fx): string
    {
        $response = $this->post(
            '/api/v1/operations/'.$fx['operation_id'].'/pirep',
            [],
            [],
            $fx['user']
        );

        $this->assertSame(
            201,
            $response->status(),
            'Hermès PREFILE rejected: '.$response->getContent()
        );

        return (string) $response->json('data.pirep_id');
    }

    private function dispatch(array $fx): array
    {
        return $this->get(
            '/api/v1/operations/'.$fx['operation_id'].'/dispatch',
            [],
            $fx['user']
        )->assertOk()->json('data');
    }

    private function operations(User $user): array
    {
        return $this->get('/api/v1/operations', [], $user)
            ->assertOk()
            ->json('data.operations');
    }

    private function telemetry(array $fx, string $phase, $at = null): void
    {
        $at ??= now();

        $this->post(
            '/api/v1/operations/'.$fx['operation_id'].'/telemetry',
            ['samples' => [[
                'sample_id' => (string) Str::uuid(),
                'recorded_at' => $at->toIso8601String(),
                'phase' => $phase,
                'lat' => 48.7,
                'lon' => 2.3,
                'altitude_msl' => $phase === 'IN' ? 300 : 5000,
                'agl' => $phase === 'IN' ? 0 : 4700,
                'ias' => $phase === 'IN' ? 0 : 220,
                'gs' => $phase === 'IN' ? 0 : 240,
                'vs' => 0,
                'heading' => 180,
                'fuel' => 5000,
                'on_ground' => in_array($phase, ['BOARDING', 'TAXI_OUT', 'IN'], true),
            ]]],
            [],
            $fx['user']
        )->assertOk();
    }

    private function filePirep(User $user, string $pirepId)
    {
        return $this->post(
            '/api/pireps/'.$pirepId.'/file',
            [
                'distance' => 250,
                'flight_time' => 55,
                'fuel_used' => 1700,
                'block_time' => 65,
                'block_off_time' => now()->subMinutes(65)->toIso8601String(),
                'block_on_time' => now()->toIso8601String(),
                'landing_rate' => -180,
            ],
            [],
            $user
        );
    }
}
