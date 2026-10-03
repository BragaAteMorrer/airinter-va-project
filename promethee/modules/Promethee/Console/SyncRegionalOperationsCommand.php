<?php

namespace Modules\Promethee\Console;

use Illuminate\Console\Command;
use Modules\Promethee\Services\RegionalOperationsService;

class SyncRegionalOperationsCommand extends Command
{
    protected $signature = 'promethee:regional-operations-sync';
    protected $description = 'Synchronise les bases régionales et les missions automatiques de rapatriement.';

    public function handle(RegionalOperationsService $operations): int
    {
        $result = $operations->sync();
        $this->info(
            'Rapatriements: '.$result['created'].' mission(s) créée(s), '.
            $result['returned'].' retour(s) automatique(s), '.
            $result['cleared'].' état(s) régularisé(s).'
        );

        return self::SUCCESS;
    }
}
