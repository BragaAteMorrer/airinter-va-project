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

/**
 * Stable Air Inter operations facade.
 *
 * Hermès consumes this contract instead of depending on phpVMS resources.
 * Keep DTO names stable and additive; phpVMS remains an implementation detail.
 */
class OperationsV1Controller extends Controller
{
    public function __construct(private readonly UserService $userSvc) {}

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
        return response()->json(['data' => $this->operationDto($bid)]);
    }

    public function aircraft(string $bidId, Request $request)
    {
        $bid = $this->bid($bidId, $request);
        $flight = $bid->flight;
        $allowed = $this->userSvc->getAllowableSubfleets($request->user())->pluck('id')->all();
        $flightAllowed = $flight->subfleets->pluck('id')->all();

        // Evaluate candidates instead of filtering them away so Hermès can
        // explain exactly why an aircraft cannot be dispatched.
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
                $freeOfp = (int) $plane->simbriefs_count === 0;
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
        $ofp = SimBrief::where('user_id', $request->user()->id)
            ->where('flight_id', $bid->flight_id)->latest('updated_at')->first();

        return response()->json(['data' => [
            'operation' => $this->operationDto($bid),
            'route' => $flight->route,
            'level' => $flight->level,
            'alternate' => $flight->alt_airport_id,
            'ofp' => $ofp ? [
                'id' => $ofp->id,
                'available' => true,
                'updated_at' => optional($ofp->updated_at)?->toIso8601String(),
            ] : ['id' => null, 'available' => false, 'updated_at' => null],
            'provenance' => [
                'schedule' => 'phpvms',
                'dispatch' => 'promethee',
                'ofp' => $ofp ? 'simbrief' : null,
            ],
        ]]);
    }

    public function readiness(string $bidId, Request $request)
    {
        $bid = $this->bid($bidId, $request);
        $ofp = SimBrief::where('user_id', $request->user()->id)
            ->where('flight_id', $bid->flight_id)->latest('updated_at')->first();

        $checks = [
            ['code' => 'OPERATION', 'ready' => true, 'label' => 'Réservation valide', 'action' => null],
            ['code' => 'AIRCRAFT', 'ready' => $bid->aircraft_id !== null, 'label' => $bid->aircraft_id ? 'Appareil affecté' : 'Appareil à sélectionner', 'action' => $bid->aircraft_id ? null : 'Sélectionnez un appareil autorisé.'],
            ['code' => 'OFP', 'ready' => $ofp !== null, 'label' => $ofp ? 'OFP SimBrief disponible' : 'OFP à préparer', 'action' => $ofp ? null : 'Générez ou importez l’OFP SimBrief.'],
        ];

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

    private function operationDto(Bid $bid): array
    {
        return [
            'id' => $bid->id,
            'flight' => [
                'id' => $bid->flight?->id,
                'ident' => $bid->flight?->ident,
                'number' => $bid->flight?->flight_number,
                'departure' => $bid->flight?->dpt_airport_id,
                'arrival' => $bid->flight?->arr_airport_id,
            ],
            'aircraft' => $bid->aircraft ? [
                'id' => $bid->aircraft->id,
                'registration' => $bid->aircraft->registration,
                'icao' => $bid->aircraft->icao,
                'subfleet' => $bid->aircraft->subfleet?->name,
            ] : null,
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
