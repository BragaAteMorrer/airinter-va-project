<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Controller;
use App\Models\Aircraft;
use App\Models\Bid;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Repositories\FlightRepository;
use App\Services\FareService;
use App\Services\SimBriefService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Modules\Promethee\Services\OperationIdentityService;

class AcarsSimBriefController extends Controller
{
    public function __construct(
        private readonly FlightRepository $flightRepo,
        private readonly FareService $fareSvc,
        private readonly SimBriefService $simBriefSvc,
        private readonly UserService $userSvc,
        private readonly OperationIdentityService $operationIdentity
    ) {}

    /**
     * Prepare a signed SimBrief API v1 request for the authenticated ACARS pilot.
     * The VA API key stays server-side; only the short-lived request signature is returned.
     */
    public function session(Request $request, string $flight_id): JsonResponse
    {
        $attrs = $this->validatePlanningRequest($request);
        $user = Auth::user();
        [$flight, $aircraft] = $this->getEligibleOperation($flight_id, $attrs['aircraft_id']);
        $plan = $this->effectivePlanning($flight, $attrs);

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
            'operation_id' => $request->input('operation_id'),
            'worker_url' => 'https://www.simbrief.com/ofp/ofp.loader.api.php',
            'ofp_id' => $ofpId,
            'flight_id' => $flight->id,
            'aircraft_id' => $aircraft->id,
            'parameters' => [
                'orig' => $flight->dpt_airport_id,
                'dest' => $flight->arr_airport_id,
                'altn' => $plan['alternate'],
                'route' => $plan['route'],
                'fl' => $plan['level'],
                'type' => $type,
                'reg' => $aircraft->registration,
                'airline' => $flight->airline->icao,
                'fltnum' => $flight->flight_number,
                'callsign' => setting('simbrief.callsign', true) ? $user->ident : $flight->airline->icao.$flight->flight_number,
                'static_id' => $this->staticId($request, $user->ident, $flight->id, $aircraft->id),
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


    /**
     * Account mode: return a SimBrief Dispatch Redirect URL with the operation
     * pre-filled. This does not require the VA API key and never handles the
     * pilot's Navigraph password.
     */
    public function redirect(Request $request, string $flight_id): JsonResponse
    {
        $attrs = $this->validatePlanningRequest($request);
        [$flight, $aircraft] = $this->getEligibleOperation($flight_id, $attrs['aircraft_id']);
        $plan = $this->effectivePlanning($flight, $attrs);
        $type = $aircraft->simbrief_type ?: ($aircraft->subfleet->simbrief_type ?: $aircraft->icao);
        abort_if(empty($type), 422, 'Le type SimBrief de cet appareil n’est pas configuré.');

        $staticId = $this->staticId($request, (string) Auth::id(), $flight->id, $aircraft->id);

        $parameters = array_filter([
            'airline' => $flight->airline->icao,
            'fltnum' => $flight->flight_number,
            'type' => $type,
            'orig' => $flight->dpt_airport_id,
            'dest' => $flight->arr_airport_id,
            'altn' => $plan['alternate'] === 'AUTO' ? null : $plan['alternate'],
            'route' => $plan['route'] ?: null,
            'fl' => $plan['level'] ?: null,
            'reg' => $aircraft->registration ?: null,
            'callsign' => $flight->airline->icao.$flight->flight_number,
            'units' => 'KGS',
            'planformat' => 'LIDO',
            'navlog' => '1',
            'maps' => 'detail',
            'static_id' => $staticId,
        ], fn ($value) => $value !== null && $value !== '');

        return response()->json([
            'operation_id' => $request->input('operation_id'),
            'url' => 'https://dispatch.simbrief.com/options/custom?'.http_build_query($parameters, '', '&', PHP_QUERY_RFC3986),
            'edit_url' => 'https://www.simbrief.com/system/dispatch.php?editflight=last&static_id='.rawurlencode($staticId),
            'static_id' => $staticId,
            'parameters' => $parameters,
        ]);
    }

    /**
     * Account mode: fetch the latest OFP explicitly requested by the pilot.
     * SimBrief documents this endpoint for user-triggered imports only.
     */
    public function importAccount(Request $request, string $flight_id): JsonResponse
    {
        $attrs = $request->validate([
            'aircraft_id' => 'required|string',
            'username' => ['nullable', 'string', 'max:100'],
            'pilot_id' => ['nullable', 'regex:/^\d{1,7}$/'],
        ]);
        abort_if(empty($attrs['username']) && empty($attrs['pilot_id']), 422, 'Renseignez votre alias Navigraph ou votre Pilot ID SimBrief.');

        [$flight, $aircraft] = $this->getEligibleOperation($flight_id, $attrs['aircraft_id']);
        $staticId = $this->staticId($request, (string) Auth::id(), $flight->id, $aircraft->id);
        $query = !empty($attrs['username'])
            ? ['username' => $attrs['username'], 'static_id' => $staticId, 'json' => 1]
            : ['userid' => $attrs['pilot_id'], 'static_id' => $staticId, 'json' => 1];

        $response = Http::acceptJson()->timeout(15)->get('https://www.simbrief.com/api/xml.fetcher.php', $query);
        abort_unless($response->successful(), 502, 'SimBrief n’a pas pu retourner le dernier OFP de ce compte.');
        $ofp = $response->json();
        abort_unless(is_array($ofp), 502, 'Réponse SimBrief invalide.');

        $origin = strtoupper((string) data_get($ofp, 'origin.icao_code', ''));
        $destination = strtoupper((string) data_get($ofp, 'destination.icao_code', ''));
        abort_unless($origin === strtoupper($flight->dpt_airport_id) && $destination === strtoupper($flight->arr_airport_id), 409,
            "Le dernier OFP SimBrief est {$origin} → {$destination}, mais l’opération sélectionnée est {$flight->dpt_airport_id} → {$flight->arr_airport_id}.");

        // Account mode used to return an ephemeral JSON plan only. Persist the
        // exact generated OFP in the existing phpVMS SimBrief table as well so
        // the following PIREP can attach to a concrete SimBrief row.
        $requestId = (string) data_get($ofp, 'params.request_id', '');
        abort_if($requestId === '', 502, 'SimBrief n’a pas retourné l’identifiant de l’OFP généré.');
        // Account import already owns the complete OFP payload returned by
        // SimBrief. Persist that exact payload instead of downloading it again
        // through the legacy request-id endpoint.
        $persisted = $this->simBriefSvc->persistFetchedOfp(
            (string) Auth::id(),
            $requestId,
            (string) $flight->id,
            (string) $aircraft->id,
            $ofp,
            $this->operationFares($flight, $aircraft)
        );
        abort_if($persisted === null, 502, 'L’OFP SimBrief a été reçu mais sa persistance dans Prométhée a échoué. Consultez les logs SimBrief pour le détail.');

        return response()->json([
            'operation_id' => $request->input('operation_id'),
            'id' => $persisted->id,
            'source' => 'simbrief_account',
            'static_id' => $staticId,
            'edit_url' => 'https://www.simbrief.com/system/dispatch.php?editflight=last&static_id='.rawurlencode($staticId),
            'aircraft_id' => $aircraft->id,
            'origin' => $origin,
            'destination' => $destination,
            'alternate' => (string) data_get($ofp, 'alternate.icao_code', ''),
            'route' => (string) data_get($ofp, 'general.route', ''),
            'initial_altitude' => (string) data_get($ofp, 'general.initial_altitude', ''),
            'block_fuel' => (float) data_get($ofp, 'fuel.plan_ramp', 0),
            'estimated_time_enroute' => (int) data_get($ofp, 'times.est_time_enroute', 0),
            'generated_at' => data_get($ofp, 'params.time_generated'),
            'aircraft_type' => data_get($ofp, 'aircraft.icaocode'),
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
            $attrs['aircraft_id'],
            $this->operationFares(...$this->getEligibleOperation($flight_id, $attrs['aircraft_id']))
        );
        abort_if($simbrief === null, 404, 'L’OFP SimBrief n’est pas encore disponible.');

        $xml = $simbrief->xml;

        return response()->json([
            'operation_id' => $request->input('operation_id'),
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


    public function sessionOperation(Request $request, string $operation): JsonResponse
    {
        [$flightId, $aircraftId, $operationId] = $this->operationContext($operation);
        $request->merge(['aircraft_id' => $aircraftId, 'operation_id' => $operationId]);
        return $this->session($request, $flightId);
    }

    public function redirectOperation(Request $request, string $operation): JsonResponse
    {
        [$flightId, $aircraftId, $operationId] = $this->operationContext($operation);
        $request->merge(['aircraft_id' => $aircraftId, 'operation_id' => $operationId]);
        return $this->redirect($request, $flightId);
    }

    public function importAccountOperation(Request $request, string $operation): JsonResponse
    {
        [$flightId, $aircraftId, $operationId] = $this->operationContext($operation);
        $request->merge(['aircraft_id' => $aircraftId, 'operation_id' => $operationId]);
        return $this->importAccount($request, $flightId);
    }

    public function importOperation(Request $request, string $operation): JsonResponse
    {
        [$flightId, $aircraftId, $operationId] = $this->operationContext($operation);
        $request->merge(['aircraft_id' => $aircraftId, 'operation_id' => $operationId]);
        return $this->import($request, $flightId);
    }

    /**
     * Planning overrides are intentionally ephemeral: Hermès may personalize the
     * dispatch sent to SimBrief without mutating the phpVMS schedule or fleet DB.
     */
    private function validatePlanningRequest(Request $request): array
    {
        return $request->validate([
            'aircraft_id' => ['required', 'string'],
            'alternate' => ['nullable', 'string', 'max:8', 'regex:/^[A-Za-z0-9]{3,8}$/'],
            'route' => ['nullable', 'string', 'max:2000'],
            'level' => ['nullable', 'integer', 'between:10,600'],
        ]);
    }

    private function effectivePlanning($flight, array $attrs): array
    {
        return [
            'alternate' => strtoupper(trim((string) ($attrs['alternate'] ?? $flight->alt_airport_id ?: 'AUTO'))),
            'route' => trim((string) ($attrs['route'] ?? $flight->route ?? '')),
            'level' => $attrs['level'] ?? $flight->level,
        ];
    }

    private function operationContext(string $reference): array
    {
        $bid = $this->operationIdentity->resolveBid($reference, (int) Auth::id());
        abort_if(!$bid, 404, 'Opération introuvable.');
        abort_if(!$bid->aircraft_id, 409, 'Sélectionnez un appareil avant de préparer SimBrief.');

        return [$bid->flight_id, (string) $bid->aircraft_id, $this->operationIdentity->id($bid)];
    }

    private function staticId(Request $request, string $pilot, string $flightId, string $aircraftId): string
    {
        if ($request->filled('operation_id')) {
            // The operation is the public correlation key. SimBrief receives the
            // same deterministic static_id for create/edit/import, so Hermès never
            // has to guess from the pilot + flight + aircraft + timestamp tuple.
            $operation = preg_replace('/[^A-Za-z0-9_]/', '_', (string) $request->input('operation_id'));

            return 'AIRINTER_OP_'.strtoupper($operation);
        }

        // Legacy flight endpoints remain available during the Prometheus ->
        // Prométhée transition, but new Hermès flows use operation_id above.
        return 'AIRINTER_'.strtoupper(str_replace('-', '_', $pilot.'_'.$flightId.'_'.$aircraftId));
    }

    private function getEligibleOperation(string $flightId, string $aircraftId): array
    {
        $flight = $this->flightRepo->with(['airline', 'fares', 'subfleets.fares'])->find($flightId);
        $aircraft = Aircraft::with('subfleet')
            ->withCount(['bid', 'simbriefs' => fn ($query) => $query->whereNull('pirep_id')])
            ->findOrFail($aircraftId);
        $user = Auth::user();
        $allowedSubfleets = $this->userSvc->getAllowableSubfleets($user)->pluck('id');
        $flightSubfleets = $flight->subfleets->pluck('id');
        // A company-wide aircraft lock must not reject the aircraft already
        // assigned to this pilot's own reservation for this exact flight.
        $reservedForThisOperation = Bid::query()
            ->where('user_id', $user->id)
            ->where('flight_id', $flight->id)
            ->where('aircraft_id', $aircraft->id)
            ->exists();

        $eligible = $allowedSubfleets->contains($aircraft->subfleet_id)
            && ($flightSubfleets->isEmpty() || $flightSubfleets->contains($aircraft->subfleet_id))
            && (!setting('pireps.only_aircraft_at_dpt_airport') || $aircraft->airport_id === $flight->dpt_airport_id)
            && (!setting('simbrief.block_aircraft') || $aircraft->simbriefs_count === 0)
            && (!setting('bids.block_aircraft') || $aircraft->bid_count === 0 || $reservedForThisOperation)
            && $aircraft->state === AircraftState::PARKED
            && $aircraft->status === AircraftStatus::ACTIVE;

        abort_unless($eligible, 403, 'Cet appareil ne peut pas être utilisé pour ce vol.');

        return [$flight, $aircraft];
    }

    /**
     * Resolve the effective phpVMS fares for the selected aircraft.
     * Flight overrides win over subfleet/base values, exactly like the native
     * phpVMS SimBrief and PIREP flows.
     */
    private function operationFares($flight, Aircraft $aircraft): array
    {
        return $this->fareSvc
            ->getFareWithOverrides($aircraft->subfleet->fares, $flight->fares)
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
            ])->values()->all();
    }
}
