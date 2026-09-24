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
use Modules\Promethee\Services\SimBriefApiSessionService;
use Modules\Promethee\Services\DemandProfileService;

class AcarsSimBriefController extends Controller
{
    public function __construct(
        private readonly FlightRepository $flightRepo,
        private readonly FareService $fareSvc,
        private readonly SimBriefService $simBriefSvc,
        private readonly UserService $userSvc,
        private readonly OperationIdentityService $operationIdentity,
        private readonly SimBriefApiSessionService $apiSessions,
        private readonly DemandProfileService $demandProfile
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
        $operationId = (string) ($request->input('operation_id') ?: $flight->id);
        $demand = $this->demandProfile->profile($aircraft, $flight, $operationId);
        $staticId = $this->staticId($request, $user->ident, $flight->id, $aircraft->id);
        $apiSession = $this->apiSessions->create(
            (int) $user->id,
            $operationId,
            (string) $flight->id,
            (string) $aircraft->id,
            $staticId
        );
        $outputPage = route('promethee.simbrief.callback', ['state' => $apiSession['state']]);
        $signatureInput = $flight->dpt_airport_id.$flight->arr_airport_id.$type.$timestamp.$outputPage;

        return response()->json([
            'operation_id' => $request->input('operation_id'),
            'worker_url' => 'https://www.simbrief.com/ofp/ofp.loader.api.php',
            'state' => $apiSession['state'],
            'expires_in' => 1800,
            'flight_id' => $flight->id,
            'aircraft_id' => $aircraft->id,
            'parameters' => [
                'orig' => $flight->dpt_airport_id,
                'dest' => $flight->arr_airport_id,
                'altn' => $plan['alternate'] === 'AUTO' ? null : $plan['alternate'],
                'route' => $plan['route'],
                'fl' => $plan['level'],
                'type' => $type,
                'reg' => $aircraft->registration,
                'pax' => $demand['passengers'],
                'airline' => $flight->airline->icao,
                'fltnum' => $flight->flight_number,
                'callsign' => setting('simbrief.callsign', true) ? $user->ident : $flight->airline->icao.$flight->flight_number,
                'static_id' => $staticId,
                'planformat' => 'lido',
                'units' => 'KGS',
                'navlog' => '1',
                'maps' => 'detail',
                'outputpage' => $outputPage,
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
        $operationId = (string) ($request->input('operation_id') ?: $flight->id);
        $demand = $this->demandProfile->profile($aircraft, $flight, $operationId);

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
            'pax' => $demand['passengers'],
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

        // A Pilot ID lets us address the exact static_id created for this Air Inter
        // operation. With an alias, SimBrief officially exposes the user's latest
        // OFP; the route check below prevents attaching an unrelated plan.
        $query = !empty($attrs['pilot_id'])
            ? ['userid' => $attrs['pilot_id'], 'static_id' => $staticId]
            : ['username' => $attrs['username']];

        $response = Http::accept('application/xml')->timeout(15)
            ->get('https://www.simbrief.com/api/xml.fetcher.php', $query);
        abort_unless($response->successful(), 502, 'SimBrief n’a pas pu retourner le dernier OFP de ce compte.');

        $body = $response->body();
        $ofp = @simplexml_load_string($body, \App\Models\SimBriefXML::class);
        abort_if($ofp === false, 502, 'Réponse XML SimBrief invalide.');

        $origin = strtoupper((string) $ofp->origin->icao_code);
        $destination = strtoupper((string) $ofp->destination->icao_code);
        abort_unless($origin === strtoupper($flight->dpt_airport_id) && $destination === strtoupper($flight->arr_airport_id), 409,
            "Le dernier OFP SimBrief est {$origin} → {$destination}, mais l’opération sélectionnée est {$flight->dpt_airport_id} → {$flight->arr_airport_id}.");

        $requestId = trim((string) $ofp->params->request_id);
        abort_if($requestId === '', 502, 'SimBrief n’a pas retourné l’identifiant interne de l’OFP généré.');

        // Persist the original XML unchanged. phpVMS, its SimBriefXML model and
        // the ACARS flight-plan parser are XML-native, so no lossy JSON -> XML
        // reconstruction is needed.
        $persisted = $this->simBriefSvc->persistFetchedXml(
            (string) Auth::id(),
            $requestId,
            (string) $flight->id,
            (string) $aircraft->id,
            $body,
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
            'alternate' => (string) $ofp->alternate->icao_code,
            'route' => (string) $ofp->general->route,
            'initial_altitude' => (string) $ofp->general->initial_altitude,
            'block_fuel' => (float) $ofp->fuel->plan_ramp,
            'estimated_time_enroute' => (int) $ofp->times->est_time_enroute,
            'generated_at' => (string) $ofp->params->time_generated,
            'aircraft_type' => (string) $ofp->aircraft->icaocode,
        ]);
    }

    /** Import the generated OFP into Promethee and return data useful to Hermes. */
    public function import(Request $request, string $flight_id): JsonResponse
    {
        $attrs = $request->validate([
            'aircraft_id' => 'required|string',
            'state' => ['nullable', 'string', 'regex:/^[A-Za-z0-9]{64}$/', 'required_without:ofp_id'],
            'ofp_id' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/', 'required_without:state'],
        ]);

        [$flight, $aircraft] = $this->getEligibleOperation($flight_id, $attrs['aircraft_id']);
        $ofpId = $attrs['ofp_id'] ?? null;
        $apiSession = null;

        if (!empty($attrs['state'])) {
            $apiSession = $this->apiSessions->forUser($attrs['state'], (int) Auth::id());
            abort_if($apiSession === null, 410, 'La session de génération SimBrief a expiré. Relancez la génération.');

            $sameOperation = (string) ($apiSession['flight_id'] ?? '') === (string) $flight->id
                && (string) ($apiSession['aircraft_id'] ?? '') === (string) $aircraft->id
                && (!$request->filled('operation_id')
                    || (string) ($apiSession['operation_id'] ?? '') === (string) $request->input('operation_id'));
            abort_unless($sameOperation, 409, 'La réponse SimBrief ne correspond pas à l’opération sélectionnée.');

            $ofpId = $apiSession['ofp_id'] ?? null;
            abort_if(empty($ofpId), 409, 'SimBrief n’a pas encore renvoyé l’OFP. Terminez la génération dans la fenêtre SimBrief.');
        }

        $simbrief = $this->simBriefSvc->downloadOfp(
            (string) Auth::id(),
            (string) $ofpId,
            (string) $flight->id,
            (string) $aircraft->id,
            $this->operationFares($flight, $aircraft)
        );
        abort_if($simbrief === null, 404, 'L’OFP SimBrief n’est pas encore disponible.');

        $xml = $simbrief->xml;
        $origin = strtoupper((string) $xml->origin->icao_code);
        $destination = strtoupper((string) $xml->destination->icao_code);
        if ($origin !== strtoupper($flight->dpt_airport_id) || $destination !== strtoupper($flight->arr_airport_id)) {
            $simbrief->delete();
            abort(409, "L’OFP SimBrief reçu est {$origin} → {$destination}, mais l’opération sélectionnée est {$flight->dpt_airport_id} → {$flight->arr_airport_id}.");
        }

        if (!empty($attrs['state'])) {
            $this->apiSessions->forget($attrs['state']);
        }

        $staticId = $apiSession['static_id'] ?? null;

        return response()->json([
            'operation_id' => $request->input('operation_id'),
            'id' => $simbrief->id,
            'source' => 'simbrief_company_api',
            'static_id' => $staticId,
            'edit_url' => $staticId
                ? 'https://www.simbrief.com/system/dispatch.php?editflight=last&static_id='.rawurlencode($staticId)
                : null,
            'aircraft_id' => $simbrief->aircraft_id,
            'origin' => $origin,
            'destination' => $destination,
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
