<?php

namespace Modules\Promethee\Console;

use App\Models\Bid;
use App\Models\Pirep;
use Illuminate\Console\Command;
use Modules\Promethee\Services\HermesPirepLifecycleService;

final class RepairHermesPirepCommand extends Command
{
    protected $signature = 'promethee:hermes-pirep-repair
        {operation : Hermès operation id (for example op_123)}
        {--pirep= : Optional PIREP id safety check}
        {--apply : Apply the repair; without this flag the command is dry-run only}';

    protected $description = 'Diagnose a legacy zero-flight Hermès PIREP and repair it only with explicit --apply.';

    public function handle(HermesPirepLifecycleService $lifecycle): int
    {
        $operation = trim((string) $this->argument('operation'));
        if (!preg_match('/^op_(.+)$/', $operation, $matches)) {
            $this->error('Invalid operation id. Expected op_<bid_id>.');
            return self::FAILURE;
        }

        $bid = Bid::query()->with(['flight', 'aircraft'])->find($matches[1]);
        if (!$bid) {
            $this->error('Operation not found: '.$operation);
            return self::FAILURE;
        }

        $query = Pirep::query()
            ->where('user_id', $bid->user_id)
            ->where('flight_id', $bid->flight_id)
            ->where('source_name', 'Hermes ACARS ['.$operation.']')
            ->latest('created_at');

        if ($this->option('pirep')) {
            $query->where('id', (string) $this->option('pirep'));
        }

        $pirep = $query->first();
        if (!$pirep) {
            $this->error('No correlated Hermès PIREP found for '.$operation.'.');
            return self::FAILURE;
        }

        $diagnosis = $lifecycle->diagnose($pirep);
        $this->table(
            ['Field', 'Value'],
            collect($diagnosis)->map(fn ($value, $key) => [
                $key,
                is_bool($value) ? ($value ? 'true' : 'false') : ($value ?? 'null'),
            ])->values()->all()
        );

        if (!$diagnosis['legacy_ghost']) {
            $this->warn('No repair applied: this PIREP is not a proven zero-flight Hermès ghost.');
            return self::SUCCESS;
        }

        if (!$this->option('apply')) {
            $this->warn('DRY RUN: no data changed. Re-run with --apply only after reviewing this diagnosis.');
            return self::SUCCESS;
        }

        $result = $lifecycle->repairLegacyGhost($pirep, $bid);
        $this->info('Repair applied to '.$result['pirep_id'].' for '.$result['operation_id'].'.');

        return self::SUCCESS;
    }
}
