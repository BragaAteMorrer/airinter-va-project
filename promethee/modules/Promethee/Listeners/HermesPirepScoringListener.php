<?php

namespace Modules\Promethee\Listeners;

use App\Events\PirepFiled;
use Modules\Promethee\Services\AirframeMaintenanceService;
use Modules\Promethee\Services\LegacyPirepScoringService;

final class HermesPirepScoringListener
{
    public function __construct(
        private readonly LegacyPirepScoringService $scoring,
        private readonly AirframeMaintenanceService $airframeMaintenance
    ) {}

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
            return;
        }

        $runwayOverrun = collect($result['items'] ?? [])
            ->first(fn (array $item) => ($item['rule_id'] ?? null) === 'RUNWAY_OVERRUN');

        $occurrence = collect($runwayOverrun['events'] ?? [])->first();
        if (!is_array($occurrence)) {
            try {
                $occurrence = $this->scoring->detectedRunwayOverrun($event->pirep);
            } catch (\Throwable $exception) {
                logger()->error('hermes_runway_overrun_detection_failed', [
                    'pirep_id' => $event->pirep->id,
                    'aircraft_id' => $event->pirep->aircraft_id,
                    'error' => $exception->getMessage(),
                ]);
                return;
            }
        }

        if (!is_array($occurrence)) return;

        try {
            $hold = $this->airframeMaintenance->placeRunwayOverrunHold(
                $event->pirep,
                is_numeric($occurrence['value'] ?? null) ? (float) $occurrence['value'] : null,
                filled($occurrence['runway'] ?? null) ? (string) $occurrence['runway'] : null,
                filled($occurrence['at'] ?? null) ? (string) $occurrence['at'] : null
            );

            logger()->warning('hermes_runway_overrun_maintenance_hold', [
                'pirep_id' => $event->pirep->id,
                'aircraft_id' => $event->pirep->aircraft_id,
                'runway' => $occurrence['runway'] ?? null,
                'distance_metres' => $occurrence['value'] ?? null,
                'hold' => $hold,
            ]);
        } catch (\Throwable $exception) {
            // The PIREP/scoring remains valid, but a failed mandatory hold must
            // be visible in logs so maintenance can intervene manually.
            logger()->error('hermes_runway_overrun_maintenance_hold_failed', [
                'pirep_id' => $event->pirep->id,
                'aircraft_id' => $event->pirep->aircraft_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
