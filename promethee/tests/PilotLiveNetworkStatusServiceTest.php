<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Promethee\Services\PilotLiveNetworkStatusService;

final class PilotLiveNetworkStatusServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('promethee:network-live:vatsim:v1');
        Cache::forget('promethee:network-live:ivao:v1');
    }

    public function test_matching_registered_cid_and_vid_sets_both_networks_green(): void
    {
        Http::fake([
            'data.vatsim.net/v3/vatsim-data.json' => Http::response([
                'pilots' => [['cid' => 123456, 'callsign' => 'ITF123']],
                'prefiles' => [['cid' => 999999, 'callsign' => 'ITF999']],
            ]),
            'api.ivao.aero/v2/tracker/whazzup' => Http::response([
                'clients' => ['pilots' => [['userId' => 456789, 'callsign' => 'ITF456']]],
            ]),
        ]);

        $user = new User();
        $user->id = 42;
        $user->vatsim_id = '123456';
        $user->ivao_id = '456789';

        $network = (new PilotLiveNetworkStatusService())->forUsers([$user])[42];

        $this->assertTrue($network['vatsim']['online']);
        $this->assertTrue($network['ivao']['online']);
        $this->assertSame('ITF123', $network['vatsim']['callsign']);
        $this->assertSame('ITF456', $network['ivao']['callsign']);
        Http::assertSentCount(2);
    }

    public function test_prefiled_or_unmatched_pilots_remain_offline(): void
    {
        Http::fake([
            'data.vatsim.net/v3/vatsim-data.json' => Http::response([
                'pilots' => [],
                'prefiles' => [['cid' => 123456, 'callsign' => 'ITF123']],
            ]),
            'api.ivao.aero/v2/tracker/whazzup' => Http::response([
                'clients' => ['pilots' => []],
            ]),
        ]);

        $user = new User();
        $user->id = 42;
        $user->vatsim_id = '123456';
        $user->ivao_id = '456789';

        $network = (new PilotLiveNetworkStatusService())->forUsers([$user])[42];

        $this->assertFalse($network['vatsim']['online']);
        $this->assertTrue($network['vatsim']['verified']);
        $this->assertFalse($network['ivao']['online']);
        $this->assertTrue($network['ivao']['verified']);
    }

    public function test_failed_feeds_are_unknown_and_never_report_online(): void
    {
        Http::fake([
            'data.vatsim.net/v3/vatsim-data.json' => Http::response([], 503),
            'api.ivao.aero/v2/tracker/whazzup' => Http::response([], 503),
        ]);

        $user = new User();
        $user->id = 42;
        $user->vatsim_id = '123456';
        $user->ivao_id = '456789';

        $network = (new PilotLiveNetworkStatusService())->forUsers([$user])[42];

        $this->assertFalse($network['vatsim']['online']);
        $this->assertFalse($network['ivao']['online']);
        $this->assertFalse($network['vatsim']['verified']);
        $this->assertFalse($network['ivao']['verified']);
    }
}
