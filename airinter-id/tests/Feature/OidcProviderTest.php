<?php

namespace Tests\Feature;

use Tests\TestCase;

class OidcProviderTest extends TestCase
{
    public function test_openid_discovery_document_is_exposed(): void
    {
        $this->getJson('/.well-known/openid-configuration')
            ->assertOk()
            ->assertJsonPath('issuer', rtrim((string) config('app.url'), '/'))
            ->assertJsonPath('authorization_endpoint', rtrim((string) config('app.url'), '/').'/oauth/authorize')
            ->assertJsonPath('token_endpoint', rtrim((string) config('app.url'), '/').'/oauth/token')
            ->assertJsonPath('userinfo_endpoint', rtrim((string) config('app.url'), '/').'/oauth/userinfo')
            ->assertJsonPath('jwks_uri', rtrim((string) config('app.url'), '/').'/.well-known/jwks.json')
            ->assertJsonFragment(['code_challenge_methods_supported' => ['S256']])
            ->assertJsonFragment(['id_token_signing_alg_values_supported' => ['RS256']]);
    }

    public function test_oauth_authorization_server_metadata_is_exposed(): void
    {
        $this->getJson('/.well-known/oauth-authorization-server')
            ->assertOk()
            ->assertJsonPath('issuer', rtrim((string) config('app.url'), '/'))
            ->assertJsonFragment(['grant_types_supported' => ['authorization_code', 'refresh_token']]);
    }

    public function test_userinfo_requires_a_valid_access_token(): void
    {
        $this->getJson('/oauth/userinfo')->assertUnauthorized();
    }
}
