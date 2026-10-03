<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** Enable the rank-to-subfleet assignments configured in phpVMS. */
    public function up(): void
    {
        if (!Schema::hasTable('settings')) return;

        // phpVMS seeds this setting with its required metadata; retain those
        // fields rather than attempting to create a partial settings record.
        DB::table('settings')->where('id', 'pireps_restrict_aircraft_to_rank')
            ->update(['value' => '1', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Do not loosen aircraft access automatically on rollback.
    }
};
