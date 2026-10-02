<?php

namespace Tests;

use App\Models\Enums\PirepSource;
use App\Models\Enums\PirepState;
use App\Models\Enums\PirepStatus;
use App\Models\Pirep;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $this->setPolicy([
            $this->rule('EXCESS_TAXI_SPEED', 5, 30),
            $this->rule('SPEED_UNDER_10K', 2),
            $this->rule('HARD_LANDING', 20, 500),
        ]);

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

        $this->setPolicy([
            $this->rule('EXCESS_BANK', 2, 60, true, 0, 60),
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

    public function test_stabilized_approach_rule_deducts_when_gear_or_flaps_are_not_configured(): void
    {
        $pirep = Pirep::factory()->create([
            'source_name' => 'Hermes ACARS [op_stabilized_approach]',
            'landing_rate' => null,
        ]);

        $this->setPolicy([
            $this->rule('STABILIZED_APPROACH', 5, 1500),
        ]);

        $at = Carbon::parse('2026-10-03 11:30:00', 'UTC');
        $this->telemetry($pirep, $at, [
            'phase' => 'APPROACH',
            'on_ground' => false,
            'agl' => 1700,
            'gear_down' => true,
            'landing_flaps' => false,
        ]);
        $this->telemetry($pirep, $at->copy()->addSeconds(15), [
            'phase' => 'APPROACH',
            'on_ground' => false,
            'agl' => 1400,
            'gear_down' => true,
            'landing_flaps' => false,
        ]);

        $result = app(HermesScoringService::class)->calculate($pirep);

        $this->assertSame(95, $result['score']);
        $this->assertSame('STABILIZED_APPROACH', $result['deductions'][0]['rule_id']);
        $this->assertSame(5, $result['deductions'][0]['deduction']);
    }

    public function test_legacy_vmsacars_table_overrides_fallback_policy(): void
    {
        Schema::create('vmsacars_rules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->decimal('parameter')->nullable();
            $table->integer('points')->default(0);
            $table->boolean('repeatable')->default(false);
            $table->integer('delay')->default(0);
            $table->integer('cooldown')->default(0);
            $table->boolean('enabled')->default(true);
            $table->integer('order')->default(0);
        });

        DB::table('vmsacars_rules')->insert([
            'id' => 'HARD_LANDING',
            'name' => 'Legacy hard landing override',
            'parameter' => 500,
            'points' => 7,
            'repeatable' => false,
            'delay' => 0,
            'cooldown' => 0,
            'enabled' => true,
            'order' => 100,
        ]);

        $this->setPolicy([
            $this->rule('HARD_LANDING', 20, 500),
        ]);

        $pirep = Pirep::factory()->create([
            'source_name' => 'Hermes ACARS [op_legacy_override]',
            'landing_rate' => -700,
        ]);

        $result = app(HermesScoringService::class)->calculate($pirep);

        $this->assertSame('vmsacars_rules', $result['policy_source']);
        $this->assertSame(93, $result['score']);
        $this->assertSame(7, $result['deductions'][0]['points']);
    }

    public function test_unknown_simulator_rule_is_never_turned_into_a_penalty(): void
    {
        $pirep = Pirep::factory()->create([
            'source_name' => 'Hermes ACARS [op_unknown_score]',
            'landing_rate' => null,
        ]);

        $this->setPolicy([
            $this->rule('EXCESS_GFORCE', 5, 1, true),
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

        $this->setPolicy([
            $this->rule('HARD_LANDING', 20, 500),
        ]);

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

        $this->setPolicy([
            $this->rule('HARD_LANDING', 20, 500),
        ]);

        $service = app(HermesScoringService::class);
        $result = $service->calculate($pirep);
        $service->persist($pirep, $result);

        $this->setPolicy([
            $this->rule('HARD_LANDING', 1, 500),
        ]);

        $stored = $service->stored($pirep);
        $recalculated = $service->calculate($pirep);

        $this->assertTrue($stored['stored']);
        $this->assertSame(80, $stored['score']);
        $this->assertSame(99, $recalculated['score']);
        $this->assertSame(20, $stored['deductions'][0]['points']);
    }

    private function setPolicy(array $rules): void
    {
        config(['promethee.vmsacars-scoring.rules' => $rules]);
    }

    private function rule(
        string $id,
        int $points,
        int|float|null $parameter = null,
        bool $repeatable = false,
        int $delay = 0,
        int $cooldown = 0
    ): array {
        return [
            'id' => $id,
            'name' => $id,
            'parameter' => $parameter,
            'points' => $points,
            'repeatable' => $repeatable,
            'delay' => $delay,
            'cooldown' => $cooldown,
            'enabled' => true,
            'order' => 0,
        ];
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
