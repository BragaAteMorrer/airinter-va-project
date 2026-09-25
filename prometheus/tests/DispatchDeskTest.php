<?php

namespace Tests;

use App\Models\Bid;
use App\Models\Enums\PirepSource;
use App\Models\Enums\PirepState;
use App\Models\Enums\PirepStatus;
use App\Models\Flight;
use App\Models\Pirep;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Promethee\Services\DatalinkService;
use Modules\Promethee\Services\DispatchDeskService;
use Modules\Promethee\Services\FlightOpsService;
use Modules\Promethee\Services\OperationIdentityService;
use Modules\Promethee\Services\SafetyAnalyzer;

final class DispatchDeskTest extends TestCase
{
    private string $datalinkFolder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->datalinkFolder = sys_get_temp_dir().DIRECTORY_SEPARATOR.'promethee-dispatch-'.bin2hex(random_bytes(8));
        mkdir($this->datalinkFolder, 0770, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->datalinkFolder)) {
            foreach (glob($this->datalinkFolder.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($this->datalinkFolder);
        }

        parent::tearDown();
    }

    public function test_board_exposes_live_hermes_operation_and_ignores_completed_flight(): void
    {
        [$pilot, $flight, $aircraft, $bid] = $this->operation();
        $pirep = $this->activePirep($pilot, $flight, $aircraft->id, $bid);

        DB::table('promethee_telemetry')->insert([
            'pirep_id' => $pirep->id,
            'sample_id' => (string) Str::uuid(),
            'recorded_at' => now()->subSeconds(5),
            'payload' => json_encode([
                'phase' => 'ENROUTE',
                'lat' => 48.2,
                'lon' => 1.8,
                'altitude_msl' => 31000,
                'ias' => 280,
                'gs' => 430,
                'vs' => 0,
                'heading' => 270,
                'fuel' => 4200,
                'on_ground' => false,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $completedFlight = Flight::factory()->create(['airline_id' => $flight->airline_id]);
        $completedBid = Bid::query()->create([
            'user_id' => $pilot->id,
            'flight_id' => $completedFlight->id,
            'aircraft_id' => $aircraft->id,
        ]);
        Pirep::factory()->create([
            'user_id' => $pilot->id,
            'aircraft_id' => $aircraft->id,
            'flight_id' => $completedFlight->id,
            'flight_number' => $completedFlight->flight_number,
            'dpt_airport_id' => $completedFlight->dpt_airport_id,
            'arr_airport_id' => $completedFlight->arr_airport_id,
            'source' => PirepSource::ACARS,
            'source_name' => 'Hermes ACARS [op_'.$completedBid->id.']',
            'state' => PirepState::ACCEPTED,
            'status' => PirepStatus::ARRIVED,
            'submitted_at' => now(),
        ]);

        $board = $this->dispatch()->board();

        $this->assertSame(1, $board['summary']['operations']);
        $this->assertSame(1, $board['summary']['airborne']);
        $operation = $board['operations'][0];
        $this->assertSame('op_'.$bid->id, $operation['operation_id']);
        $this->assertSame('IN_PROGRESS', $operation['status']);
        $this->assertSame('ENROUTE', $operation['phase']);
        $this->assertSame('LIVE', $operation['signal']['state']);
        $this->assertSame(31000.0, (float) $operation['live']['altitude']);
        $this->assertSame(430.0, (float) $operation['live']['gs']);
    }

    public function test_detail_aggregates_fdm_track_timeline_and_pending_ops_ack(): void
    {
        [$pilot, $flight, $aircraft, $bid] = $this->operation();
        $pirep = $this->activePirep($pilot, $flight, $aircraft->id, $bid);

        foreach ([
            ['phase' => 'TAXI_OUT', 'lat' => 48.72, 'lon' => 2.36, 'altitude_msl' => 300, 'gs' => 18, 'on_ground' => true],
            ['phase' => 'TAKEOFF', 'lat' => 48.75, 'lon' => 2.30, 'altitude_msl' => 1800, 'gs' => 155, 'on_ground' => false],
            ['phase' => 'CLIMB', 'lat' => 48.80, 'lon' => 2.10, 'altitude_msl' => 6500, 'gs' => 250, 'on_ground' => false],
        ] as $index => $payload) {
            DB::table('promethee_telemetry')->insert([
                'pirep_id' => $pirep->id,
                'sample_id' => (string) Str::uuid(),
                'recorded_at' => now()->subSeconds(20 - ($index * 5)),
                'payload' => json_encode($payload + [
                    'ias' => $payload['gs'],
                    'vs' => $index === 0 ? 0 : 1800,
                    'heading' => 250,
                    'fuel' => 5000 - ($index * 100),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $datalink = new DatalinkService($this->datalinkFolder);
        $message = $datalink->send(
            'op_'.$bid->id,
            (int) $pilot->id,
            'COCKPIT_TO_OPS',
            'DISPATCH',
            'HIGH',
            'Request fuel check.',
            true,
            $pilot->pilot_id ?: 'PILOT'
        );

        $detail = $this->dispatch($datalink)->detail('op_'.$bid->id);

        $this->assertSame('op_'.$bid->id, $detail['operation']['operation_id']);
        $this->assertSame(3, $detail['track']['sample_count']);
        $this->assertNotEmpty($detail['track']['points']);
        $this->assertSame('LOT_6_PENDING', $detail['sop']['status']);
        $this->assertSame(1, $detail['operation']['datalink']['pending_ops_ack']);
        $this->assertSame($message['id'], $detail['messages']['messages'][0]['id']);
        $this->assertNotEmpty($detail['timeline']);
        $this->assertSame(3, $detail['fdm']['data_quality']['samples']);
    }

    public function test_dispatch_routes_are_admin_only(): void
    {
        $this->get('/admin/promethee/dispatch')->assertRedirect();

        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'web')
            ->get('/admin/promethee/dispatch')
            ->assertOk()
            ->assertSee('DISPATCH DESK', false);

        $this->actingAs($admin, 'web')
            ->get('/admin/promethee/dispatch/data')
            ->assertOk()
            ->assertJsonPath('data.contract_version', '1.0');
    }

    private function dispatch(?DatalinkService $datalink = null): DispatchDeskService
    {
        return new DispatchDeskService(
            app(OperationIdentityService::class),
            $datalink ?? new DatalinkService($this->datalinkFolder),
            app(FlightOpsService::class),
            app(SafetyAnalyzer::class),
        );
    }

    private function operation(): array
    {
        $fleet = $this->createSubfleetWithAircraft(1);
        $aircraft = $fleet['aircraft']->first();

        $pilot = User::factory()->create([
            'airline_id' => $fleet['subfleet']->airline_id,
        ]);

        $flight = Flight::factory()->create([
            'airline_id' => $fleet['subfleet']->airline_id,
            'route' => 'LGL BANOX',
            'level' => 310,
        ]);
        $flight->subfleets()->syncWithoutDetaching([$fleet['subfleet']->id]);

        $bid = Bid::query()->create([
            'user_id' => $pilot->id,
            'flight_id' => $flight->id,
            'aircraft_id' => $aircraft->id,
        ]);

        return [$pilot, $flight, $aircraft, $bid];
    }

    private function activePirep(User $pilot, Flight $flight, int $aircraftId, Bid $bid): Pirep
    {
        return Pirep::factory()->create([
            'user_id' => $pilot->id,
            'aircraft_id' => $aircraftId,
            'flight_id' => $flight->id,
            'flight_number' => $flight->flight_number,
            'dpt_airport_id' => $flight->dpt_airport_id,
            'arr_airport_id' => $flight->arr_airport_id,
            'source' => PirepSource::ACARS,
            'source_name' => 'Hermes ACARS [op_'.$bid->id.']',
            'state' => PirepState::IN_PROGRESS,
            'status' => PirepStatus::ENROUTE,
            'submitted_at' => null,
            'block_on_time' => null,
        ]);
    }
}
