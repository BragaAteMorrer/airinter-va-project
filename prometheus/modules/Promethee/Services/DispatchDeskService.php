<?php

namespace Modules\Promethee\Services;

use App\Models\Bid;
use App\Models\Pirep;
use App\Models\SimBrief;
use App\Models\Enums\PirepState;
use App\Models\Enums\PirepStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DispatchDeskService
{
    public function __construct(
        private readonly OperationIdentityService $operationIdentity,
        private readonly DatalinkService $datalink,
        private readonly FlightOpsService $flightOps,
        private readonly SafetyAnalyzer $safetyAnalyzer,
    ) {}

    public function board(): array
    {
        $bids = Bid::query()
            ->with([
                'user:id,name,pilot_id',
                'flight.airline',
                'flight.dpt_airport',
                'flight.arr_airport',
                'aircraft.subfleet',
            ])
            ->latest('updated_at')
            ->limit(100)
            ->get();

        if ($bids->isEmpty()) {
            return $this->boardPayload(collect());
        }

        $sourceNames = $bids->map(
            fn (Bid $bid) => $this->sourceName($this->operationIdentity->id($bid))
        )->all();

        $pireps = Pirep::query()
            ->whereIn('source_name', $sourceNames)
            ->latest('created_at')
            ->get()
            ->unique('source_name')
            ->keyBy('source_name');

        $activePireps = $pireps->reject(fn (Pirep $pirep) => $this->isTerminal($pirep));
        $latestTelemetry = $this->latestTelemetry($activePireps->pluck('id')->filter()->values());

        $operations = $bids
            ->map(function (Bid $bid) use ($pireps, $latestTelemetry) {
                $operationId = $this->operationIdentity->id($bid);
                $pirep = $pireps->get($this->sourceName($operationId));

                if ($pirep && $this->isTerminal($pirep)) {
                    return null;
                }

                $sample = $pirep ? $latestTelemetry->get((string) $pirep->id) : null;

                return $this->summary($bid, $pirep, $sample);
            })
            ->filter()
            ->sortBy(function (array $operation) {
                $statusOrder = [
                    'IN_PROGRESS' => 0,
                    'PAUSED' => 1,
                    'READY' => 2,
                    'PREPARING' => 3,
                ];

                return sprintf(
                    '%02d-%s',
                    $statusOrder[$operation['status']] ?? 9,
                    $operation['flight']['ident'] ?? ''
                );
            })
            ->values();

        return $this->boardPayload($operations);
    }

    public function detail(string $reference): array
    {
        $bidId = str_starts_with($reference, 'op_') ? substr($reference, 3) : $reference;
        abort_if($bidId === '', 404, 'Opération introuvable.');

        $bid = Bid::query()
            ->with([
                'user:id,name,pilot_id,email',
                'flight.airline',
                'flight.dpt_airport',
                'flight.arr_airport',
                'flight.alt_airport',
                'flight.subfleets',
                'aircraft.subfleet.airline',
            ])
            ->findOrFail($bidId);

        $operationId = $this->operationIdentity->id($bid);
        $pirep = Pirep::query()
            ->where('source_name', $this->sourceName($operationId))
            ->where('user_id', $bid->user_id)
            ->latest('created_at')
            ->first();

        $samples = $pirep ? $this->telemetrySamples((string) $pirep->id) : collect();
        $latest = $samples->last();
        $summary = $this->summary($bid, $pirep, $latest);
        $messages = $this->safeDatalink($operationId, (int) $bid->user_id);
        $ofp = $this->operationOfp($bid, $pirep);
        $briefing = Schema::hasTable('promethee_briefings')
            ? DB::table('promethee_briefings')
                ->where('user_id', $bid->user_id)
                ->where('flight_id', $bid->flight_id)
                ->first()
            : null;

        $maintenance = null;
        if ($bid->aircraft_id && Schema::hasTable('disposable_maintenance')) {
            $maintenance = DB::table('disposable_maintenance')
                ->where('aircraft_id', $bid->aircraft_id)
                ->first();
        }

        $sampleArray = $samples->values()->all();
        $fdm = $pirep
            ? $this->safetyAnalyzer->debrief($pirep->landing_rate, $sampleArray)
            : [
                'contract_version' => '1.0',
                'data_quality' => ['samples' => 0],
                'safety' => ['status' => 'INSUFFICIENT_DATA', 'events' => []],
                'operations' => ['status' => 'INSUFFICIENT_DATA', 'events' => []],
                'flight' => ['status' => 'INSUFFICIENT_DATA', 'events' => [], 'timeline' => []],
            ];

        return [
            'contract_version' => '1.0',
            'updated_at' => now()->toIso8601String(),
            'operation' => $summary,
            'overview' => [
                'pilot' => [
                    'id' => $bid->user_id,
                    'ident' => $bid->user?->pilot_id,
                    'name' => $bid->user?->name,
                ],
                'preflight_checks' => [
                    ['code' => 'OPERATION', 'ready' => true, 'label' => 'Opération réservée'],
                    ['code' => 'AIRCRAFT', 'ready' => (bool) $bid->aircraft_id, 'label' => $bid->aircraft_id ? 'Appareil affecté' : 'Appareil à affecter'],
                    ['code' => 'OFP', 'ready' => (bool) $ofp, 'label' => $ofp ? 'OFP SimBrief lié' : 'OFP absent'],
                    ['code' => 'PIREP', 'ready' => (bool) $pirep, 'label' => $pirep ? 'PIREP Hermès préparé' : 'PIREP à préparer'],
                ],
                'planned_fuel' => $briefing?->planned_fuel,
                'notes' => $briefing?->notes,
            ],
            'aircraft' => $bid->aircraft ? [
                'id' => $bid->aircraft->id,
                'registration' => $bid->aircraft->registration,
                'icao' => $bid->aircraft->icao,
                'name' => $bid->aircraft->name,
                'subfleet' => $bid->aircraft->subfleet?->name,
                'airline' => $bid->aircraft->subfleet?->airline?->name,
                'airport' => $bid->aircraft->airport_id,
                'state' => $bid->aircraft->state,
                'status' => $bid->aircraft->status,
                'fuel_onboard' => $bid->aircraft->getAttribute('fuel_onboard'),
                'flight_time' => $bid->aircraft->getAttribute('flight_time'),
                'maintenance' => $maintenance ? (array) $maintenance : null,
            ] : null,
            'ofp' => [
                'available' => (bool) $ofp,
                'id' => $ofp?->id,
                'updated_at' => optional($ofp?->updated_at)?->toIso8601String(),
                'aircraft_id' => $ofp?->aircraft_id,
                'reference' => $briefing?->ofp_reference,
                'planned_fuel' => $briefing?->planned_fuel,
            ],
            'route' => [
                'departure' => $bid->flight?->dpt_airport_id,
                'departure_name' => $bid->flight?->dpt_airport?->name,
                'arrival' => $bid->flight?->arr_airport_id,
                'arrival_name' => $bid->flight?->arr_airport?->name,
                'alternate' => $pirep?->alt_airport_id ?: ($briefing?->alternate ?: $bid->flight?->alt_airport_id),
                'route' => $pirep?->route ?: $bid->flight?->route,
                'level' => $bid->flight?->level,
                'remaining_nm' => $summary['live']['remaining_nm'] ?? null,
                'eta' => $summary['live']['eta'] ?? null,
            ],
            'weather' => [
                'provider' => null,
                'status' => 'DATALINK_ONLY',
                'departure' => $bid->flight?->dpt_airport_id,
                'arrival' => $bid->flight?->arr_airport_id,
                'messages' => collect($messages['messages'] ?? [])
                    ->where('category', 'WEATHER')
                    ->take(-10)
                    ->values()
                    ->all(),
                'note' => 'Aucun fournisseur météo serveur n’est configuré : le dispatcher peut transmettre METAR, piste et informations météo via Datalink.',
            ],
            'track' => [
                'points' => $this->trackPoints($samples),
                'sample_count' => $samples->count(),
                'latest' => $latest,
            ],
            'fdm' => $fdm,
            'sop' => [
                'available' => false,
                'status' => 'LOT_6_PENDING',
                'checks' => [],
                'message' => 'Le moteur SOP Prométhée appartient au Lot 6. Le Dispatch Desk expose déjà son emplacement sans inventer de conformité.',
            ],
            'messages' => $messages,
            'timeline' => $this->timeline($bid, $pirep, $ofp, $samples, $messages),
        ];
    }

    private function boardPayload(Collection $operations): array
    {
        return [
            'contract_version' => '1.0',
            'updated_at' => now()->toIso8601String(),
            'poll_after_seconds' => 5,
            'summary' => [
                'operations' => $operations->count(),
                'airborne' => $operations->where('status', 'IN_PROGRESS')->count(),
                'paused' => $operations->where('status', 'PAUSED')->count(),
                'attention' => $operations->filter(fn (array $operation) => count($operation['alerts']) > 0)->count(),
                'pending_ops_ack' => $operations->sum('datalink.pending_ops_ack'),
            ],
            'operations' => $operations->values(),
        ];
    }

    private function summary(Bid $bid, ?Pirep $pirep, mixed $sampleRow): array
    {
        $operationId = $this->operationIdentity->id($bid);
        $payload = $this->samplePayload($sampleRow);
        $recordedAt = $payload['recorded_at'] ?? null;
        $age = $recordedAt ? now()->diffInSeconds(\Carbon\CarbonImmutable::parse($recordedAt)) : null;
        $signal = $pirep === null
            ? 'WAITING'
            : ($age === null ? 'NO_SIGNAL' : ($age > 180 ? 'LOST' : ($age > 60 ? 'STALE' : 'LIVE')));

        $status = $pirep === null
            ? 'PREPARING'
            : ((int) $pirep->state === PirepState::PAUSED
                ? 'PAUSED'
                : ($recordedAt ? 'IN_PROGRESS' : 'READY'));

        $messages = $this->safeDatalink($operationId, (int) $bid->user_id);
        $pendingOpsAck = collect($messages['messages'] ?? [])->filter(fn (array $message) =>
            ($message['direction'] ?? null) === 'COCKPIT_TO_OPS'
            && ($message['requires_ack'] ?? false)
            && blank($message['acknowledged_at'] ?? null)
        )->count();

        $live = $this->flightOps->liveFlight([
            'id' => $pirep?->id,
            'operation_id' => $operationId,
            'ident' => $bid->flight?->ident,
            'pilot' => $bid->user?->name,
            'aircraft' => $bid->aircraft?->registration,
            'departure' => $bid->flight?->dpt_airport_id,
            'arrival' => $bid->flight?->arr_airport_id,
            'state' => $status,
            'phase' => $payload['phase'] ?? null,
            'recorded_at' => $recordedAt,
            'signal' => $signal,
            'lat' => $payload['lat'] ?? null,
            'lon' => $payload['lon'] ?? null,
            'altitude' => $payload['altitude_msl'] ?? null,
            'ias' => $payload['ias'] ?? null,
            'gs' => $payload['gs'] ?? null,
            'vs' => $payload['vs'] ?? null,
            'heading' => $payload['heading'] ?? null,
            'fuel' => $payload['fuel'] ?? null,
            'on_ground' => $payload['on_ground'] ?? null,
        ], $payload);

        $alerts = $live['alerts'] ?? [];
        if ($pendingOpsAck > 0) {
            $alerts[] = [
                'level' => 'warning',
                'label' => $pendingOpsAck.' message(s) cockpit en attente d’acquittement OPS',
                'code' => 'DATALINK_ACK_REQUIRED',
            ];
        }

        return [
            'operation_id' => $operationId,
            'bid_id' => $bid->id,
            'pirep_id' => $pirep?->id,
            'status' => $status,
            'phase' => $payload['phase'] ?? null,
            'flight' => [
                'id' => $bid->flight_id,
                'ident' => $bid->flight?->ident,
                'departure' => $bid->flight?->dpt_airport_id,
                'arrival' => $bid->flight?->arr_airport_id,
                'airline' => $bid->flight?->airline?->name,
            ],
            'pilot' => [
                'id' => $bid->user_id,
                'ident' => $bid->user?->pilot_id,
                'name' => $bid->user?->name,
            ],
            'aircraft' => $bid->aircraft ? [
                'id' => $bid->aircraft->id,
                'registration' => $bid->aircraft->registration,
                'icao' => $bid->aircraft->icao,
                'name' => $bid->aircraft->name,
                'subfleet' => $bid->aircraft->subfleet?->name,
            ] : null,
            'signal' => [
                'state' => $signal,
                'age_seconds' => $age,
                'recorded_at' => $recordedAt,
            ],
            'live' => [
                'lat' => $live['lat'] ?? null,
                'lon' => $live['lon'] ?? null,
                'altitude' => $live['altitude'] ?? null,
                'ias' => $live['ias'] ?? null,
                'gs' => $live['gs'] ?? null,
                'vs' => $live['vs'] ?? null,
                'heading' => $live['heading'] ?? null,
                'fuel' => $live['fuel'] ?? null,
                'remaining_nm' => $live['remaining_nm'] ?? null,
                'eta' => $live['eta'] ?? null,
            ],
            'datalink' => [
                'messages' => count($messages['messages'] ?? []),
                'pending_cockpit_ack' => (int) ($messages['pending_ack_count'] ?? 0),
                'pending_ops_ack' => $pendingOpsAck,
            ],
            'alerts' => $alerts,
        ];
    }

    private function latestTelemetry(Collection $pirepIds): Collection
    {
        if ($pirepIds->isEmpty() || !Schema::hasTable('promethee_telemetry')) {
            return collect();
        }

        return DB::table('promethee_telemetry')
            ->whereIn('pirep_id', $pirepIds)
            ->orderByDesc('recorded_at')
            ->get()
            ->unique('pirep_id')
            ->keyBy(fn ($row) => (string) $row->pirep_id);
    }

    private function telemetrySamples(string $pirepId): Collection
    {
        if (!Schema::hasTable('promethee_telemetry')) {
            return collect();
        }

        return DB::table('promethee_telemetry')
            ->where('pirep_id', $pirepId)
            ->orderBy('recorded_at')
            ->limit(5000)
            ->get()
            ->map(fn ($row) => $this->samplePayload($row));
    }

    private function samplePayload(mixed $row): array
    {
        if (!$row) return [];
        if (is_array($row) && array_key_exists('recorded_at', $row) && !array_key_exists('payload', $row)) {
            return $row;
        }

        $payload = json_decode((string) ($row->payload ?? '[]'), true) ?: [];
        $payload['recorded_at'] = (string) ($row->recorded_at ?? ($payload['recorded_at'] ?? ''));

        return $payload;
    }

    private function trackPoints(Collection $samples): array
    {
        $positioned = $samples->filter(fn (array $sample) =>
            isset($sample['lat'], $sample['lon'])
            && is_numeric($sample['lat'])
            && is_numeric($sample['lon'])
        )->values();

        if ($positioned->isEmpty()) return [];

        $step = max(1, (int) ceil($positioned->count() / 300));

        return $positioned
            ->filter(fn ($_sample, int $index) => $index % $step === 0 || $index === $positioned->count() - 1)
            ->map(fn (array $sample) => [
                'lat' => (float) $sample['lat'],
                'lon' => (float) $sample['lon'],
                'altitude' => isset($sample['altitude_msl']) ? (float) $sample['altitude_msl'] : null,
                'phase' => $sample['phase'] ?? null,
                'at' => $sample['recorded_at'] ?? null,
            ])
            ->values()
            ->all();
    }

    private function timeline(Bid $bid, ?Pirep $pirep, ?SimBrief $ofp, Collection $samples, array $messages): array
    {
        $events = [];

        if ($bid->created_at) {
            $events[] = [
                'at' => $bid->created_at->toIso8601String(),
                'type' => 'OPERATION',
                'label' => 'Opération réservée dans Prométhée',
                'level' => 'info',
            ];
        }

        if ($ofp?->updated_at) {
            $events[] = [
                'at' => $ofp->updated_at->toIso8601String(),
                'type' => 'OFP',
                'label' => 'OFP SimBrief disponible',
                'level' => 'info',
            ];
        }

        if ($pirep?->created_at) {
            $events[] = [
                'at' => $pirep->created_at->toIso8601String(),
                'type' => 'PIREP',
                'label' => 'PIREP Hermès pré-déposé',
                'level' => 'info',
            ];
        }

        $lastPhase = null;
        foreach ($samples as $sample) {
            $phase = strtoupper(trim((string) ($sample['phase'] ?? '')));
            if ($phase === '' || $phase === $lastPhase) continue;
            $events[] = [
                'at' => $sample['recorded_at'] ?? null,
                'type' => 'PHASE',
                'label' => 'Phase Hermès : '.$phase,
                'level' => 'info',
            ];
            $lastPhase = $phase;
        }

        foreach ($messages['messages'] ?? [] as $message) {
            $events[] = [
                'at' => $message['created_at'] ?? null,
                'type' => 'DATALINK',
                'label' => (($message['direction'] ?? null) === 'OPS_TO_COCKPIT' ? 'OPS → cockpit' : 'Cockpit → OPS')
                    .' · '.($message['category'] ?? 'OPS')
                    .' · '.($message['body'] ?? ''),
                'level' => in_array($message['priority'] ?? 'NORMAL', ['HIGH', 'URGENT', 'IMPORTANT'], true) ? 'warning' : 'info',
                'message_id' => $message['id'] ?? null,
            ];
        }

        usort($events, fn (array $a, array $b) => strcmp((string) ($a['at'] ?? ''), (string) ($b['at'] ?? '')));

        return array_values($events);
    }

    private function operationOfp(Bid $bid, ?Pirep $pirep): ?SimBrief
    {
        if (!$bid->aircraft_id) return null;

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

    private function safeDatalink(string $operationId, int $pilotId): array
    {
        try {
            return $this->datalink->list($operationId, $pilotId);
        } catch (Throwable $exception) {
            report($exception);

            return [
                'contract_version' => '1.0',
                'operation_id' => $operationId,
                'messages' => [],
                'pending_ack_count' => 0,
                'degraded' => true,
            ];
        }
    }

    private function sourceName(string $operationId): string
    {
        return 'Hermes ACARS ['.$operationId.']';
    }

    private function isTerminal(Pirep $pirep): bool
    {
        return $pirep->submitted_at !== null
            || in_array((int) $pirep->state, [
                PirepState::PENDING,
                PirepState::ACCEPTED,
                PirepState::REJECTED,
                PirepState::CANCELLED,
            ], true)
            || in_array($pirep->status, [PirepStatus::ARRIVED, PirepStatus::CANCELLED], true);
    }
}
