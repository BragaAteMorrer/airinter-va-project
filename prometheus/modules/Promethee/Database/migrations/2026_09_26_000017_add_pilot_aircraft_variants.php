<?php

use Illuminate\Database\Migrations\Migration;
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
                $table->primary(['user_id', 'variant_id']);
                $table->index(['user_id', 'preferred']);
            });
        }

        if (!Schema::hasTable('promethee_operation_aircraft_variants')) {
            Schema::create('promethee_operation_aircraft_variants', function (Blueprint $table) {
                $table->unsignedInteger('bid_id')->primary();
                $table->unsignedInteger('user_id');
                $table->string('variant_id', 80);
                $table->string('simulator', 20)->default('msfs2020');
                $table->timestamps();
                $table->index(['user_id', 'variant_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_operation_aircraft_variants');
        Schema::dropIfExists('promethee_pilot_aircraft_variants');
    }
};
