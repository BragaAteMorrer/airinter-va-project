<?php

namespace App\Services;

use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class TrustedDeviceService
{
    public const COOKIE = 'argos_trusted_device';

    public function resolve(Request $request, User $user): ?TrustedDevice
    {
        $token = (string) $request->cookie(self::COOKIE);
        if ($token === '') {
            return null;
        }

        $device = TrustedDevice::query()
            ->where('user_id', $user->id)
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$device) {
            return null;
        }

        if (!hash_equals($device->user_agent_hash, $this->userAgentHash($request))) {
            return null;
        }

        $device->forceFill([
            'last_used_at' => now(),
            'last_ip_address' => $request->ip(),
        ])->save();

        return $device;
    }

    public function trust(Request $request, User $user, int $days = 30): Cookie
    {
        $plain = Str::random(80);

        TrustedDevice::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plain),
            'name' => $this->deviceName($request),
            'user_agent_hash' => $this->userAgentHash($request),
            'last_ip_address' => $request->ip(),
            'last_used_at' => now(),
            'expires_at' => now()->addDays($days),
        ]);

        return cookie(
            self::COOKIE,
            $plain,
            $days * 24 * 60,
            '/',
            null,
            true,
            true,
            false,
            'Lax',
        );
    }

    public function revoke(TrustedDevice $device): void
    {
        $device->forceFill(['revoked_at' => now()])->save();
    }

    private function userAgentHash(Request $request): string
    {
        return hash('sha256', mb_substr((string) $request->userAgent(), 0, 1000));
    }

    private function deviceName(Request $request): string
    {
        $ua = (string) $request->userAgent();

        return match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'iPhone') => 'iPhone',
            str_contains($ua, 'iPad') => 'iPad',
            str_contains($ua, 'Macintosh') => 'Mac',
            str_contains($ua, 'Android') => 'Android',
            default => 'Navigateur',
        };
    }
}
