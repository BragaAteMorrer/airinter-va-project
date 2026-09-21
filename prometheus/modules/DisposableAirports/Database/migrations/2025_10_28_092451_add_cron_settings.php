<?php

use App\Contracts\Migration;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        if (Schema::hasTable('disposable_settings')) {
            // Setting for CRON usage
            DB::table('disposable_settings')->updateOrInsert(
                [
                    'key' => 'dairports.cron',
                ],
                [
                    'group'      => 'General',
                    'name'       => 'Auto Updates',
                    'field_type' => 'check',
                    'default'    => 'false',
                    'order'      => '1001',
                ]
            );
        }
    }
};
