<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_route_pricing_profiles')) {
            Schema::create('promethee_route_pricing_profiles', function (Blueprint $table) {
                $table->id();
                $table->string('flight_id', 36)->unique();
                $table->string('network_class', 24)->default('principal');
                $table->string('notes', 500)->nullable();
                $table->timestamps();
                $table->index('network_class');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_route_pricing_profiles');
    }
};
