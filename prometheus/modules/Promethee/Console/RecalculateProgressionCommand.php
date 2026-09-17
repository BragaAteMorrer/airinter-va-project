<?php
namespace Modules\Promethee\Console;
use Illuminate\Console\Command;
use Modules\Promethee\Services\ProgressionService;
class RecalculateProgressionCommand extends Command {
    protected $signature = 'promethee:progression-recalculate {--user=}';
    protected $description = 'Recalculate Prométhée badge and rank rules.';
    public function handle(ProgressionService $progression): int { $result=$progression->recalculate(); $this->info("{$result['awards']} badges, {$result['promotions']} promotions."); return self::SUCCESS; }
}
