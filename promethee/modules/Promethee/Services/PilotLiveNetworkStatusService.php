<?php

namespace Modules\Promethee\Services;

use App\Models\Enums\PirepState;
use App\Models\Pirep;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Read-only, server-side flight and online-network indicators.
 *
 * A PIREP left IN_PROGRESS is not proof that the aircraft is flying.
 * Live status requires fresh Hermès telemetry and an airborne phase.
 * Network membership is matched by the pilot's registered CID/VID, never name.
 */
class PilotLiveNetworkStatusService
{
    private const TELEMETRY_MAX_AGE_SECONDS = 180;
    private const AIRBORNE_PHASES = [
        'TAKEOFF', 'INITIAL_CLIMB', 'INIT_CLIMB', 'CLIMB', 'CRUISE',
        'ENROUTE', 'AIRBORNE', 'DESCENT', 'APPROACH', 'FINAL', 'LANDING',
    ];

    private const VATSIM_URL = 'https://data.vatsim.net/v3/vatsim-data.json';
    private const IVAO_URL = 'https://api.ivao.aero/v2/tracker/whazzup';

    /** @return array<int, bool> */
    public function inFlightPilotIds(): array
    {
        $pireps = Pirep::query()->where('state', PirepState::IN_PROGRESS)
            ->get(['id', 'user_id']);

        if ($pireps->isEmpty()) {
            return [];
        }

        $latest = DB::table('promethee_telemetry')
            ->whereIn('pirep_id', $pireps->pluck('id'))
            ->where('recorded_at', '>=', now()->subSeconds(self::TELEMETRY_MAX_AGE_SECONDS))
            ->orderByDesc('recorded_at')
            ->get(['pirep_id', 'payload'])
            ->unique('pirep_id')
            ->keyBy('pirep_id');

        $inFlight = [];
        foreach ($pireps as $pirep) {
            $sample = $latest->get($pirep->id);
            if (!$sample) {
                continue;
            }

            $data = json_decode((string) $sample->payload, true);
            if (!is_array($data)) {
                continue;
            }

            $phase = strtoupper(trim((string) ($data['phase'] ?? '')));
            $isAirborne = in_array($phase, self::AIRBORNE_PHASES, true)
                || ($phase === '' && ($data['on_ground'] ?? null) === false);

            if ($isAirborne) {
                $inFlight[(int) $pirep->user_id] = true;
            }
        }

        return $inFlight;
    }

    /**
     * @param iterable<\App\Models\User> $users
     * @return array<int, array<string, array{online: bool, verified: bool, callsign: ?string}>>
     */
    public function forUsers(iterable $users): array
    {
        $users = collect($users)->filter()->values();
        $needsVatsim = $users->contains(fn ($user) => $this->validId($user->vatsim_id ?? null) !== null);
        $needsIvao = $users->contains(fn ($user) => $this->validId($user->ivao_id ?? null) !== null);

        $vatsim = $needsVatsim ? $this->feed('vatsim', self::VATSIM_URL) : ['available' => false, 'members' => []];
        $ivao = $needsIvao ? $this->feed('ivao', self::IVAO_URL) : ['available' => false, 'members' => []];

        $result = [];
        foreach ($users as $user) {
            $result[(int) $user->id] = [
                'vatsim' => $this->memberStatus($vatsim, $this->validId($user->vatsim_id ?? null)),
                'ivao' => $this->memberStatus($ivao, $this->validId($user->ivao_id ?? null)),
            ];
        }

        return $result;
    }

    private function validId(mixed $id): ?string
    {
        $value = trim((string) ($id ?? ''));
        return preg_match('/^[0-9]{4,10}$/', $value) ? $value : null;
    }

    /** @return array{online: bool, verified: bool, callsign: ?string} */
    private function memberStatus(array $feed, ?string $id): array
    {
        $verified = $id !== null && ($feed['available'] ?? false) === true;
        $member = $verified ? ($feed['members'][$id] ?? null) : null;

        return [
            'online' => $member !== null,
            'verified' => $verified,
            'callsign' => $member['callsign'] ?? null,
        ];
    }

    /** @return array{available: bool, members: array<string, array{callsign: string}>} */
    private function feed(string $network, string $url): array
    {
        $key = 'promethee:network-live:'.$network.':v1';
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }

        $result = ['available' => false, 'members' => []];
        try {
            $response = Http::acceptJson()->connectTimeout(2)->timeout(5)->get($url);
            if ($response->successful()) {
                $payload = $response->json();
                $pilots = $network === 'vatsim'
                    ? ($payload['pilots'] ?? null)
                    : ($payload['clients']['pilots'] ?? $payload['pilots'] ?? null);

                if (is_array($pilots)) {
                    $result['available'] = true;
                    foreach ($pilots as $pilot) {
                        if (!is_array($pilot)) {
                            continue;
                        }

                        $id = $this->validId($network === 'vatsim'
                            ? ($pilot['cid'] ?? null)
                            : ($pilot['userId'] ?? $pilot['vid'] ?? null));

                        if ($id !== null) {
                            $result['members'][$id] = [
                                'callsign' => substr((string) ($pilot['callsign'] ?? ''), 0, 32),
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable) {
            // A failed feed must never turn an unknown network status green.
        }

        Cache::put($key, $result, now()->addSeconds($result['available'] ? 60 : 20));

        return $result;
    }
}
