<?php

namespace Tests;

use Modules\Promethee\Services\SopEngineService;

final class SopEngineServiceTest extends TestCase
{
    private string $folder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->folder = sys_get_temp_dir().DIRECTORY_SEPARATOR.'promethee-sop-'.bin2hex(random_bytes(8));
        mkdir($this->folder, 0770, true);
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->folder);
        parent::tearDown();
    }

    public function test_facts_are_idempotent_and_numeric_rules_are_server_side(): void
    {
        $service = new SopEngineService($this->folder);
        $fact = [
            'fact_id' => '11111111-1111-4111-8111-111111111111',
            'code' => 'TOUCHDOWN',
            'category' => 'landing',
            'occurred_at' => '2026-09-24T20:00:00Z',
            'message' => 'Touchdown confirmed.',
            'value' => -710,
            'unit' => 'ft/min',
            'phase' => 'LANDING',
        ];

        $first = $service->ingest('op_123', 42, [$fact]);
        $retry = $service->ingest('op_123', 42, [$fact]);

        $this->assertSame(1, $first['inserted_facts']);
        $this->assertSame(0, $retry['inserted_facts']);
        $this->assertCount(1, array_filter($first['new_evaluations'], fn ($item) => $item['rule_id'] === 'default_hard_landing'));
        $this->assertSame(1, $service->operation('op_123', 42)['summary']['warnings']);
    }

    public function test_rule_threshold_can_change_without_changing_hermes_fact_contract(): void
    {
        $service = new SopEngineService($this->folder);
        $rule = $service->upsertRule([
            'name' => 'Taxi custom',
            'enabled' => true,
            'fact_code' => 'TAXI_SPEED_MAX',
            'operator' => 'gt',
            'threshold' => 40,
            'severity' => 'ADVISORY',
            'pilot_review' => true,
            'dispatch_alert' => false,
            'phases' => [],
            'message' => 'Taxi {value} {unit}',
        ], 'custom_taxi');

        $base = [
            'code' => 'TAXI_SPEED_MAX',
            'category' => 'ground',
            'occurred_at' => '2026-09-24T20:00:00Z',
            'message' => 'Taxi peak.',
            'value' => 37,
            'unit' => 'kt',
            'phase' => 'TAXI_OUT',
        ];

        $low = $service->ingest('op_456', 42, [$base + ['fact_id'=>'22222222-2222-4222-8222-222222222222']]);
        $this->assertCount(0, array_filter($low['new_evaluations'], fn ($item) => $item['rule_id'] === $rule['id']));

        $service->upsertRule(array_merge($rule, ['threshold' => 30]), 'custom_taxi');
        $high = $service->ingest('op_456', 42, [$base + ['fact_id'=>'33333333-3333-4333-8333-333333333333']]);
        $this->assertCount(1, array_filter($high['new_evaluations'], fn ($item) => $item['rule_id'] === 'custom_taxi'));
    }

    public function test_review_and_dispatch_ack_are_independent_and_idempotent(): void
    {
        $service = new SopEngineService($this->folder);
        $result = $service->ingest('op_789', 42, [[
            'fact_id' => '44444444-4444-4444-8444-444444444444',
            'code' => 'APPROACH_500_UNSTABLE',
            'category' => 'approach',
            'occurred_at' => '2026-09-24T20:00:00Z',
            'message' => 'VS excessive.',
            'phase' => 'FINAL',
        ]]);

        $evaluation = collect($result['new_evaluations'])->firstWhere('rule_id', 'default_unstable_500');
        $this->assertNotNull($evaluation);

        $reviewed = $service->pilotReview('op_789', 42, $evaluation['id']);
        $reviewedAgain = $service->pilotReview('op_789', 42, $evaluation['id']);
        $this->assertSame($reviewed['pilot_reviewed_at'], $reviewedAgain['pilot_reviewed_at']);

        $acked = $service->dispatchAcknowledge('op_789', 42, $evaluation['id']);
        $ackedAgain = $service->dispatchAcknowledge('op_789', 42, $evaluation['id']);
        $this->assertSame($acked['dispatch_acknowledged_at'], $ackedAgain['dispatch_acknowledged_at']);
    }

    private function removeTree(string $path): void
    {
        if (!is_dir($path)) return;
        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $child = $path.DIRECTORY_SEPARATOR.$entry;
            is_dir($child) ? $this->removeTree($child) : @unlink($child);
        }
        @rmdir($path);
    }
}
