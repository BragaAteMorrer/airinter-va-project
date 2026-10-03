<?php

namespace Modules\Promethee\Console;

use Illuminate\Console\Command;
use Modules\Promethee\Services\FleetRotationService;

class RotateFleetCommand extends Command
{
    protected $signature = 'promethee:fleet-rotate {--force : Ignore la fréquence et l’état activé pour un lancement manuel}';
    protected $description = 'Fait tourner automatiquement les appareils éligibles entre bases sans perturber les rapatriements.';

    public function handle(FleetRotationService $rotation): int
    {
        $result = $rotation->rotate((bool) $this->option('force'));

        $this->info(
            'Rotation flotte : '.$result['pairs'].' permutation(s), '
            .$result['aircraft'].' appareil(s), '
            .$result['maintenance_priority'].' priorité(s) moteur, '
            .$result['airframe_maintenance_priority'].' priorité(s) cellule. '
            .'['.$result['reason'].']'
        );

        return self::SUCCESS;
    }
}
