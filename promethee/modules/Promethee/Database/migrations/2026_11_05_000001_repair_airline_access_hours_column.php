<?php

use App\Contracts\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // The original regional-operations migration creates this table only
        // when absent. Earlier/partial installations can have the table but
        // lack the column expected by CompanyAccessService.
        if (!Schema::hasTable('promethee_airline_access_rules')) {
            Schema::create('promethee_airline_access_rules', function (Blueprint $table) {
                $table->unsignedInteger('airline_id')->primary();
                $table->unsignedInteger('min_flight_hours')->default(0);
                $table->timestamps();
            });
            return;
        }

        if (!Schema::hasColumn('promethee_airline_access_rules', 'min_flight_hours')) {
            Schema::table('promethee_airline_access_rules', function (Blueprint $table) {
                $table->unsignedInteger('min_flight_hours')->default(0);
            });
        }
    }

    public function down(): void
    {
        // Intentional no-op: this is a repair migration. Rolling it back must
        // not delete the airline access policies or a pre-existing table.
    }
};
