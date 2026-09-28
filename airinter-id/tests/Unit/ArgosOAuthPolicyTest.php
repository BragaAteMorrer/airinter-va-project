<?php

namespace Tests\Unit;

use App\Services\ArgosOAuthPolicy;
use PHPUnit\Framework\TestCase;

class ArgosOAuthPolicyTest extends TestCase
{
    public function test_scope_policy_rejects_scopes_outside_client_allowlist(): void
    {
        $policy = new ArgosOAuthPolicy();
        $definition = ['scopes' => ['profile', 'email', 'hermes:operate']];

        $this->assertSame(
            ['promethee:read'],
            $policy->disallowedScopes($definition, 'profile email promethee:read')
        );
    }

    public function test_redirect_uri_matching_is_exact(): void
    {
        $policy = new ArgosOAuthPolicy();
        $definition = [
            'redirect_uris' => ['https://promethee.airinter-va.org/auth/argos/callback'],
        ];

        $this->assertTrue($policy->redirectUriIsAllowed(
            $definition,
            'https://promethee.airinter-va.org/auth/argos/callback'
        ));

        $this->assertFalse($policy->redirectUriIsAllowed(
            $definition,
            'https://evil.example/auth/argos/callback'
        ));

        $this->assertFalse($policy->redirectUriIsAllowed(
            $definition,
            'https://promethee.airinter-va.org/auth/argos/callback/'
        ));
    }

    public function test_pkce_and_state_are_required_by_default(): void
    {
        $policy = new ArgosOAuthPolicy();

        $this->assertTrue($policy->pkceIsRequired([]));
        $this->assertTrue($policy->stateIsRequired([]));
    }

    public function test_only_authorization_code_and_refresh_are_default_grants(): void
    {
        $policy = new ArgosOAuthPolicy();

        $this->assertSame(
            ['authorization_code', 'refresh_token'],
            $policy->allowedGrantTypes([])
        );
    }
}
