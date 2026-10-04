<?php

namespace Tests;

use App\Models\Enums\UserState;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

final class ArgosAcarsIdentityTest extends TestCase
{
    public function test_linked_promethee_identity_wins_over_matching_email(): void
    {
        $linked = User::factory()->create([
            'email' => 'linked-pilot@example.test',
            'state' => UserState::ACTIVE,
        ]);
        $emailMatch = User::factory()->create([
            'email' => 'argos-mail@example.test',
            'state' => UserState::ACTIVE,
        ]);

        Http::fake([
            'https://argos.airinter-va.org/api/v1/hermes/session' => Http::response([
                'sub' => 'argos-subject-linked',
                'name' => 'Pilote Argos',
                'email' => $emailMatch->email,
                'state' => 'active',
                'identities' => [
                    [
                        'provider' => 'promethee',
                        'id' => (string) $linked->id,
                        'ident' => 'IT999',
                    ],
                ],
            ], 200),
        ]);

        $response = parent::postJson('/api/acars/argos', [
            'access_token' => 'argos-access-token-linked',
        ])->assertOk();

        $token = $response->json('data.access_token');
        $this->assertNotEmpty($token);
        $this->assertSame('argos', $response->json('data.auth_provider'));

        $this->assertDatabaseHas('acars_access_tokens', [
            'user_id' => $linked->id,
            'token_hash' => hash('sha256', $token),
            'auth_provider' => 'argos',
            'identity_subject' => 'argos-subject-linked',
        ]);
        $this->assertDatabaseMissing('acars_access_tokens', [
            'user_id' => $emailMatch->id,
            'token_hash' => hash('sha256', $token),
        ]);

        parent::withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $linked->id)
            ->assertJsonPath('data.auth.provider', 'argos')
            ->assertJsonPath('data.auth.sso', true)
            ->assertJsonPath('data.auth.account_url', 'https://argos.airinter-va.org/account');
    }

    public function test_existing_but_invalid_link_never_falls_back_to_email(): void
    {
        $emailMatch = User::factory()->create([
            'email' => 'fallback-must-not-win@example.test',
            'state' => UserState::ACTIVE,
        ]);

        Http::fake([
            'https://argos.airinter-va.org/api/v1/hermes/session' => Http::response([
                'sub' => 'argos-subject-invalid-link',
                'name' => 'Pilote Argos',
                'email' => $emailMatch->email,
                'state' => 'active',
                'identities' => [
                    [
                        'provider' => 'promethee',
                        'id' => '999999999',
                        'ident' => 'IT404',
                    ],
                ],
            ], 200),
        ]);

        parent::postJson('/api/acars/argos', [
            'access_token' => 'argos-access-token-invalid-link',
        ])
            ->assertStatus(403)
            ->assertJsonPath('error.message', 'Le compte Prométhée lié à cette identité Argos est introuvable.');

        $this->assertSame(0, DB::table('acars_access_tokens')
            ->where('user_id', $emailMatch->id)
            ->where('auth_provider', 'argos')
            ->count());
    }

    public function test_legacy_argos_payload_without_identities_keeps_email_compatibility(): void
    {
        $pilot = User::factory()->create([
            'email' => 'legacy-argos@example.test',
            'state' => UserState::ACTIVE,
        ]);

        Http::fake([
            'https://argos.airinter-va.org/api/v1/hermes/session' => Http::response([
                'sub' => 'argos-subject-legacy',
                'name' => 'Pilote Legacy',
                'email' => strtoupper($pilot->email),
                'state' => 'active',
            ], 200),
        ]);

        $response = parent::postJson('/api/acars/argos', [
            'access_token' => 'argos-access-token-legacy',
        ])->assertOk();

        $this->assertDatabaseHas('acars_access_tokens', [
            'user_id' => $pilot->id,
            'token_hash' => hash('sha256', $response->json('data.access_token')),
            'auth_provider' => 'argos',
            'identity_subject' => 'argos-subject-legacy',
        ]);
    }
}
