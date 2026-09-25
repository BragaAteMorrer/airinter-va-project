<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Controller;
use App\Services\SimBriefService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Modules\Promethee\Services\SimBriefApiSessionService;
use Modules\Promethee\Services\SimBriefOperationResolver;

class AcarsSimBriefController extends Controller
{
    public function __construct(
        private readonly SimBriefService $simBriefSvc,
        private readonly SimBriefApiSessionService $apiSessions,
        private readonly SimBriefOperationResolver $resolver
    ) {}

    /**
     * Prepare a signed SimBrief API v1 request for the authenticated ACARS pilot.
     * The VA API key stays server-side; only the short-lived request signature is returned.
     */
    public function session(Request $request, string $flight_id): JsonResponse
    {
        $attrs = $this->validatePlanningRequest($request);
        $user = Auth::user();
        $resolved = $this->resolver->resolveFlightAircraft(
            $flight_id,
            $attrs['aircraft_id'],
            $user,
            $request->input('operation_id'),
            $attrs
        );
        $flight = $resolved['flight_model'];
        $aircraft = $resolved['aircraft_model'];

        $apiKey = app(\Modules\Promethee\Services\SimBriefCompanyKeyService::class)->get();
        abort_if(empty($apiKey), 503, 'La clé API SimBrief de la compagnie n’est pas configurée.');

        $timestamp = now()->timestamp;
        $operationId = $resolved['operation_id'];
        $staticId = $this->staticId($request, $user->ident, (string) $flight->id, (string) $aircraft->id);
        $apiSession = $this->apiSessions->create(
            (int) $user->id,
            $operationId,
            (string) $flight->id,
            (string) $aircraft->id,
            $staticId
        );

        $outputPage = route('promethee.simbrief.callback', ['state' => $apiSession['state']]);
        $parameters = $resolved['parameters'];
        $signatureInput = $parameters['orig'].$parameters['dest'].$parameters['type'].$timestamp.$outputPage;
        $parameters['static_id'] = $staticId;
        $parameters['outputpage'] = $outputPage;
        $parameters['timestamp'] = $timestamp;
        $parameters['apicode'] = md5($apiKey.$signatureInput);

        return response()->json([
            'operation_id' => $operationId,
            'worker_url' => 'https://www.simbrief.com/ofp/ofp.loader.api.php',
            'state' => $apiSession['state'],
            'expires_in' => 1800,
            'flight_id' => $flight->id,
            'aircraft_id' => $aircraft->id,
            'resolved' => $this->resolver->publicView($resolved),
            'parameters' => $parameters,
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
        $user = Auth::user();
        $resolved = $this->resolver->resolveFlightAircraft(
            $flight_id,
            $attrs['aircraft_id'],
            $user,
            $request->input('operation_id'),
            $attrs
        );
        $flight = $resolved['flight_model'];
        $aircraft = $resolved['aircraft_model'];

        $staticId = $this->staticId($request, (string) $user->ident, (string) $flight->id, (string) $aircraft->id);
        $parameters = $resolved['parameters'];
        $parameters['static_id'] = $staticId;

        return response()->json([
            'operation_id' => $resolved['operation_id'],
            'url' => 'https://dispatch.simbrief.com/options/custom?'.http_build_query($parameters, '', '&', PHP_QUERY_RFC3986),
            'edit_url' => 'https://www.simbrief.com/system/dispatch.php?editflight=last&static_id='.rawurlencode($staticId),
            'static_id' => $staticId,
            'resolved' => $this->resolver->publicView($resolved),
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
        abort_if(empty($attrs['username']) && empty($attrs['pilot_id']), 422,
            'Renseignez votre alias Navigraph ou votre Pilot ID SimBrief.');

        $resolved = $this->resolver->resolveFlightAircraft(
            $flight_id,
            $attrs['aircraft_id'],
            Auth::user(),
            $request->input('operation_id')
        );
        $flight = $resolved['flight_model'];
        $aircraft = $resolved['aircraft_model'];
        $staticId = $this->staticId($request, (string) Auth::id(), (string) $flight->id, (string) $aircraft->id);

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
        abort_unless(
            $origin === $resolved['origin']['icao'] && $destination === $resolved['destination']['icao'],
            409,
            "Le dernier OFP SimBrief est {$origin} → {$destination}, mais l’opération résolue est "
            .$resolved['origin']['icao'].' → '.$resolved['destination']['icao'].'.'
        );

        $requestId = trim((string) $ofp->params->request_id);
        abort_if($requestId === '', 502, 'SimBrief n’a pas retourné l’identifiant interne de l’OFP généré.');

        $persisted = $this->simBriefSvc->persistFetchedXml(
            (string) Auth::id(),
            $requestId,
            (string) $flight->id,
            (string) $aircraft->id,
            $body,
            $resolved['fares']
        );
        abort_if($persisted === null, 502,
            'L’OFP SimBrief a été reçu mais sa persistance dans Prométhée a échoué. Consultez les logs SimBrief pour le détail.');

        return response()->json([
            'operation_id' => $resolved['operation_id'],
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
            'resolved' => $this->resolver->publicView($resolved),
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

        $resolved = $this->resolver->resolveFlightAircraft(
            $flight_id,
            $attrs['aircraft_id'],
            Auth::user(),
            $request->input('operation_id')
        );
        $flight = $resolved['flight_model'];
        $aircraft = $resolved['aircraft_model'];
        $ofpId = $attrs['ofp_id'] ?? null;
        $apiSession = null;

        if (!empty($attrs['state'])) {
            $apiSession = $this->apiSessions->forUser($attrs['state'], (int) Auth::id());
            abort_if($apiSession === null, 410, 'La session de génération SimBrief a expiré. Relancez la génération.');

            $sameOperation = (string) ($apiSession['flight_id'] ?? '') === (string) $flight->id
                && (string) ($apiSession['aircraft_id'] ?? '') === (string) $aircraft->id
                && (!$request->filled('operation_id')
                    || (string) ($apiSession['operation_id'] ?? '') === (string) $resolved['operation_id']);
            abort_unless($sameOperation, 409, 'La réponse SimBrief ne correspond pas à l’opération sélectionnée.');

            $ofpId = $apiSession['ofp_id'] ?? null;
            abort_if(empty($ofpId), 409,
                'SimBrief n’a pas encore renvoyé l’OFP. Terminez la génération dans la fenêtre SimBrief.');
        }

        $simbrief = $this->simBriefSvc->downloadOfp(
            (string) Auth::id(),
            (string) $ofpId,
            (string) $flight->id,
            (string) $aircraft->id,
            $resolved['fares']
        );
        abort_if($simbrief === null, 404, 'L’OFP SimBrief n’est pas encore disponible.');

        $xml = $simbrief->xml;
        $origin = strtoupper((string) $xml->origin->icao_code);
        $destination = strtoupper((string) $xml->destination->icao_code);
        if ($origin !== $resolved['origin']['icao'] || $destination !== $resolved['destination']['icao']) {
            $simbrief->delete();
            abort(409,
                "L’OFP SimBrief reçu est {$origin} → {$destination}, mais l’opération résolue est "
                .$resolved['origin']['icao'].' → '.$resolved['destination']['icao'].'.'
            );
        }

        if (!empty($attrs['state'])) {
            $this->apiSessions->forget($attrs['state']);
        }

        $staticId = $apiSession['static_id'] ?? null;

        return response()->json([
            'operation_id' => $resolved['operation_id'],
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
            'resolved' => $this->resolver->publicView($resolved),
        ]);
    }

    public function readinessOperation(Request $request, string $operation): JsonResponse
    {
        [, $aircraftId, $operationId] = $this->operationContext($operation);
        $request->merge(['aircraft_id' => $aircraftId, 'operation_id' => $operationId]);
        $attrs = $this->validatePlanningRequest($request);
        $resolved = $this->resolver->resolveOperation($operation, Auth::user(), $attrs);

        return response()->json([
            'data' => $this->resolver->publicView($resolved),
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
     * Planning overrides are intentionally ephemeral. They are validated here,
     * then resolved against the existing DB by SimBriefOperationResolver.
     */
    private function validatePlanningRequest(Request $request): array
    {
        $alternate = trim((string) $request->input('alternate', ''));
        $route = trim((string) $request->input('route', ''));
        $level = $request->input('level');

        $request->merge([
            'alternate' => $alternate === '' ? null : strtoupper($alternate),
            'route' => $route === '' ? null : $route,
            'level' => $level === '' || $level === null ? null : $level,
        ]);

        return $request->validate([
            'aircraft_id' => ['required', 'string'],
            'alternate' => ['nullable', 'string', 'max:8', 'regex:/^[A-Z0-9]{3,8}$/'],
            'route' => ['nullable', 'string', 'max:2000'],
            'level' => ['nullable', 'integer', 'between:10,600'],
        ], [
            'aircraft_id.required' => 'Sélectionnez un appareil avant de préparer SimBrief.',
            'alternate.regex' => 'Le dégagement doit être un code aéroport valide ou rester vide pour AUTO.',
            'alternate.max' => 'Le code de dégagement est trop long.',
            'route.max' => 'La route SimBrief dépasse 2000 caractères.',
            'level.integer' => 'Le niveau de vol doit être un entier, par exemple 350.',
            'level.between' => 'Le niveau de vol doit être compris entre FL010 et FL600.',
        ]);
    }

    private function operationContext(string $reference): array
    {
        $resolved = $this->resolver->resolveOperation($reference, Auth::user());

        return [
            (string) $resolved['flight']['id'],
            (string) $resolved['aircraft']['id'],
            (string) $resolved['operation_id'],
        ];
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


}
