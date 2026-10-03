<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;

class AirInterCabinProfileService
{
    public function __construct(
        private readonly AircraftConfigurationResolver $aircraftConfigurations
    ) {}

    public function resolve(Aircraft $aircraft, int $databaseCapacity): array
    {
        $technical = $this->aircraftConfigurations->resolveAircraft($aircraft);
        $resolvedCapacity = (int) ($technical['effective']['max_pax'] ?? 0);

        if ($resolvedCapacity > 0) {
            return [
                'key' => $technical['configuration']['code']
                    ?? $technical['variant']['code']
                    ?? $technical['aircraft']['type_key'],
                'label' => $technical['configuration']['name']
                    ?? $technical['variant']['name']
                    ?? $technical['aircraft']['type_name'],
                'capacity' => $resolvedCapacity,
                'source' => 'aircraft_configuration',
                'database_capacity' => max(0, $databaseCapacity),
                'resolved_profile' => $technical,
            ];
        }

        $profiles = (array) config('promethee.cabin-profiles.profiles', []);
        $profileKey = $this->profileKey($aircraft);

        if ($profileKey && isset($profiles[$profileKey])) {
            $profile = $profiles[$profileKey];

            return [
                'key' => $profileKey,
                'label' => (string) ($profile['label'] ?? $profileKey),
                'capacity' => max(0, (int) ($profile['capacity'] ?? $databaseCapacity)),
                'source' => 'air_inter_profile',
                'database_capacity' => max(0, $databaseCapacity),
            ];
        }

        return [
            'key' => null,
            'label' => 'Capacité phpVMS',
            'capacity' => max(0, $databaseCapacity),
            'source' => 'phpvms',
            'database_capacity' => max(0, $databaseCapacity),
        ];
    }

    private function profileKey(Aircraft $aircraft): ?string
    {
        $registration = strtoupper(trim((string) $aircraft->registration));
        $simbriefType = strtoupper(trim((string) ($aircraft->simbrief_type ?: $aircraft->subfleet?->simbrief_type)));
        $icao = strtoupper(trim((string) $aircraft->icao));

        $byRegistration = (array) config('promethee.cabin-profiles.by_registration', []);
        if ($registration !== '' && isset($byRegistration[$registration])) {
            return (string) $byRegistration[$registration];
        }

        $bySimbrief = (array) config('promethee.cabin-profiles.by_simbrief_type', []);
        if ($simbriefType !== '' && isset($bySimbrief[$simbriefType])) {
            return (string) $bySimbrief[$simbriefType];
        }

        $byIcao = (array) config('promethee.cabin-profiles.by_icao', []);
        if ($icao !== '' && isset($byIcao[$icao])) {
            return (string) $byIcao[$icao];
        }

        return null;
    }
}
