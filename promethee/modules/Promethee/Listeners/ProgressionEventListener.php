<?php

namespace Modules\Promethee\Listeners;

use App\Events\PirepAccepted;
use App\Events\UserStatsChanged;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Modules\Promethee\Services\ProgressionService;
use Throwable;

/**
 * Keeps Promethee progression reactive without coupling phpVMS core to the module.
 *
 * Flight-related criteria are evaluated immediately when phpVMS confirms the
 * pilot's new state. The scheduled fallback still covers time-based criteria
 * such as seniority when no application event occurs.
 */
class ProgressionEventListener
{
    public function __construct(private readonly ProgressionService $progression) {}

    public function onPirepAccepted(PirepAccepted $event): void
    {
        $this->recalculate($event->pirep->user, 'event:pirep_accepted');
    }

    public function onUserStatsChanged(UserStatsChanged $event): void
    {
        $this->recalculate($event->user, 'event:user_stats_changed');
    }

    private function recalculate(?User $user, string $origin): void
    {
        if (!$user || !$this->tablesReady()) {
            return;
        }

        try {
            $user->refresh();
            $this->progression->recalculate($user, $origin);
        } catch (Throwable $exception) {
            // Progression must never make a PIREP acceptance or a phpVMS stats
            // update fail. The scheduled safety net will retry automatically.
            report($exception);
        }
    }

    private function tablesReady(): bool
    {
        return Schema::hasTable('promethee_badge_rules')
            && Schema::hasTable('promethee_rank_rules')
            && Schema::hasTable('promethee_progression_history');
    }
}
