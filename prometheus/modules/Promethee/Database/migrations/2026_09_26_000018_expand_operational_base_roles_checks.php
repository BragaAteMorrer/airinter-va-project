<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_operational_bases')) return;

        Schema::table('promethee_operational_bases', function (Blueprint $table) {
            if (!Schema::hasColumn('promethee_operational_bases', 'is_hub')) {
                $table->boolean('is_hub')->default(false)->after('kind');
            }
            if (!Schema::hasColumn('promethee_operational_bases', 'is_regional_platform')) {
                $table->boolean('is_regional_platform')->default(false)->after('is_hub');
            }
            if (!Schema::hasColumn('promethee_operational_bases', 'is_technical_stop')) {
                $table->boolean('is_technical_stop')->default(false)->after('is_regional_platform');
            }
            if (!Schema::hasColumn('promethee_operational_bases', 'check_a')) {
                $table->boolean('check_a')->default(true)->after('is_technical_stop');
            }
            if (!Schema::hasColumn('promethee_operational_bases', 'check_b')) {
                $table->boolean('check_b')->default(false)->after('check_a');
            }
            if (!Schema::hasColumn('promethee_operational_bases', 'check_c')) {
                $table->boolean('check_c')->default(false)->after('check_b');
            }
        });

        DB::table('promethee_operational_bases')->where('kind', 'hub')->update([
            'is_hub' => true,
            'is_regional_platform' => false,
            'check_a' => true,
            'check_b' => true,
            'check_c' => true,
            'small_maintenance' => true,
            'heavy_maintenance' => true,
        ]);

        DB::table('promethee_operational_bases')->where('kind', 'regional')->update([
            'is_regional_platform' => true,
            'check_a' => true,
        ]);

        // CDG is the second full maintenance station: A/B/C without becoming
        // the company's principal hub. Orly remains the sole phpVMS hub.
        DB::table('promethee_operational_bases')->where('airport_id', 'LFPG')->update([
            'is_regional_platform' => true,
            'check_a' => true,
            'check_b' => true,
            'check_c' => true,
            'small_maintenance' => true,
            'heavy_maintenance' => true,
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('promethee_operational_bases')) return;

        Schema::table('promethee_operational_bases', function (Blueprint $table) {
            foreach (['is_hub','is_regional_platform','is_technical_stop','check_a','check_b','check_c'] as $column) {
                if (Schema::hasColumn('promethee_operational_bases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
