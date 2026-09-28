<?php

namespace App\Services;

use App\Models\OauthRefreshTokenLineage;
use App\Models\OauthTokenFamily;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;

class TokenSecurityService
{
    public function beginFamily(User $user, string $clientId, string $refreshToken, ?string $accessTokenId): OauthTokenFamily
    {
        return DB::transaction(function () use ($user, $clientId, $refreshToken, $accessTokenId) {
            $family = OauthTokenFamily::create([
                'user_id' => $user->id,
                'client_id' => $clientId,
                'status' => 'active',
                'last_used_at' => now(),
            ]);

            $family->refreshTokens()->create([
                'token_hash' => $this->hash($refreshToken),
                'access_token_id' => $accessTokenId,
                'status' => 'active',
            ]);

            return $family;
        });
    }

    public function inspectRefreshToken(string $refreshToken): ?OauthRefreshTokenLineage
    {
        return OauthRefreshTokenLineage::query()
            ->with('family')
            ->where('token_hash', $this->hash($refreshToken))
            ->first();
    }

    public function rotate(OauthRefreshTokenLineage $current, string $newRefreshToken, ?string $newAccessTokenId): void
    {
        DB::transaction(function () use ($current, $newRefreshToken, $newAccessTokenId) {
            $current->forceFill([
                'status' => 'used',
                'used_at' => now(),
            ])->save();

            $current->family->refreshTokens()->create([
                'token_hash' => $this->hash($newRefreshToken),
                'access_token_id' => $newAccessTokenId,
                'status' => 'active',
            ]);

            $current->family->forceFill([
                'last_used_at' => now(),
            ])->save();
        });
    }

    public function revokeFamily(OauthTokenFamily $family, string $reason): void
    {
        DB::transaction(function () use ($family, $reason) {
            $family->loadMissing('refreshTokens');

            $accessTokenIds = $family->refreshTokens
                ->pluck('access_token_id')
                ->filter()
                ->unique()
                ->values();

            if ($accessTokenIds->isNotEmpty()) {
                DB::table('oauth_access_tokens')
                    ->whereIn('id', $accessTokenIds->all())
                    ->update(['revoked' => true]);

                DB::table('oauth_refresh_tokens')
                    ->whereIn('access_token_id', $accessTokenIds->all())
                    ->update(['revoked' => true]);
            }

            $family->refreshTokens()
                ->where('status', '!=', 'revoked')
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                ]);

            $family->forceFill([
                'status' => 'revoked',
                'revoked_at' => now(),
                'revoke_reason' => $reason,
            ])->save();

            SecurityEvent::create([
                'user_id' => $family->user_id,
                'type' => 'oauth.family.revoked',
                'metadata' => [
                    'client_id' => $family->client_id,
                    'family_id' => $family->id,
                    'reason' => $reason,
                ],
                'created_at' => now(),
            ]);
        });
    }

    public function handleReuse(OauthRefreshTokenLineage $token): void
    {
        $this->revokeFamily($token->family, 'refresh_token_reuse');

        SecurityEvent::create([
            'user_id' => $token->family->user_id,
            'type' => 'oauth.refresh.reuse_detected',
            'metadata' => [
                'client_id' => $token->family->client_id,
                'family_id' => $token->family->id,
            ],
            'created_at' => now(),
        ]);
    }

    public function revokeUserClient(User $user, string $clientId, string $reason = 'user_revoked'): int
    {
        $families = OauthTokenFamily::query()
            ->where('user_id', $user->id)
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->get();

        foreach ($families as $family) {
            $this->revokeFamily($family, $reason);
        }

        return $families->count();
    }

    public function revokeAllForUser(User $user, string $reason = 'user_revoked_all'): int
    {
        $families = OauthTokenFamily::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        foreach ($families as $family) {
            $this->revokeFamily($family, $reason);
        }

        return $families->count();
    }

    public function clientName(string $clientId): string
    {
        $client = Passport::client()->newQuery()->find($clientId);

        return $client?->name ?: 'Application OAuth';
    }

    public function hash(string $refreshToken): string
    {
        return hash('sha256', $refreshToken);
    }
}
