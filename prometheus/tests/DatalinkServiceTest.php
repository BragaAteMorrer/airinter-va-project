<?php

namespace Tests;

use Modules\Promethee\Services\DatalinkService;

final class DatalinkServiceTest extends TestCase
{
    private string $folder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->folder = sys_get_temp_dir().DIRECTORY_SEPARATOR.'promethee-datalink-'.bin2hex(random_bytes(8));
        mkdir($this->folder, 0770, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->folder)) {
            foreach (glob($this->folder.DIRECTORY_SEPARATOR.'*') ?: [] as $file) @unlink($file);
            @rmdir($this->folder);
        }
        parent::tearDown();
    }

    public function test_client_message_id_makes_retries_idempotent(): void
    {
        $service = new DatalinkService($this->folder);

        $first = $service->send(
            'op_123', 42, 'COCKPIT_TO_OPS', 'CREW', 'NORMAL',
            'Request startup.', false, 'IT199', '11111111-1111-4111-8111-111111111111'
        );
        $retry = $service->send(
            'op_123', 42, 'COCKPIT_TO_OPS', 'CREW', 'NORMAL',
            'Request startup.', false, 'IT199', '11111111-1111-4111-8111-111111111111'
        );

        $this->assertSame($first['id'], $retry['id']);
        $this->assertCount(1, $service->list('op_123', 42)['messages']);
    }

    public function test_acknowledgement_is_idempotent_and_scoped_to_recipient_direction(): void
    {
        $service = new DatalinkService($this->folder);
        $message = $service->send(
            'op_456', 42, 'OPS_TO_COCKPIT', 'OPS', 'HIGH',
            'Return to stand.', true, 'AIR INTER OPS'
        );

        $first = $service->acknowledge('op_456', 42, $message['id'], 'OPS_TO_COCKPIT');
        $retry = $service->acknowledge('op_456', 42, $message['id'], 'OPS_TO_COCKPIT');

        $this->assertSame('ACKNOWLEDGED', $first['status']);
        $this->assertSame($first['acknowledged_at'], $retry['acknowledged_at']);
        $this->assertSame(0, $service->list('op_456', 42)['pending_ack_count']);
    }

    public function test_list_never_leaks_messages_between_pilots(): void
    {
        $service = new DatalinkService($this->folder);
        $service->send('op_shared', 42, 'OPS_TO_COCKPIT', 'OPS', 'NORMAL', 'Pilot 42', false, 'OPS');
        $service->send('op_shared', 43, 'OPS_TO_COCKPIT', 'OPS', 'NORMAL', 'Pilot 43', false, 'OPS');

        $messages = $service->list('op_shared', 42)['messages'];

        $this->assertCount(1, $messages);
        $this->assertSame('Pilot 42', $messages[0]['body']);
    }
}
