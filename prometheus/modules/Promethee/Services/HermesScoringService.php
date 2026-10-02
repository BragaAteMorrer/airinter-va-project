<?php

namespace Modules\Promethee\Services;

use App\Models\Pirep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\VMSAcars\Models\Rule;

final class HermesScoringService
{
    public const VERSION = 1;
    public const BASE_SCORE = 100;

    /**
     * Legacy vmsACARS rules which can be evaluated from Hermès telemetry today.
     * Unknown simulator data never becomes a penalty.
     */
    private const SUPPORTED_RULES = [
        'EXCESS_TAXI_SPEED',
        'FUEL_REFILLED',
        'OVERSPEED_WARNING',
        'EXCESS_BANK',
        'EXCESS_PITCH',
        'SIMRATE_INCREASED',
        'SLEW_ACTIVATED',
        'SPEED_UNDER_10K',
        'HARD_LANDING',
    ];

    public function calculate(Pirep $pirep, ?float $landingRate = null): array
    {
        $samples = $this->samples($pirep);
        // The final landing rate comes from Hermès' confirmed ON event
        // (or an explicit preview value). Do not infer it from arbitrary vertical
        // speed samples: some adapters expose current VS through the touchdown field.
        $landingRate ??= is_numeric($pirep->landing_rate)
            ? (float) $pirep->landing_rate
            : null;

        $policy = $this->resolvePolicy();
        $rules = $policy['rules'];
        if ($rules === [] && !($policy['configured'] ?? false)) {
            return $this->unavailableResult('Aucun barème vmsACARS n’est disponible.', $policy['source']);
        }

        $deductions = [];
        $evaluated = [];
        $unavailable = [];
        $policyRules = [];

        foreach ($rules as $rule) {
            $policyRules[] = $this->ruleSnapshot($rule);

            if (!in_array($rule->id, self::SUPPORTED_RULES, true)) {
                $unavailable[] = [
                    'rule_id' => $rule->id,
                    'label' => $rule->name,
                    'reason' => 'Hermès ne fournit pas encore la donnée simulateur nécessaire à cette règle.',
                ];
                continue;
            }

            $evaluation = $this->evaluateRule($rule, $samples, $landingRate);
            if (!$evaluation['evaluable']) {
                $unavailable[] = [
                    'rule_id' => $rule->id,
                    'label' => $rule->name,
                    'reason' => $evaluation['reason'] ?? 'Donnée insuffisante.',
                ];
                continue;
            }

            $evaluated[] = $rule->id;
            $occurrences = $evaluation['occurrences'];
            if ($occurrences === []) {
                continue;
            }

            $points = max(0, (int) $rule->points);
            $count = count($occurrences);
            $deductions[] = [
                'rule_id' => $rule->id,
                'label' => $rule->name,
                'points' => $points,
                'count' => $count,
                'deduction' => $points * $count,
                'parameter' => $rule->parameter,
                'repeatable' => (bool) $rule->repeatable,
                'occurrences' => $occurrences,
            ];
        }

        $deductionsTotal = array_sum(array_column($deductions, 'deduction'));

        return [
            'available' => true,
            'stored' => false,
            'engine' => 'vmsacars-compatible',
            'engine_version' => self::VERSION,
            'policy_source' => $policy['source'],
            'base_score' => self::BASE_SCORE,
            'score' => max(0, self::BASE_SCORE - $deductionsTotal),
            'deductions_total' => $deductionsTotal,
            'deductions' => $deductions,
            'evaluated_rule_ids' => array_values($evaluated),
            'unavailable_rules' => array_values($unavailable),
            'policy_rules' => $policyRules,
            'telemetry_samples' => count($samples),
            'landing_rate_fpm' => $landingRate,
        ];
    }

    public function persist(Pirep $pirep, array $result): void
    {
        if (($result['available'] ?? false) !== true || !isset($result['score'])) {
            return;
        }

        if (!Schema::hasTable('promethee_pirep_scores')) {
            return;
        }

        $snapshot = $result;
        $snapshot['stored'] = true;

        DB::table('promethee_pirep_scores')->updateOrInsert(
            ['pirep_id' => $pirep->id],
            [
                'score' => (int) $result['score'],
                'score_data' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'engine_version' => (string) self::VERSION,
                'calculated_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function stored(Pirep $pirep): ?array
    {
        if (!Schema::hasTable('promethee_pirep_scores')) {
            return null;
        }

        $row = DB::table('promethee_pirep_scores')->where('pirep_id', $pirep->id)->first();
        if (!$row) {
            return null;
        }

        $data = json_decode((string) $row->score_data, true);
        if (!is_array($data)) {
            return null;
        }

        $data['stored'] = true;
        $data['score'] = (int) $row->score;

        return $data;
    }

    private function samples(Pirep $pirep): array
    {
        if (!Schema::hasTable('promethee_telemetry')) {
            return [];
        }

        $rows = DB::table('promethee_telemetry')
            ->where('pirep_id', $pirep->id)
            ->orderBy('recorded_at')
            ->get();

        $samples = [];
        $previousFuel = null;

        foreach ($rows as $row) {
            $sample = json_decode((string) $row->payload, true);
            if (!is_array($sample)) {
                continue;
            }

            $sample['recorded_at'] = (string) $row->recorded_at;
            $sample['_timestamp'] = strtotime((string) $row->recorded_at) ?: 0;
            $sample['_fuel_added'] = null;

            if (isset($sample['fuel']) && is_numeric($sample['fuel'])) {
                $fuel = (float) $sample['fuel'];
                if ($previousFuel !== null && strtoupper((string) ($sample['phase'] ?? '')) !== 'BOARDING') {
                    $tolerance = max(1.0, abs($previousFuel) * 0.0005);
                    $delta = $fuel - $previousFuel;
                    $sample['_fuel_added'] = $delta > $tolerance ? $delta : 0.0;
                }
                $previousFuel = $fuel;
            }

            $samples[] = $sample;
        }

        return $samples;
    }

    private function evaluateRule(object $rule, array $samples, ?float $landingRate): array
    {
        $parameter = is_numeric($rule->parameter) ? (float) $rule->parameter : null;

        return match ($rule->id) {
            'EXCESS_TAXI_SPEED' => $this->evaluateCondition(
                $rule,
                $samples,
                function (array $sample) use ($parameter) {
                    if (!array_key_exists('on_ground', $sample) || !isset($sample['gs'])) {
                        return null;
                    }
                    $phase = strtoupper((string) ($sample['phase'] ?? ''));
                    $taxiPhase = in_array($phase, ['PUSHBACK', 'TAXI_OUT', 'TAXI_IN'], true);
                    $limit = $parameter ?? 30.0;
                    if ($sample['on_ground'] !== true || !$taxiPhase || (float) $sample['gs'] <= $limit) {
                        return false;
                    }
                    return [
                        'value' => round((float) $sample['gs'], 1),
                        'unit' => 'kt',
                        'detail' => 'Vitesse taxi supérieure à '.round($limit, 1).' kt.',
                    ];
                }
            ),
            'FUEL_REFILLED' => $this->evaluateFuelRefill($rule, $samples),
            'OVERSPEED_WARNING' => $this->evaluateCondition(
                $rule,
                $samples,
                function (array $sample) {
                    if (!isset($sample['ias'], $sample['max_ias'])) {
                        return null;
                    }
                    if ((float) $sample['max_ias'] <= 0 || (float) $sample['ias'] <= (float) $sample['max_ias']) {
                        return false;
                    }
                    return [
                        'value' => round((float) $sample['ias'], 1),
                        'unit' => 'kt',
                        'detail' => 'IAS supérieure à la limite avion transmise par le simulateur.',
                    ];
                }
            ),
            'EXCESS_BANK' => $this->evaluateCondition(
                $rule,
                $samples,
                function (array $sample) use ($parameter) {
                    if (!array_key_exists('on_ground', $sample) || !isset($sample['bank'])) {
                        return null;
                    }
                    $limit = $parameter ?? 60.0;
                    $value = abs((float) $sample['bank']);
                    if ($sample['on_ground'] !== false || $value <= $limit) {
                        return false;
                    }
                    return [
                        'value' => round($value, 1),
                        'unit' => 'deg',
                        'detail' => 'Inclinaison supérieure à '.round($limit, 1).'°.',
                    ];
                }
            ),
            'EXCESS_PITCH' => $this->evaluateCondition(
                $rule,
                $samples,
                function (array $sample) use ($parameter) {
                    if (!array_key_exists('on_ground', $sample) || !isset($sample['pitch'])) {
                        return null;
                    }
                    $limit = $parameter ?? 30.0;
                    $value = abs((float) $sample['pitch']);
                    if ($sample['on_ground'] !== false || $value <= $limit) {
                        return false;
                    }
                    return [
                        'value' => round($value, 1),
                        'unit' => 'deg',
                        'detail' => 'Assiette supérieure à '.round($limit, 1).'°.',
                    ];
                }
            ),
            'SIMRATE_INCREASED' => $this->evaluateCondition(
                $rule,
                $samples,
                function (array $sample) use ($parameter) {
                    if (!isset($sample['simulation_rate'])) {
                        return null;
                    }
                    $limit = $parameter ?? 1.0;
                    $value = (float) $sample['simulation_rate'];
                    if ($value <= $limit + 0.001) {
                        return false;
                    }
                    return [
                        'value' => round($value, 2),
                        'unit' => 'x',
                        'detail' => 'Vitesse de simulation supérieure à x'.round($limit, 2).'.',
                    ];
                }
            ),
            'SLEW_ACTIVATED' => $this->evaluateCondition(
                $rule,
                $samples,
                function (array $sample) {
                    if (!array_key_exists('slew_active', $sample)) {
                        return null;
                    }
                    if ($sample['slew_active'] !== true) {
                        return false;
                    }
                    return [
                        'value' => 1,
                        'unit' => 'bool',
                        'detail' => 'Mode Slew détecté pendant le vol.',
                    ];
                }
            ),
            'SPEED_UNDER_10K' => $this->evaluateCondition(
                $rule,
                $samples,
                function (array $sample) {
                    if (!array_key_exists('on_ground', $sample) || !isset($sample['ias'], $sample['altitude_msl'])) {
                        return null;
                    }
                    if ($sample['on_ground'] !== false
                        || (float) $sample['altitude_msl'] >= 10000
                        || (float) $sample['ias'] <= 250) {
                        return false;
                    }
                    return [
                        'value' => round((float) $sample['ias'], 1),
                        'unit' => 'kt',
                        'detail' => 'IAS supérieure à 250 kt sous 10 000 ft.',
                    ];
                }
            ),
            'HARD_LANDING' => $this->evaluateHardLanding($rule, $landingRate),
            default => ['evaluable' => false, 'occurrences' => [], 'reason' => 'Règle non prise en charge.'],
        };
    }

    private function evaluateHardLanding(object $rule, ?float $landingRate): array
    {
        if ($landingRate === null) {
            return ['evaluable' => false, 'occurrences' => [], 'reason' => 'Taux d’atterrissage indisponible.'];
        }

        $limit = is_numeric($rule->parameter) ? abs((float) $rule->parameter) : 500.0;
        $value = abs($landingRate);
        if ($value <= $limit) {
            return ['evaluable' => true, 'occurrences' => []];
        }

        return [
            'evaluable' => true,
            'occurrences' => [[
                'occurred_at' => null,
                'value' => round($landingRate, 1),
                'unit' => 'ft/min',
                'detail' => 'Taux d’atterrissage supérieur au seuil de '.round($limit, 0).' ft/min.',
            ]],
        ];
    }

    private function evaluateFuelRefill(object $rule, array $samples): array
    {
        $known = false;
        $occurrences = [];
        $lastTrigger = null;
        $cooldown = max(0, (int) $rule->cooldown);

        foreach ($samples as $sample) {
            if ($sample['_fuel_added'] === null) {
                continue;
            }
            $known = true;
            $added = (float) $sample['_fuel_added'];
            if ($added <= 0) {
                continue;
            }

            $timestamp = (int) ($sample['_timestamp'] ?? 0);
            if ($lastTrigger !== null && $timestamp > 0 && $timestamp - $lastTrigger < $cooldown) {
                continue;
            }

            $occurrences[] = [
                'occurred_at' => $sample['recorded_at'] ?? null,
                'value' => round($added, 1),
                'unit' => 'fuel',
                'detail' => 'Ajout de carburant détecté après le début de l’opération.',
            ];
            $lastTrigger = $timestamp ?: $lastTrigger;

            if (!(bool) $rule->repeatable) {
                break;
            }
        }

        return $known
            ? ['evaluable' => true, 'occurrences' => $occurrences]
            : ['evaluable' => false, 'occurrences' => [], 'reason' => 'Historique carburant insuffisant.'];
    }

    /**
     * Evaluate sustained conditions while respecting legacy delay/cooldown and
     * repeatable semantics. A telemetry gap resets the sustained-condition timer.
     */
    private function evaluateCondition(object $rule, array $samples, callable $condition): array
    {
        $known = false;
        $occurrences = [];
        $activeSince = null;
        $lastTrigger = null;
        $lastKnownAt = null;
        $delay = max(0, (int) $rule->delay);
        $cooldown = max(0, (int) $rule->cooldown);

        foreach ($samples as $sample) {
            $result = $condition($sample);
            if ($result === null) {
                continue;
            }

            $known = true;
            $timestamp = (int) ($sample['_timestamp'] ?? 0);

            if ($lastKnownAt !== null && $timestamp > 0 && $timestamp - $lastKnownAt > max(45, $delay + 30)) {
                $activeSince = null;
            }
            $lastKnownAt = $timestamp ?: $lastKnownAt;

            if ($result === false) {
                $activeSince = null;
                continue;
            }

            $activeSince ??= $timestamp;
            if ($timestamp > 0 && $activeSince !== null && $timestamp - $activeSince < $delay) {
                continue;
            }

            if ($lastTrigger !== null && $timestamp > 0 && $timestamp - $lastTrigger < $cooldown) {
                continue;
            }

            $occurrences[] = [
                'occurred_at' => $sample['recorded_at'] ?? null,
                'value' => $result['value'] ?? null,
                'unit' => $result['unit'] ?? null,
                'detail' => $result['detail'] ?? null,
            ];
            $lastTrigger = $timestamp ?: $lastTrigger;

            if (!(bool) $rule->repeatable) {
                break;
            }
        }

        return $known
            ? ['evaluable' => true, 'occurrences' => $occurrences]
            : ['evaluable' => false, 'occurrences' => [], 'reason' => 'Télémétrie nécessaire indisponible.'];
    }

    private function landingRateFromSamples(array $samples): ?float
    {
        $values = [];
        foreach ($samples as $sample) {
            if (isset($sample['touchdown_rate']) && is_numeric($sample['touchdown_rate'])) {
                $values[] = (float) $sample['touchdown_rate'];
            }
        }

        if ($values === []) {
            return null;
        }

        usort($values, fn (float $a, float $b) => abs($b) <=> abs($a));
        return $values[0];
    }

    private function resolvePolicy(): array
    {
        if (Schema::hasTable('vmsacars_rules')) {
            try {
                $configuredRules = Rule::query()->orderBy('order')->get();
                if ($configuredRules->isNotEmpty()) {
                    $databaseRules = $configuredRules
                        ->filter(fn (Rule $rule) => (bool) $rule->enabled && (int) $rule->points > 0)
                        ->map(fn (Rule $rule) => (object) [
                            'id' => (string) $rule->id,
                            'name' => (string) $rule->name,
                            'parameter' => $rule->parameter,
                            'points' => (int) $rule->points,
                            'repeatable' => (bool) $rule->repeatable,
                            'delay' => (int) $rule->delay,
                            'cooldown' => (int) $rule->cooldown,
                            'enabled' => (bool) $rule->enabled,
                            'order' => (int) $rule->order,
                        ])
                        ->values()
                        ->all();

                    // An existing table is authoritative even when an admin has
                    // deliberately disabled every penalty or set all points to 0.
                    return [
                        'source' => 'vmsacars_rules',
                        'configured' => true,
                        'rules' => $databaseRules,
                    ];
                }
            } catch (\Throwable) {
                // An old/incomplete legacy schema must not break first-party ACARS.
            }
        }

        $fallback = collect(config('promethee.vmsacars-scoring.rules', []))
            ->filter(fn ($rule) => (bool) ($rule['enabled'] ?? true) && (int) ($rule['points'] ?? 0) > 0)
            ->sortBy(fn ($rule) => (int) ($rule['order'] ?? 0))
            ->map(fn ($rule) => (object) [
                'id' => (string) ($rule['id'] ?? ''),
                'name' => (string) ($rule['name'] ?? $rule['id'] ?? 'VMSAcars'),
                'parameter' => $rule['parameter'] ?? null,
                'points' => (int) ($rule['points'] ?? 0),
                'repeatable' => (bool) ($rule['repeatable'] ?? false),
                'delay' => (int) ($rule['delay'] ?? 0),
                'cooldown' => (int) ($rule['cooldown'] ?? 0),
                'enabled' => (bool) ($rule['enabled'] ?? true),
                'order' => (int) ($rule['order'] ?? 0),
            ])
            ->filter(fn (object $rule) => $rule->id !== '')
            ->values()
            ->all();

        return [
            'source' => 'promethee_legacy_vmsacars_fallback',
            'configured' => $fallback !== [],
            'rules' => $fallback,
        ];
    }

    private function ruleSnapshot(object $rule): array
    {
        return [
            'id' => $rule->id,
            'name' => $rule->name,
            'points' => (int) $rule->points,
            'parameter' => $rule->parameter,
            'repeatable' => (bool) $rule->repeatable,
            'delay' => (int) $rule->delay,
            'cooldown' => (int) $rule->cooldown,
        ];
    }

    private function unavailableResult(string $reason, string $policySource = 'unavailable'): array
    {
        return [
            'available' => false,
            'stored' => false,
            'engine' => 'vmsacars-compatible',
            'engine_version' => self::VERSION,
            'policy_source' => $policySource,
            'base_score' => self::BASE_SCORE,
            'score' => null,
            'deductions_total' => 0,
            'deductions' => [],
            'evaluated_rule_ids' => [],
            'unavailable_rules' => [],
            'policy_rules' => [],
            'telemetry_samples' => 0,
            'landing_rate_fpm' => null,
            'reason' => $reason,
        ];
    }
}
