<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_season_pricing_adjustments')) {
            Schema::create('promethee_season_pricing_adjustments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('season_id');
                $table->string('scope', 16)->default('global');
                $table->string('flight_id', 36)->nullable();
                $table->string('direction', 16);
                $table->string('mode', 16);
                $table->decimal('value', 12, 4);
                $table->string('notes', 1000)->nullable();
                $table->boolean('active')->default(true);
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['season_id', 'active'], 'prom_season_price_active_idx');
                $table->index(['flight_id', 'active'], 'prom_season_price_flight_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_season_pricing_adjustments');
    }
};
