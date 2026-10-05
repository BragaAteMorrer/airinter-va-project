<?php

use App\Contracts\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('promethee_airframe_maintenance')) return;

        Schema::table('promethee_airframe_maintenance', function (Blueprint $table) {
            if (!Schema::hasColumn('promethee_airframe_maintenance', 'safety_hold_reason')) {
                $table->string('safety_hold_reason', 255)->nullable()->after('active_started_by');
            }
            if (!Schema::hasColumn('promethee_airframe_maintenance', 'safety_hold_at')) {
                $table->timestamp('safety_hold_at')->nullable()->after('safety_hold_reason');
            }
            if (!Schema::hasColumn('promethee_airframe_maintenance', 'safety_hold_pirep_id')) {
                $table->string('safety_hold_pirep_id', 36)->nullable()->after('safety_hold_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('promethee_airframe_maintenance')) return;

        $columns = array_values(array_filter([
            Schema::hasColumn('promethee_airframe_maintenance', 'safety_hold_reason') ? 'safety_hold_reason' : null,
            Schema::hasColumn('promethee_airframe_maintenance', 'safety_hold_at') ? 'safety_hold_at' : null,
            Schema::hasColumn('promethee_airframe_maintenance', 'safety_hold_pirep_id') ? 'safety_hold_pirep_id' : null,
        ]));

        if ($columns) {
            Schema::table('promethee_airframe_maintenance', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
