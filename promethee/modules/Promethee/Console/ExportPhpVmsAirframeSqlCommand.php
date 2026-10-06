<?php

namespace Modules\Promethee\Console;

use Illuminate\Console\Command;
use Modules\Promethee\Services\PhpVmsAirframeImportService;
use Modules\Promethee\Services\PhpVmsAirframeSqlSnapshotService;
use Throwable;

final class ExportPhpVmsAirframeSqlCommand extends Command
{
    protected $signature = 'promethee:airframes-export-sql
        {--path= : Chemin absolu du fichier SQL à générer}
        {--sync-local : Resynchroniser aussi les Airframes locaux via la logique de la PR #246}';

    protected $description = 'Fige les données flotte phpVMS et les Airframes Prométhée associés dans un snapshot SQL réimportable.';

    public function handle(
        PhpVmsAirframeImportService $import,
        PhpVmsAirframeSqlSnapshotService $snapshot
    ): int {
        try {
            if ($this->option('sync-local')) {
                $this->info('Synchronisation des tables locales phpVMS → Airframes…');
                $result = $import->import();
                $this->line(sprintf(
                    '%d sous-flotte(s), %d appareil(s), %d affectation(s) créée(s), %d mise(s) à jour.',
                    $result['subfleets'],
                    $result['aircraft'],
                    $result['assignments_created'],
                    $result['assignments_updated']
                ));
            }

            $this->info('Lecture de la flotte depuis la connexion Prometheus…');

            $path = $this->option('path');
            $export = $snapshot->export(is_string($path) && $path !== '' ? $path : null);

            $this->newLine();
            $this->info('Snapshot SQL généré : '.$export['path']);
            $this->line(number_format($export['bytes'], 0, ',', ' ').' octets');

            foreach ($export['counts'] as $table => $count) {
                $this->line(sprintf(' - %s : %d', $table, $count));
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
