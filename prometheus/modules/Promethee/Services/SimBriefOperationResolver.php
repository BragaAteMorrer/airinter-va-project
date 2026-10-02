<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Airport;
use App\Models\Bid;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Enums\FareType;
use App\Models\Flight;
use App\Models\User;
use App\Services\FareService;
use App\Services\UserService;

/**
 * Single source of truth for SimBrief planning.
 *
 * No schema changes: every value is resolved from existing phpVMS/Promethee
 * models and relations. Hermès only identifies an operation and may provide
 * ephemeral planning overrides (alternate/route/level).
 */
class SimBriefOperationResolver
{
    public function __construct(
        private readonly OperationIdentityService $operations,
        private readonly UserService $users,
        private readonly FareService $fares,
        private readonly DemandProfileService $demand,
        private readonly SimBriefCompanyKeyService $companyKey,
        private readonly AircraftVariantService $aircraftVariants,
        private readonly AircraftConfigurationResolver $aircraftConfigurations,
        private readonly SimBriefAircraftProfileResolver $aircraftProfiles,
        private readonly SimBriefAircraftPayloadBuilder $aircraftPayloads
    ) {}

    public function resolveOperation(string $reference, User $user, array $overrides = []): array
    {
        $bid = $this->operations->resolveBid($reference, (int) $user->id);
        abort_if(!$bid, 404, 'Opération Prométhée introuvable.');
        abort_if(!$bid->aircraft_id, 409, 'Sélectionnez un appareil précis avant de préparer SimBrief.');

        $operationId = $this->operations->id($bid);

        return $this->resolveFlightAircraft(
            (string) $bid->flight_id,
            (string) $bid->aircraft_id,
            $user,
            $operationId,
            $overrides,
            $bid
        );
    }

    public function resolveFlightAircraft(
        string $flightId,
        string $aircraftId,
        User $user,
        ?string $operationId = null,
        array $overrides = [],
        ?Bid $bid = null
    ): array {
        $flight = Flight::query()
            ->with([
                'airline',
                'fares',
                'subfleets',
                'dpt_airport',
                'arr_airport',
                'alt_airport',
            ])
            ->findOrFail($flightId);

        $aircraft = Aircraft::query()
            ->with(['subfleet.fares', 'airport'])
            ->withCount([
                'bid',
                'simbriefs' => fn ($query) => $query->whereNull('pirep_id'),
            ])
            ->findOrFail($aircraftId);

        $operationId = $operationId ?: 'legacy_'.$user->id.'_'.$flight->id.'_'.$aircraft->id;
        $this->assertEligible($flight, $aircraft, $user, $bid);

        $technicalProfile = $this->aircraftConfigurations->resolveAircraft($aircraft);
        $technicalSimBrief = $technicalProfile['simbrief'] ?? [];
        $hasConfiguredTechnicalProfile = (($technicalSimBrief['source'] ?? 'phpvms') !== 'phpvms');

        $variant = $bid
            ? $this->aircraftVariants->selectedForBid($bid, $user)
            : $this->aircraftVariants->defaultForAircraft($aircraft);

        $legacyProfile = null;
        $aircraftPayload = ['parameters' => [], 'acdata' => []];

        if ($hasConfiguredTechnicalProfile) {
            $resolvedType = strtoupper(trim((string) ($technicalSimBrief['value'] ?? '')));
            abort_if($resolvedType === '', 422,
                'La configuration sb-airframe de '.$aircraft->registration.' ne contient aucun type SimBrief exploitable.');

            $type = [
                'value' => $resolvedType,
                'source' => 'aircraft_configuration.'.($technicalSimBrief['source'] ?? 'resolved'),
            ];
            if (filled($overrides['simbrief_type'] ?? null)) {
                $type = [
                    'value' => strtoupper(trim((string) $overrides['simbrief_type'])),
                    'source' => 'planning_override',
                ];
            }

            $simbriefStrategy = strtolower((string) ($technicalSimBrief['strategy'] ?? 'native'));
            $simbriefDisplayType = strtoupper((string) (
                $technicalSimBrief['actual_aircraft']
                ?? $aircraft->icao
                ?? $aircraft->subfleet?->type
                ?? $type['value']
            ));
            $simbriefDisplayName = trim((string) (
                $technicalProfile['variant']['short_name']
                ?? $technicalProfile['variant']['name']
                ?? $aircraft->subfleet?->name
                ?? $aircraft->name
                ?? $simbriefDisplayType
            ));

            $aircraftPayload['parameters']['type'] = $type['value'];
            if (!empty($technicalSimBrief['acdata'])) {
                $encodedAcData = json_encode(
                    $technicalSimBrief['acdata'],
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
                );
                abort_if($encodedAcData === false, 500, 'Impossible d’encoder les données sb-airframe destinées à SimBrief.');
                $aircraftPayload['parameters']['acdata'] = $encodedAcData;
                $aircraftPayload['acdata'] = $technicalSimBrief['acdata'];
            }

            $simbriefProfileSummary = [
                'strategy' => $simbriefStrategy,
                'actual_icao' => $simbriefDisplayType,
                'actual_name' => $simbriefDisplayName,
                'calculation_type' => $type['value'],
                'proxy_type' => $technicalSimBrief['proxy_type'] ?? null,
                'internal_id' => $technicalSimBrief['internal_id'] ?? null,
                'airframe_db_id' => null,
            ];
        } else {
            $legacyProfile = $this->aircraftProfiles->resolve(
                $aircraft,
                $variant,
                filled($overrides['simbrief_type'] ?? null) ? (string) $overrides['simbrief_type'] : null
            );
            $aircraftPayload = $this->aircraftPayloads->build($legacyProfile);
            $type = [
                'value' => $aircraftPayload['parameters']['type'],
                'source' => $legacyProfile['source'],
            ];
            $simbriefStrategy = $legacyProfile['strategy'];
            $simbriefDisplayType = $legacyProfile['actual_icao'];
            $simbriefDisplayName = $legacyProfile['actual_name'];
            $simbriefProfileSummary = [
                'strategy' => $legacyProfile['strategy'],
                'actual_icao' => $legacyProfile['actual_icao'],
                'actual_name' => $legacyProfile['actual_name'],
                'calculation_type' => $legacyProfile['type'],
                'proxy_type' => $legacyProfile['proxy_type'],
                'internal_id' => $legacyProfile['internal_id'],
                'airframe_db_id' => $legacyProfile['airframe_db_id'],
            ];
        }

        $origin = $this->airportFromFlight($flight->dpt_airport, (string) $flight->dpt_airport_id, 'départ');
        $destination = $this->airportFromFlight($flight->arr_airport, (string) $flight->arr_airport_id, 'arrivée');
        $alternate = $this->resolveAlternate($flight, $overrides['alternate'] ?? null);

        $airline = $flight->airline;
        abort_if(!$airline || blank($airline->icao), 422, 'Le code ICAO de la compagnie du vol n’est pas configuré.');
        abort_if(blank($aircraft->registration), 422, 'L’immatriculation de l’appareil sélectionné est vide.');

        $route = array_key_exists('route', $overrides)
            ? trim((string) ($overrides['route'] ?? ''))
            : trim((string) ($flight->route ?? ''));

        $hasLevelOverride = array_key_exists('level', $overrides)
            && $overrides['level'] !== null
            && $overrides['level'] !== '';
        $rawLevel = $hasLevelOverride ? $overrides['level'] : $flight->level;
        $level = $this->normalizeFlightLevel($rawLevel);

        // A malformed schedule level must never make the whole SimBrief
        // workflow unusable. If phpVMS contains a legacy/odd value, omit the
        // "fl" parameter and let SimBrief choose the optimum level.
        if ($rawLevel !== null && trim((string) $rawLevel) !== '' && $level === null) {
            report(new \UnexpectedValueException(
                'Promethee SimBrief ignored invalid flight level "'.(string) $rawLevel
                .'" for flight '.(string) $flight->id
            ));
            $level = null;
        }

        $profile = $this->demand->profile($aircraft, $flight, $operationId);
        $technicalMaxPax = $technicalProfile['effective']['max_pax'] ?? null;
        $maxPax = is_numeric($technicalMaxPax) && (int) $technicalMaxPax > 0
            ? (int) $technicalMaxPax
            : ($legacyProfile ? $this->aircraftPayloads->maxPassengers($legacyProfile) : null);
        if ($maxPax !== null && $maxPax > 0) {
            $profile['capacity'] = $maxPax;
            $profile['capacity_source'] = $hasConfiguredTechnicalProfile ? 'aircraft_configuration' : 'sb_airframe';
            $profile['passengers'] = min(
                $maxPax,
                max(0, (int) round($maxPax * (float) $profile['load_factor_percent'] / 100))
            );
        }
        $effectiveFares = $this->effectiveFares($flight, $aircraft, (int) $profile['capacity']);

        $defaultCallsign = setting('simbrief.callsign', true)
            ? trim((string) $user->ident)
            : strtoupper((string) $airline->icao).$flight->flight_number;
        $callsign = filled($overrides['callsign'] ?? null)
            ? strtoupper((string) $overrides['callsign'])
            : $defaultCallsign;

        $value = static fn (string $key, mixed $default = null) =>
            array_key_exists($key, $overrides) && $overrides[$key] !== null && $overrides[$key] !== ''
                ? $overrides[$key]
                : $default;

        $planningPax = $value('pax', $profile['capacity'] > 0 ? $profile['passengers'] : null);
        if ($maxPax !== null && $planningPax !== null) {
            abort_if((int) $planningPax > $maxPax, 422,
                'Le nombre de passagers demandé ('.(int) $planningPax.') dépasse la capacité réelle sb-airframe '
                .$maxPax.' de '.$simbriefDisplayType.'.');
        }

        $parameters = array_merge($aircraftPayload['parameters'], array_filter([
            'airline' => strtoupper((string) $airline->icao),
            'fltnum' => (string) $flight->flight_number,
            'type' => $type['value'],
            'orig' => $origin['icao'],
            'dest' => $destination['icao'],
            'altn' => $alternate['icao'] === 'AUTO' ? null : $alternate['icao'],
            'route' => $route !== '' ? $route : null,
            'fl' => $level,
            'reg' => strtoupper((string) $aircraft->registration),
            'pax' => $planningPax,
            'callsign' => $callsign !== '' ? $callsign : strtoupper((string) $airline->icao).$flight->flight_number,

            // SimBrief dispatch options exposed by Hermès. Values are request
            // scoped only; defaults preserve the previous Prométhée behaviour.
            'units' => strtoupper((string) $value('units', 'KGS')),
            'planformat' => strtolower((string) $value('planformat', 'LIDO')),
            'navlog' => (string) $value('navlog', '1'),
            'maps' => strtolower((string) $value('maps', 'DETAIL')),
            'tlr' => (string) $value('tlr'),
            'notams' => (string) $value('notams'),
            'firnot' => (string) $value('firnot'),
            'stepclimbs' => (string) $value('stepclimbs'),
            'etops' => (string) $value('etops'),
            'find_sidstar' => $value('find_sidstar'),
            'fuelfactor' => $value(
                'fuelfactor',
                $this->simBriefFuelFactor($technicalProfile['effective']['fuel_factor'] ?? null)
            ),
            'climb' => $value('climb', $technicalProfile['effective']['climb_profile'] ?? null),
            'cruise' => $value('cruise', $technicalProfile['effective']['cruise_profile'] ?? null),
            'descent' => $value('descent', $technicalProfile['effective']['descent_profile'] ?? null),
            'equipment' => $value('equipment', $technicalProfile['effective']['equipment'] ?? null),
            'transponder' => $value('transponder', $technicalProfile['effective']['transponder'] ?? null),
            'pbn' => $value('pbn', $technicalProfile['effective']['pbn'] ?? null),
            'acdata' => !empty($technicalProfile['simbrief']['acdata'])
                ? json_encode($technicalProfile['simbrief']['acdata'], JSON_UNESCAPED_SLASHES)
                : null,
            'civalue' => $value('civalue'),
            'contpct' => $value('contpct'),
            'resvrule' => $value('resvrule'),
            'selcal' => $value('selcal', $aircraft->selcal ?? null),
            'deprwy' => $value('deprwy'),
            'arrrwy' => $value('arrrwy'),
            'taxiout' => $value('taxiout'),
            'taxiin' => $value('taxiin'),
            'manualrmk' => $value('manualrmk'),
        ], fn ($value) => $value !== null && $value !== ''));

        logger()->info('[SimBrief] Aircraft profile resolved', [
            'aircraft_registration' => $aircraft->registration,
            'actual_icao' => $simbriefDisplayType,
            'strategy' => $simbriefStrategy,
            'calculation_type' => $type['value'],
            'proxy_aircraft' => $technicalSimBrief['proxy_type'] ?? ($legacyProfile['proxy_type'] ?? null),
            'internal_id' => $technicalSimBrief['internal_id'] ?? ($legacyProfile['internal_id'] ?? null),
            'pax' => $planningPax,
            'maxpax' => $maxPax,
            'mtow' => $technicalProfile['effective']['mtow'] ?? ($legacyProfile['config']['weights_kg']['mtow'] ?? null),
            'fuelfactor' => $technicalProfile['effective']['fuel_factor'] ?? ($legacyProfile['config']['performance']['fuelfactor'] ?? null),
        ]);

        return [
            'operation_id' => $operationId,
            'bid' => $bid,
            'flight_model' => $flight,
            'aircraft_model' => $aircraft,
            'flight' => [
                'id' => $flight->id,
                'ident' => $flight->ident,
                'number' => $flight->flight_number,
                'route' => $route,
                'level' => $level,
            ],
            'airline' => [
                'id' => $airline->id,
                'icao' => strtoupper((string) $airline->icao),
                'iata' => $airline->iata,
                'name' => $airline->name,
            ],
            'origin' => $origin,
            'destination' => $destination,
            'alternate' => $alternate,
            'aircraft' => [
                'id' => $aircraft->id,
                'registration' => strtoupper((string) $aircraft->registration),
                'icao' => strtoupper((string) $aircraft->icao),
                'name' => $aircraft->name,
                'airport' => $aircraft->airport_id,
                'subfleet_id' => $aircraft->subfleet_id,
                'subfleet' => $aircraft->subfleet?->name,
                'simbrief_type' => $type['value'],
                'simbrief_display_type' => $simbriefDisplayType,
                'simbrief_type_source' => $type['source'],
                'simbrief_strategy' => $simbriefStrategy,
                'simbrief_profile' => array_merge($simbriefProfileSummary, ['maxpax' => $maxPax]),
                'type_key' => $this->demand->typeKey($aircraft),
                'type_label' => $this->demand->typeLabel($aircraft),
                // "variant" is retained for old Hermès builds: historically
                // it meant the simulator/add-on profile, not the real variant.
                'variant' => $variant,
                'simulator_profile' => $variant,
                'historical_variant' => $technicalProfile['variant'] ?? null,
                'configuration' => $technicalProfile['configuration'] ?? null,
                'resolved_profile' => $technicalProfile,
                'variant' => $variant,
            ],
            'demand' => $profile,
            'fares' => $effectiveFares,
            'parameters' => $parameters,
            'company_api_available' => $this->companyKey->configured(),
            'sources' => [
                'operation' => $bid ? 'bids' : 'legacy_flight_aircraft',
                'flight' => 'flights',
                'origin' => 'airports',
                'destination' => 'airports',
                'alternate' => $alternate['source'],
                'aircraft' => 'aircraft',
                'subfleet' => 'subfleets',
                'simbrief_type' => $type['source'],
                'simbrief_profile' => $hasConfiguredTechnicalProfile
                    ? 'aircraft_configuration.'.($technicalSimBrief['source'] ?? 'resolved')
                    : ($legacyProfile['source'] ?? null),
                'aircraft_variant' => $technicalProfile['variant'] ? 'promethee_aircraft_historical_variants' : null,
                'aircraft_configuration' => $technicalProfile['configuration'] ? 'promethee_airframe_configurations' : null,
                'registration_profile' => $technicalProfile['period'] ? 'promethee_aircraft_configuration_assignments' : null,
                'simulator_profile' => $variant ? 'promethee_operation_aircraft_variants' : null,
                'passengers' => 'DemandProfileService',
                'fares' => 'FareService',
            ],
            'checks' => $this->readinessChecks(
                $flight,
                $aircraft,
                $origin,
                $destination,
                $type['value'],
                $profile
            ),
        ];
    }

    public function publicView(array $resolved): array
    {
        $checks = collect($resolved['checks']);
        $accountChecks = $checks->reject(fn ($check) => ($check['code'] ?? null) === 'COMPANY_API');

        return [
            'contract_version' => '1.0',
            'operation_id' => $resolved['operation_id'],
            'ready' => $checks->every(fn ($check) => $check['ready']),
            'ready_company_api' => $checks->every(fn ($check) => $check['ready']),
            'ready_account' => $accountChecks->every(fn ($check) => $check['ready']),
            'flight' => $resolved['flight'],
            'airline' => $resolved['airline'],
            'origin' => $resolved['origin'],
            'destination' => $resolved['destination'],
            'alternate' => $resolved['alternate'],
            'aircraft' => $resolved['aircraft'],
            'demand' => $resolved['demand'],
            'parameters' => $resolved['parameters'],
            'company_api_available' => $resolved['company_api_available'],
            'sources' => $resolved['sources'],
            'checks' => $resolved['checks'],
        ];
    }

    private function normalizeFlightLevel(mixed $value): ?int
    {
        if ($value === null) return null;

        $raw = strtoupper(trim((string) $value));
        if ($raw === '') return null;

        $raw = preg_replace('/^FL\s*/', '', $raw);
        $raw = preg_replace('/\s*(?:FT|FEET|PIEDS?)$/', '', $raw);
        $raw = str_replace([' ', ','], ['', '.'], $raw);

        if (!is_numeric($raw)) return null;

        $numeric = (float) $raw;
        if ($numeric <= 0) return null;

        $level = $numeric > 600
            ? (int) round($numeric / 100)
            : (int) round($numeric);

        return $level >= 10 && $level <= 600 ? $level : null;
    }

    private function assertEligible(Flight $flight, Aircraft $aircraft, User $user, ?Bid $bid): void
    {
        $allowedSubfleets = $this->users->getAllowableSubfleets($user)->pluck('id');
        $flightSubfleets = $flight->subfleets->pluck('id');

        abort_unless($allowedSubfleets->contains($aircraft->subfleet_id), 403,
            'Votre qualification ne permet pas d’utiliser la sous-flotte de cet appareil.');
        abort_unless($flightSubfleets->isEmpty() || $flightSubfleets->contains($aircraft->subfleet_id), 403,
            'La sous-flotte de cet appareil n’est pas autorisée sur ce vol.');

        if (setting('pireps.only_aircraft_at_dpt_airport')) {
            abort_unless(
                strtoupper((string) $aircraft->airport_id) === strtoupper((string) $flight->dpt_airport_id),
                409,
                'L’appareil '.$aircraft->registration.' est à '.($aircraft->airport_id ?: 'une position inconnue')
                .' au lieu de '.$flight->dpt_airport_id.'.'
            );
        }

        $reservedForThisOperation = $bid
            ? (string) $bid->aircraft_id === (string) $aircraft->id
            : Bid::query()
                ->where('user_id', $user->id)
                ->where('flight_id', $flight->id)
                ->where('aircraft_id', $aircraft->id)
                ->exists();

        if (setting('bids.block_aircraft')) {
            abort_unless((int) $aircraft->bid_count === 0 || $reservedForThisOperation, 409,
                'Cet appareil est déjà réservé sur une autre opération.');
        }

        if (setting('simbrief.block_aircraft')) {
            $ownActiveOfp = $aircraft->simbriefs()
                ->whereNull('pirep_id')
                ->where('user_id', $user->id)
                ->where('flight_id', $flight->id)
                ->exists();

            abort_unless((int) $aircraft->simbriefs_count === 0 || $ownActiveOfp, 409,
                'Un autre OFP actif utilise déjà cet appareil.');
        }

        abort_unless($aircraft->state === AircraftState::PARKED, 409,
            'L’appareil '.$aircraft->registration.' n’est pas au parking.');
        abort_unless($aircraft->status === AircraftStatus::ACTIVE, 409,
            'L’appareil '.$aircraft->registration.' n’est pas actif.');
    }

    private function simbriefType(Aircraft $aircraft): array
    {
        $candidates = [
            'aircraft.simbrief_type' => $aircraft->simbrief_type,
            'subfleet.simbrief_type' => $aircraft->subfleet?->simbrief_type,
            'aircraft.icao' => $aircraft->icao,
            'subfleet.type' => $aircraft->subfleet?->type,
        ];

        foreach ($candidates as $source => $value) {
            $value = strtoupper(trim((string) $value));
            if ($value !== '') return ['value' => $value, 'source' => $source];
        }

        return ['value' => null, 'source' => null];
    }

    private function airportFromFlight($airport, string $fallbackId, string $role): array
    {
        abort_if(!$airport, 422, 'L’aéroport de '.$role.' '.$fallbackId.' est absent de la BDD.');

        return [
            'id' => $airport->id,
            'icao' => strtoupper((string) ($airport->icao ?: $airport->id)),
            'iata' => $airport->iata,
            'name' => $airport->name,
            'lat' => $airport->lat,
            'lon' => $airport->lon,
            'elevation' => $airport->elevation,
            'timezone' => $airport->timezone,
            'source' => 'flights.'.$role.'_airport_id -> airports',
        ];
    }

    private function resolveAlternate(Flight $flight, mixed $override): array
    {
        $code = strtoupper(trim((string) ($override ?? '')));
        $source = 'override';

        if ($code === '') {
            $code = strtoupper(trim((string) ($flight->alt_airport_id ?? '')));
            $source = 'flights.alt_airport_id';
        }

        if ($code === '' || $code === 'AUTO') {
            return [
                'id' => null,
                'icao' => 'AUTO',
                'iata' => null,
                'name' => 'Automatic',
                'lat' => null,
                'lon' => null,
                'elevation' => null,
                'timezone' => null,
                'source' => $code === 'AUTO' && $source === 'override' ? 'override' : 'AUTO',
            ];
        }

        $airport = $flight->alt_airport && strtoupper((string) $flight->alt_airport->id) === $code
            ? $flight->alt_airport
            : Airport::query()
                ->where('id', $code)
                ->orWhere('icao', $code)
                ->first();

        abort_if(!$airport, 422, 'Le dégagement '.$code.' n’existe pas dans la BDD aéroports.');

        return [
            'id' => $airport->id,
            'icao' => strtoupper((string) ($airport->icao ?: $airport->id)),
            'iata' => $airport->iata,
            'name' => $airport->name,
            'lat' => $airport->lat,
            'lon' => $airport->lon,
            'elevation' => $airport->elevation,
            'timezone' => $airport->timezone,
            'source' => $source,
        ];
    }

    private function simBriefFuelFactor(mixed $factor): ?string
    {
        if ($factor === null || $factor === '' || !is_numeric($factor)) return null;

        $value = round((float) $factor, 1);
        $prefix = $value < 0 ? 'M' : 'P';
        $absolute = abs($value);
        $formatted = rtrim(rtrim(number_format($absolute, 1, '.', ''), '0'), '.');

        return $prefix.str_pad($formatted, 2, '0', STR_PAD_LEFT);
    }

    private function effectiveFares(Flight $flight, Aircraft $aircraft, int $cabinCapacity): array
    {
        $fares = $this->fares
            ->getFareWithOverrides($aircraft->subfleet?->fares ?? collect(), $flight->fares)
            ->filter(fn ($fare) => $fare->active && !empty($fare->capacity))
            ->map(fn ($fare) => [
                'id' => $fare->id,
                'fare_id' => $fare->id,
                'code' => $fare->code,
                'name' => $fare->name,
                'type' => $fare->type,
                'capacity' => (int) $fare->capacity,
                'price' => (float) $fare->price,
                'cost' => (float) $fare->cost,
            ])
            ->values();

        $passengerIndexes = $fares
            ->keys()
            ->filter(fn ($index) => ($fares[$index]['type'] ?? null) === FareType::PASSENGER)
            ->values();

        $databasePassengerCapacity = $passengerIndexes
            ->sum(fn ($index) => (int) ($fares[$index]['capacity'] ?? 0));

        if ($cabinCapacity > 0 && $databasePassengerCapacity > 0
            && $databasePassengerCapacity !== $cabinCapacity
            && $passengerIndexes->isNotEmpty()) {
            $remaining = $cabinCapacity;
            $lastIndex = $passengerIndexes->last();

            foreach ($passengerIndexes as $index) {
                if ($index === $lastIndex) {
                    $newCapacity = $remaining;
                } else {
                    $share = (int) round(
                        $cabinCapacity * ((int) $fares[$index]['capacity'] / $databasePassengerCapacity)
                    );
                    $newCapacity = max(0, min($remaining, $share));
                    $remaining -= $newCapacity;
                }

                $fare = $fares[$index];
                $fare['capacity'] = $newCapacity;
                $fares[$index] = $fare;
            }
        }

        return $fares->values()->all();
    }

    private function readinessChecks(
        Flight $flight,
        Aircraft $aircraft,
        array $origin,
        array $destination,
        ?string $type,
        array $profile
    ): array {
        return [
            ['code' => 'FLIGHT', 'ready' => filled($flight->id), 'label' => 'Vol BDD résolu'],
            ['code' => 'ORIGIN', 'ready' => filled($origin['icao']), 'label' => 'Aéroport de départ résolu'],
            ['code' => 'DESTINATION', 'ready' => filled($destination['icao']), 'label' => 'Aéroport d’arrivée résolu'],
            ['code' => 'AIRCRAFT', 'ready' => filled($aircraft->registration), 'label' => 'Appareil précis résolu'],
            ['code' => 'SIMBRIEF_TYPE', 'ready' => filled($type), 'label' => 'Type SimBrief résolu'],
            ['code' => 'PAX', 'ready' => ($profile['capacity'] ?? 0) <= 0 || isset($profile['passengers']), 'label' => 'Charge passagers résolue'],
            ['code' => 'COMPANY_API', 'ready' => $this->companyKey->configured(), 'label' => 'Clé API compagnie configurée'],
        ];
    }
}
