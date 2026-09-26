<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_pilot_aircraft_variants')) {
            Schema::create('promethee_pilot_aircraft_variants', function (Blueprint $table) {
                $table->unsignedInteger('user_id');
                $table->string('variant_id', 80);
                $table->boolean('enabled')->default(true);
                $table->boolean('preferred')->default(false);
                $table->timestamps();
                $table->primary(['user_id', 'variant_id'], 'prom_pilot_variant_pk');
                $table->index(['user_id', 'preferred'], 'prom_pilot_pref_idx');
            });
        } elseif (!$this->indexExists('promethee_pilot_aircraft_variants', 'prom_pilot_pref_idx')) {
            // MySQL DDL is not transactional. A previous run may have created
            // the table and then failed while Laravel was adding its generated,
            // >64-character index name. Repair that partial migration in place.
            Schema::table('promethee_pilot_aircraft_variants', function (Blueprint $table) {
                $table->index(['user_id', 'preferred'], 'prom_pilot_pref_idx');
            });
        }

        if (!Schema::hasTable('promethee_operation_aircraft_variants')) {
            Schema::create('promethee_operation_aircraft_variants', function (Blueprint $table) {
                $table->unsignedInteger('bid_id');
                $table->unsignedInteger('user_id');
                $table->string('variant_id', 80);
                $table->string('simulator', 20)->default('msfs2020');
                $table->timestamps();
                $table->primary('bid_id', 'prom_op_variant_pk');
                $table->index(['user_id', 'variant_id'], 'prom_op_user_variant_idx');
            });
        } elseif (!$this->indexExists('promethee_operation_aircraft_variants', 'prom_op_user_variant_idx')) {
            Schema::table('promethee_operation_aircraft_variants', function (Blueprint $table) {
                $table->index(['user_id', 'variant_id'], 'prom_op_user_variant_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_operation_aircraft_variants');
        Schema::dropIfExists('promethee_pilot_aircraft_variants');
    }

    private function indexExists(string $table, string $index): bool
    {
        $physicalTable = DB::getTablePrefix().$table;

        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $physicalTable)
            ->where('index_name', $index)
            ->exists();
    }
};
