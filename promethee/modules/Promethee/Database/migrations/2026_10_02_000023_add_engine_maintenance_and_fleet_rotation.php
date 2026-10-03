<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('promethee_operational_bases') && !Schema::hasColumn('promethee_operational_bases', 'engine_overhaul')) {
            Schema::table('promethee_operational_bases', function (Blueprint $table) {
                $table->boolean('engine_overhaul')->default(false)->after('check_c');
            });

            DB::table('promethee_operational_bases')
                ->whereIn('airport_id', ['LFPO', 'LFPG'])
                ->update(['engine_overhaul' => true, 'updated_at' => now()]);
        }

        if (Schema::hasTable('promethee_aircraft_bases')) {
            Schema::table('promethee_aircraft_bases', function (Blueprint $table) {
                if (!Schema::hasColumn('promethee_aircraft_bases', 'rotation_locked')) {
                    $table->boolean('rotation_locked')->default(false)->after('last_auto_return_at');
                }
                if (!Schema::hasColumn('promethee_aircraft_bases', 'last_rotated_at')) {
                    $table->timestamp('last_rotated_at')->nullable()->after('rotation_locked');
                }
            });
        }

        if (!Schema::hasTable('promethee_engine_profiles')) {
            Schema::create('promethee_engine_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('subfleet_id')->unique();
                $table->string('engine_type', 80);
                $table->unsignedTinyInteger('engine_count')->default(2);
                $table->decimal('tbo_hours', 10, 2)->nullable();
                $table->unsignedInteger('tbo_cycles')->nullable();
                $table->decimal('warning_hours', 10, 2)->default(100);
                $table->unsignedInteger('warning_cycles')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('promethee_engines')) {
            Schema::create('promethee_engines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('engine_profile_id');
                $table->string('serial_number', 96)->unique();
                $table->string('engine_type', 80);
                $table->decimal('tbo_hours', 10, 2)->nullable();
                $table->unsignedInteger('tbo_cycles')->nullable();
                $table->decimal('hours_since_overhaul', 12, 2)->default(0);
                $table->unsignedInteger('cycles_since_overhaul')->default(0);
                $table->string('status', 20)->default('serviceable');
                $table->timestamp('last_overhaul_at')->nullable();
                $table->timestamps();
                $table->index(['engine_profile_id', 'status'], 'prom_engine_profile_status_idx');
            });
        }

        if (!Schema::hasTable('promethee_aircraft_engines')) {
            Schema::create('promethee_aircraft_engines', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('aircraft_id');
                $table->unsignedBigInteger('engine_id');
                $table->unsignedTinyInteger('position');
                $table->timestamp('installed_at')->nullable();
                $table->timestamps();
                $table->unique(['aircraft_id', 'position'], 'prom_aircraft_engine_position_uq');
                $table->unique('engine_id', 'prom_engine_installation_uq');
                $table->index('aircraft_id', 'prom_aircraft_engine_aircraft_idx');
            });
        }

        if (!Schema::hasTable('promethee_engine_usage_events')) {
            Schema::create('promethee_engine_usage_events', function (Blueprint $table) {
                $table->id();
                $table->string('pirep_id', 36);
                $table->unsignedBigInteger('engine_id');
                $table->unsignedInteger('flight_minutes')->default(0);
                $table->unsignedTinyInteger('cycles')->default(1);
                $table->timestamp('recorded_at')->nullable();
                $table->timestamps();
                $table->unique(['pirep_id', 'engine_id'], 'prom_engine_usage_pirep_engine_uq');
                $table->index('engine_id', 'prom_engine_usage_engine_idx');
            });
        }

        if (!Schema::hasTable('promethee_engine_maintenance_events')) {
            Schema::create('promethee_engine_maintenance_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('engine_id');
                $table->unsignedInteger('aircraft_id')->nullable();
                $table->string('event_type', 24);
                $table->string('airport_id', 8)->nullable();
                $table->decimal('hours_before', 12, 2)->nullable();
                $table->unsignedInteger('cycles_before')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('occurred_at');
                $table->timestamps();
                $table->index(['engine_id', 'occurred_at'], 'prom_engine_event_history_idx');
            });
        }

        if (!Schema::hasTable('promethee_fleet_rotation_log')) {
            Schema::create('promethee_fleet_rotation_log', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('first_aircraft_id');
                $table->unsignedInteger('second_aircraft_id');
                $table->string('first_from_base', 8);
                $table->string('second_from_base', 8);
                $table->string('reason', 40)->default('routine');
                $table->timestamp('rotated_at');
                $table->timestamps();
                $table->index('rotated_at', 'prom_fleet_rotation_rotated_idx');
            });
        }

        if (Schema::hasTable('promethee_settings')) {
            $defaults = [
                'regional.rotation_enabled' => '0',
                'regional.rotation_frequency' => 'daily',
                'regional.rotation_percent' => '20',
                'regional.rotation_min_idle_hours' => '8',
                'regional.rotation_cooldown_days' => '3',
                'regional.rotation_maintenance_bias_hours' => '50',
                'regional.rotation_last_run_at' => '',
            ];

            foreach ($defaults as $key => $value) {
                DB::table('promethee_settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_fleet_rotation_log');
        Schema::dropIfExists('promethee_engine_maintenance_events');
        Schema::dropIfExists('promethee_engine_usage_events');
        Schema::dropIfExists('promethee_aircraft_engines');
        Schema::dropIfExists('promethee_engines');
        Schema::dropIfExists('promethee_engine_profiles');

        if (Schema::hasTable('promethee_aircraft_bases')) {
            Schema::table('promethee_aircraft_bases', function (Blueprint $table) {
                if (Schema::hasColumn('promethee_aircraft_bases', 'last_rotated_at')) {
                    $table->dropColumn('last_rotated_at');
                }
                if (Schema::hasColumn('promethee_aircraft_bases', 'rotation_locked')) {
                    $table->dropColumn('rotation_locked');
                }
            });
        }

        if (Schema::hasTable('promethee_operational_bases') && Schema::hasColumn('promethee_operational_bases', 'engine_overhaul')) {
            Schema::table('promethee_operational_bases', function (Blueprint $table) {
                $table->dropColumn('engine_overhaul');
            });
        }
    }
};
