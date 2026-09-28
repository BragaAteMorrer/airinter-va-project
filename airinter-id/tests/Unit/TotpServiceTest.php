<?php

namespace Tests\Unit;

use App\Services\TotpService;
use PHPUnit\Framework\TestCase;

class TotpServiceTest extends TestCase
{
    public function test_rfc6238_compatible_six_digit_totp_value(): void
    {
        $service = new TotpService();

        // RFC 6238 SHA-1 shared secret "12345678901234567890" in Base32.
        // At T=59 seconds the RFC 8-digit value is 94287082; the 6-digit
        // HOTP/TOTP truncation therefore ends with 287082.
        $this->assertSame(
            '287082',
            $service->at('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', 1)
        );
    }

    public function test_generated_recovery_codes_are_unique(): void
    {
        $service = new TotpService();
        $codes = $service->recoveryCodes();

        $this->assertCount(8, $codes);
        $this->assertCount(8, array_unique($codes));

        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^[A-F0-9]{10}$/', $code);
        }
    }
}
