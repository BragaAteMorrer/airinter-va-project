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

    public function test_client_message_id_makes_retries_idempotent_and_priorities_are_canonical(): void
    {
        $service = new DatalinkService($this->folder);

        $first = $service->send(
            'op_123', 42, 'COCKPIT_TO_OPS', 'CREW', 'HIGH',
            'Request startup.', false, 'IT199', '11111111-1111-4111-8111-111111111111'
        );
        $retry = $service->send(
            'op_123', 42, 'COCKPIT_TO_OPS', 'CREW', 'HIGH',
            'Request startup.', false, 'IT199', '11111111-1111-4111-8111-111111111111'
        );

        $this->assertSame($first['id'], $retry['id']);
        $this->assertSame('IMPORTANT', $first['priority']);
        $this->assertSame('SENT', $first['status']);
        $this->assertNotNull($first['sent_at']);
        $this->assertCount(1, $service->list('op_123', 42)['messages']);
    }

    public function test_delivery_read_and_acknowledgement_lifecycle_is_idempotent(): void
    {
        $service = new DatalinkService($this->folder);
        $message = $service->send(
            'op_456', 42, 'OPS_TO_COCKPIT', 'OPS', 'ADVISORY',
            'Return to stand.', true, 'AIR INTER OPS'
        );

        $delivered = $service->list('op_456', 42, 'OPS_TO_COCKPIT');
        $item = $delivered['messages'][0];
        $this->assertSame('DELIVERED', $item['status']);
        $this->assertNotNull($item['delivered_at']);
        $this->assertSame(1, $delivered['unread_count']);
        $this->assertSame(1, $delivered['pending_ack_count']);

        $read = $service->markRead('op_456', 42, $message['id'], 'OPS_TO_COCKPIT');
        $readAgain = $service->markRead('op_456', 42, $message['id'], 'OPS_TO_COCKPIT');
        $this->assertSame('READ', $read['status']);
        $this->assertSame($read['read_at'], $readAgain['read_at']);

        $afterRead = $service->list('op_456', 42);
        $this->assertSame(0, $afterRead['unread_count']);
        $this->assertSame(1, $afterRead['pending_ack_count']);

        $first = $service->acknowledge('op_456', 42, $message['id'], 'OPS_TO_COCKPIT');
        $retry = $service->acknowledge('op_456', 42, $message['id'], 'OPS_TO_COCKPIT');

        $this->assertSame('ACKNOWLEDGED', $first['status']);
        $this->assertSame($first['acknowledged_at'], $retry['acknowledged_at']);
        $this->assertSame(0, $service->list('op_456', 42)['pending_ack_count']);
    }

    public function test_delivery_is_scoped_to_the_actual_recipient(): void
    {
        $service = new DatalinkService($this->folder);
        $outgoing = $service->send(
            'op_direction', 42, 'COCKPIT_TO_OPS', 'CREW', 'ROUTINE',
            'Ready.', false, 'IT199'
        );
        $incoming = $service->send(
            'op_direction', 42, 'OPS_TO_COCKPIT', 'OPS', 'IMPORTANT',
            'Stand changed.', false, 'AIR INTER OPS'
        );

        $pilot = $service->list('op_direction', 42, 'OPS_TO_COCKPIT');
        $pilotMessages = collect($pilot['messages'])->keyBy('id');
        $this->assertSame('SENT', $pilotMessages[$outgoing['id']]['status']);
        $this->assertSame('DELIVERED', $pilotMessages[$incoming['id']]['status']);

        $ops = $service->list('op_direction', 42, 'COCKPIT_TO_OPS');
        $opsMessages = collect($ops['messages'])->keyBy('id');
        $this->assertSame('DELIVERED', $opsMessages[$outgoing['id']]['status']);
    }

    public function test_list_never_leaks_messages_between_pilots(): void
    {
        $service = new DatalinkService($this->folder);
        $service->send('op_shared', 42, 'OPS_TO_COCKPIT', 'OPS', 'ROUTINE', 'Pilot 42', false, 'OPS');
        $service->send('op_shared', 43, 'OPS_TO_COCKPIT', 'OPS', 'ROUTINE', 'Pilot 43', false, 'OPS');

        $messages = $service->list('op_shared', 42)['messages'];

        $this->assertCount(1, $messages);
        $this->assertSame('Pilot 42', $messages[0]['body']);
    }
}
