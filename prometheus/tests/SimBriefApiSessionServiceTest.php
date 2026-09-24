<?php

namespace Tests;

use Illuminate\Support\Facades\Cache;
use Modules\Promethee\Services\SimBriefApiSessionService;

final class SimBriefApiSessionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    public function test_generation_state_is_short_lived_user_scoped_and_completable(): void
    {
        Cache::flush();

        /** @var SimBriefApiSessionService $sessions */
        $sessions = app(SimBriefApiSessionService::class);
        $session = $sessions->create(42, 'OP_TEST', 'FLIGHT_TEST', 'AIRCRAFT_TEST', 'AIRINTER_OP_TEST');

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{64}$/', $session['state']);
        $this->assertSame('AIRINTER_OP_TEST', $session['static_id']);
        $this->assertNotNull($sessions->forUser($session['state'], 42));
        $this->assertNull($sessions->forUser($session['state'], 43));

        $this->assertTrue($sessions->complete($session['state'], 'OFP_12345'));
        $completed = $sessions->forUser($session['state'], 42);
        $this->assertSame('OFP_12345', $completed['ofp_id']);
        $this->assertNotNull($completed['completed_at']);

        $sessions->forget($session['state']);
        $this->assertNull($sessions->find($session['state']));
    }
}
