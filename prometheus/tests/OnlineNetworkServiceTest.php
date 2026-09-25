<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Promethee\Services\OnlineNetworkService;

final class OnlineNetworkServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        config([
            'services.vatsim.data_url' => 'https://data.vatsim.test/live.json',
            'services.ivao.data_url' => 'https://api.ivao.test/whazzup',
        ]);
    }

    public function test_linked_pilot_is_resolved_on_vatsim_and_ivao_without_client_claims(): void
    {
        $user = User::factory()->create([
            'vatsim_id' => '1234567',
            'ivao_id' => '765432',
        ]);

        Http::fake([
            'https://data.vatsim.test/live.json' => Http::response([
                'general' => ['update_timestamp' => '2026-09-25T01:00:00Z'],
                'pilots' => [[
                    'cid' => 1234567,
                    'callsign' => 'ITF143',
                    'latitude' => 48.5,
                    'longitude' => 2.3,
                    'altitude' => 33000,
                    'groundspeed' => 445,
                    'heading' => 162,
                    'logon_time' => '2026-09-25T00:30:00Z',
                    'last_updated' => '2026-09-25T01:00:00Z',
                    'flight_plan' => [
                        'departure' => 'LFPO',
                        'arrival' => 'LIRF',
                        'alternate' => 'LIMC',
                        'aircraft_short' => 'A320',
                        'route' => 'DCT TEST',
                        'flight_rules' => 'I',
                    ],
                ]],
            ], 200),
            'https://api.ivao.test/whazzup' => Http::response([
                'updatedAt' => '2026-09-25T01:00:00Z',
                'payload' => [
                    'clients' => [
                        'pilots' => [[
                            'userId' => 765432,
                            'callsign' => 'ITF143',
                            'createdAt' => '2026-09-25T00:40:00Z',
                            'lastTrack' => [
                                'latitude' => 48.4,
                                'longitude' => 2.4,
                                'altitude' => 32900,
                                'groundSpeed' => 440,
                                'heading' => 160,
                                'timestamp' => '2026-09-25T01:00:00Z',
                            ],
                            'flightPlan' => [
                                'departureId' => 'LFPO',
                                'arrivalId' => 'LIRF',
                                'alternativeId' => 'LIMC',
                                'aircraftId' => 'A320',
                                'route' => 'DCT TEST',
                                'flightRules' => 'I',
                            ],
                        ]],
                    ],
                ],
            ], 200),
        ]);

        /** @var OnlineNetworkService $service */
        $service = app(OnlineNetworkService::class);
        $result = $service->users(collect([$user]));

        $pilot = $result['users'][$user->id];
        $this->assertTrue($pilot['linked']);
        $this->assertTrue($pilot['online']);
        $this->assertCount(2, $pilot['online_connections']);
        $this->assertSame('ITF143', $pilot['connections']['vatsim']['callsign']);
        $this->assertSame('LFPO', $pilot['connections']['vatsim']['flight_plan']['departure']);
        $this->assertSame('ITF143', $pilot['connections']['ivao']['callsign']);
        $this->assertSame('LIRF', $pilot['connections']['ivao']['flight_plan']['arrival']);

        Http::assertSentCount(2);
    }

    public function test_feed_failure_never_turns_a_linked_account_into_a_false_online_presence(): void
    {
        $user = User::factory()->create([
            'vatsim_id' => '1234567',
            'ivao_id' => null,
        ]);

        Http::fake([
            'https://data.vatsim.test/live.json' => Http::response(['error' => 'temporary'], 503),
        ]);

        /** @var OnlineNetworkService $service */
        $service = app(OnlineNetworkService::class);
        $result = $service->users(collect([$user]));
        $connection = $result['users'][$user->id]['connections']['vatsim'];

        $this->assertTrue($connection['linked']);
        $this->assertFalse($connection['online']);
        $this->assertFalse($connection['feed_available']);
        $this->assertFalse($result['providers']['vatsim']['available']);
    }

    public function test_unlinked_pilot_does_not_trigger_external_network_requests(): void
    {
        $user = User::factory()->create([
            'vatsim_id' => null,
            'ivao_id' => null,
        ]);

        Http::fake();

        /** @var OnlineNetworkService $service */
        $service = app(OnlineNetworkService::class);
        $result = $service->users(collect([$user]));

        $this->assertFalse($result['users'][$user->id]['linked']);
        $this->assertFalse($result['users'][$user->id]['online']);
        Http::assertNothingSent();
    }
}
