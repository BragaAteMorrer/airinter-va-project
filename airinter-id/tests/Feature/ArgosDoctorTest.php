<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ArgosDoctorTest extends TestCase
{
    public function test_doctor_command_is_registered_and_emits_json(): void
    {
        Artisan::call('argos:doctor', ['--json' => true]);

        $payload = json_decode(Artisan::output(), true);

        $this->assertIsArray($payload);
        $this->assertSame('Argos', $payload['service'] ?? null);
        $this->assertArrayHasKey('checks', $payload);
        $this->assertNotEmpty($payload['checks']);
    }
}
