<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('airinter-id:about', function () {
    $this->info('Air Inter ID · id.airinter-va.org');
})->purpose('Show the Air Inter ID service identity.');
