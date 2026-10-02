<?php

namespace Modules\Promethee\Listeners;

use App\Events\PirepAccepted;
use Modules\Promethee\Services\EngineMaintenanceService;
use Throwable;

class EngineMaintenanceEventListener
{
    public function onPirepAccepted(PirepAccepted $event): void
    {
        try {
            app(EngineMaintenanceService::class)->recordAcceptedPirep($event->pirep);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
