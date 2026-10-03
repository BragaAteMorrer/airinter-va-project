<?php
namespace Modules\Promethee\Console;
use Illuminate\Console\Command;
use Modules\Promethee\Services\BulletinService;
class BulletinCommand extends Command
{
    protected $signature = 'promethee:bulletin {month? : Mois AAAA-MM, mois précédent par défaut}';
    protected $description = 'Produit les statistiques anonymisées du bulletin mensuel.';
    public function handle(BulletinService $service): int
    {
        $month=$this->argument('month') ?? now('Europe/Paris')->subMonthNoOverflow()->format('Y-m');
        if (!preg_match('/^20[0-9]{2}-(0[1-9]|1[0-2])$/',$month)) { $this->error('Mois invalide.'); return self::FAILURE; }
        $report=$service->save($month);
        $this->info($month.' : '.$report['flights'].' PIREP acceptés. Aucun envoi de message effectué.');
        return self::SUCCESS;
    }
}
