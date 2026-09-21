<?php

namespace Modules\DisposableAirports\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\DisposableAirports\Listeners\Gen_Cron;

class DA_EventProvider extends ServiceProvider
{
    // Subscribe to multiple events
    protected $subscribe =
    [
        Gen_Cron::class,
    ];

    // Register Module Events
    public function boot()
    {
        parent::boot();
    }
}
