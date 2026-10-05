<?php

namespace Tests;

use App\Models\Airport;
use App\Models\Bid;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Enums\FareType;
use App\Models\Enums\PirepState;
use App\Models\Enums\PirepStatus;
use App\Models\Fare;
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

    public function test_04b_prefile_persists_simbrief_passengers_in_pirep_fares(): void
    {
        $fx = $this->operationFixture();

        $fare = Fare::factory()->create([
            'code' => 'Y',
            'name' => 'Economy',
            'type' => FareType::PASSENGER,
            'capacity' => 150,
            'active' => true,
        ]);
        $fx['subfleet']->fares()->syncWithoutDetaching([
            $fare->id => ['capacity' => 150],
        ]);

        $ofp = SimBrief::query()
            ->where('user_id', $fx['user']->id)
            ->where('flight_id', $fx['flight']->id)
            ->where('aircraft_id', $fx['aircraft']->id)
            ->firstOrFail();
        $ofp->fare_data = json_encode([[
            'id' => $fare->id,
            'fare_id' => $fare->id,
            'code' => 'Y',
            'name' => 'Economy',
            'type' => FareType::PASSENGER,
            'capacity' => 150,
        ]]);
        $ofp->ofp_xml = str_replace(
            '</general>',
            '</general><weights><pax_count>86</pax_count></weights>',
            $this->minimalSimBriefXml()
        );
        $ofp->save();

        $pirepId = $this->prefile($fx);

        $this->assertDatabaseHas('pirep_fares', [
            'pirep_id' => $pirepId,
            'code' => 'Y',
            'type' => FareType::PASSENGER,
            'count' => 86,
        ]);
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

    public function test_pilot_can_abandon_own_active_pirep_and_keep_reservation(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);
        $this->telemetry($fx, 'CLIMB');

        $this->delete('/api/v1/pireps/'.$pirepId, [], [], $fx['user'])
            ->assertOk()
            ->assertJsonPath('data.deleted', true)
            ->assertJsonPath('data.pirep_id', $pirepId)
            ->assertJsonPath('data.operation_id', $fx['operation_id']);

        $this->assertDatabaseMissing('pireps', ['id' => $pirepId]);
        $this->assertDatabaseMissing('promethee_telemetry', ['pirep_id' => $pirepId]);
        $this->assertDatabaseMissing('promethee_pirep_aircraft_profiles', ['pirep_id' => $pirepId]);
        $this->assertDatabaseHas('bids', ['id' => $fx['bid']->id, 'user_id' => $fx['user']->id]);

        $dispatch = $this->dispatch($fx);
        $this->assertFalse((bool) $dispatch['server_checks']['pirep']);
        $this->assertNotSame('COMPLETED', $dispatch['status']);
    }

    public function test_pilot_can_delete_own_pending_pirep_before_acceptance(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);
        $pirep = Pirep::findOrFail($pirepId);
        $pirep->state = PirepState::PENDING;
        $pirep->status = PirepStatus::ARRIVED;
        $pirep->submitted_at = now();
        $pirep->save();

        $this->delete('/api/v1/pireps/'.$pirepId, [], [], $fx['user'])
            ->assertOk();

        $this->assertDatabaseMissing('pireps', ['id' => $pirepId]);
    }

    public function test_pilot_cannot_delete_another_users_pirep(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);
        $other = User::factory()->create([
            'airline_id' => $fx['user']->airline_id,
            'rank_id' => $fx['user']->rank_id,
            'state' => \App\Models\Enums\UserState::ACTIVE,
            'curr_airport_id' => $fx['origin']->id,
            'home_airport_id' => $fx['origin']->id,
        ]);

        $this->delete('/api/v1/pireps/'.$pirepId, [], [], $other)
            ->assertStatus(403);

        $this->assertDatabaseHas('pireps', ['id' => $pirepId]);
    }

    public function test_pilot_cannot_delete_accepted_pirep(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);
        $pirep = Pirep::findOrFail($pirepId);
        $pirep->state = PirepState::ACCEPTED;
        $pirep->status = PirepStatus::ARRIVED;
        $pirep->submitted_at = now();
        $pirep->save();

        $this->delete('/api/v1/pireps/'.$pirepId, [], [], $fx['user'])
            ->assertStatus(409);

        $this->assertDatabaseHas('pireps', ['id' => $pirepId, 'state' => PirepState::ACCEPTED]);
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

    public function test_pirep_journal_is_reconstructed_from_hermes_telemetry_when_acars_logs_are_missing(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);
        $at = now();

        $this->telemetry($fx, 'BOARDING', $at);
        $this->telemetry($fx, 'TAKEOFF', $at->copy()->addSeconds(30));
        $this->telemetry($fx, 'IN', $at->copy()->addMinutes(2));

        $pirep = Pirep::with('acars_logs')->findOrFail($pirepId);
        $this->assertCount(0, $pirep->acars_logs);

        // Journal reconstruction is a domain service now; keep this test
        // coupled to the actual owner instead of the old PortalController seam.
        $journal = app(\Modules\Promethee\Services\PirepJournalService::class)
            ->build($pirep);

        $codes = $journal->pluck('code')->all();
        $this->assertContains('BOARDING', $codes);
        $this->assertContains('TAKEOFF', $codes);
        $this->assertContains('IN', $codes);
        $this->assertSame('TÉLÉMÉTRIE HERMÈS', $journal->firstWhere('code', 'TAKEOFF')['source']);
        $this->assertSame('Décollage', $journal->firstWhere('code', 'TAKEOFF')['message']);
    }

    public function test_pirep_journal_reconstructs_aircraft_system_changes_from_rich_telemetry(): void
    {
        $fx = $this->operationFixture();
        $pirepId = $this->prefile($fx);
        $at = now();

        $this->telemetry($fx, 'BOARDING', $at, [
            'altitude_msl' => 300,
            'on_ground' => true,
            'parking_brake' => true,
            'gear_down' => true,
            'flaps_percent' => 0,
            'engines_running' => [false, false],
            'beacon_light' => false,
            'navigation_light' => false,
            'strobe_light' => false,
            'landing_light' => false,
            'taxi_light' => false,
            'logo_light' => false,
            'wing_light' => false,
            'spoilers_armed' => false,
            'apu_running' => false,
            'apu_rpm_percent' => 0,
            'battery_on' => true,
            'external_power_on' => true,
            'seatbelt_sign' => false,
            'doors_open' => true,
            'autopilot_enabled' => false,
            'autothrottle_armed' => false,
            'simulation_rate' => 1,
            'qnh_hpa' => 1018,
            'oat_c' => 17,
            'wind_speed' => 4,
            'wind_direction' => 260,
            'transponder_code' => 1200,
            'aircraft_icao' => 'A320',
            'aircraft_model' => 'FenixA320 CFM WF',
            'aircraft_title' => 'Fenix A320 Air Inter F-GGEB',
        ]);
        $this->telemetry($fx, 'PUSHBACK', $at->copy()->addSeconds(10), [
            'altitude_msl' => 300,
            'on_ground' => true,
            'parking_brake' => false,
            'gear_down' => true,
            'flaps_percent' => 18,
            'engines_running' => [true, true],
            'beacon_light' => true,
            'navigation_light' => true,
            'strobe_light' => false,
            'landing_light' => false,
            'taxi_light' => true,
            'logo_light' => true,
            'wing_light' => false,
            'spoilers_armed' => false,
            'apu_running' => true,
            'apu_rpm_percent' => 100,
            'battery_on' => true,
            'external_power_on' => false,
            'seatbelt_sign' => true,
            'doors_open' => false,
            'autopilot_enabled' => false,
            'autothrottle_armed' => true,
            'transponder_code' => 5723,
        ]);
        $this->telemetry($fx, 'TAKEOFF', $at->copy()->addSeconds(40), [
            'altitude_msl' => 600,
            'on_ground' => false,
            'parking_brake' => false,
            'gear_down' => false,
            'flaps_percent' => 18,
            'engines_running' => [true, true],
            'beacon_light' => true,
            'navigation_light' => true,
            'transponder_code' => 5723,
        ]);
        $this->telemetry($fx, 'CLIMB', $at->copy()->addMinutes(2), [
            'altitude_msl' => 10500,
            'on_ground' => false,
            'parking_brake' => false,
            'gear_down' => false,
            'flaps_percent' => 0,
            'engines_running' => [true, true],
            'beacon_light' => true,
            'navigation_light' => true,
            'transponder_code' => 5723,
        ]);

        $pirep = Pirep::with('acars_logs')->findOrFail($pirepId);
        $journal = app(\Modules\Promethee\Services\PirepJournalService::class)->build($pirep);
        $codes = $journal->pluck('code')->all();

        $this->assertContains('AIRCRAFT_IDENTIFIED', $codes);
        $this->assertContains('ENGINE_1_OFF', $codes);
        $this->assertContains('ENGINE_2_OFF', $codes);
        $this->assertContains('BATTERY_ON', $codes);
        $this->assertContains('EXTERNAL_POWER_ON', $codes);
        $this->assertContains('APU_OFF', $codes);
        $this->assertContains('TRANSPONDER_SET', $codes);
        $this->assertContains('SIM_RATE_INITIAL', $codes);
        $this->assertContains('WEATHER_INITIAL', $codes);
        $this->assertContains('PARKING_BRAKE_RELEASED', $codes);
        $this->assertContains('ENGINE_1_ON', $codes);
        $this->assertContains('ENGINE_2_ON', $codes);
        $this->assertContains('BEACON_ON', $codes);
        $this->assertContains('NAV_LIGHTS_ON', $codes);
        $this->assertContains('TRANSPONDER_CHANGED', $codes);
        $this->assertContains('GEAR_UP', $codes);
        $this->assertContains('FLAPS_UP', $codes);
        $this->assertContains('CROSS_10000_UP', $codes);
        $this->assertContains('TAKEOFF_DATA', $codes);
        $this->assertContains('TAXI_OUT_TIME', $codes);
    }

    public function test_15_reference_pilot_journey_crosses_the_full_operation_contract(): void
    {
        $fx = $this->operationFixture();

        $operation = $this->get(
            '/api/v1/operations/'.$fx['operation_id'],
            [],
            $fx['user']
        )->assertOk()->json('data');

        $this->assertSame($fx['operation_id'], $operation['operation_id']);
        $this->assertSame('planned', $operation['status']);
        $this->assertTrue((bool) $operation['simbrief']['available']);
        $this->assertSame($fx['aircraft']->id, $operation['aircraft']['id']);

        $pirepId = $this->prefile($fx);
        $ready = $this->dispatch($fx);
        $this->assertSame('READY', $ready['status']);
        $this->assertSame('START_RECORDING', $ready['workflow_state']['next_action']['code']);
        $this->assertSame($pirepId, $ready['pirep']['id']);

        $at = now();
        $phases = [
            'BOARDING',
            'TAXI_OUT',
            'TAKEOFF',
            'CLIMB',
            'CRUISE',
            'DESCENT',
            'APPROACH',
            'FINAL',
            'LANDING',
            'TAXI_IN',
            'IN',
        ];

        foreach ($phases as $index => $phase) {
            $this->telemetry($fx, $phase, $at->copy()->addSeconds($index + 1));
        }

        $active = $this->dispatch($fx);
        $this->assertNotSame('COMPLETED', $active['status']);
        $this->assertSame($pirepId, $active['pirep']['id']);
        $this->assertDatabaseHas('bids', ['id' => $fx['bid']->id]);
        $this->assertContains($fx['operation_id'], array_column($this->operations($fx['user']), 'operation_id'));

        $rehydrated = $this->get(
            '/api/v1/operations/'.$fx['operation_id'].'/pirep',
            [],
            $fx['user']
        )->assertOk()->json('data');
        $this->assertSame($pirepId, $rehydrated['pirep_id']);

        $this->assertGreaterThanOrEqual(
            count($phases),
            DB::table('promethee_telemetry')->where('pirep_id', $pirepId)->count()
        );
        $this->assertSame(
            1,
            Pirep::where('source_name', 'Hermes ACARS ['.$fx['operation_id'].']')->count()
        );

        $this->filePirep($fx['user'], $pirepId)->assertOk();

        $pirep = Pirep::findOrFail($pirepId);
        $this->assertNotNull($pirep->submitted_at);
        $this->assertTrue(app(HermesPirepLifecycleService::class)->isFiled($pirep));
        $this->assertDatabaseMissing('bids', ['id' => $fx['bid']->id]);
        $this->assertNotContains($fx['operation_id'], array_column($this->operations($fx['user']), 'operation_id'));
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
                'track' => 178,
                'mach' => 0.42,
                'fuel' => 5000,
                'gross_weight' => 132000,
                'qnh_hpa' => 1013,
                'oat_c' => 12,
                'wind_speed' => 18,
                'wind_direction' => 240,
                'bank' => 3,
                'pitch' => 2,
                'g_force' => 1.15,
                'on_ground' => false,
                'parking_brake' => false,
                'gear_down' => true,
                'flaps_percent' => 35,
                'spoilers_armed' => true,
                'engines_running' => [true, true],
                'beacon_light' => true,
                'navigation_light' => true,
                'strobe_light' => true,
                'landing_light' => true,
                'taxi_light' => false,
                'logo_light' => true,
                'wing_light' => false,
                'apu_running' => true,
                'apu_rpm_percent' => 96,
                'battery_on' => true,
                'external_power_on' => false,
                'autothrottle_armed' => false,
                'seatbelt_sign' => true,
                'doors_open' => false,
                'transponder_code' => 5723,
                'autopilot_enabled' => false,
                'aircraft_title' => 'Fenix A320 Air Inter F-GGEB',
                'aircraft_icao' => 'A320',
                'aircraft_model' => 'FenixA320 CFM WF',
                'touchdown_rate' => -223,
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
        $this->assertFalse($payload['parking_brake']);
        $this->assertTrue($payload['gear_down']);
        $this->assertSame(35.0, (float) $payload['flaps_percent']);
        $this->assertTrue($payload['spoilers_armed']);
        $this->assertTrue($payload['beacon_light']);
        $this->assertTrue($payload['navigation_light']);
        $this->assertTrue($payload['strobe_light']);
        $this->assertTrue($payload['landing_light']);
        $this->assertFalse($payload['taxi_light']);
        $this->assertTrue($payload['logo_light']);
        $this->assertFalse($payload['wing_light']);
        $this->assertTrue($payload['apu_running']);
        $this->assertSame(96.0, (float) $payload['apu_rpm_percent']);
        $this->assertTrue($payload['battery_on']);
        $this->assertFalse($payload['external_power_on']);
        $this->assertFalse($payload['autothrottle_armed']);
        $this->assertTrue($payload['seatbelt_sign']);
        $this->assertFalse($payload['doors_open']);
        $this->assertSame(5723, (int) $payload['transponder_code']);
        $this->assertFalse($payload['autopilot_enabled']);
        $this->assertSame('Fenix A320 Air Inter F-GGEB', $payload['aircraft_title']);
        $this->assertSame('A320', $payload['aircraft_icao']);
        $this->assertSame('FenixA320 CFM WF', $payload['aircraft_model']);
        $this->assertSame(-223.0, (float) $payload['touchdown_rate']);
        $this->assertSame(1.15, (float) $payload['g_force']);
        $this->assertFalse($payload['overspeed_warning']);
        $this->assertFalse($payload['stall_warning']);
        $this->assertSame([0, 0], $payload['reverser_percent']);
        $this->assertSame(1.0, (float) $payload['simulation_rate']);
    }

    public function test_scoring_tolerates_mixed_telemetry_schema_after_hermes_update(): void
    {
        $service = app(\Modules\Promethee\Services\LegacyPirepScoringService::class);
        $method = new \ReflectionMethod($service, 'telemetryEpisodesIfAvailable');
        $method->setAccessible(true);

        $at = now();
        $samples = [
            [
                'recorded_at' => $at->toIso8601String(),
                // Legacy Hermès sample: simulation_rate did not exist yet.
            ],
            [
                'recorded_at' => $at->copy()->addSeconds(20)->toIso8601String(),
                'simulation_rate' => 2.0,
            ],
            [
                'recorded_at' => $at->copy()->addSeconds(35)->toIso8601String(),
                'simulation_rate' => 2.0,
            ],
        ];

        $episodes = $method->invoke(
            $service,
            $samples,
            ['simulation_rate'],
            fn ($sample) => (float) $sample['simulation_rate'] > 1,
            10,
            fn ($sample) => (float) $sample['simulation_rate']
        );

        $this->assertIsArray($episodes);
        $this->assertCount(1, $episodes);
        $this->assertSame(2.0, (float) $episodes[0]['value']);
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

    private function telemetry(array $fx, string $phase, $at = null, array $extra = []): void
    {
        $at ??= now();

        $sample = array_merge([
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
        ], $extra);

        $this->post(
            '/api/v1/operations/'.$fx['operation_id'].'/telemetry',
            ['samples' => [$sample]],
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
