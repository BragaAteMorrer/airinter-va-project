<?php

namespace Modules\Promethee\Services;

use App\Models\User;

/**
 * Unified Air Inter Network view: Hermès operational presence enriched with
 * verified VATSIM/IVAO identities from phpVMS.
 */
class AirInterNetworkService
{
    public function __construct(
        private readonly PresenceService $presence,
        private readonly OnlineNetworkService $onlineNetworks
    ) {}

    public function network(): array
    {
        $base = $this->presence->network();
        $pilotIds = collect($base['crews'] ?? [])
            ->pluck('pilot.id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $users = $pilotIds->isEmpty()
            ? collect()
            : User::query()
                ->select(['id', 'vatsim_id', 'ivao_id'])
                ->whereIn('id', $pilotIds)
                ->get();

        $external = $this->onlineNetworks->users($users);
        $byNetwork = ['VATSIM' => 0, 'IVAO' => 0];

        $crews = collect($base['crews'] ?? [])->map(function (array $crew) use ($external, &$byNetwork) {
            $pilotId = (int) data_get($crew, 'pilot.id', 0);
            $network = $external['users'][$pilotId] ?? [
                'linked' => false,
                'online' => false,
                'connections' => [],
                'online_connections' => [],
                'primary' => null,
            ];

            foreach ($network['online_connections'] ?? [] as $connection) {
                $name = strtoupper((string) ($connection['network'] ?? ''));
                if (isset($byNetwork[$name])) $byNetwork[$name]++;
            }

            $crew['online_networks'] = $network;
            return $crew;
        })->values()->all();

        return array_merge($base, [
            'contract_version' => '1.1',
            'crews' => $crews,
            'by_network' => $byNetwork,
            'network_providers' => $external['providers'],
        ]);
    }

    public function pilot(User $user): array
    {
        $external = $this->onlineNetworks->users(collect([$user]));

        return $external['users'][(int) $user->id] ?? [
            'linked' => false,
            'online' => false,
            'connections' => [],
            'online_connections' => [],
            'primary' => null,
        ];
    }
}
