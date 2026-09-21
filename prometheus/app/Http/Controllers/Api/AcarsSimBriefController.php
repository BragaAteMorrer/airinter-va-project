<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Controller;
use App\Models\Aircraft;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Repositories\FlightRepository;
use App\Services\SimBriefService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcarsSimBriefController extends Controller
{
    public function __construct(
        private readonly FlightRepository $flightRepo,
        private readonly SimBriefService $simBriefSvc,
        private readonly UserService $userSvc
    ) {}

    /**
     * Prepare a signed SimBrief API v1 request for the authenticated ACARS pilot.
     * The VA API key stays server-side; only the short-lived request signature is returned.
     */
    public function session(Request $request, string $flight_id): JsonResponse
    {
        $attrs = $request->validate(['aircraft_id' => 'required|string']);
        $user = Auth::user();
        [$flight, $aircraft] = $this->getEligibleOperation($flight_id, $attrs['aircraft_id']);

        $apiKey = setting('simbrief.api_key');
        abort_if(empty($apiKey), 503, 'La clé API SimBrief de la compagnie n’est pas configurée.');

        $type = $aircraft->simbrief_type ?: ($aircraft->subfleet->simbrief_type ?: $aircraft->icao);
        abort_if(empty($type), 422, 'Le type SimBrief de cet appareil n’est pas configuré.');

        $timestamp = now()->timestamp;
        $outputPage = url('/');
        $outputPageForSignature = str_replace('http://', '', $outputPage);
        $signatureInput = $flight->dpt_airport_id.$flight->arr_airport_id.$type.$timestamp.$outputPageForSignature;
        $ofpId = $timestamp.'_'.md5($flight->dpt_airport_id.$flight->arr_airport_id.$type);

        return response()->json([
            'worker_url' => 'https://www.simbrief.com/ofp/ofp.loader.api.php',
            'ofp_id' => $ofpId,
            'flight_id' => $flight->id,
            'aircraft_id' => $aircraft->id,
            'parameters' => [
                'orig' => $flight->dpt_airport_id,
                'dest' => $flight->arr_airport_id,
                'altn' => $flight->alt_airport_id ?: 'AUTO',
                'route' => $flight->route,
                'fl' => $flight->level,
                'type' => $type,
                'reg' => $aircraft->registration,
                'airline' => $flight->airline->icao,
                'fltnum' => $flight->flight_number,
                'callsign' => setting('simbrief.callsign', true) ? $user->ident : $flight->airline->icao.$flight->flight_number,
                'static_id' => $user->ident.'_'.$flight->id,
                'planformat' => 'lido',
                'units' => 'KGS',
                'navlog' => '1',
                'maps' => 'detail',
                'outputpage' => $outputPageForSignature,
                'timestamp' => $timestamp,
                'apicode' => md5($apiKey.$signatureInput),
            ],
        ]);
    }

    /** Import the generated OFP into Promethee and return data useful to Hermes. */
    public function import(Request $request, string $flight_id): JsonResponse
    {
        $attrs = $request->validate([
            'aircraft_id' => 'required|string',
            'ofp_id' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);

        // Recheck authorization on import: callers must not be able to bypass session creation.
        $this->getEligibleOperation($flight_id, $attrs['aircraft_id']);
        $simbrief = $this->simBriefSvc->downloadOfp(
            (string) Auth::id(),
            $attrs['ofp_id'],
            $flight_id,
            $attrs['aircraft_id']
        );
        abort_if($simbrief === null, 404, 'L’OFP SimBrief n’est pas encore disponible.');

        $xml = $simbrief->xml;

        return response()->json([
            'id' => $simbrief->id,
            'aircraft_id' => $simbrief->aircraft_id,
            'origin' => (string) $xml->origin->icao_code,
            'destination' => (string) $xml->destination->icao_code,
            'alternate' => (string) $xml->alternate->icao_code,
            'route' => (string) $xml->general->route,
            'initial_altitude' => (string) $xml->general->initial_altitude,
            'block_fuel' => (float) $xml->fuel->plan_ramp,
            'estimated_time_enroute' => (int) $xml->times->est_time_enroute,
            'briefing_url' => route('api.flights.briefing', ['id' => $simbrief->id]),
        ]);
    }

    private function getEligibleOperation(string $flightId, string $aircraftId): array
    {
        $flight = $this->flightRepo->with(['airline', 'subfleets'])->find($flightId);
        $aircraft = Aircraft::with('subfleet')
            ->withCount(['bid', 'simbriefs' => fn ($query) => $query->whereNull('pirep_id')])
            ->findOrFail($aircraftId);
        $allowedSubfleets = $this->userSvc->getAllowableSubfleets(Auth::user())->pluck('id');
        $flightSubfleets = $flight->subfleets->pluck('id');

        $eligible = $allowedSubfleets->contains($aircraft->subfleet_id)
            && ($flightSubfleets->isEmpty() || $flightSubfleets->contains($aircraft->subfleet_id))
            && (!setting('pireps.only_aircraft_at_dpt_airport') || $aircraft->airport_id === $flight->dpt_airport_id)
            && (!setting('simbrief.block_aircraft') || $aircraft->simbriefs_count === 0)
            && (!setting('bids.block_aircraft') || $aircraft->bid_count === 0)
            && $aircraft->state === AircraftState::PARKED
            && $aircraft->status === AircraftStatus::ACTIVE;

        abort_unless($eligible, 403, 'Cet appareil ne peut pas être utilisé pour ce vol.');

        return [$flight, $aircraft];
    }
}
