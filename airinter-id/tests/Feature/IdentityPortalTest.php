<?php

namespace Tests\Feature;

use Tests\TestCase;

class IdentityPortalTest extends TestCase
{
    public function test_public_identity_home_is_available(): void
    {
        $this->get('/')->assertOk()->assertSee('Air Inter ID');
    }

    public function test_account_requires_login(): void
    {
        $this->get('/account')->assertRedirect('/login');
    }

    public function test_me_endpoint_requires_an_oauth_token(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }
}
