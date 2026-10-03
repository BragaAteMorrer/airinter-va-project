<?php
namespace Modules\Promethee\Console;

use App\Models\User;
use Illuminate\Console\Command;
use Modules\Promethee\Services\ProgressionService;

class RecalculateProgressionCommand extends Command
{
    protected $signature = 'promethee:progression-recalculate {--user=}';
    protected $description = 'Recalculate Prométhée badge and rank rules.';

    public function handle(ProgressionService $progression): int
    {
        $userId = $this->option('user');
        $user = $userId ? User::find($userId) : null;

        if ($userId && !$user) {
            $this->error('Pilot not found: '.$userId);
            return self::FAILURE;
        }

        $result = $progression->recalculate($user, $user ? 'cli:user' : 'scheduled');
        $this->info("{$result['awards']} badges, {$result['promotions']} promotions.");

        return self::SUCCESS;
    }
}
