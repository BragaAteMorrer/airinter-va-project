<?php

namespace Modules\Promethee\Services;

use App\Models\Pirep;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Applies the historical vmsACARS point rules to Hermès observations.
 *
 * Hermès only observes simulator facts. Company scoring stays authoritative
 * on Prométhée and the applied result is snapshotted when the PIREP is filed.
 */
final class LegacyPirepScoringService
{
    public const VERSION = 1;
    public const STARTING_SCORE = 100;

    public function __construct(private readonly SopEngineService $sop) {}

    public function calculate(Pirep $pirep, ?string $operationId = null): array
    {
        $operationId ??= $this->operationId($pirep);
        $rules = $this->rules();

        if ($rules === []) {
            return [
                'available' => false,
                'version' => self::VERSION,
                'score' => null,
                'starting_score' => self::STARTING_SCORE,
                'penalty_total' => 0,
                'items' => [],
                'unavailable_rules' => [],
                'rules_source' => 'vmsacars_rules',
                'message' => 'Le barème VMSAcars n’est pas disponible sur Prométhée.',
            ];
        }

        $facts = $this->facts($operationId, (int) $pirep->user_id);
        $samples = $this->samples($pirep->id);
        $items = [];
        $unavailable = [];
        $penaltyTotal = 0;

        foreach ($rules as $rule) {
            $points = max(0, (int) ($rule['points'] ?? 0));
            if ($points === 0) continue;

            $occurrences = $this->occurrences($rule, $pirep, $facts, $samples);
            if ($occurrences === null) {
                $unavailable[] = [
                    'rule_id' => $rule['id'],
                    'name' => $rule['name'],
                    'points' => $points,
                    'reason' => 'Télémétrie Hermès non disponible pour cette règle.',
                ];
                continue;
            }

            if ($occurrences === []) continue;

            if (!(bool) ($rule['repeatable'] ?? false)) {
                $occurrences = [reset($occurrences)];
            } else {
                $occurrences = $this->respectCooldown($occurrences, (int) ($rule['cooldown'] ?? 0));
            }

            $deduction = min(self::STARTING_SCORE, $points * count($occurrences));
            $penaltyTotal += $deduction;
            $items[] = [
                'rule_id' => $rule['id'],
                'name' => $rule['name'],
                'points_each' => $points,
                'occurrences' => count($occurrences),
                'deduction' => $deduction,
                'repeatable' => (bool) ($rule['repeatable'] ?? false),
                'parameter' => $rule['parameter'],
                'events' => array_values($occurrences),
            ];
        }

        $penaltyTotal = min(self::STARTING_SCORE, $penaltyTotal);

        return [
            'available' => true,
            'version' => self::VERSION,
            'score' => max(0, self::STARTING_SCORE - $penaltyTotal),
            'starting_score' => self::STARTING_SCORE,
            'penalty_total' => $penaltyTotal,
            'items' => $items,
            'unavailable_rules' => $unavailable,
            'rules_source' => 'vmsacars_rules',
            'calculated_at' => now()->toIso8601String(),
        ];
    }

    public function apply(Pirep $pirep): array
    {
        if (!$this->isHermes($pirep)) {
            return ['available' => false, 'score' => null, 'reason' => 'not_hermes'];
        }

        $result = $this->calculate($pirep);
        if (!($result['available'] ?? false)) return $result;

        DB::transaction(function () use ($pirep, $result) {
            DB::table('pireps')->where('id', $pirep->id)->update([
                'score' => $result['score'],
                'updated_at' => now(),
            ]);

            DB::table('promethee_pirep_scores')->updateOrInsert(
                ['pirep_id' => $pirep->id],
                [
                    'score' => $result['score'],
                    'starting_score' => $result['starting_score'],
                    'penalty_total' => $result['penalty_total'],
                    'breakdown' => json_encode([
                        'items' => $result['items'],
                        'unavailable_rules' => $result['unavailable_rules'],
                    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'rules_snapshot' => json_encode($this->rules(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'scoring_version' => self::VERSION,
                    'calculated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });

        $pirep->score = $result['score'];
        return $result + ['persisted' => true];
    }

    public function forPirep(Pirep $pirep): array
    {
        try {
            $stored = DB::table('promethee_pirep_scores')->where('pirep_id', $pirep->id)->first();
        } catch (Throwable) {
            $stored = null;
        }

        if (!$stored) return $this->calculate($pirep);

        $breakdown = json_decode((string) $stored->breakdown, true) ?: [];
        return [
            'available' => true,
            'persisted' => true,
            'version' => (int) $stored->scoring_version,
            'score' => (int) $stored->score,
            'starting_score' => (int) $stored->starting_score,
            'penalty_total' => (int) $stored->penalty_total,
            'items' => array_values($breakdown['items'] ?? []),
            'unavailable_rules' => array_values($breakdown['unavailable_rules'] ?? []),
            'rules_source' => 'vmsacars_rules_snapshot',
            'calculated_at' => optional(Carbon::parse($stored->calculated_at))->toIso8601String(),
        ];
    }

    private function isHermes(Pirep $pirep): bool
    {
        return str_starts_with((string) $pirep->source_name, 'Hermes ACARS [op_');
    }

    private function operationId(Pirep $pirep): ?string
    {
        return preg_match('/Hermes ACARS \[(op_[^\]]+)\]/', (string) $pirep->source_name, $matches)
            ? $matches[1]
            : null;
    }

    private function rules(): array
    {
        try {
            return DB::table('vmsacars_rules')
                ->where('enabled', true)
                ->orderBy('order')
                ->get()
                ->map(fn ($rule) => [
                    'id' => (string) $rule->id,
                    'name' => (string) $rule->name,
                    'parameter' => is_numeric($rule->parameter) ? (float) $rule->parameter : null,
                    'points' => (int) $rule->points,
                    'repeatable' => (bool) $rule->repeatable,
                    'delay' => (int) $rule->delay,
                    'cooldown' => (int) $rule->cooldown,
                ])->all();
        } catch (Throwable $exception) {
            logger()->warning('hermes_scoring_rules_unavailable', ['error' => $exception->getMessage()]);
            return [];
        }
    }

    private function facts(?string $operationId, int $pilotId): array
    {
        if (!$operationId) return [];
        try {
            return array_values($this->sop->operation($operationId, $pilotId)['facts'] ?? []);
        } catch (Throwable $exception) {
            logger()->warning('hermes_scoring_facts_unavailable', [
                'operation_id' => $operationId,
                'error' => $exception->getMessage(),
            ]);
            return [];
        }
    }

    private function samples(string $pirepId): array
    {
        return DB::table('promethee_telemetry')
            ->where('pirep_id', $pirepId)
            ->orderBy('recorded_at')
            ->get()
            ->map(function ($row) {
                $payload = json_decode((string) $row->payload, true) ?: [];
                $payload['recorded_at'] = Carbon::parse($row->recorded_at)->utc()->toIso8601String();
                return $payload;
            })->all();
    }

    private function occurrences(array $rule, Pirep $pirep, array $facts, array $samples): ?array
    {
        $parameter = (float) ($rule['parameter'] ?? 0);
        $delay = max(0, (int) ($rule['delay'] ?? 0));

        return match ($rule['id']) {
            'EXCESS_TAXI_SPEED' => $this->telemetryEpisodes(
                $samples,
                fn ($s) => ($s['on_ground'] ?? null) === true
                    && in_array(strtoupper((string) ($s['phase'] ?? '')), ['PUSHBACK','TAXI_OUT','TAXI_IN'], true)
                    && isset($s['gs']) && (float) $s['gs'] > $parameter,
                $delay,
                fn ($s) => isset($s['gs']) ? (float) $s['gs'] : null
            ),
            'EXCESS_BANK' => $this->telemetryEpisodes(
                $samples,
                fn ($s) => ($s['on_ground'] ?? null) === false
                    && isset($s['bank']) && abs((float) $s['bank']) > $parameter,
                $delay,
                fn ($s) => isset($s['bank']) ? abs((float) $s['bank']) : null
            ),
            'EXCESS_PITCH' => $this->telemetryEpisodes(
                $samples,
                fn ($s) => ($s['on_ground'] ?? null) === false
                    && isset($s['pitch']) && abs((float) $s['pitch']) > $parameter,
                $delay,
                fn ($s) => isset($s['pitch']) ? abs((float) $s['pitch']) : null
            ),
            'SPEED_UNDER_10K' => $this->telemetryEpisodes(
                $samples,
                fn ($s) => ($s['on_ground'] ?? null) === false
                    && isset($s['altitude_msl'], $s['ias'])
                    && (float) $s['altitude_msl'] < 10000
                    && (float) $s['ias'] > 250,
                $delay,
                fn ($s) => isset($s['ias']) ? (float) $s['ias'] : null
            ),
            'FUEL_REFILLED' => $this->factOccurrences($facts, ['FUEL_ADDED'], fn ($f) => (float) ($f['value'] ?? 0) > 0),
            'SIMRATE_INCREASED' => $this->factOccurrences($facts, ['SIM_RATE'], fn ($f) => (float) ($f['value'] ?? 0) > $parameter),
            'SLEW_ACTIVATED' => $this->factOccurrences($facts, ['SLEW']),
            'STABILIZED_APPROACH' => $this->factOccurrences($facts, ['APPROACH_1000_UNSTABLE','APPROACH_500_UNSTABLE']),
            'HARD_LANDING' => $this->hardLanding($pirep, $facts, $parameter),
            // Hermès does not currently expose trustworthy signals for these
            // historical rules. Unknown data must never become a penalty.
            'EXCESS_GFORCE', 'OVERSPEED_WARNING', 'RUNWAY_OVERRUN', 'STALL_WARNING',
            'THRUST_REVERSERS_INFLIGHT', 'THRUST_REVERSERS_SPEED' => null,
            default => [],
        };
    }

    private function hardLanding(Pirep $pirep, array $facts, float $threshold): array
    {
        if ($pirep->landing_rate !== null) {
            $rate = (float) $pirep->landing_rate;
            if (abs($rate) < abs($threshold)) return [];

            return [[
                'at' => optional($pirep->block_on_time)?->toIso8601String(),
                'value' => $rate,
                'unit' => 'ft/min',
                'source' => 'pirep',
            ]];
        }

        // Before FILE, phpVMS has not received landing_rate yet. Hermès has,
        // however, already synchronized the TOUCHDOWN FDM fact, which lets the
        // Flight Review preview the same hard-landing penalty before submission.
        return $this->factOccurrences($facts, ['TOUCHDOWN'], function ($fact) use ($threshold) {
            if (!is_numeric($fact['value'] ?? null)) return false;
            return abs((float) $fact['value']) >= abs($threshold);
        });
    }

    private function factOccurrences(array $facts, array $codes, ?callable $predicate = null): array
    {
        $codes = array_flip($codes);
        $result = [];
        foreach ($facts as $fact) {
            if (!isset($codes[strtoupper((string) ($fact['code'] ?? ''))])) continue;
            if ($predicate && !$predicate($fact)) continue;
            $result[] = [
                'at' => $fact['occurred_at'] ?? null,
                'value' => $fact['value'] ?? null,
                'unit' => $fact['unit'] ?? null,
                'source' => 'hermes_fdm',
                'code' => $fact['code'] ?? null,
            ];
        }

        usort($result, fn ($a, $b) => strcmp((string) ($a['at'] ?? ''), (string) ($b['at'] ?? '')));
        return $result;
    }

    private function telemetryEpisodes(array $samples, callable $matches, int $delay, callable $value): array
    {
        $episodes = [];
        $start = null;
        $last = null;
        $peak = null;

        $close = function () use (&$episodes, &$start, &$last, &$peak, $delay) {
            if ($start !== null && $last !== null) {
                $duration = max(0, Carbon::parse($start)->diffInSeconds(Carbon::parse($last)));
                if ($delay === 0 || $duration >= $delay) {
                    $episodes[] = [
                        'at' => $start,
                        'value' => $peak,
                        'unit' => null,
                        'duration_seconds' => $duration,
                        'source' => 'hermes_telemetry',
                    ];
                }
            }
            $start = $last = null;
            $peak = null;
        };

        foreach ($samples as $sample) {
            $at = $sample['recorded_at'] ?? null;
            if (!$at) continue;

            if ($last !== null && Carbon::parse($last)->diffInSeconds(Carbon::parse($at)) > 45) {
                $close();
            }

            if (!$matches($sample)) {
                $close();
                continue;
            }

            $start ??= $at;
            $last = $at;
            $observed = $value($sample);
            if ($observed !== null) $peak = $peak === null ? $observed : max($peak, $observed);
        }

        $close();
        return $episodes;
    }

    private function respectCooldown(array $occurrences, int $cooldown): array
    {
        if ($cooldown <= 0 || count($occurrences) < 2) return array_values($occurrences);

        usort($occurrences, fn ($a, $b) => strcmp((string) ($a['at'] ?? ''), (string) ($b['at'] ?? '')));
        $kept = [];
        $lastAt = null;
        foreach ($occurrences as $occurrence) {
            $at = $occurrence['at'] ?? null;
            if (!$at) {
                $kept[] = $occurrence;
                continue;
            }
            if ($lastAt === null || Carbon::parse($lastAt)->diffInSeconds(Carbon::parse($at)) >= $cooldown) {
                $kept[] = $occurrence;
                $lastAt = $at;
            }
        }
        return $kept;
    }
}
