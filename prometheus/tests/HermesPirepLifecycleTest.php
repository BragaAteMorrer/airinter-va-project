<?php

namespace Tests;

use App\Models\Enums\PirepSource;
use App\Models\Enums\PirepState;
use App\Models\Enums\PirepStatus;
use App\Models\Pirep;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Promethee\Services\HermesPirepLifecycleService;

final class HermesPirepLifecycleTest extends TestCase
{
    public function test_arrived_phase_is_not_the_same_as_a_filed_pirep(): void
    {
        $pirep = $this->hermesDraft('op_lifecycle-arrived');
        $pirep->status = PirepStatus::ARRIVED;
        $pirep->save();

        /** @var HermesPirepLifecycleService $lifecycle */
        $lifecycle = app(HermesPirepLifecycleService::class);

        $this->assertFalse($lifecycle->isFiled($pirep));
        $this->assertFalse($lifecycle->isAwaitingFiling($pirep));

        $this->insertTelemetry($pirep, 'IN');

        $pirep->refresh();
        $this->assertFalse($lifecycle->isFiled($pirep));
        $this->assertTrue($lifecycle->isAwaitingFiling($pirep));
    }

    public function test_final_filing_requires_phpvms_final_state_and_submitted_at(): void
    {
        $pirep = $this->hermesDraft('op_lifecycle-filed');

        /** @var HermesPirepLifecycleService $lifecycle */
        $lifecycle = app(HermesPirepLifecycleService::class);

        $pirep->state = PirepState::PENDING;
        $pirep->submitted_at = null;
        $pirep->save();
        $this->assertFalse($lifecycle->isFiled($pirep));

        $pirep->submitted_at = now();
        $pirep->save();
        $this->assertTrue($lifecycle->isFiled($pirep));
    }

    public function test_hermes_file_endpoint_cannot_complete_prefile_without_real_flight_and_in(): void
    {
        $pirep = $this->hermesDraft('op_file-guard');

        $payload = [
            'distance' => 120,
            'flight_time' => 55,
            'fuel_used' => 900,
            'block_time' => 70,
            'block_off_time' => Carbon::now('UTC')->subMinutes(70)->toIso8601String(),
            'block_on_time' => Carbon::now('UTC')->toIso8601String(),
            'landing_rate' => -220,
        ];

        // PREFILE only: an old/cached Hermès client must not be able to mark
        // the operation completed by calling phpVMS' final filing endpoint.
        $this->post('/api/pireps/'.$pirep->id.'/file', $payload, [], $this->user)
            ->assertStatus(409);

        $pirep->refresh();
        $this->assertSame(PirepState::IN_PROGRESS, (int) $pirep->state);
        $this->assertNull($pirep->submitted_at);

        // Flight telemetry exists, but the aircraft has not reached IN yet.
        $this->insertTelemetry($pirep, 'CRUISE');

        $this->post('/api/pireps/'.$pirep->id.'/file', $payload, [], $this->user)
            ->assertStatus(409);

        $pirep->refresh();
        $this->assertSame(PirepState::IN_PROGRESS, (int) $pirep->state);
        $this->assertNull($pirep->submitted_at);

        // Only after Hermès has emitted the final IN phase may FILE change the
        // report from the active draft to phpVMS' terminal filing lifecycle.
        $this->insertTelemetry($pirep, 'IN');

        $this->post('/api/pireps/'.$pirep->id.'/file', $payload, [], $this->user)
            ->assertOk();

        $pirep->refresh();
        $this->assertNotSame(PirepState::IN_PROGRESS, (int) $pirep->state);
        $this->assertNotNull($pirep->submitted_at);
    }

    public function test_simbrief_route_rows_do_not_count_as_flight_evidence(): void
    {
        $pirep = $this->hermesDraft('op_route-only');
        DB::table('acars')->insert([
            'pirep_id' => $pirep->id,
            'type' => \App\Models\Enums\AcarsType::ROUTE,
            'name' => 'DCT',
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /** @var HermesPirepLifecycleService $lifecycle */
        $lifecycle = app(HermesPirepLifecycleService::class);

        $this->assertFalse($lifecycle->hasFlightEvidence($pirep));
    }

    private function hermesDraft(string $operationId): Pirep
    {
        $pirep = $this->createPirep([], [
            'source' => PirepSource::ACARS,
            'source_name' => 'Hermes ACARS ['.$operationId.']',
            'state' => PirepState::IN_PROGRESS,
            'status' => PirepStatus::INITIATED,
            'submitted_at' => null,
            'flight_time' => 0,
            'distance' => 0,
            'fuel_used' => 0,
            'block_off_time' => null,
            'block_on_time' => null,
            'landing_rate' => null,
        ]);
        $pirep->save();

        return $pirep;
    }

    private function insertTelemetry(Pirep $pirep, string $phase): void
    {
        DB::table('promethee_telemetry')->insert([
            'pirep_id' => $pirep->id,
            'sample_id' => (string) Str::uuid(),
            'recorded_at' => now(),
            'payload' => json_encode([
                'phase' => $phase,
                'lat' => 48.7,
                'lon' => 2.3,
                'altitude_msl' => $phase === 'IN' ? 300 : 31000,
                'gs' => $phase === 'IN' ? 0 : 430,
                'on_ground' => $phase === 'IN',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
