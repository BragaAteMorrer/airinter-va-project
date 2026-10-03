<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Promethee provides the required public pages natively. These optional
     * legacy modules must remain disabled: their cached providers can point to
     * files that are not included in a current deployment.
     */
    public function up(): void
    {
        if (!Schema::hasTable('modules')) return;

        DB::table('modules')->whereIn('name', [
            'DisposableAirports', 'DisposableBasic', 'DisposableSpecial', 'SPTransfer',
        ])->update(['enabled' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Module activation is an explicit administrator decision; do not
        // automatically re-enable legacy code on rollback.
    }
};
