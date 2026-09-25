<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\Bid;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Promethee\Services\OperationIdentityService;
use Modules\Promethee\Services\PresenceService;
use Modules\Promethee\Services\AirInterNetworkService;

class PresenceController extends Controller
{
    private const SIMULATORS = ['fs2004', 'fsx', 'p3d', 'msfs2020', 'msfs2024', 'xplane', 'unknown'];
    private const STATES = ['STANDBY', 'READY', 'TRACKING', 'RECOVERY'];

    public function __construct(
        private readonly PresenceService $presence,
        private readonly AirInterNetworkService $airInterNetwork,
        private readonly OperationIdentityService $operationIdentity
    ) {}

    public function heartbeat(string $operation, Request $request)
    {
        $bid = $this->pilotBid($operation, $request);
        $bid->loadMissing(['flight.airline', 'aircraft.subfleet']);

        $data = $request->validate([
            'simulator' => ['nullable', Rule::in(self::SIMULATORS)],
            'connector' => 'nullable|string|max:64',
            'phase' => ['nullable','string','max:32','regex:/^[A-Z0-9_ -]+$/'],
            'lat' => 'nullable|numeric|between:-90,90',
            'lon' => 'nullable|numeric|between:-180,180',
            'altitude_msl' => 'nullable|numeric|between:-2000,70000',
            'hermes_version' => 'nullable|string|max:64',
            'recording' => 'nullable|boolean',
            'client_state' => ['nullable', Rule::in(self::STATES)],
        ]);

        $user = $request->user();
        $record = $this->presence->heartbeat(
            $this->operationIdentity->id($bid),
            [
                'id' => (int) $user->id,
                'ident' => (string) ($user->ident ?? ''),
                'name' => trim((string) ($user->name_private ?? $user->name ?? '')),
            ],
            [
                'flight' => $bid->flight ? [
                    'id' => $bid->flight->id,
                    'ident' => $bid->flight->ident,
                    'number' => $bid->flight->flight_number,
                    'departure' => $bid->flight->dpt_airport_id,
                    'arrival' => $bid->flight->arr_airport_id,
                ] : null,
                'aircraft' => $bid->aircraft ? [
                    'id' => $bid->aircraft->id,
                    'registration' => $bid->aircraft->registration,
                    'icao' => $bid->aircraft->icao,
                    'name' => $bid->aircraft->name,
                    'subfleet' => $bid->aircraft->subfleet?->name,
                ] : null,
            ],
            [
                'simulator' => $data['simulator'] ?? 'unknown',
                'connector' => $data['connector'] ?? null,
                'phase' => isset($data['phase']) ? strtoupper($data['phase']) : null,
                'lat' => $data['lat'] ?? null,
                'lon' => $data['lon'] ?? null,
                'altitude_msl' => $data['altitude_msl'] ?? null,
                'hermes_version' => $data['hermes_version'] ?? null,
                'recording' => (bool) ($data['recording'] ?? false),
                'client_state' => $data['client_state'] ?? 'STANDBY',
            ]
        );

        return response()->json(['data' => [
            'presence' => $record,
            'network' => $this->airInterNetwork->network(),
        ]]);
    }

    public function index()
    {
        return response()->json(['data' => $this->airInterNetwork->network()]);
    }

    public function me(Request $request)
    {
        return response()->json(['data' => [
            'pilot' => [
                'id' => (int) $request->user()->id,
                'ident' => (string) $request->user()->ident,
            ],
            'networks' => $this->airInterNetwork->pilot($request->user()),
        ]]);
    }

    private function pilotBid(string $reference, Request $request): Bid
    {
        $bid = $this->operationIdentity->resolveBid($reference, (int) $request->user()->id);
        abort_if(!$bid, 404, 'Opération introuvable.');
        return $bid;
    }
}
