<?php

namespace Tests\Feature;

use Tests\TestCase;

class IdentityPortalTest extends TestCase
{
    public function test_public_identity_home_is_available(): void
    {
        $this->get('/')->assertOk()->assertSee('Argos');
    }

    public function test_account_requires_login(): void
    {
        $this->get('/account')->assertRedirect('/login');
    }

    public function test_me_endpoint_requires_an_oauth_token(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }
    public function test_2fa_security_ui_keeps_qr_and_recovery_secrets_local(): void
    {
        $view = file_get_contents(resource_path('views/auth/two-factor-settings.blade.php'));
        $recovery = file_get_contents(resource_path('views/auth/recovery-codes.blade.php'));
        $script = file_get_contents(public_path('argos.js'));

        $this->assertStringContainsString('data-totp-uri', $view);
        $this->assertStringContainsString('argos-totp-qr', $view);
        $this->assertStringContainsString('data-copy-recovery-codes', $recovery);
        $this->assertStringContainsString('data-export-recovery-codes', $recovery);
        $this->assertStringContainsString('new QRCode(qrTarget', $script);
        $this->assertStringContainsString('new Blob(', $script);
        $this->assertStringNotContainsString('stefansundin.github.io', $script);
    }

}
