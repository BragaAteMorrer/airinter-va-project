<?php

namespace App\Services;

use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountSecurityService
{
    public function revokeOtherSecurityContexts(User $user, ?string $currentSessionId = null): void
    {
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->when($currentSessionId, fn ($query) => $query->where('id', '!=', $currentSessionId))
            ->delete();

        if (DB::getSchemaBuilder()->hasTable('oauth_access_tokens')) {
            $accessIds = DB::table('oauth_access_tokens')
                ->where('user_id', $user->id)
                ->pluck('id');

            DB::table('oauth_access_tokens')
                ->where('user_id', $user->id)
                ->where('revoked', false)
                ->update(['revoked' => true]);

            if ($accessIds->isNotEmpty() && DB::getSchemaBuilder()->hasTable('oauth_refresh_tokens')) {
                DB::table('oauth_refresh_tokens')
                    ->whereIn('access_token_id', $accessIds->all())
                    ->where('revoked', false)
                    ->update(['revoked' => true]);
            }
        }

        if (DB::getSchemaBuilder()->hasTable('oauth_token_families')) {
            DB::table('oauth_token_families')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                    'revoke_reason' => 'account_security_change',
                ]);
        }
    }

    public function record(Request $request, User $user, string $type, array $metadata = []): void
    {
        SecurityEvent::create([
            'user_id' => $user->id,
            'type' => $type,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }

    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $code = strtoupper(trim($code));
        $codes = (array) ($user->two_factor_recovery_codes ?? []);

        foreach ($codes as $index => $hash) {
            if (Hash::check($code, $hash)) {
                unset($codes[$index]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }
}
