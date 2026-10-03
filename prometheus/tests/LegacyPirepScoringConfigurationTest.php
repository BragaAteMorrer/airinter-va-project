<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
