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
                    'key' => 'dairports.update_only',
                ],
                [
                    'group'      => 'General',
                    'name'       => 'Update Only',
                    'field_type' => 'check',
                    'default'    => 'true',
                    'order'      => '1002',
                ]
            );
        }
    }
};
