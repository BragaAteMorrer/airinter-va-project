<?php

namespace Modules\Promethee\Listeners;

use App\Events\PirepAccepted;
use App\Events\PirepRejected;
use Modules\Promethee\Services\AirframeMaintenanceService;
use Throwable;

class AirframeMaintenanceEventListener
{
    public function onPirepAccepted(PirepAccepted $event): void
    {
        try {
            app(AirframeMaintenanceService::class)->recordAcceptedPirep($event->pirep);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function onPirepRejected(PirepRejected $event): void
    {
        try {
            app(AirframeMaintenanceService::class)->rollbackPirep($event->pirep);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
