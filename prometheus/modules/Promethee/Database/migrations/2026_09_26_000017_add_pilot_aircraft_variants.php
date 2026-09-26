<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
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
        } else {
            $this->ensureIndex(
                'promethee_pilot_aircraft_variants',
                fn (Blueprint $table) => $table->index(['user_id', 'preferred'], 'prom_pilot_pref_idx')
            );
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
        } else {
            $this->ensureIndex(
                'promethee_operation_aircraft_variants',
                fn (Blueprint $table) => $table->index(['user_id', 'variant_id'], 'prom_op_user_variant_idx')
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_operation_aircraft_variants');
        Schema::dropIfExists('promethee_pilot_aircraft_variants');
    }

    /**
     * Shared hosting often denies SELECT on information_schema. MySQL DDL also
     * isn't transactional, so a failed migration may leave the table behind.
     * Re-attempt the short-named index and ignore only MySQL's duplicate-index
     * error (1061); every other SQL error is still surfaced.
     */
    private function ensureIndex(string $table, callable $definition): void
    {
        try {
            Schema::table($table, $definition);
        } catch (QueryException $exception) {
            $mysqlCode = (int) ($exception->errorInfo[1] ?? 0);

            if ($mysqlCode !== 1061) {
                throw $exception;
            }
        }
    }
};
