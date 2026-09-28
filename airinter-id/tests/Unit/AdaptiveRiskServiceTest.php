<?php

namespace Tests\Unit;

use App\Services\AdaptiveRiskService;
use PHPUnit\Framework\TestCase;

class AdaptiveRiskServiceTest extends TestCase
{
    public function test_risk_severity_thresholds_are_deterministic(): void
    {
        $service = new AdaptiveRiskService();

        $this->assertSame('info', $service->severityForScore(0));
        $this->assertSame('info', $service->severityForScore(29));
        $this->assertSame('medium', $service->severityForScore(30));
        $this->assertSame('medium', $service->severityForScore(59));
        $this->assertSame('high', $service->severityForScore(60));
        $this->assertSame('high', $service->severityForScore(100));
    }
}
