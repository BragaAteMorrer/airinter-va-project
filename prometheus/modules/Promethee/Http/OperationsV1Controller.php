<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\Aircraft;
use App\Models\Bid;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\SimBrief;
use App\Models\Pirep;
use App\Models\Enums\PirepSource;
use App\Services\PirepService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Promethee\Services\OperationIdentityService;

/**
 * Stable Air Inter operations facade.
 *
 * Hermès consumes this contract instead of depending on phpVMS resources.
 * Keep DTO names stable and additive; phpVMS remains an implementation detail.
 */
class OperationsV1Controller extends Controller
{
    private const SIMULATORS = ['fs2004', 'fsx', 'msfs2020', 'msfs2024', 'xplane'];

    public function __construct(
        private readonly UserService $userSvc,
        private readonly PirepService $pirepSvc,
        private readonly OperationIdentityService $operationIdentity
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

        $candidates = Aircraft::query()
            ->with(['subfleet:id,name'])
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

            $dto = [
                'id' => $plane->id,
                'registration' => $plane->registration,
                'name' => $plane->name,
                'icao' => $plane->icao,
                'subfleet' => $plane->subfleet?->name,
                'airport' => $plane->airport_id,
                'eligible' => count($reasons) === 0,
                'checks' => $checks,
                'reasons' => $reasons,
            ];

            if (count($reasons) === 0) {
                $available[] = $dto;
            } else {
                $unavailable[] = $dto;
            }
        }

        return response()->json(['data' => [
            'operation_id' => $this->operationIdentity->id($bid),
            'bid_id' => $bid->id,
            'available' => $available,
            'unavailable' => $unavailable,
            'reason_codes' => [
                'RANK_NOT_ALLOWED', 'FLIGHT_SUBFLEET_NOT_ALLOWED', 'WRONG_AIRPORT',
                'NOT_PARKED', 'INACTIVE', 'ALREADY_BID', 'ACTIVE_OFP',
            ],
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

    public function dispatch(string $bidId, Request $request)
    {
        $bid = $this->bid($bidId, $request);
        $ofp = $this->operationOfp($bid);
        $pirep = $this->operationPirep($bid);
        $checks = $this->readinessChecks($bid, $ofp, $pirep);
        $serverReady = collect($checks)->every(fn ($check) => $check['ready']);

        return response()->json(['data' => [
            'contract_version' => '1.0',
            'operation' => $this->operationDto($bid),
            'ofp' => $this->ofpDto($ofp),
            'status' => $serverReady ? 'READY' : 'PREPARATION_REQUIRED',
            'ready' => $serverReady,
            'server_checks' => collect($checks)->mapWithKeys(fn ($check) => [strtolower($check['code']) => $check['ready']]),
            'checks' => $checks,
            'actions' => collect($checks)->where('ready', false)->pluck('action')->filter()->values(),
            'pirep' => $this->pirepDto($pirep),
            'client_checks_required' => ['SIMULATOR_CONNECTED', 'AIRCRAFT_MATCH', 'DEPARTURE_MATCH'],
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
            'checks' => $checks,
            'server_checks_complete' => true,
            'pirep' => $this->pirepDto($pirep),
            'client_checks_required' => ['SIMULATOR_CONNECTED', 'AIRCRAFT_MATCH', 'DEPARTURE_MATCH'],
        ]]);
    }

    public function pirep(string $reference, Request $request)
    {
        $bid = $this->bid($reference, $request);
        return response()->json(['data' => $this->pirepDto($this->operationPirep($bid))]);
    }

    public function prefilePirep(string $reference, Request $request)
    {
        $bid = $this->bid($reference, $request);
        $existing = $this->operationPirep($bid);
        if ($existing) {
            return response()->json(['data' => $this->pirepDto($existing)]);
        }

        abort_if(!$bid->aircraft_id, 409, 'Sélectionnez un appareil avant de préparer le PIREP.');
        $ofp = $this->operationOfp($bid);
        abort_if(!$ofp, 409, 'Préparez l’OFP SimBrief avant le PIREP.');

        $flight = $bid->flight;
        $operationId = $this->operationIdentity->id($bid);
        $attrs = [
            'flight_id' => $flight->id,
            'airline_id' => $flight->airline_id,
            'aircraft_id' => $bid->aircraft_id,
            'flight_number' => $flight->flight_number,
            'route_code' => $flight->route_code,
            'route_leg' => $flight->route_leg,
            'dpt_airport_id' => $flight->dpt_airport_id,
            'arr_airport_id' => $flight->arr_airport_id,
            'alt_airport_id' => $flight->alt_airport_id,
            'level' => $flight->level,
            'route' => $flight->route,
            'source' => PirepSource::ACARS,
            'source_name' => 'Hermes ACARS ['.$operationId.']',
        ];

        $pirep = $this->pirepSvc->prefile($request->user(), $attrs, [], []);
        return response()->json(['data' => $this->pirepDto($pirep)], 201);
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
        return [
            'id' => $this->operationIdentity->id($bid),
            'operation_id' => $this->operationIdentity->id($bid),
            'bid_id' => $bid->id,
            'status' => 'reserved',
            'pirep_id' => null,
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
            ],
            'aircraft' => $aircraft ? [
                'id' => $aircraft->id,
                'registration' => $aircraft->registration,
                'name' => $aircraft->name,
                'icao' => $aircraft->icao,
                'subfleet' => $aircraft->subfleet?->name,
                'airport' => $aircraft->airport_id,
            ] : null,
            'simbrief' => [
                'type' => $simbriefType,
                'addon' => $fallback['addon'] ?? null,
                'ofp_id' => $ofp?->id,
                'available' => $ofp !== null,
            ],
            'operating_rules' => [
                'passenger_weight_kg' => config('acars.passenger_weight_kg'),
                'checked_baggage_kg' => config('acars.checked_baggage_kg'),
                'load_factor_percent' => $loadFactor,
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
        return [
            ['code' => 'OPERATION', 'ready' => true, 'label' => 'Réservation valide', 'action' => null],
            ['code' => 'AIRCRAFT', 'ready' => $bid->aircraft_id !== null, 'label' => $bid->aircraft_id ? 'Appareil affecté' : 'Appareil à sélectionner', 'action' => $bid->aircraft_id ? null : 'Sélectionnez un appareil autorisé.'],
            ['code' => 'OFP', 'ready' => $ofp !== null, 'label' => $ofp ? 'OFP SimBrief lié à cette opération' : 'OFP à préparer', 'action' => $ofp ? null : 'Générez ou importez l’OFP SimBrief pour cette réservation.'],
            ['code' => 'PIREP', 'ready' => $pirep !== null, 'label' => $pirep ? 'PIREP pré-déposé' : 'PIREP à préparer', 'action' => $pirep ? null : 'Pré-déposez le PIREP depuis Hermès.'],
        ];
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

    private function reason(string $code, string $message): array
    {
        return ['code' => $code, 'message' => $message];
    }

    private function check(string $code, bool $passed, string $label): array
    {
        return ['code' => $code, 'passed' => $passed, 'label' => $label];
    }
}
