<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\Aircraft;
use App\Models\Bid;
use App\Models\Flight;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\SimBrief;
use App\Models\Pirep;
use App\Models\Enums\PirepSource;
use App\Models\Enums\PirepState;
use App\Models\Enums\PirepStatus;
use App\Services\PirepService;
use App\Services\BidService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Modules\Promethee\Services\OperationIdentityService;
use Modules\Promethee\Services\SafetyAnalyzer;
use Modules\Promethee\Services\AircraftOperationalStateService;
use Modules\Promethee\Services\DemandProfileService;

/**
 * Stable Air Inter operations facade.
 *
 * Hermès consumes this contract instead of depending on phpVMS resources.
 * Keep DTO names stable and additive; phpVMS remains an implementation detail.
 */
class OperationsV1Controller extends Controller
{
    private const SIMULATORS = ['fs2004', 'fsx', 'p3d', 'msfs2020', 'msfs2024', 'xplane'];

    public function __construct(
        private readonly UserService $userSvc,
        private readonly PirepService $pirepSvc,
        private readonly OperationIdentityService $operationIdentity,
        private readonly SafetyAnalyzer $safetyAnalyzer,
        private readonly AircraftOperationalStateService $aircraftState,
        private readonly DemandProfileService $demandProfile
    ) {}

    public function index(Request $request)
    {
        $data = $request->validate(['simulator' => 'nullable|in:'.implode(',', self::SIMULATORS)]);
        $simulator = $data['simulator'] ?? 'msfs2020';
        $bids = Bid::with(['flight.airline', 'flight.subfleets', 'aircraft.subfleet'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json(['data' => [
            'contract_version' => '1.0',
            'simulator' => $simulator,
            'simulators' => self::SIMULATORS,
            'operations' => $bids->map(fn (Bid $bid) => $this->operationDto($bid, $simulator))->values(),
        ]]);
    }


    public function searchFlights(Request $request)
    {
        $data = $request->validate([
            'flight_number' => 'nullable|string|max:12',
            'dep_icao' => 'nullable|string|max:8',
            'arr_icao' => 'nullable|string|max:8',
            'icao_type' => 'nullable|string|max:16',
        ]);

        $flightNumber = strtoupper(trim((string) ($data['flight_number'] ?? '')));
        $flightNumber = preg_replace('/^ITF[ -]?/', '', $flightNumber);
        $departure = strtoupper(trim((string) ($data['dep_icao'] ?? '')));
        $arrival = strtoupper(trim((string) ($data['arr_icao'] ?? '')));
        $typeFilter = strtoupper(preg_replace('/[^A-Z0-9]+/', '', (string) ($data['icao_type'] ?? '')));

        $allowedSubfleets = $this->userSvc->getAllowableSubfleets($request->user())->values();
        $allowedIds = $allowedSubfleets->pluck('id')->map(fn ($id) => (int) $id)->all();

        $query = Flight::query()
            ->with(['airline:id,icao,iata,name', 'subfleets:id,name,type'])
            ->where('active', true)
            ->where('visible', true)
            ->when($flightNumber !== '', fn ($q) => $q->where('flight_number', $flightNumber))
            ->when($departure !== '', fn ($q) => $q->where('dpt_airport_id', $departure))
            ->when($arrival !== '', fn ($q) => $q->where('arr_airport_id', $arrival))
            ->orderBy('dpt_airport_id')
            ->orderBy('arr_airport_id')
            ->orderBy('flight_number')
            ->limit(100);

        $flights = $query->get()
            ->filter(function (Flight $flight) use ($allowedSubfleets, $allowedIds, $typeFilter) {
                $flightSubfleetIds = $flight->subfleets->pluck('id')->map(fn ($id) => (int) $id);
                $compatible = $flightSubfleetIds->isEmpty()
                    ? $allowedSubfleets
                    : $allowedSubfleets->filter(fn ($subfleet) => in_array((int) $subfleet->id, $allowedIds, true)
                        && $flightSubfleetIds->contains((int) $subfleet->id));

                if ($compatible->isEmpty()) return false;
                if ($typeFilter === '') return true;

                return $compatible->contains(function ($subfleet) use ($typeFilter) {
                    $type = strtoupper(preg_replace('/[^A-Z0-9]+/', '', (string) ($subfleet->type ?? '')));
                    $name = strtoupper(preg_replace('/[^A-Z0-9]+/', '', (string) ($subfleet->name ?? '')));
                    return $type === $typeFilter || str_contains($name, $typeFilter);
                });
            })
            ->map(function (Flight $flight) {
                return [
                    'id' => $flight->id,
                    'program' => true,
                    'ident' => $flight->ident,
                    'airline_id' => $flight->airline_id,
                    'airline' => $flight->airline ? [
                        'id' => $flight->airline->id,
                        'icao' => $flight->airline->icao,
                        'iata' => $flight->airline->iata,
                        'name' => $flight->airline->name,
                    ] : null,
                    'flight_number' => $flight->flight_number,
                    'departure' => $flight->dpt_airport_id,
                    'arrival' => $flight->arr_airport_id,
                    'alternate' => $flight->alt_airport_id,
                    'route' => $flight->route,
                    'level' => $flight->level,
                ];
            })
            ->values();

        return response()->json(['data' => $flights]);
    }

    public function reserveFlight(string $flightId, Request $request, BidService $bids)
    {
        $flight = Flight::query()
            ->where('id', $flightId)
            ->where('active', true)
            ->where('visible', true)
            ->firstOrFail();

        try {
            $bid = $bids->addBid($flight, $request->user());
        } catch (\Throwable $exception) {
            abort(409, $exception->getMessage() ?: 'Cette réservation ne peut pas être créée.');
        }

        $bid = Bid::with(['flight.airline', 'flight.subfleets', 'aircraft.subfleet'])
            ->where('id', $bid->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'data' => $this->operationDto($bid, 'msfs2020'),
        ], 201);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json(['data' => [
            'id' => $user->id,
            'ident' => $user->ident,
            'name' => trim(($user->name_private ?? $user->name ?? '')),
            'contract_version' => '1.0',
        ]]);
    }

    public function show(string $bidId, Request $request)
    {
        $bid = $this->bid($bidId, $request);
        $simulator = $request->validate(['simulator' => 'nullable|in:'.implode(',', self::SIMULATORS)])['simulator'] ?? 'msfs2020';
        return response()->json(['data' => $this->operationDto($bid, $simulator)]);
    }

    public function destroy(string $bidId, Request $request)
    {
        $bid = $this->bid($bidId, $request);
        $ident = $bid->flight?->ident ?? $bid->flight_id;
        abort_if($this->operationPirep($bid), 409, 'Cette opération possède déjà un PIREP et ne peut plus être supprimée.');
        // A reservation is only an intention to fly. PIREPs are separate
        // operational records and are never removed by cancelling a bid.
        $bid->delete();

        return response()->json(['data' => [
            'cancelled' => true,
            'operation_id' => $this->operationIdentity->id($bid),
            'bid_id' => $bid->id,
            'flight_ident' => $ident,
        ]]);
    }

    public function aircraft(string $bidId, Request $request)
    {
        $bid = $this->bid($bidId, $request);
        $flight = $bid->flight;
        $allowed = $this->userSvc->getAllowableSubfleets($request->user())->pluck('id')->all();
        $flightAllowed = $flight->subfleets->pluck('id')->all();

        $operationId = $this->operationIdentity->id($bid);

        $candidates = Aircraft::query()
            ->with(['subfleet:id,name,type'])
            ->withCount(['bid', 'simbriefs' => fn ($query) => $query->whereNull('pirep_id')])
            ->orderBy('icao')->orderBy('registration')->get();

        $available = [];
        $unavailable = [];

        foreach ($candidates as $plane) {
            $reasons = [];
            $checks = [];

            $pilotAllowed = in_array($plane->subfleet_id, $allowed, true);
            $checks[] = $this->check('pilot_qualification', $pilotAllowed, 'Qualification pilote');
            if (!$pilotAllowed) $reasons[] = $this->reason('RANK_NOT_ALLOWED', 'Votre qualification ne permet pas cette sous-flotte.');

            $lineAllowed = !$flightAllowed || in_array($plane->subfleet_id, $flightAllowed, true);
            $checks[] = $this->check('flight_subfleet', $lineAllowed, 'Compatible avec la ligne');
            if (!$lineAllowed) $reasons[] = $this->reason('FLIGHT_SUBFLEET_NOT_ALLOWED', 'Cette sous-flotte n’est pas autorisée sur ce vol.');

            $active = $plane->status === AircraftStatus::ACTIVE;
            $checks[] = $this->check('active', $active, 'Appareil actif');
            if (!$active) $reasons[] = $this->reason('INACTIVE', 'Cet appareil est hors service ou inactif.');

            $parked = $plane->state === AircraftState::PARKED;
            $checks[] = $this->check('parked', $parked, 'Appareil au parking');
            if (!$parked) $reasons[] = $this->reason('NOT_PARKED', 'Cet appareil n’est pas actuellement au parking.');

            if (setting('pireps.only_aircraft_at_dpt_airport')) {
                $atDeparture = strtoupper((string) $plane->airport_id) === strtoupper((string) $flight->dpt_airport_id);
                $checks[] = $this->check('departure_airport', $atDeparture, 'Position '.$flight->dpt_airport_id);
                if (!$atDeparture) $reasons[] = $this->reason('WRONG_AIRPORT', 'Appareil actuellement à '.($plane->airport_id ?: 'une position inconnue').'.');
            }

            if (setting('bids.block_aircraft')) {
                $free = (int) $plane->bid_count === 0 || (string) $bid->aircraft_id === (string) $plane->id;
                $checks[] = $this->check('no_other_bid', $free, 'Aucune autre réservation active');
                if (!$free) $reasons[] = $this->reason('ALREADY_BID', 'Cet appareil est déjà réservé sur une autre opération.');
            }

            if (setting('simbrief.block_aircraft')) {
                $ownOfp = $this->operationOfp($bid);
                $freeOfp = (int) $plane->simbriefs_count === 0 || (string) $ownOfp?->aircraft_id === (string) $plane->id;
                $checks[] = $this->check('no_active_ofp', $freeOfp, 'Aucun OFP actif concurrent');
                if (!$freeOfp) $reasons[] = $this->reason('ACTIVE_OFP', 'Un OFP actif utilise déjà cet appareil.');
            }

            $profile = count($reasons) === 0
                ? $this->demandProfile->profile($plane, $flight, $operationId)
                : null;

            $dto = [
                'id' => $plane->id,
                'registration' => $plane->registration,
                'name' => $plane->name,
                'icao' => $plane->icao,
                'subfleet' => $plane->subfleet?->name,
                'type_key' => $this->demandProfile->typeKey($plane),
                'type_label' => $this->demandProfile->typeLabel($plane),
                'airport' => $plane->airport_id,
                'eligible' => count($reasons) === 0,
                'checks' => $checks,
                'reasons' => $reasons,
                'capacity' => $profile['capacity'] ?? null,
                'database_capacity' => $profile['database_capacity'] ?? null,
                'capacity_source' => $profile['capacity_source'] ?? null,
                'cabin_profile' => $profile['cabin_profile'] ?? null,
                'passengers' => $profile['passengers'] ?? null,
                'load_factor_percent' => $profile['load_factor_percent'] ?? null,
                'pricing_band' => $profile['band'] ?? null,
                'fare_percent' => $profile['fare_percent'] ?? null,
                'load_range' => $profile['load_range'] ?? null,
            ];

            if (count($reasons) === 0) {
                $available[] = $dto;
            } else {
                $unavailable[] = $dto;
            }
        }

        $types = collect($available)
            ->groupBy('type_key')
            ->map(function ($planes, $typeKey) {
                $primary = $planes->sortBy('registration')->first();

                return [
                    'type_key' => $typeKey,
                    'type_label' => $primary['type_label'],
                    'available_count' => $planes->count(),
                    'suggested_aircraft_id' => $primary['id'],
                    'capacity' => $primary['capacity'],
                    'database_capacity' => $primary['database_capacity'] ?? null,
                    'capacity_source' => $primary['capacity_source'] ?? null,
                    'cabin_profile' => $primary['cabin_profile'] ?? null,
                    'passengers' => $primary['passengers'],
                    'load_factor_percent' => $primary['load_factor_percent'],
                    'pricing_band' => $primary['pricing_band'],
                    'fare_percent' => $primary['fare_percent'],
                    'load_range' => $primary['load_range'],
                ];
            })
            ->sortBy('type_label')
            ->values();

        return response()->json(['data' => [
            'operation_id' => $operationId,
            'bid_id' => $bid->id,
            'types' => $types,
            'available' => $available,
            'unavailable' => $unavailable,
            'reason_codes' => [
                'RANK_NOT_ALLOWED', 'FLIGHT_SUBFLEET_NOT_ALLOWED', 'WRONG_AIRPORT',
                'NOT_PARKED', 'INACTIVE', 'ALREADY_BID', 'ACTIVE_OFP',
            ],
        ]]);
    }

    public function selectAircraft(string $bidId, Request $request)
    {
        $bid = $this->bid($bidId, $request);
        abort_if($this->operationPirep($bid), 409, 'L’appareil ne peut plus être modifié après le pré-dépôt du PIREP.');

        $data = $request->validate([
            'aircraft_id' => 'nullable|integer|required_without:aircraft_type',
            'aircraft_type' => 'nullable|string|max:32|required_without:aircraft_id',
        ]);

        $eligibility = $this->aircraft($bidId, $request)->getData(true)['data'] ?? [];
        $available = collect($eligibility['available'] ?? []);
        $selected = !empty($data['aircraft_id'])
            ? $available->first(fn ($aircraft) => (string) ($aircraft['id'] ?? '') === (string) $data['aircraft_id'])
            : $available
                ->where('type_key', strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', (string) $data['aircraft_type'])))
                ->sortBy('registration')
                ->first();
        abort_if(!$selected, 409, 'Ce type d’appareil n’est pas autorisé ou aucun exemplaire n’est disponible pour cette opération.');

        $data['aircraft_id'] = (int) $selected['id'];

        // Changing aircraft invalidates any active OFP linked to the previous
        // aircraft. Do not silently switch once planning has started.
        $existingOfp = $this->operationOfp($bid);
        abort_if($existingOfp && (string) $bid->aircraft_id !== (string) $data['aircraft_id'], 409, 'Un OFP existe déjà pour cette opération. Supprimez-le avant de changer d’appareil.');

        $bid->aircraft_id = (int) $data['aircraft_id'];
        $bid->save();

        $bid = $this->bid($bidId, $request);
        $ofp = $this->operationOfp($bid);
        $pirep = $this->operationPirep($bid);
        $checks = $this->readinessChecks($bid, $ofp, $pirep);
        $serverReady = collect($checks)->every(fn ($check) => $check['ready']);

        return response()->json(['data' => [
            'operation_id' => $this->operationIdentity->id($bid),
            'bid_id' => $bid->id,
            'aircraft' => $this->operationDto($bid)['aircraft'],
            'status' => $this->dispatchStatus($bid, $pirep, $serverReady),
            'server_checks' => collect($checks)->mapWithKeys(fn ($check) => [strtolower($check['code']) => $check['ready']]),
            'checks' => $checks,
        ]]);
    }

    public function briefing(string $bidId, Request $request)
    {
        $bid = $this->bid($bidId, $request);
        $flight = $bid->flight;
        $ofp = $this->operationOfp($bid);

        return response()->json(['data' => [
            'operation' => $this->operationDto($bid),
            'route' => $flight->route,
            'level' => $flight->level,
            'alternate' => $flight->alt_airport_id,
            'ofp' => $this->ofpDto($ofp),
            'provenance' => [
                'schedule' => 'phpvms',
                'dispatch' => 'promethee',
                'ofp' => $ofp ? 'simbrief' : null,
            ],
        ]]);
    }

    public function operationDispatch(string $bidId, Request $request)
    {
        $bid = $this->bid($bidId, $request);
        $ofp = $this->operationOfp($bid);
        $pirep = $this->operationPirep($bid);
        $checks = $this->readinessChecks($bid, $ofp, $pirep);
        $serverReady = collect($checks)->every(fn ($check) => $check['ready']);
        $status = $this->dispatchStatus($bid, $pirep, $serverReady);

        return response()->json(['data' => [
            'contract_version' => '1.0',
            'operation_id' => $this->operationIdentity->id($bid),
            'operation' => $this->operationDto($bid),
            'ofp' => $this->ofpDto($ofp),
            'status' => $status,
            'ready' => $status === 'READY',
            'can_start' => $status === 'READY',
            'server_checks' => collect($checks)->mapWithKeys(fn ($check) => [strtolower($check['code']) => $check['ready']]),
            'checks' => $checks,
            'actions' => collect($checks)->where('ready', false)->pluck('action')->filter()->values(),
            'pirep' => $this->pirepDto($pirep),
            'client_checks_required' => $status === 'READY'
                ? ['SIMULATOR_CONNECTED', 'AIRCRAFT_MATCH', 'DEPARTURE_MATCH']
                : [],
            'terminal' => in_array($status, ['COMPLETED', 'CANCELLED'], true),
        ]]);
    }

    public function readiness(string $bidId, Request $request)
    {
        $bid = $this->bid($bidId, $request);
        $ofp = $this->operationOfp($bid);
        $pirep = $this->operationPirep($bid);
        $checks = $this->readinessChecks($bid, $ofp, $pirep);

        return response()->json(['data' => [
            'operation_id' => $this->operationIdentity->id($bid),
            'bid_id' => $bid->id,
            'ready' => collect($checks)->every(fn ($check) => $check['ready']),
            'status' => $this->dispatchStatus($bid, $pirep, collect($checks)->every(fn ($check) => $check['ready'])),
            'checks' => $checks,
            'server_checks_complete' => true,
            'pirep' => $this->pirepDto($pirep),
            'client_checks_required' => ['SIMULATOR_CONNECTED', 'AIRCRAFT_MATCH', 'DEPARTURE_MATCH'],
        ]]);
    }

    public function debrief(string $reference, Request $request)
    {
        $pirep = $this->operationIdentity->resolvePirep($reference, (int) $request->user()->id);
        abort_if(!$pirep, 404, 'Aucun PIREP Hermès trouvé pour cette opération.');

        $samples = DB::table('promethee_telemetry')
            ->where('pirep_id', $pirep->id)
            ->orderBy('recorded_at')
            ->get()
            ->map(function ($sample) {
                $payload = json_decode($sample->payload, true) ?: [];
                $payload['recorded_at'] = (string) $sample->recorded_at;
                return $payload;
            })->all();

        $debrief = $this->safetyAnalyzer->debrief($pirep->landing_rate, $samples);
        $first = $samples[0] ?? null;
        $last = $samples ? $samples[array_key_last($samples)] : null;
        $blockMinutes = ($pirep->block_off_time && $pirep->block_on_time)
            ? $pirep->block_off_time->diffInMinutes($pirep->block_on_time)
            : null;

        return response()->json(['data' => [
            'contract_version' => '1.0',
            'operation_id' => str_starts_with($reference, 'op_') ? $reference : 'op_'.$reference,
            'pirep' => $this->pirepDto($pirep),
            'summary' => [
                'flight' => $pirep->ident,
                'departure' => $pirep->dpt_airport_id,
                'arrival' => $pirep->arr_airport_id,
                'aircraft_id' => $pirep->aircraft_id,
                'block_off_at' => optional($pirep->block_off_time)?->toIso8601String(),
                'block_on_at' => optional($pirep->block_on_time)?->toIso8601String(),
                'block_minutes' => $blockMinutes,
                'flight_minutes' => $pirep->flight_time,
                'fuel_used' => $this->scalarValue($pirep->fuel_used),
                'landing_rate_fpm' => $pirep->landing_rate,
                'telemetry_first_at' => $first['recorded_at'] ?? null,
                'telemetry_last_at' => $last['recorded_at'] ?? null,
                'telemetry_samples' => count($samples),
            ],
            'debrief' => $debrief,
            'provenance' => [
                'flight_record' => 'phpvms_pirep',
                'telemetry' => 'hermes',
                'analysis' => 'promethee_safety_analyzer_v'.SafetyAnalyzer::VERSION,
            ],
        ]]);
    }

    public function fleetState(string $reference, Request $request)
    {
        $pirep = $this->operationIdentity->resolvePirep($reference, (int) $request->user()->id);
        abort_if(!$pirep, 404, 'Aucun PIREP Hermès trouvé pour cette opération.');
        $pirep->loadMissing('aircraft');

        return response()->json(['data' => [
            'operation_id' => str_starts_with($reference, 'op_') ? $reference : 'op_'.$reference,
            'pirep_id' => $pirep->id,
            'aircraft' => $this->aircraftState->snapshot($pirep),
        ]]);
    }

    public function reconcileFleet(string $reference, Request $request)
    {
        $pirep = $this->operationIdentity->resolvePirep($reference, (int) $request->user()->id);
        abort_if(!$pirep, 404, 'Aucun PIREP Hermès trouvé pour cette opération.');
        $pirep->loadMissing('aircraft');

        return response()->json(['data' => [
            'operation_id' => str_starts_with($reference, 'op_') ? $reference : 'op_'.$reference,
            'pirep_id' => $pirep->id,
            'reconciliation' => $this->aircraftState->reconcile($pirep),
        ]]);
    }

    public function pirep(string $reference, Request $request)
    {
        $bid = $this->bid($reference, $request);
        $pirep = $this->operationPirep($bid);

        return response()->json(['data' => [
            'operation_id' => $this->operationIdentity->id($bid),
            'pirep' => $this->pirepDto($pirep),
            'pirep_id' => $pirep?->id,
        ]]);
    }

    public function prefilePirep(string $reference, Request $request)
    {
        $bid = $this->bid($reference, $request);
        $existing = $this->operationPirep($bid);
        if ($existing) {
            return response()->json(['data' => [
                'operation_id' => $this->operationIdentity->id($bid),
                'pirep' => $this->pirepDto($existing),
                'pirep_id' => $existing->id,
                'already_prefiled' => true,
            ]]);
        }

        abort_if(!$bid->aircraft_id, 409, 'Sélectionnez un appareil avant de préparer le PIREP.');
        $ofp = $this->operationOfp($bid);
        $plan = $request->validate([
            'route' => 'nullable|string|max:4000',
            'level' => 'nullable|integer|min:10|max:600',
            'block_fuel' => 'nullable|numeric|min:0',
            'simbrief_source' => 'nullable|string|in:simbrief_account,simbrief_api,simbrief',
        ]);
        abort_if(!$ofp && empty($plan['simbrief_source']), 409, 'Préparez ou importez l’OFP SimBrief avant le PIREP.');

        $flight = $bid->flight;
        $operationId = $this->operationIdentity->id($bid);
        $attrs = [
            'flight_id' => $flight->id,
            'simbrief_id' => $ofp?->id,
            'airline_id' => $flight->airline_id,
            'aircraft_id' => $bid->aircraft_id,
            'flight_number' => $flight->flight_number,
            'route_code' => $flight->route_code,
            'route_leg' => $flight->route_leg,
            'dpt_airport_id' => $flight->dpt_airport_id,
            'arr_airport_id' => $flight->arr_airport_id,
            'alt_airport_id' => $flight->alt_airport_id,
            'level' => $plan['level'] ?? $flight->level,
            'route' => $plan['route'] ?? $flight->route,
            'block_fuel' => $plan['block_fuel'] ?? null,
            'source' => PirepSource::ACARS,
            'source_name' => 'Hermes ACARS ['.$operationId.']',
        ];

        $pirep = $this->pirepSvc->prefile($request->user(), $attrs, [], []);

        // phpVMS may return an existing duplicate prefile. Reassert the
        // Prométhée correlation marker so a retry still resolves to this
        // operation deterministically instead of relying on timestamps.
        if ($pirep->source_name !== $attrs['source_name']) {
            $pirep->source_name = $attrs['source_name'];
            $pirep->save();
        }
        $pirep->refresh();

        return response()->json(['data' => [
            'operation_id' => $operationId,
            'pirep' => $this->pirepDto($pirep),
            'pirep_id' => $pirep->id,
            'simbrief_id' => $ofp?->id,
            'correlation' => [
                'operation' => $operationId,
                'bid' => $bid->id,
                'flight' => $flight->id,
                'aircraft' => $bid->aircraft_id,
                'simbrief' => $ofp?->id,
                'pirep' => $pirep->id,
            ],
        ]], 201);
    }

    private function bid(string $reference, Request $request): Bid
    {
        $bid = $this->operationIdentity->resolveBid($reference, (int) $request->user()->id);
        abort_if(!$bid, 404, 'Opération introuvable.');

        return Bid::with(['flight.airline', 'flight.subfleets', 'aircraft.subfleet'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($bid->id);
    }

    /**
     * phpVMS stores SimBrief against a flight, not a bid. Restrict the lookup
     * to the selected aircraft and to OFPs generated after this reservation
     * was created so an older IT749 cannot leak into a newer IT749 operation.
     */
    private function operationOfp(Bid $bid): ?SimBrief
    {
        if (!$bid->aircraft_id) return null;

        // Once a PIREP is prefiled phpVMS moves the exact SimBrief row from
        // "active OFP" to that PIREP. Resolve that exact row first so Dispatch
        // never loses the OFP just because the flight moved to the next stage.
        $pirep = $this->operationPirep($bid);
        if ($pirep) {
            $attached = SimBrief::query()
                ->where('user_id', $bid->user_id)
                ->where('flight_id', $bid->flight_id)
                ->where('aircraft_id', $bid->aircraft_id)
                ->where('pirep_id', $pirep->id)
                ->latest('updated_at')
                ->first();
            if ($attached) return $attached;
        }

        return SimBrief::query()
            ->where('user_id', $bid->user_id)
            ->where('flight_id', $bid->flight_id)
            ->where('aircraft_id', $bid->aircraft_id)
            ->whereNull('pirep_id')
            ->when($bid->created_at, fn ($query) => $query->where('updated_at', '>=', $bid->created_at))
            ->latest('updated_at')
            ->first();
    }

    private function operationDto(Bid $bid, string $simulator = 'msfs2020'): array
    {
        $flight = $bid->flight;
        $aircraft = $bid->aircraft;
        $subfleet = $aircraft?->subfleet ?? $flight?->subfleets->first();
        $name = (string) ($subfleet?->name ?? $aircraft?->name ?? '');
        $key = Str::of($name)->lower()->replace(['_', '-'], ' ')->squish()->toString();
        $fallback = config('acars.substitutions.'.$key.'.'.$simulator, []);
        $simbriefType = $fallback['simbrief_type'] ?? ($aircraft?->simbrief_type ?: ($subfleet?->simbrief_type ?: $aircraft?->icao));
        $airline = Str::lower((string) ($flight?->airline?->name ?? ''));
        $loadFactor = str_contains($airline, 'charter') ? config('acars.load_factors.air_charter_international')
            : (str_contains($airline, 'cargo') ? config('acars.load_factors.inter_cargo_service') : config('acars.load_factors.air_inter'));
        $ofp = $this->operationOfp($bid);
        $pirep = $this->operationPirep($bid);
        $ofpAvailable = $ofp !== null || ($pirep !== null && filled($pirep->route));
        return [
            'id' => $this->operationIdentity->id($bid),
            'operation_id' => $this->operationIdentity->id($bid),
            'bid_id' => $bid->id,
            'status' => $pirep ? 'prefiled' : ($ofpAvailable ? 'planned' : 'reserved'),
            'pirep_id' => $pirep?->id,
            'created_at' => optional($bid->created_at)?->toIso8601String(),
            'flight' => [
                'id' => $flight?->id,
                'ident' => $flight?->ident,
                'number' => $flight?->flight_number,
                'flight_number' => $flight?->flight_number,
                'departure' => $flight?->dpt_airport_id,
                'arrival' => $flight?->arr_airport_id,
                'alternate' => $flight?->alt_airport_id,
                'route' => $flight?->route,
                'level' => $flight?->level,
                'pricing_band' => $flight ? $this->demandProfile->bandForFlight($flight) : null,
            ],
            'aircraft' => $aircraft ? array_merge([
                'id' => $aircraft->id,
                'registration' => $aircraft->registration,
                'name' => $aircraft->name,
                'icao' => $aircraft->icao,
                'subfleet' => $aircraft->subfleet?->name,
                'type_key' => $this->demandProfile->typeKey($aircraft),
                'type_label' => $this->demandProfile->typeLabel($aircraft),
                'airport' => $aircraft->airport_id,
            ], $flight ? $this->demandProfile->profile($aircraft, $flight, $this->operationIdentity->id($bid)) : []) : null,
            'simbrief' => [
                'type' => $simbriefType,
                'addon' => $fallback['addon'] ?? null,
                'ofp_id' => $ofp?->id,
                'available' => $ofpAvailable,
                // Hermès only needs to know whether company generation can be
                // offered. The actual API key never leaves Prométhée.
                'company_api_available' => app(\Modules\Promethee\Services\SimBriefCompanyKeyService::class)->configured(),
            ],
            'operating_rules' => [
                'passenger_weight_kg' => config('acars.passenger_weight_kg'),
                'checked_baggage_kg' => config('acars.checked_baggage_kg'),
                'load_factor_percent' => $aircraft && $flight
                    ? $this->demandProfile->profile($aircraft, $flight, $this->operationIdentity->id($bid))['load_factor_percent']
                    : $loadFactor,
                'passengers' => $aircraft && $flight
                    ? $this->demandProfile->profile($aircraft, $flight, $this->operationIdentity->id($bid))['passengers']
                    : null,
                'capacity' => $aircraft && $flight
                    ? $this->demandProfile->profile($aircraft, $flight, $this->operationIdentity->id($bid))['capacity']
                    : null,
                'pricing_band' => $flight ? $this->demandProfile->bandForFlight($flight) : null,
                'fuel_policy' => 'trip + 5% + alternate + expected holding + 45 minutes reserve',
            ],
        ];
    }

    private function operationPirep(Bid $bid): ?Pirep
    {
        $operationId = $this->operationIdentity->id($bid);
        return Pirep::query()
            ->where('user_id', $bid->user_id)
            ->where('flight_id', $bid->flight_id)
            ->where('aircraft_id', $bid->aircraft_id)
            ->where('source_name', 'Hermes ACARS ['.$operationId.']')
            ->latest('created_at')
            ->first();
    }

    private function readinessChecks(Bid $bid, ?SimBrief $ofp, ?Pirep $pirep = null): array
    {
        $aircraft = $bid->aircraft;
        $flight = $bid->flight;
        $aircraftReady = $aircraft !== null
            && $aircraft->status === AircraftStatus::ACTIVE
            && $aircraft->state === AircraftState::PARKED
            && (!setting('pireps.only_aircraft_at_dpt_airport')
                || strtoupper((string) $aircraft->airport_id) === strtoupper((string) $flight?->dpt_airport_id));
        $ofpReady = $ofp !== null;
        $pirepReady = $pirep !== null
            && (int) $pirep->state === PirepState::IN_PROGRESS
            && $pirep->status !== PirepStatus::CANCELLED;

        return [
            ['code' => 'OPERATION', 'ready' => true, 'label' => 'Réservation valide', 'action' => null],
            ['code' => 'AIRCRAFT', 'ready' => $aircraftReady, 'label' => $aircraftReady ? 'Appareil autorisé et disponible' : 'Appareil non prêt', 'action' => $aircraftReady ? null : 'Sélectionnez un appareil actif, au parking et à l’aéroport de départ lorsque la règle de position est active.'],
            ['code' => 'OFP', 'ready' => $ofpReady, 'label' => $ofpReady ? 'OFP SimBrief lié à cette opération' : 'OFP à préparer', 'action' => $ofpReady ? null : 'Générez ou importez l’OFP SimBrief pour cette opération.'],
            ['code' => 'PIREP', 'ready' => $pirepReady, 'label' => $pirepReady ? 'PIREP pré-déposé et actif' : 'PIREP à préparer', 'action' => $pirepReady ? null : 'Pré-déposez le PIREP depuis Hermès.'],
        ];
    }

    private function dispatchStatus(Bid $bid, ?Pirep $pirep, bool $serverReady): string
    {
        if ($pirep) {
            if ((int) $pirep->state === PirepState::CANCELLED || $pirep->status === PirepStatus::CANCELLED) {
                return 'CANCELLED';
            }

            if ($pirep->submitted_at !== null
                || in_array((int) $pirep->state, [PirepState::PENDING, PirepState::ACCEPTED, PirepState::REJECTED], true)
                || $pirep->status === PirepStatus::ARRIVED) {
                return 'COMPLETED';
            }

            if ($this->hasOperationTelemetry($pirep)) {
                return 'IN_PROGRESS';
            }
        }

        return $serverReady ? 'READY' : 'PREPARATION_REQUIRED';
    }

    private function hasOperationTelemetry(Pirep $pirep): bool
    {
        return DB::table('promethee_telemetry')
            ->where('pirep_id', $pirep->id)
            ->exists();
    }

    private function ofpDto(?SimBrief $ofp): array
    {
        return $ofp ? [
            'id' => $ofp->id,
            'available' => true,
            'aircraft_id' => $ofp->aircraft_id,
            'updated_at' => optional($ofp->updated_at)?->toIso8601String(),
        ] : ['id' => null, 'available' => false, 'aircraft_id' => null, 'updated_at' => null];
    }

    private function pirepDto(?Pirep $pirep): array
    {
        return $pirep ? [
            'id' => $pirep->id,
            'available' => true,
            'state' => $pirep->state,
            'status' => $pirep->status,
            'created_at' => optional($pirep->created_at)?->toIso8601String(),
            'submitted_at' => optional($pirep->submitted_at)?->toIso8601String(),
        ] : [
            'id' => null,
            'available' => false,
            'state' => null,
            'status' => null,
            'created_at' => null,
            'submitted_at' => null,
        ];
    }

    private function scalarValue(mixed $value): mixed
    {
        if (is_scalar($value) || $value === null) return $value;
        if (is_object($value)) {
            foreach (['value', 'local', 'raw'] as $property) {
                if (isset($value->{$property}) && is_scalar($value->{$property})) return $value->{$property};
            }
            if (method_exists($value, '__toString')) return (string) $value;
        }
        return null;
    }

    private function reason(string $code, string $message): array
    {
        return ['code' => $code, 'message' => $message];
    }

    private function check(string $code, bool $passed, string $label): array
    {
        return ['code' => $code, 'passed' => $passed, 'label' => $label];
    }
}
