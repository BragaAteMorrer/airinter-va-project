<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Pirep;
use Modules\Promethee\Services\LegacyPirepScoringService;

final class LegacyPirepScoringConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('vmsacars_rules')) {
            Schema::create('vmsacars_rules', function (Blueprint $table) {
                $table->string('id', 50)->primary();
                $table->string('name');
                $table->string('description')->nullable();
                $table->integer('parameter')->nullable();
                $table->unsignedInteger('points')->default(5);
                $table->boolean('enabled')->default(true);
                $table->boolean('has_parameter')->default(true);
                $table->boolean('repeatable')->default(false);
                $table->unsignedInteger('delay')->default(0);
                $table->unsignedInteger('cooldown')->default(0);
                $table->unsignedInteger('order')->default(0);
                $table->timestamps();
            });
        }

        DB::table('vmsacars_rules')->updateOrInsert(
            ['id' => 'EXCESS_TAXI_SPEED'],
            [
                'name' => 'Excess Taxi Speed',
                'description' => 'If an aircraft exceeds this speed while taxiing (knots)',
                'parameter' => 25,
                'points' => 5,
                'enabled' => true,
                'has_parameter' => true,
                'repeatable' => true,
                'delay' => 30,
                'cooldown' => 60,
                'order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('vmsacars_rules')->updateOrInsert(
            ['id' => 'STABILIZED_APPROACH'],
            [
                'name' => 'Approche stabilisée · taux de descente',
                'description' => 'Entre 1000 ft AGL et le toucher : VS >= -1000 ft/min pendant 4 s.',
                'parameter' => null,
                'points' => 10,
                'enabled' => true,
                'has_parameter' => false,
                'repeatable' => false,
                'delay' => 4,
                'cooldown' => 0,
                'order' => 170,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function test_admin_configuration_exposes_authoritative_vmsacars_profile(): void
    {
        $service = app(LegacyPirepScoringService::class);

        $taxi = collect($service->configurationRules())->firstWhere('id', 'EXCESS_TAXI_SPEED');

        $this->assertNotNull($taxi);
        $this->assertSame(25.0, $taxi['parameter']);
        $this->assertSame(5, $taxi['points']);
        $this->assertSame(30, $taxi['delay']);
        $this->assertSame(60, $taxi['cooldown']);
        $this->assertTrue($taxi['repeatable']);
        $this->assertTrue($taxi['enabled']);
    }

    public function test_stabilized_approach_rule_keeps_points_configurable_with_fixed_criterion(): void
    {
        $service = app(LegacyPirepScoringService::class);

        $rule = collect($service->configurationRules())->firstWhere('id', 'STABILIZED_APPROACH');
        $this->assertNotNull($rule);
        $this->assertFalse($rule['has_parameter']);
        $this->assertNull($rule['parameter']);
        $this->assertSame(4, $rule['delay']);
        $this->assertSame(10, $rule['points']);

        $updated = $service->updateRuleConfiguration('STABILIZED_APPROACH', [
            'points' => 12,
            'delay' => 4,
            'cooldown' => 0,
            'repeatable' => false,
            'enabled' => true,
        ]);

        $this->assertSame(12, $updated['points']);
        $this->assertNull($updated['parameter']);
        $this->assertSame(4, $updated['delay']);
    }

    public function test_stabilized_approach_scoring_uses_only_sustained_descent_rate_fact(): void
    {
        $service = app(LegacyPirepScoringService::class);
        $facts = [
            [
                'code' => 'APPROACH_1000_UNSTABLE',
                'occurred_at' => '2026-10-05T00:10:00Z',
                'value' => -800,
                'unit' => 'ft/min',
            ],
            [
                'code' => 'APPROACH_DESCENT_RATE_UNSTABLE',
                'occurred_at' => '2026-10-05T00:10:10Z',
                'value' => -1450,
                'unit' => 'ft/min',
            ],
        ];

        $occurrences = $this->invokeMethod($service, 'stabilizedApproach', [$facts]);

        $this->assertCount(1, $occurrences);
        $this->assertSame('APPROACH_DESCENT_RATE_UNSTABLE', $occurrences[0]['code']);
        $this->assertSame(-1450, $occurrences[0]['value']);
    }

    public function test_runway_overrun_is_detected_from_hermes_trace_and_runway_geometry(): void
    {
        if (!Schema::hasTable('disposable_runways')) {
            $this->markTestSkipped('Disposable runway geometry is not installed in this test environment.');
        }

        DB::table('disposable_runways')->updateOrInsert(
            ['airport_id' => 'LFXX', 'runway_ident' => '36'],
            [
                'lat' => '48.000000',
                'lon' => '2.000000',
                'heading' => '0',
                'length' => '1000',
                'ils_freq' => null,
                'loc_course' => null,
                'airac' => '2609',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $pirep = new Pirep();
        $pirep->arr_airport_id = 'LFXX';

        $samples = [
            [
                'recorded_at' => '2026-10-05T08:00:00Z',
                'lat' => 48.0018,
                'lon' => 2.0,
                'on_ground' => true,
                'gs' => 115,
                'heading' => 1,
                'phase' => 'LANDING',
            ],
            [
                'recorded_at' => '2026-10-05T08:00:20Z',
                'lat' => 48.00965,
                'lon' => 2.0,
                'on_ground' => true,
                'gs' => 42,
                'heading' => 0,
                'phase' => 'LANDING',
            ],
        ];

        $service = app(LegacyPirepScoringService::class);
        $occurrences = $this->invokeMethod($service, 'runwayOverrun', [$pirep, $samples]);

        $this->assertCount(1, $occurrences);
        $this->assertSame('RUNWAY_OVERRUN', $occurrences[0]['code']);
        $this->assertSame('36', $occurrences[0]['runway']);
        $this->assertGreaterThan(60, $occurrences[0]['value']);
    }

    public function test_runway_rollout_inside_physical_end_is_not_an_overrun(): void
    {
        if (!Schema::hasTable('disposable_runways')) {
            $this->markTestSkipped('Disposable runway geometry is not installed in this test environment.');
        }

        DB::table('disposable_runways')->updateOrInsert(
            ['airport_id' => 'LFXY', 'runway_ident' => '36'],
            [
                'lat' => '48.000000',
                'lon' => '2.000000',
                'heading' => '0',
                'length' => '1000',
                'ils_freq' => null,
                'loc_course' => null,
                'airac' => '2609',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $pirep = new Pirep();
        $pirep->arr_airport_id = 'LFXY';

        $samples = [
            [
                'recorded_at' => '2026-10-05T08:00:00Z',
                'lat' => 48.0018,
                'lon' => 2.0,
                'on_ground' => true,
                'gs' => 115,
                'heading' => 0,
                'phase' => 'LANDING',
            ],
            [
                'recorded_at' => '2026-10-05T08:00:25Z',
                'lat' => 48.0081,
                'lon' => 2.0,
                'on_ground' => true,
                'gs' => 35,
                'heading' => 0,
                'phase' => 'LANDING',
            ],
        ];

        $service = app(LegacyPirepScoringService::class);
        $occurrences = $this->invokeMethod($service, 'runwayOverrun', [$pirep, $samples]);

        $this->assertSame([], $occurrences);
    }

    public function test_admin_update_changes_the_same_rule_consumed_by_hermes_scoring(): void
    {
        $service = app(LegacyPirepScoringService::class);

        $updated = $service->updateRuleConfiguration('EXCESS_TAXI_SPEED', [
            'parameter' => 22,
            'points' => 7,
            'delay' => 12,
            'cooldown' => 90,
            'repeatable' => true,
            'enabled' => true,
        ]);

        $this->assertSame(22.0, $updated['parameter']);
        $this->assertSame(7, $updated['points']);
        $this->assertSame(12, $updated['delay']);
        $this->assertSame(90, $updated['cooldown']);

        $this->assertDatabaseHas('vmsacars_rules', [
            'id' => 'EXCESS_TAXI_SPEED',
            'parameter' => 22,
            'points' => 7,
            'delay' => 12,
            'cooldown' => 90,
            'repeatable' => true,
            'enabled' => true,
        ]);

        $scoringRules = $this->invokeMethod($service, 'rules');
        $scoringTaxi = collect($scoringRules)->firstWhere('id', 'EXCESS_TAXI_SPEED');

        $this->assertNotNull($scoringTaxi);
        $this->assertSame(22.0, $scoringTaxi['parameter']);
        $this->assertSame(7, $scoringTaxi['points']);
        $this->assertSame(12, $scoringTaxi['delay']);
        $this->assertSame(90, $scoringTaxi['cooldown']);

        $this->assertSame(
            7,
            (int) DB::table('vmsacars_rules')->where('id', 'EXCESS_TAXI_SPEED')->value('points')
        );
    }
}
