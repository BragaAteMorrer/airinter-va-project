<?php

namespace Tests\Unit;

use App\Services\TokenSecurityService;
use PHPUnit\Framework\TestCase;

class TokenSecurityServiceTest extends TestCase
{
    public function test_refresh_tokens_are_stored_as_sha256_fingerprints(): void
    {
        $service = new TokenSecurityService();

        $this->assertSame(
            hash('sha256', 'super-secret-refresh-token'),
            $service->hash('super-secret-refresh-token')
        );

        $this->assertNotSame(
            'super-secret-refresh-token',
            $service->hash('super-secret-refresh-token')
        );
    }
}
