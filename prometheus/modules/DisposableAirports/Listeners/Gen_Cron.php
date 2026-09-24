<?php

namespace Modules\DisposableAirports\Listeners;

use App\Contracts\Listener;
use App\Events\CronMonthly;
use App\Events\CronNightly;
use App\Events\CronWeekly;
use Illuminate\Support\Facades\Log;
use Modules\DisposableAirports\Services\DA_AirportServices;

class Gen_Cron extends Listener
{
    public static $callbacks = [
        CronNightly::class => 'cron_nightly',
        CronWeekly::class  => 'cron_weekly',
        CronMonthly::class => 'cron_monthly',
    ];

    public function cron_nightly()
    {
        // $this->DA_WriteToLog('Nightly test');
    }

    public function cron_weekly()
    {
        // $this->DA_WriteToLog('Weekly test');
        if (DA_Setting('dairports.cron', false)) {
            $DA_AirportSVC = app(DA_AirportServices::class);
            $DA_AirportSVC->UpdateAirports();
        }
    }

    public function cron_monthly()
    {
        // $this->DA_WriteToLog('Monthly test');
    }

    // Test Method
    public function DA_WriteToLog($text = null)
    {
        Log::debug('Disposable Airports | ' . $text);
    }
}
