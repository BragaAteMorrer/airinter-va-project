<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Airport;
use App\Models\Bid;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
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
        private readonly SimBriefCompanyKeyService $companyKey
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

        $type = $this->simbriefType($aircraft);
        abort_if($type['value'] === null, 422,
            'Aucun type SimBrief n’est défini pour '.$aircraft->registration
            .' (aircraft.simbrief_type, subfleet.simbrief_type et aircraft.icao sont vides).');

        $origin = $this->airportFromFlight($flight->dpt_airport, (string) $flight->dpt_airport_id, 'départ');
        $destination = $this->airportFromFlight($flight->arr_airport, (string) $flight->arr_airport_id, 'arrivée');
        $alternate = $this->resolveAlternate($flight, $overrides['alternate'] ?? null);

        $airline = $flight->airline;
        abort_if(!$airline || blank($airline->icao), 422, 'Le code ICAO de la compagnie du vol n’est pas configuré.');
        abort_if(blank($aircraft->registration), 422, 'L’immatriculation de l’appareil sélectionné est vide.');

        $route = array_key_exists('route', $overrides)
            ? trim((string) ($overrides['route'] ?? ''))
            : trim((string) ($flight->route ?? ''));

        $level = array_key_exists('level', $overrides) && $overrides['level'] !== null && $overrides['level'] !== ''
            ? (int) $overrides['level']
            : ($flight->level ? (int) $flight->level : null);
        abort_if($level !== null && ($level < 10 || $level > 600), 422,
            'Le niveau de vol résolu doit être compris entre FL010 et FL600.');

        $profile = $this->demand->profile($aircraft, $flight, $operationId);
        $effectiveFares = $this->effectiveFares($flight, $aircraft);

        $callsign = setting('simbrief.callsign', true)
            ? trim((string) $user->ident)
            : strtoupper((string) $airline->icao).$flight->flight_number;

        $parameters = array_filter([
            'airline' => strtoupper((string) $airline->icao),
            'fltnum' => (string) $flight->flight_number,
            'type' => $type['value'],
            'orig' => $origin['icao'],
            'dest' => $destination['icao'],
            'altn' => $alternate['icao'] === 'AUTO' ? null : $alternate['icao'],
            'route' => $route !== '' ? $route : null,
            'fl' => $level,
            'reg' => strtoupper((string) $aircraft->registration),
            'pax' => $profile['capacity'] > 0 ? $profile['passengers'] : null,
            'callsign' => $callsign !== '' ? $callsign : strtoupper((string) $airline->icao).$flight->flight_number,
            'units' => 'KGS',
            'planformat' => 'LIDO',
            'navlog' => '1',
            'maps' => 'detail',
        ], fn ($value) => $value !== null && $value !== '');

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
                'simbrief_type_source' => $type['source'],
                'type_key' => $this->demand->typeKey($aircraft),
                'type_label' => $this->demand->typeLabel($aircraft),
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
        return [
            'contract_version' => '1.0',
            'operation_id' => $resolved['operation_id'],
            'ready' => collect($resolved['checks'])->every(fn ($check) => $check['ready']),
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

    private function effectiveFares(Flight $flight, Aircraft $aircraft): array
    {
        return $this->fares
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
            ->values()
            ->all();
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
