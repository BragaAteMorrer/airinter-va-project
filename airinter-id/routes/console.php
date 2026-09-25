<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('airinter-id:about', function () {
    $this->info('Air Inter ID · identity.airinter-va.org');
})->purpose('Show the Air Inter ID service identity.');
