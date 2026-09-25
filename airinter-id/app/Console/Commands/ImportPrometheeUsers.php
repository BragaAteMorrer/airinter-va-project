<?php

namespace App\Console\Commands;

use App\Services\PrometheeUserImporter;
use Illuminate\Console\Command;

class ImportPrometheeUsers extends Command
{
    protected $signature = 'airinter-id:import-promethee
        {--sync-passwords : Also overwrite hashes for identities already imported}
        {--force : Required when APP_ENV=production}';

    protected $description = 'Import/link existing phpVMS users into Air Inter ID without moving operational data.';

    public function handle(PrometheeUserImporter $importer): int
    {
        if (app()->isProduction() && !$this->option('force')) {
            $this->error('Production import requires --force.');
            return self::FAILURE;
        }

        if (!$this->confirm('Import Prométhée identities into Air Inter ID?', true)) {
            return self::SUCCESS;
        }

        $stats = $importer->import(
            syncPasswords: (bool) $this->option('sync-passwords'),
            progress: fn ($legacy, $user) => $this->line(sprintf(
                '#%s -> %s (%s)',
                $legacy->id,
                $user->subject,
                $user->email,
            )),
        );

        $this->newLine();
        $this->table(
            ['Created', 'Updated', 'New links', 'Skipped'],
            [[$stats['created'], $stats['updated'], $stats['linked'], $stats['skipped']]],
        );

        return self::SUCCESS;
    }
}
