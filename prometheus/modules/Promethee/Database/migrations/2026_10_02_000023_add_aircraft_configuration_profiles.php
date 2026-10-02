<?php

use App\Contracts\Model as BaseModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_aircraft_type_profiles')) {
            Schema::create('promethee_aircraft_type_profiles', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type_key', 32)->unique();
                $table->string('name', 120);
                $table->longText('data')->nullable();
                $table->string('simbrief_strategy', 24)->nullable();
                $table->string('simbrief_type', 80)->nullable();
                $table->string('simbrief_internal_id', 120)->nullable();
                $table->string('simbrief_proxy_type', 80)->nullable();
                $table->string('source', 255)->nullable();
                $table->string('source_url', 500)->nullable();
                $table->text('notes')->nullable();
                $table->string('historical_confidence', 24)->default('VA_configuration');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('promethee_aircraft_historical_variants')) {
            Schema::create('promethee_aircraft_historical_variants', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type_key', 32);
                $table->string('code', 64);
                $table->string('name', 120);
                $table->string('short_name', 80)->nullable();
                $table->string('icao_type', 16)->nullable();
                $table->string('manufacturer_variant', 120)->nullable();
                $table->string('operator_variant', 120)->nullable();
                $table->text('description')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_until')->nullable();
                $table->longText('data')->nullable();
                $table->string('simbrief_strategy', 24)->nullable();
                $table->string('simbrief_type', 80)->nullable();
                $table->string('simbrief_internal_id', 120)->nullable();
                $table->string('simbrief_proxy_type', 80)->nullable();
                $table->boolean('active')->default(true);
                $table->string('source', 255)->nullable();
                $table->string('source_url', 500)->nullable();
                $table->text('notes')->nullable();
                $table->string('historical_confidence', 24)->default('VA_configuration');
                $table->timestamps();
                $table->unique(['type_key', 'code'], 'prom_air_variant_code_uq');
                $table->index(['type_key', 'active'], 'prom_air_variant_type_idx');
            });
        }

        if (!Schema::hasTable('promethee_airframe_configurations')) {
            Schema::create('promethee_airframe_configurations', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('variant_id');
                $table->string('code', 64);
                $table->string('name', 120);
                $table->string('configuration_kind', 32)->default('VA_operational');
                $table->string('phase', 80)->nullable();
                $table->text('description')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_until')->nullable();
                $table->longText('data')->nullable();
                $table->string('simbrief_strategy', 24)->nullable();
                $table->string('simbrief_type', 80)->nullable();
                $table->string('simbrief_internal_id', 120)->nullable();
                $table->string('simbrief_proxy_type', 80)->nullable();
                $table->boolean('active')->default(true);
                $table->string('source', 255)->nullable();
                $table->string('source_url', 500)->nullable();
                $table->text('notes')->nullable();
                $table->string('historical_confidence', 24)->default('VA_configuration');
                $table->timestamps();
                $table->unique(['variant_id', 'code'], 'prom_air_config_code_uq');
                $table->index(['variant_id', 'active'], 'prom_air_config_variant_idx');
            });
        }

        if (!Schema::hasTable('promethee_aircraft_configuration_assignments')) {
            Schema::create('promethee_aircraft_configuration_assignments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('aircraft_id');
                $table->unsignedInteger('variant_id');
                $table->unsignedInteger('configuration_id')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_until')->nullable();
                $table->boolean('active')->default(true);
                $table->longText('overrides')->nullable();
                $table->string('source', 255)->nullable();
                $table->string('source_url', 500)->nullable();
                $table->text('notes')->nullable();
                $table->string('historical_confidence', 24)->default('VA_configuration');
                $table->timestamps();
                $table->index(['aircraft_id', 'active'], 'prom_air_assign_current_idx');
                $table->index(['aircraft_id', 'valid_from', 'valid_until'], 'prom_air_assign_date_idx');
            });
        }

        if (!Schema::hasTable('promethee_airframe_simulator_profiles')) {
            Schema::create('promethee_airframe_simulator_profiles', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('variant_id');
                $table->unsignedInteger('configuration_id')->nullable();
                $table->string('simulator', 24);
                $table->string('addon_name', 120);
                $table->string('addon_version', 80)->nullable();
                $table->string('aircraft_identifier', 160)->nullable();
                $table->unsignedInteger('simbrief_airframe_id')->nullable();
                $table->string('telemetry_profile', 120)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->index(['variant_id', 'configuration_id', 'active'], 'prom_sim_profile_lookup_idx');
            });
        }

        if (!Schema::hasTable('promethee_pirep_aircraft_profiles')) {
            Schema::create('promethee_pirep_aircraft_profiles', function (Blueprint $table) {
                $table->string('pirep_id', BaseModel::ID_MAX_LENGTH);
                $table->unsignedInteger('aircraft_id');
                $table->longText('snapshot');
                $table->timestamps();
                $table->primary('pirep_id');
                $table->index('aircraft_id', 'prom_pirep_aircraft_idx');
            });
        }

        if (!Schema::hasTable('promethee_aircraft_modifications')) {
            Schema::create('promethee_aircraft_modifications', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('aircraft_id')->nullable();
                $table->unsignedInteger('variant_id')->nullable();
                $table->unsignedInteger('configuration_id')->nullable();
                $table->string('name', 160);
                $table->text('description')->nullable();
                $table->string('category', 32)->default('Other');
                $table->date('effective_from')->nullable();
                $table->date('effective_until')->nullable();
                $table->text('previous_value')->nullable();
                $table->text('new_value')->nullable();
                $table->string('source', 255)->nullable();
                $table->string('source_url', 500)->nullable();
                $table->timestamps();
                $table->index(['aircraft_id', 'effective_from'], 'prom_air_mod_aircraft_idx');
                $table->index(['variant_id', 'configuration_id'], 'prom_air_mod_profile_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_aircraft_modifications');
        Schema::dropIfExists('promethee_pirep_aircraft_profiles');
        Schema::dropIfExists('promethee_airframe_simulator_profiles');
        Schema::dropIfExists('promethee_aircraft_configuration_assignments');
        Schema::dropIfExists('promethee_airframe_configurations');
        Schema::dropIfExists('promethee_aircraft_historical_variants');
        Schema::dropIfExists('promethee_aircraft_type_profiles');
    }
};
