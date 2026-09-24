<?php

namespace Modules\Promethee\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Short-lived server-side state for a SimBrief API generation.
 *
 * The state token is the only correlation value exposed to the callback URL.
 * The company API key never leaves Prométhée and no SimBrief credential is
 * stored in the desktop client.
 */
class SimBriefApiSessionService
{
    private const TTL_MINUTES = 30;

    public function create(int $userId, string $operationId, string $flightId, string $aircraftId, string $staticId): array
    {
        $state = Str::random(64);
        $session = [
            'state' => $state,
            'user_id' => $userId,
            'operation_id' => $operationId,
            'flight_id' => $flightId,
            'aircraft_id' => $aircraftId,
            'static_id' => $staticId,
            'ofp_id' => null,
            'created_at' => now()->toIso8601String(),
            'completed_at' => null,
        ];

        Cache::put($this->key($state), $session, now()->addMinutes(self::TTL_MINUTES));

        return $session;
    }

    public function find(string $state): ?array
    {
        $session = Cache::get($this->key($state));

        return is_array($session) ? $session : null;
    }

    public function forUser(string $state, int $userId): ?array
    {
        $session = $this->find($state);

        return $session && (int) ($session['user_id'] ?? 0) === $userId ? $session : null;
    }

    public function complete(string $state, string $ofpId): bool
    {
        $session = $this->find($state);
        if (!$session) {
            return false;
        }

        $session['ofp_id'] = $ofpId;
        $session['completed_at'] = now()->toIso8601String();
        Cache::put($this->key($state), $session, now()->addMinutes(self::TTL_MINUTES));

        return true;
    }

    public function forget(string $state): void
    {
        Cache::forget($this->key($state));
    }

    private function key(string $state): string
    {
        return 'promethee:simbrief:api-session:'.$state;
    }
}
