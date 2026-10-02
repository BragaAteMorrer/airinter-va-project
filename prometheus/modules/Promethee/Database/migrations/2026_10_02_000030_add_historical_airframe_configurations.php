<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_aircraft_variants')) {
            Schema::create('promethee_aircraft_variants', function (Blueprint $table) {
                $table->id();
                $table->string('aircraft_type_key', 32)->index();
                $table->string('name', 120);
                $table->string('short_name', 80)->nullable();
                $table->string('icao_type', 12)->nullable()->index();
                $table->string('manufacturer_variant', 120)->nullable();
                $table->string('operator_variant', 120)->nullable();
                $table->text('description')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_until')->nullable();
                $table->string('phase', 80)->nullable();
                $table->string('engine_manufacturer', 120)->nullable();
                $table->string('engine_model', 120)->nullable();
                $table->string('engine_variant', 120)->nullable();
                $table->unsignedTinyInteger('engine_count')->nullable();
                $table->string('engine_simbrief_label', 120)->nullable();
                $table->unsignedSmallInteger('max_pax')->nullable();
                $table->string('seat_configuration', 120)->nullable();
                $table->unsignedInteger('oew')->nullable();
                $table->unsignedInteger('mzfw')->nullable();
                $table->unsignedInteger('mtow')->nullable();
                $table->unsignedInteger('mlw')->nullable();
                $table->unsignedInteger('max_fuel')->nullable();
                $table->unsignedInteger('max_cargo')->nullable();
                $table->unsignedSmallInteger('cruise_speed')->nullable();
                $table->decimal('cruise_mach', 4, 3)->nullable();
                $table->unsignedInteger('ceiling')->nullable();
                $table->unsignedInteger('range_nm')->nullable();
                $table->string('equipment', 120)->nullable();
                $table->string('transponder', 60)->nullable();
                $table->string('pbn', 120)->nullable();
                $table->string('simbrief_strategy', 24)->nullable();
                $table->string('simbrief_type', 32)->nullable();
                $table->string('simbrief_internal_id', 120)->nullable();
                $table->string('simbrief_proxy_type', 32)->nullable();
                $table->decimal('fuel_factor', 7, 3)->nullable();
                $table->string('climb_profile', 120)->nullable();
                $table->string('cruise_profile', 120)->nullable();
                $table->string('descent_profile', 120)->nullable();
                $table->boolean('active')->default(true)->index();
                $table->string('source', 255)->nullable();
                $table->text('source_url')->nullable();
                $table->text('notes')->nullable();
                $table->string('historical_confidence', 32)->default('va_configuration');
                $table->json('overrides')->nullable();
                $table->timestamps();
                $table->index(['aircraft_type_key', 'active'], 'prom_hist_variant_type_active_idx');
            });
        }

        if (!Schema::hasTable('promethee_airframe_configurations')) {
            Schema::create('promethee_airframe_configurations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_id')->nullable()->index();
                $table->string('code', 80)->unique();
                $table->string('name', 160);
                $table->text('description')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_until')->nullable();
                $table->string('phase', 80)->nullable();
                $table->string('kind', 32)->default('va_operational');
                $table->unsignedSmallInteger('max_pax')->nullable();
                $table->string('seat_configuration', 120)->nullable();
                $table->unsignedInteger('oew')->nullable();
                $table->unsignedInteger('mzfw')->nullable();
                $table->unsignedInteger('mtow')->nullable();
                $table->unsignedInteger('mlw')->nullable();
                $table->unsignedInteger('max_fuel')->nullable();
                $table->unsignedInteger('max_cargo')->nullable();
                $table->string('engine_manufacturer', 120)->nullable();
                $table->string('engine_model', 120)->nullable();
                $table->string('engine_variant', 120)->nullable();
                $table->unsignedTinyInteger('engine_count')->nullable();
                $table->string('engine_simbrief_label', 120)->nullable();
                $table->unsignedSmallInteger('cruise_speed')->nullable();
                $table->decimal('cruise_mach', 4, 3)->nullable();
                $table->unsignedInteger('ceiling')->nullable();
                $table->unsignedInteger('range_nm')->nullable();
                $table->string('equipment', 120)->nullable();
                $table->string('transponder', 60)->nullable();
                $table->string('pbn', 120)->nullable();
                $table->string('simbrief_strategy', 24)->nullable();
                $table->string('simbrief_type', 32)->nullable();
                $table->string('simbrief_internal_id', 120)->nullable();
                $table->string('simbrief_proxy_type', 32)->nullable();
                $table->decimal('fuel_factor', 7, 3)->nullable();
                $table->string('climb_profile', 120)->nullable();
                $table->string('cruise_profile', 120)->nullable();
                $table->string('descent_profile', 120)->nullable();
                $table->boolean('active')->default(true)->index();
                $table->string('source', 255)->nullable();
                $table->text('source_url')->nullable();
                $table->text('notes')->nullable();
                $table->string('historical_confidence', 32)->default('va_configuration');
                $table->json('overrides')->nullable();
                $table->timestamps();
                $table->index(['variant_id', 'valid_from', 'valid_until'], 'prom_hist_config_period_idx');
            });
        }

        if (!Schema::hasTable('promethee_aircraft_configuration_assignments')) {
            Schema::create('promethee_aircraft_configuration_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('aircraft_id')->index();
                $table->unsignedBigInteger('variant_id')->nullable()->index();
                $table->unsignedBigInteger('configuration_id')->nullable()->index();
                $table->date('valid_from')->nullable();
                $table->date('valid_until')->nullable();
                $table->boolean('active_for_va')->default(false)->index();
                $table->json('overrides')->nullable();
                $table->string('source', 255)->nullable();
                $table->text('source_url')->nullable();
                $table->text('notes')->nullable();
                $table->string('historical_confidence', 32)->default('va_configuration');
                $table->timestamps();
                $table->index(['aircraft_id', 'valid_from', 'valid_until'], 'prom_hist_assign_period_idx');
            });
        }

        if (!Schema::hasTable('promethee_aircraft_simulator_profiles')) {
            Schema::create('promethee_aircraft_simulator_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_id')->nullable()->index();
                $table->unsignedBigInteger('configuration_id')->nullable()->index();
                $table->string('simulator', 24);
                $table->string('addon_name', 160);
                $table->string('addon_version', 80)->nullable();
                $table->string('aircraft_identifier', 160)->nullable();
                $table->string('simbrief_airframe', 120)->nullable();
                $table->string('telemetry_profile', 120)->nullable();
                $table->boolean('active')->default(true)->index();
                $table->timestamps();
                $table->index(['simulator', 'active'], 'prom_hist_sim_profile_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_aircraft_simulator_profiles');
        Schema::dropIfExists('promethee_aircraft_configuration_assignments');
        Schema::dropIfExists('promethee_airframe_configurations');
        Schema::dropIfExists('promethee_aircraft_variants');
    }
};
