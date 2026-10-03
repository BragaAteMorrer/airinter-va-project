<?php

namespace Modules\Promethee\Console;

use Illuminate\Console\Command;
use Modules\Promethee\Services\AirframeMaintenanceService;

class ProcessAirframeMaintenanceCommand extends Command
{
    protected $signature = 'promethee:airframe-maintenance-process';
    protected $description = 'Termine les checks cellule A/B/C arrivés à échéance et remet les appareils en service.';

    public function handle(AirframeMaintenanceService $maintenance): int
    {
        $completed = $maintenance->releaseCompletedChecks();
        $this->info($completed.' check(s) cellule terminé(s).');

        return self::SUCCESS;
    }
}
