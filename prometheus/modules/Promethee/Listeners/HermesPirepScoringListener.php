<?php

namespace Modules\Promethee\Listeners;

use App\Events\PirepFiled;
use Modules\Promethee\Services\LegacyPirepScoringService;

final class HermesPirepScoringListener
{
    public function __construct(private readonly LegacyPirepScoringService $scoring) {}

    public function onPirepFiled(PirepFiled $event): void
    {
        if (!str_starts_with((string) $event->pirep->source_name, 'Hermes ACARS [op_')) return;

        try {
            $result = $this->scoring->apply($event->pirep);
            logger()->info('hermes_pirep_scored', [
                'pirep_id' => $event->pirep->id,
                'score' => $result['score'] ?? null,
                'penalty_total' => $result['penalty_total'] ?? null,
            ]);
        } catch (\Throwable $exception) {
            // Scoring must never make a valid flight impossible to file.
            logger()->error('hermes_pirep_scoring_failed', [
                'pirep_id' => $event->pirep->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
