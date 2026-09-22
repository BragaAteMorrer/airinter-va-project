<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\Aircraft;
use App\Models\Bid;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\SimBrief;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Stable Air Inter operations facade.
 *
 * Hermès consumes this contract instead of depending on phpVMS resources.
 * Keep DTO names stable and additive; phpVMS remains an implementation detail.
 */
class OperationsV1Controller extends Controller
{
    private const SIMULATORS = ['fs2004', 'fsx', 'msfs2020', 'msfs2024', 'xplane'];

    public function __construct(private readonly UserService $userSvc) {}

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

        // A reservation is only an intention to fly. PIREPs are separate
        // operational records and are never removed by cancelling a bid.
        $bid->delete();

        return response()->json(['data' => [
            'cancelled' => true,
            'operation_id' => $bidId,
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

            (count($reasons) === 0 ? $available : $unavailable)[] = $dto;
        }

        return response()->json(['data' => [
            'operation_id' => $bid->id,
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
        $checks = $this->readinessChecks($bid, $ofp);

        return response()->json(['data' => [
            'contract_version' => '1.0',
            'operation' => $this->operationDto($bid),
            'ofp' => $this->ofpDto($ofp),
            'ready' => collect($checks)->every(fn ($check) => $check['ready']),
            'checks' => $checks,
            'actions' => collect($checks)->where('ready', false)->pluck('action')->filter()->values(),
            'client_checks_required' => ['SIMULATOR_CONNECTED', 'AIRCRAFT_MATCH', 'DEPARTURE_MATCH', 'PIREP_PREFILED'],
        ]]);
    }

    public function readiness(string $bidId, Request $request)
    {
        $bid = $this->bid($bidId, $request);
        $ofp = $this->operationOfp($bid);
        $checks = $this->readinessChecks($bid, $ofp);

        return response()->json(['data' => [
            'operation_id' => $bid->id,
            'ready' => collect($checks)->every(fn ($check) => $check['ready']),
            'checks' => $checks,
            'server_checks_complete' => true,
            'client_checks_required' => ['SIMULATOR_CONNECTED', 'AIRCRAFT_MATCH', 'DEPARTURE_MATCH', 'PIREP_PREFILED'],
        ]]);
    }

    private function bid(string $bidId, Request $request): Bid
    {
        return Bid::with(['flight.airline', 'flight.subfleets', 'aircraft.subfleet'])
            ->where('user_id', $request->user()->id)->findOrFail($bidId);
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
            'id' => $bid->id,
            'bid_id' => $bid->id,
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

    private function readinessChecks(Bid $bid, ?SimBrief $ofp): array
    {
        return [
            ['code' => 'OPERATION', 'ready' => true, 'label' => 'Réservation valide', 'action' => null],
            ['code' => 'AIRCRAFT', 'ready' => $bid->aircraft_id !== null, 'label' => $bid->aircraft_id ? 'Appareil affecté' : 'Appareil à sélectionner', 'action' => $bid->aircraft_id ? null : 'Sélectionnez un appareil autorisé.'],
            ['code' => 'OFP', 'ready' => $ofp !== null, 'label' => $ofp ? 'OFP SimBrief lié à cette opération' : 'OFP à préparer', 'action' => $ofp ? null : 'Générez ou importez l’OFP SimBrief pour cette réservation.'],
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

    private function reason(string $code, string $message): array
    {
        return ['code' => $code, 'message' => $message];
    }

    private function check(string $code, bool $passed, string $label): array
    {
        return ['code' => $code, 'passed' => $passed, 'label' => $label];
    }
}
