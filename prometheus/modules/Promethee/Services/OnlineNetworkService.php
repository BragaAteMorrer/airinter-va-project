<?php

namespace Modules\Promethee\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Read-only bridge to the public VATSIM/IVAO live feeds.
 *
 * Identity is always taken from the authenticated phpVMS user (vatsim_id /
 * ivao_id). Hermès never declares its own network identity.
 */
class OnlineNetworkService
{
    private const CACHE_SECONDS = 15;

    public function users(Collection $users): array
    {
        $users = $users->keyBy(fn (User $user) => (int) $user->id);
        if ($users->isEmpty()) {
            return ['users' => [], 'providers' => $this->providerState([])];
        }

        $vatsim = $users->contains(fn (User $user) => filled($user->vatsim_id))
            ? $this->vatsimSnapshot()
            : ['ok' => true, 'pilots' => [], 'updated_at' => null, 'skipped' => true];
        $ivao = $users->contains(fn (User $user) => filled($user->ivao_id))
            ? $this->ivaoSnapshot()
            : ['ok' => true, 'pilots' => [], 'updated_at' => null, 'skipped' => true];

        $result = [];
        foreach ($users as $user) {
            $connections = [];
            $vatsimId = trim((string) $user->vatsim_id);
            $ivaoId = trim((string) $user->ivao_id);

            if ($vatsimId !== '') {
                $pilot = $vatsim['pilots'][$vatsimId] ?? null;
                $connections['vatsim'] = $this->networkConnection('VATSIM', $vatsimId, $pilot, $vatsim['ok']);
            }
            if ($ivaoId !== '') {
                $pilot = $ivao['pilots'][$ivaoId] ?? null;
                $connections['ivao'] = $this->networkConnection('IVAO', $ivaoId, $pilot, $ivao['ok']);
            }

            $online = collect($connections)->filter(fn (array $connection) => $connection['online'])->values()->all();
            $result[(int) $user->id] = [
                'linked' => !empty($connections),
                'online' => !empty($online),
                'connections' => $connections,
                'online_connections' => $online,
                'primary' => $online[0] ?? null,
            ];
        }

        return [
            'users' => $result,
            'providers' => [
                'vatsim' => $this->providerState($vatsim),
                'ivao' => $this->providerState($ivao),
            ],
        ];
    }

    private function networkConnection(string $network, string $memberId, ?array $pilot, bool $feedOk): array
    {
        return [
            'network' => $network,
            'member_id' => $memberId,
            'linked' => true,
            'online' => $feedOk && $pilot !== null,
            'feed_available' => $feedOk,
            'callsign' => $pilot['callsign'] ?? null,
            'position' => $pilot['position'] ?? null,
            'flight_plan' => $pilot['flight_plan'] ?? null,
            'connected_at' => $pilot['connected_at'] ?? null,
            'last_updated_at' => $pilot['last_updated_at'] ?? null,
        ];
    }

    private function vatsimSnapshot(): array
    {
        return Cache::remember('promethee:network:vatsim', self::CACHE_SECONDS, function () {
            try {
                $response = Http::acceptJson()
                    ->timeout(7)
                    ->retry(1, 150)
                    ->get(config('services.vatsim.data_url', 'https://data.vatsim.net/v3/vatsim-data.json'));

                if (!$response->successful()) {
                    return ['ok' => false, 'pilots' => [], 'updated_at' => null, 'status' => $response->status()];
                }

                $json = $response->json();
                $pilots = [];
                foreach (($json['pilots'] ?? []) as $pilot) {
                    $cid = trim((string) ($pilot['cid'] ?? ''));
                    if ($cid === '') continue;
                    $plan = $pilot['flight_plan'] ?? [];
                    $pilots[$cid] = [
                        'callsign' => $pilot['callsign'] ?? null,
                        'position' => [
                            'lat' => $this->number($pilot['latitude'] ?? null),
                            'lon' => $this->number($pilot['longitude'] ?? null),
                            'altitude_msl' => $this->number($pilot['altitude'] ?? null),
                            'groundspeed' => $this->number($pilot['groundspeed'] ?? null),
                            'heading' => $this->number($pilot['heading'] ?? null),
                        ],
                        'flight_plan' => $plan ? [
                            'departure' => $plan['departure'] ?? null,
                            'arrival' => $plan['arrival'] ?? null,
                            'alternate' => $plan['alternate'] ?? null,
                            'aircraft' => $plan['aircraft_short'] ?? $plan['aircraft'] ?? null,
                            'route' => $plan['route'] ?? null,
                            'flight_rules' => $plan['flight_rules'] ?? null,
                        ] : null,
                        'connected_at' => $pilot['logon_time'] ?? null,
                        'last_updated_at' => $pilot['last_updated'] ?? null,
                    ];
                }

                return [
                    'ok' => true,
                    'pilots' => $pilots,
                    'updated_at' => data_get($json, 'general.update_timestamp'),
                    'status' => 200,
                ];
            } catch (Throwable) {
                return ['ok' => false, 'pilots' => [], 'updated_at' => null, 'status' => null];
            }
        });
    }

    private function ivaoSnapshot(): array
    {
        return Cache::remember('promethee:network:ivao', self::CACHE_SECONDS, function () {
            try {
                $response = Http::acceptJson()
                    ->timeout(7)
                    ->retry(1, 150)
                    ->get(config('services.ivao.data_url', 'https://api.ivao.aero/v2/tracker/whazzup'));

                if (!$response->successful()) {
                    return ['ok' => false, 'pilots' => [], 'updated_at' => null, 'status' => $response->status()];
                }

                $json = $response->json();
                // IVAO Whazzup v2 has historically wrapped clients under
                // payload.clients, while some mirrors expose clients at root.
                $source = data_get($json, 'payload.clients.pilots', data_get($json, 'clients.pilots', []));
                $pilots = [];
                foreach (is_array($source) ? $source : [] as $pilot) {
                    $vid = trim((string) ($pilot['userId'] ?? ''));
                    if ($vid === '') continue;

                    $track = $pilot['lastTrack'] ?? [];
                    $plan = $pilot['flightPlan'] ?? [];
                    $pilots[$vid] = [
                        'callsign' => $pilot['callsign'] ?? null,
                        'position' => [
                            'lat' => $this->number($track['latitude'] ?? null),
                            'lon' => $this->number($track['longitude'] ?? null),
                            'altitude_msl' => $this->number($track['altitude'] ?? null),
                            'groundspeed' => $this->number($track['groundSpeed'] ?? null),
                            'heading' => $this->number($track['heading'] ?? null),
                        ],
                        'flight_plan' => $plan ? [
                            'departure' => $plan['departureId'] ?? null,
                            'arrival' => $plan['arrivalId'] ?? null,
                            'alternate' => $plan['alternativeId'] ?? null,
                            'aircraft' => $plan['aircraftId'] ?? data_get($plan, 'aircraft.icaoCode'),
                            'route' => $plan['route'] ?? null,
                            'flight_rules' => $plan['flightRules'] ?? null,
                        ] : null,
                        'connected_at' => $pilot['createdAt'] ?? data_get($pilot, 'pilotSession.createdAt'),
                        'last_updated_at' => $track['timestamp'] ?? null,
                    ];
                }

                return [
                    'ok' => true,
                    'pilots' => $pilots,
                    'updated_at' => data_get($json, 'updatedAt', data_get($json, 'payload.updatedAt')),
                    'status' => 200,
                ];
            } catch (Throwable) {
                return ['ok' => false, 'pilots' => [], 'updated_at' => null, 'status' => null];
            }
        });
    }

    private function providerState(array $snapshot): array
    {
        return [
            'available' => (bool) ($snapshot['ok'] ?? true),
            'updated_at' => $snapshot['updated_at'] ?? null,
            'status' => $snapshot['status'] ?? null,
        ];
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
