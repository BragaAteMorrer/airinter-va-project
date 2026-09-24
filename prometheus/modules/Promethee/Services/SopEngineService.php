<?php

namespace Modules\Promethee\Services;

use Illuminate\Support\Str;
use RuntimeException;

/**
 * Air Inter SOP engine.
 *
 * Hermès sends observed facts. This service applies company policy server-side
 * and produces reviewable evaluations. It never computes points or penalties.
 */
class SopEngineService
{
    private const MAX_FACTS = 1500;
    private const MAX_EVALUATIONS = 1500;
    private const OPERATORS = ['exists', 'gt', 'gte', 'lt', 'lte', 'eq', 'neq'];
    private const SEVERITIES = ['INFO', 'ADVISORY', 'WARNING'];

    public function __construct(private readonly ?string $root = null) {}

    public function rules(): array
    {
        $path = $this->rulesPath();
        if (!is_file($path)) return $this->defaultRules();

        $decoded = $this->readJson($path, []);
        return is_array($decoded) && $decoded !== [] ? array_values($decoded) : $this->defaultRules();
    }

    public function upsertRule(array $input, ?string $id = null): array
    {
        $rules = $this->rules();
        $id = $id ?: 'sop_'.Str::lower((string) Str::ulid());
        $normalized = $this->normalizeRule($input + ['id' => $id]);

        $replaced = false;
        foreach ($rules as $index => $rule) {
            if (($rule['id'] ?? null) !== $id) continue;
            $rules[$index] = $normalized;
            $replaced = true;
            break;
        }
        if (!$replaced) $rules[] = $normalized;

        $this->writeJson($this->rulesPath(), array_values($rules));
        return $normalized;
    }

    public function deleteRule(string $id): void
    {
        $rules = array_values(array_filter(
            $this->rules(),
            fn (array $rule) => ($rule['id'] ?? null) !== $id
        ));
        $this->writeJson($this->rulesPath(), $rules);
    }

    public function ingest(string $operationId, int $pilotId, array $facts): array
    {
        $rules = array_values(array_filter($this->rules(), fn (array $rule) => (bool) ($rule['enabled'] ?? false)));

        return $this->mutateOperation($operationId, $pilotId, function (array &$state) use ($facts, $rules) {
            $known = [];
            foreach ($state['facts'] as $fact) $known[(string) ($fact['fact_id'] ?? '')] = true;

            $inserted = 0;
            $created = [];
            foreach ($facts as $incoming) {
                $fact = $this->normalizeFact($incoming);
                if (isset($known[$fact['fact_id']])) continue;

                $state['facts'][] = $fact;
                $known[$fact['fact_id']] = true;
                $inserted++;

                foreach ($rules as $rule) {
                    if (!$this->matches($rule, $fact)) continue;
                    $evaluation = $this->evaluation($rule, $fact);
                    $state['evaluations'][] = $evaluation;
                    $created[] = $evaluation;
                }
            }

            $state['facts'] = array_slice($state['facts'], -self::MAX_FACTS);
            $state['evaluations'] = $this->trimEvaluations($state['evaluations']);
            $state['updated_at'] = now()->toIso8601String();

            return [
                'inserted_facts' => $inserted,
                'received_facts' => count($facts),
                'new_evaluations' => $created,
                'summary' => $this->summary($state['evaluations']),
            ];
        });
    }

    public function operation(string $operationId, int $pilotId): array
    {
        $state = $this->readOperation($operationId);
        if ($state === null) return $this->emptyState($operationId, $pilotId);
        if ((int) ($state['pilot_id'] ?? 0) !== $pilotId) {
            throw new RuntimeException('Les données SOP appartiennent à un autre pilote.');
        }

        $state['summary'] = $this->summary($state['evaluations'] ?? []);
        return $state;
    }

    public function pilotReview(string $operationId, int $pilotId, string $evaluationId): array
    {
        return $this->mutateOperation($operationId, $pilotId, function (array &$state) use ($evaluationId) {
            foreach ($state['evaluations'] as &$evaluation) {
                if (($evaluation['id'] ?? null) !== $evaluationId) continue;
                if (!($evaluation['pilot_review_required'] ?? false)) return $evaluation;
                $evaluation['pilot_reviewed_at'] ??= now()->toIso8601String();
                return $evaluation;
            }
            unset($evaluation);
            throw new RuntimeException('Évaluation SOP introuvable.');
        });
    }

    public function dispatchAcknowledge(string $operationId, int $pilotId, string $evaluationId): array
    {
        return $this->mutateOperation($operationId, $pilotId, function (array &$state) use ($evaluationId) {
            foreach ($state['evaluations'] as &$evaluation) {
                if (($evaluation['id'] ?? null) !== $evaluationId) continue;
                if (!($evaluation['dispatch_alert'] ?? false)) return $evaluation;
                $evaluation['dispatch_acknowledged_at'] ??= now()->toIso8601String();
                return $evaluation;
            }
            unset($evaluation);
            throw new RuntimeException('Évaluation SOP introuvable.');
        });
    }

    public function recentDispatchAlerts(int $limit = 100): array
    {
        $alerts = [];
        $folder = $this->operationsRoot();
        if (!is_dir($folder)) return [];

        foreach (glob($folder.DIRECTORY_SEPARATOR.'*.json') ?: [] as $path) {
            $state = $this->readJson($path, []);
            if (!is_array($state)) continue;
            foreach (($state['evaluations'] ?? []) as $evaluation) {
                if (!($evaluation['dispatch_alert'] ?? false)) continue;
                $alerts[] = $evaluation + [
                    'operation_id' => $state['operation_id'] ?? null,
                    'pilot_id' => $state['pilot_id'] ?? null,
                ];
            }
        }

        usort($alerts, fn (array $a, array $b) => strcmp(
            (string) ($b['created_at'] ?? ''),
            (string) ($a['created_at'] ?? '')
        ));

        return array_slice($alerts, 0, max(1, min(500, $limit)));
    }

    private function normalizeRule(array $rule): array
    {
        $operator = strtolower((string) ($rule['operator'] ?? 'exists'));
        if (!in_array($operator, self::OPERATORS, true)) $operator = 'exists';

        $severity = strtoupper((string) ($rule['severity'] ?? 'ADVISORY'));
        if (!in_array($severity, self::SEVERITIES, true)) $severity = 'ADVISORY';

        $phases = array_values(array_unique(array_filter(array_map(
            fn ($phase) => strtoupper(trim((string) $phase)),
            is_array($rule['phases'] ?? null) ? $rule['phases'] : []
        ))));

        $threshold = $rule['threshold'] ?? null;
        if ($operator !== 'exists' && !is_numeric($threshold)) {
            throw new RuntimeException('Une règle numérique doit définir un seuil.');
        }

        return [
            'id' => (string) $rule['id'],
            'name' => trim((string) ($rule['name'] ?? $rule['fact_code'] ?? 'SOP')),
            'enabled' => filter_var($rule['enabled'] ?? true, FILTER_VALIDATE_BOOL),
            'fact_code' => strtoupper(trim((string) ($rule['fact_code'] ?? ''))),
            'operator' => $operator,
            'threshold' => $operator === 'exists' ? null : (float) $threshold,
            'phases' => $phases,
            'severity' => $severity,
            'pilot_review' => filter_var($rule['pilot_review'] ?? false, FILTER_VALIDATE_BOOL),
            'dispatch_alert' => filter_var($rule['dispatch_alert'] ?? false, FILTER_VALIDATE_BOOL),
            'message' => trim((string) ($rule['message'] ?? '')),
        ];
    }

    private function normalizeFact(array $fact): array
    {
        return [
            'fact_id' => (string) $fact['fact_id'],
            'code' => strtoupper(trim((string) $fact['code'])),
            'category' => trim((string) ($fact['category'] ?? 'operational')),
            'occurred_at' => (string) $fact['occurred_at'],
            'message' => trim((string) ($fact['message'] ?? '')),
            'source_severity' => trim((string) ($fact['source_severity'] ?? 'info')),
            'value' => isset($fact['value']) && is_numeric($fact['value']) ? (float) $fact['value'] : null,
            'unit' => isset($fact['unit']) ? trim((string) $fact['unit']) : null,
            'phase' => isset($fact['phase']) ? strtoupper(trim((string) $fact['phase'])) : null,
            'status' => isset($fact['status']) ? strtoupper(trim((string) $fact['status'])) : null,
        ];
    }

    private function matches(array $rule, array $fact): bool
    {
        if (($rule['fact_code'] ?? '') !== ($fact['code'] ?? '')) return false;

        $phases = $rule['phases'] ?? [];
        if ($phases !== [] && !in_array($fact['phase'] ?? null, $phases, true)) return false;

        $operator = $rule['operator'] ?? 'exists';
        if ($operator === 'exists') return true;
        if (!is_numeric($fact['value'] ?? null)) return false;

        $value = (float) $fact['value'];
        $threshold = (float) ($rule['threshold'] ?? 0);

        return match ($operator) {
            'gt' => $value > $threshold,
            'gte' => $value >= $threshold,
            'lt' => $value < $threshold,
            'lte' => $value <= $threshold,
            'eq' => abs($value - $threshold) < 0.000001,
            'neq' => abs($value - $threshold) >= 0.000001,
            default => false,
        };
    }

    private function evaluation(array $rule, array $fact): array
    {
        $id = substr(hash('sha256', $fact['fact_id'].'|'.$rule['id']), 0, 32);
        $message = $rule['message'] !== '' ? $rule['message'] : $rule['name'];
        $replacements = [
            '{code}' => $fact['code'],
            '{value}' => $fact['value'] === null ? 'UNKNOWN' : rtrim(rtrim(number_format((float) $fact['value'], 2, '.', ''), '0'), '.'),
            '{unit}' => (string) ($fact['unit'] ?? ''),
            '{phase}' => (string) ($fact['phase'] ?? 'UNKNOWN'),
            '{message}' => (string) ($fact['message'] ?? ''),
        ];

        return [
            'id' => $id,
            'rule_id' => $rule['id'],
            'rule_name' => $rule['name'],
            'fact_id' => $fact['fact_id'],
            'fact_code' => $fact['code'],
            'severity' => $rule['severity'],
            'message' => strtr($message, $replacements),
            'observed_value' => $fact['value'],
            'unit' => $fact['unit'],
            'phase' => $fact['phase'],
            'pilot_review_required' => (bool) $rule['pilot_review'],
            'dispatch_alert' => (bool) $rule['dispatch_alert'],
            'pilot_reviewed_at' => null,
            'dispatch_acknowledged_at' => null,
            'created_at' => now()->toIso8601String(),
        ];
    }

    private function summary(array $evaluations): array
    {
        return [
            'total' => count($evaluations),
            'warnings' => count(array_filter($evaluations, fn (array $item) => ($item['severity'] ?? null) === 'WARNING')),
            'pilot_review_pending' => count(array_filter($evaluations, fn (array $item) =>
                ($item['pilot_review_required'] ?? false) && blank($item['pilot_reviewed_at'] ?? null)
            )),
            'dispatch_pending' => count(array_filter($evaluations, fn (array $item) =>
                ($item['dispatch_alert'] ?? false) && blank($item['dispatch_acknowledged_at'] ?? null)
            )),
        ];
    }

    private function trimEvaluations(array $evaluations): array
    {
        if (count($evaluations) <= self::MAX_EVALUATIONS) return array_values($evaluations);

        $open = [];
        foreach ($evaluations as $evaluation) {
            if ((($evaluation['pilot_review_required'] ?? false) && blank($evaluation['pilot_reviewed_at'] ?? null))
                || (($evaluation['dispatch_alert'] ?? false) && blank($evaluation['dispatch_acknowledged_at'] ?? null))) {
                $open[(string) ($evaluation['id'] ?? '')] = true;
            }
        }

        $keep = $open;
        $room = max(0, self::MAX_EVALUATIONS - count($keep));
        for ($i = count($evaluations) - 1; $i >= 0 && $room > 0; $i--) {
            $id = (string) ($evaluations[$i]['id'] ?? '');
            if (isset($keep[$id])) continue;
            $keep[$id] = true;
            $room--;
        }

        return array_values(array_filter($evaluations, fn (array $evaluation) =>
            isset($keep[(string) ($evaluation['id'] ?? '')])
        ));
    }

    private function mutateOperation(string $operationId, int $pilotId, callable $callback): mixed
    {
        $path = $this->operationPath($operationId);
        $this->ensureDirectory(dirname($path));
        $handle = fopen($path, 'c+b');
        if (!$handle) throw new RuntimeException('Impossible d’ouvrir le stockage SOP.');

        try {
            if (!flock($handle, LOCK_EX)) throw new RuntimeException('Impossible de verrouiller le stockage SOP.');
            rewind($handle);
            $raw = stream_get_contents($handle);
            $decoded = json_decode($raw ?: '{}', true);
            $state = is_array($decoded) && $decoded !== [] ? $decoded : $this->emptyState($operationId, $pilotId);

            if ((int) ($state['pilot_id'] ?? $pilotId) !== $pilotId) {
                throw new RuntimeException('Les données SOP appartiennent à un autre pilote.');
            }

            $result = $callback($state);

            rewind($handle);
            if (!ftruncate($handle, 0)) throw new RuntimeException('Impossible de réécrire le stockage SOP.');
            $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            if (fwrite($handle, $json) === false) throw new RuntimeException('Impossible d’écrire le stockage SOP.');
            fflush($handle);

            return $result;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function readOperation(string $operationId): ?array
    {
        $path = $this->operationPath($operationId);
        if (!is_file($path)) return null;
        $decoded = $this->readJson($path, null);
        return is_array($decoded) ? $decoded : null;
    }

    private function emptyState(string $operationId, int $pilotId): array
    {
        return [
            'contract_version' => '1.0',
            'operation_id' => $operationId,
            'pilot_id' => $pilotId,
            'facts' => [],
            'evaluations' => [],
            'updated_at' => null,
        ];
    }

    private function readJson(string $path, mixed $fallback): mixed
    {
        $handle = fopen($path, 'rb');
        if (!$handle) return $fallback;
        try {
            if (!flock($handle, LOCK_SH)) return $fallback;
            $raw = stream_get_contents($handle);
            return json_decode($raw ?: 'null', true) ?? $fallback;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function writeJson(string $path, array $payload): void
    {
        $this->ensureDirectory(dirname($path));
        $handle = fopen($path, 'c+b');
        if (!$handle) throw new RuntimeException('Impossible d’ouvrir le stockage SOP.');
        try {
            if (!flock($handle, LOCK_EX)) throw new RuntimeException('Impossible de verrouiller le stockage SOP.');
            rewind($handle);
            if (!ftruncate($handle, 0)) throw new RuntimeException('Impossible de réécrire le stockage SOP.');
            $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            if (fwrite($handle, $json) === false) throw new RuntimeException('Impossible d’écrire le stockage SOP.');
            fflush($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function ensureDirectory(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
            throw new RuntimeException('Impossible de créer le stockage SOP.');
        }
    }

    private function rootPath(): string
    {
        return $this->root ?: storage_path('app/promethee/sop');
    }

    private function rulesPath(): string
    {
        return rtrim($this->rootPath(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'rules.json';
    }

    private function operationsRoot(): string
    {
        return rtrim($this->rootPath(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'operations';
    }

    private function operationPath(string $operationId): string
    {
        return $this->operationsRoot().DIRECTORY_SEPARATOR.hash('sha256', $operationId).'.json';
    }

    private function defaultRules(): array
    {
        return [
            ['id'=>'default_taxi_speed','name'=>'Vitesse de roulage','enabled'=>true,'fact_code'=>'TAXI_SPEED_MAX','operator'=>'gt','threshold'=>30.0,'phases'=>[],'severity'=>'ADVISORY','pilot_review'=>true,'dispatch_alert'=>false,'message'=>'Vitesse maximale au roulage : {value} {unit}.'],
            ['id'=>'default_unstable_1000','name'=>'Approche instable 1000 ft','enabled'=>true,'fact_code'=>'APPROACH_1000_UNSTABLE','operator'=>'exists','threshold'=>null,'phases'=>[],'severity'=>'ADVISORY','pilot_review'=>true,'dispatch_alert'=>false,'message'=>'Approche non stabilisée à 1000 ft : {message}'],
            ['id'=>'default_unstable_500','name'=>'Approche instable 500 ft','enabled'=>true,'fact_code'=>'APPROACH_500_UNSTABLE','operator'=>'exists','threshold'=>null,'phases'=>[],'severity'=>'WARNING','pilot_review'=>true,'dispatch_alert'=>true,'message'=>'Approche non stabilisée à 500 ft : {message}'],
            ['id'=>'default_hard_landing','name'=>'Touchdown vertical élevé','enabled'=>true,'fact_code'=>'TOUCHDOWN','operator'=>'lt','threshold'=>-600.0,'phases'=>[],'severity'=>'WARNING','pilot_review'=>true,'dispatch_alert'=>true,'message'=>'Touchdown observé à {value} {unit}.'],
            ['id'=>'default_excessive_bank','name'=>'Inclinaison élevée','enabled'=>true,'fact_code'=>'EXCESSIVE_BANK','operator'=>'gt','threshold'=>35.0,'phases'=>[],'severity'=>'ADVISORY','pilot_review'=>true,'dispatch_alert'=>false,'message'=>'Inclinaison maximale observée : {value} {unit}.'],
            ['id'=>'default_slew','name'=>'Mode slew','enabled'=>true,'fact_code'=>'SLEW','operator'=>'exists','threshold'=>null,'phases'=>[],'severity'=>'WARNING','pilot_review'=>true,'dispatch_alert'=>true,'message'=>'Mode slew détecté pendant le vol.'],
            ['id'=>'default_sim_rate','name'=>'Simulation accélérée','enabled'=>true,'fact_code'=>'SIM_RATE','operator'=>'gt','threshold'=>1.0,'phases'=>[],'severity'=>'ADVISORY','pilot_review'=>true,'dispatch_alert'=>false,'message'=>'Simulation accélérée jusqu’à x{value}.'],
            ['id'=>'default_fuel_added','name'=>'Ajout de carburant','enabled'=>true,'fact_code'=>'FUEL_ADDED','operator'=>'gt','threshold'=>0.0,'phases'=>[],'severity'=>'WARNING','pilot_review'=>true,'dispatch_alert'=>true,'message'=>'Carburant ajouté pendant le vol : {value} {unit}.'],
            ['id'=>'default_bounce','name'=>'Rebond à l’atterrissage','enabled'=>true,'fact_code'=>'BOUNCE','operator'=>'gt','threshold'=>0.0,'phases'=>[],'severity'=>'ADVISORY','pilot_review'=>true,'dispatch_alert'=>false,'message'=>'{value} rebond(s) observé(s) à l’atterrissage.'],
        ];
    }
}
