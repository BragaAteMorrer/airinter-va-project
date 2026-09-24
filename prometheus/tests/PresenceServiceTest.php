<?php

namespace Tests;

use Carbon\Carbon;
use Modules\Promethee\Services\PresenceService;

final class PresenceServiceTest extends TestCase
{
    private string $folder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->folder = sys_get_temp_dir().DIRECTORY_SEPARATOR.'promethee-presence-'.bin2hex(random_bytes(8));
        mkdir($this->folder, 0770, true);
        Carbon::setTestNow(Carbon::parse('2026-09-25T20:00:00Z'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        if (is_dir($this->folder)) {
            foreach (glob($this->folder.DIRECTORY_SEPARATOR.'*') ?: [] as $file) @unlink($file);
            @rmdir($this->folder);
        }
        parent::tearDown();
    }

    public function test_heartbeat_exposes_operational_presence_and_network_summary(): void
    {
        $service = new PresenceService($this->folder);
        $presence = $service->heartbeat(
            'op_123',
            ['id'=>42,'ident'=>'IT199','name'=>'Pilot Test'],
            [
                'flight'=>['ident'=>'ITF749','departure'=>'LFPO','arrival'=>'LIRF'],
                'aircraft'=>['registration'=>'F-GAAA','icao'=>'A320'],
            ],
            [
                'simulator'=>'msfs2024',
                'connector'=>'simconnect',
                'phase'=>'CRUISE',
                'lat'=>47.2,
                'lon'=>6.1,
                'altitude_msl'=>37000,
                'hermes_version'=>'1.7.0',
                'recording'=>true,
                'client_state'=>'TRACKING',
            ]
        );

        $this->assertTrue($presence['online']);
        $this->assertSame('CRUISE', $presence['phase']);
        $this->assertSame('F-GAAA', $presence['aircraft']['registration']);

        $network = $service->network();
        $this->assertSame(1, $network['online_count']);
        $this->assertSame(1, $network['by_simulator']['msfs2024']);
        $this->assertSame('IT199', $network['crews'][0]['pilot']['ident']);
    }

    public function test_presence_expires_without_heartbeat(): void
    {
        $service = new PresenceService($this->folder);
        $service->heartbeat(
            'op_123',
            ['id'=>42,'ident'=>'IT199','name'=>'Pilot Test'],
            ['flight'=>null,'aircraft'=>null],
            ['simulator'=>'msfs2024','phase'=>'BOARDING']
        );

        Carbon::setTestNow(now()->addSeconds(PresenceService::ONLINE_TTL_SECONDS + 1));

        $this->assertSame(0, $service->network()['online_count']);
    }

    public function test_new_operation_replaces_previous_presence_for_same_pilot(): void
    {
        $service = new PresenceService($this->folder);
        $pilot = ['id'=>42,'ident'=>'IT199','name'=>'Pilot Test'];

        $service->heartbeat('op_123', $pilot, ['flight'=>['ident'=>'ITF749'],'aircraft'=>null], ['simulator'=>'msfs2024']);
        Carbon::setTestNow(now()->addSeconds(10));
        $service->heartbeat('op_456', $pilot, ['flight'=>['ident'=>'ITF1872'],'aircraft'=>null], ['simulator'=>'xplane']);

        $network = $service->network();
        $this->assertSame(1, $network['online_count']);
        $this->assertSame('op_456', $network['crews'][0]['operation_id']);
        $this->assertSame('ITF1872', $network['crews'][0]['flight']['ident']);
        $this->assertSame('xplane', $network['crews'][0]['simulator']);
    }
}
