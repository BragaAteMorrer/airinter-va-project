<?php

namespace Tests;

use App\Models\Enums\PirepSource;
use App\Models\Enums\PirepState;
use App\Models\Enums\PirepStatus;
use App\Models\Pirep;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Promethee\Services\HermesScoringService;

final class PrometheeHermesScoringTest extends TestCase
{
    public function test_hermes_score_uses_enabled_vmsacars_points(): void
    {
        $pirep = Pirep::factory()->create([
            'source_name' => 'Hermes ACARS [op_score_test]',
            'landing_rate' => null,
        ]);

        DB::table('vmsacars_rules')->update(['enabled' => false]);
        $this->configureRule('EXCESS_TAXI_SPEED', 5, 30, false);
        $this->configureRule('SPEED_UNDER_10K', 2, null, false);
        $this->configureRule('HARD_LANDING', 20, 500, false);

        $at = Carbon::parse('2026-10-03 10:00:00', 'UTC');
        $this->telemetry($pirep, $at, [
            'phase' => 'TAXI_OUT',
            'on_ground' => true,
            'gs' => 42,
            'fuel' => 12000,
        ]);
        $this->telemetry($pirep, $at->copy()->addSeconds(20), [
            'phase' => 'CLIMB',
            'on_ground' => false,
            'altitude_msl' => 6500,
            'ias' => 272,
            'fuel' => 11800,
        ]);
        $this->telemetry($pirep, $at->copy()->addMinutes(40), [
            'phase' => 'LANDING',
            'on_ground' => false,
            'altitude_msl' => 300,
            'ias' => 130,
            'fuel' => 9000,
            'touchdown_rate' => -650,
        ]);

        $result = app(HermesScoringService::class)->calculate($pirep, -650);

        $this->assertTrue($result['available']);
        $this->assertSame(73, $result['score']);
        $this->assertSame(27, $result['deductions_total']);
        $this->assertSame(
            ['EXCESS_TAXI_SPEED', 'SPEED_UNDER_10K', 'HARD_LANDING'],
            array_column($result['deductions'], 'rule_id')
        );
        $this->assertSame([], $result['unavailable_rules']);
    }

    public function test_repeatable_rule_respects_cooldown(): void
    {
        $pirep = Pirep::factory()->create([
            'source_name' => 'Hermes ACARS [op_repeatable_score]',
            'landing_rate' => null,
        ]);

        DB::table('vmsacars_rules')->update(['enabled' => false]);
        DB::table('vmsacars_rules')->where('id', 'EXCESS_BANK')->update([
            'enabled' => true,
            'points' => 2,
            'parameter' => 60,
            'repeatable' => true,
            'delay' => 0,
            'cooldown' => 60,
        ]);

        $at = Carbon::parse('2026-10-03 11:00:00', 'UTC');
        foreach ([[0, 70], [30, 75], [61, 80]] as [$seconds, $bank]) {
            $this->telemetry($pirep, $at->copy()->addSeconds($seconds), [
                'phase' => 'CRUISE',
                'on_ground' => false,
                'bank' => $bank,
            ]);
        }

        $result = app(HermesScoringService::class)->calculate($pirep);

        $this->assertSame(96, $result['score']);
        $this->assertSame(4, $result['deductions_total']);
        $this->assertSame(2, $result['deductions'][0]['count']);
    }

    public function test_unknown_simulator_rule_is_never_turned_into_a_penalty(): void
    {
        $pirep = Pirep::factory()->create([
            'source_name' => 'Hermes ACARS [op_unknown_score]',
            'landing_rate' => null,
        ]);

        DB::table('vmsacars_rules')->update(['enabled' => false]);
        DB::table('vmsacars_rules')->where('id', 'EXCESS_GFORCE')->update([
            'enabled' => true,
            'points' => 5,
            'parameter' => 1,
            'repeatable' => true,
            'delay' => 0,
            'cooldown' => 0,
        ]);

        $result = app(HermesScoringService::class)->calculate($pirep);

        $this->assertSame(100, $result['score']);
        $this->assertSame(0, $result['deductions_total']);
        $this->assertSame('EXCESS_GFORCE', $result['unavailable_rules'][0]['rule_id']);
    }

    public function test_hermes_file_ignores_client_score_and_persists_server_score(): void
    {
        $rank = $this->createRank(10, []);
        $this->user = User::factory()->create(['rank_id' => $rank->id]);

        $pirep = Pirep::factory()->create([
            'user_id' => $this->user->id,
            'source' => PirepSource::ACARS,
            'source_name' => 'Hermes ACARS [op_file_score]',
            'state' => PirepState::IN_PROGRESS,
            'status' => PirepStatus::INITIATED,
            'submitted_at' => null,
            'landing_rate' => null,
        ]);

        DB::table('vmsacars_rules')->update(['enabled' => false]);
        $this->configureRule('HARD_LANDING', 20, 500, false);

        $at = Carbon::parse('2026-10-03 12:00:00', 'UTC');
        $this->telemetry($pirep, $at, [
            'phase' => 'IN',
            'on_ground' => true,
            'gs' => 0,
            'touchdown_rate' => -700,
        ]);

        $response = $this->post('/api/pireps/'.$pirep->id.'/file', [
            'flight_time' => 60,
            'distance' => 250,
            'fuel_used' => 2500,
            'landing_rate' => -700,
            'score' => 100,
        ]);

        $response->assertOk();

        $pirep->refresh();
        $this->assertSame(80, $pirep->score);
        $this->assertDatabaseHas('promethee_pirep_scores', [
            'pirep_id' => $pirep->id,
            'score' => 80,
        ]);
    }

    public function test_scoring_snapshot_keeps_historical_breakdown(): void
    {
        $pirep = Pirep::factory()->create([
            'source_name' => 'Hermes ACARS [op_score_snapshot]',
            'landing_rate' => -700,
        ]);

        DB::table('vmsacars_rules')->update(['enabled' => false]);
        $this->configureRule('HARD_LANDING', 20, 500, false);

        $service = app(HermesScoringService::class);
        $result = $service->calculate($pirep);
        $service->persist($pirep, $result);

        DB::table('vmsacars_rules')->where('id', 'HARD_LANDING')->update(['points' => 1]);

        $stored = $service->stored($pirep);
        $recalculated = $service->calculate($pirep);

        $this->assertTrue($stored['stored']);
        $this->assertSame(80, $stored['score']);
        $this->assertSame(99, $recalculated['score']);
        $this->assertSame(20, $stored['deductions'][0]['points']);
    }

    private function configureRule(string $id, int $points, ?int $parameter, bool $repeatable): void
    {
        DB::table('vmsacars_rules')->where('id', $id)->update([
            'enabled' => true,
            'points' => $points,
            'parameter' => $parameter,
            'repeatable' => $repeatable,
            'delay' => 0,
            'cooldown' => 0,
        ]);
    }

    private function telemetry(Pirep $pirep, Carbon $at, array $payload): void
    {
        DB::table('promethee_telemetry')->insert([
            'pirep_id' => $pirep->id,
            'sample_id' => (string) Str::uuid(),
            'recorded_at' => $at,
            'payload' => json_encode($payload),
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }
}
