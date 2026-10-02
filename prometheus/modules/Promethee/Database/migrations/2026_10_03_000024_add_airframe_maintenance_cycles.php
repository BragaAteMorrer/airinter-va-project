<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_airframe_maintenance')) {
            Schema::create('promethee_airframe_maintenance', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('aircraft_id')->unique();

                $table->unsignedBigInteger('a_minutes')->default(0);
                $table->unsignedInteger('a_cycles')->default(0);
                $table->unsignedBigInteger('b_minutes')->default(0);
                $table->unsignedInteger('b_cycles')->default(0);
                $table->unsignedBigInteger('c_minutes')->default(0);
                $table->unsignedInteger('c_cycles')->default(0);

                $table->string('active_check', 1)->nullable();
                $table->timestamp('active_started_at')->nullable();
                $table->timestamp('active_due_at')->nullable();
                $table->unsignedInteger('active_started_by')->nullable();

                $table->timestamp('last_a_at')->nullable();
                $table->timestamp('last_b_at')->nullable();
                $table->timestamp('last_c_at')->nullable();

                $table->timestamps();

                $table->index(['active_check', 'active_due_at'], 'prom_airframe_active_check_idx');
            });
        }

        if (!Schema::hasTable('promethee_airframe_usage_events')) {
            Schema::create('promethee_airframe_usage_events', function (Blueprint $table) {
                $table->id();
                $table->string('pirep_id', 36)->unique();
                $table->unsignedInteger('aircraft_id');
                $table->unsignedInteger('flight_minutes')->default(0);
                $table->unsignedTinyInteger('cycles')->default(1);
                $table->timestamp('recorded_at')->nullable();
                $table->timestamps();

                $table->index(['aircraft_id', 'recorded_at'], 'prom_airframe_usage_aircraft_idx');
            });
        }

        if (!Schema::hasTable('promethee_airframe_maintenance_events')) {
            Schema::create('promethee_airframe_maintenance_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('aircraft_id');
                $table->string('check_type', 1);
                $table->string('event_type', 24);
                $table->string('airport_id', 8)->nullable();
                $table->unsignedBigInteger('minutes_before')->nullable();
                $table->unsignedInteger('cycles_before')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('occurred_at');
                $table->timestamps();

                $table->index(['aircraft_id', 'occurred_at'], 'prom_airframe_event_history_idx');
            });
        }

        if (Schema::hasTable('promethee_airframe_maintenance') && Schema::hasTable('aircraft')) {
            $now = now();

            DB::table('aircraft')
                ->select('id')
                ->orderBy('id')
                ->chunkById(250, function ($aircraft) use ($now) {
                    $rows = $aircraft->map(fn ($plane) => [
                        'aircraft_id' => (int) $plane->id,
                        'a_minutes' => 0,
                        'a_cycles' => 0,
                        'b_minutes' => 0,
                        'b_cycles' => 0,
                        'c_minutes' => 0,
                        'c_cycles' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    if ($rows) {
                        DB::table('promethee_airframe_maintenance')->insertOrIgnore($rows);
                    }
                });
        }

        if (Schema::hasTable('promethee_settings')) {
            $defaults = [
                'maintenance.airframe.a.time_limit_hours' => '20',
                'maintenance.airframe.a.cycle_limit' => '20',
                'maintenance.airframe.a.duration_hours' => '20',
                'maintenance.airframe.b.time_limit_hours' => '60',
                'maintenance.airframe.b.cycle_limit' => '60',
                'maintenance.airframe.b.duration_hours' => '96',
                'maintenance.airframe.c.time_limit_hours' => '180',
                'maintenance.airframe.c.cycle_limit' => '180',
                'maintenance.airframe.c.duration_hours' => '120',
                'maintenance.airframe.warning_percent' => '10',
                'regional.rotation_airframe_bias_percent' => '10',
            ];

            foreach ($defaults as $key => $value) {
                DB::table('promethee_settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => DB::raw('COALESCE(value, '.DB::getPdo()->quote($value).')'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('promethee_settings')) {
            DB::table('promethee_settings')->whereIn('key', [
                'maintenance.airframe.a.time_limit_hours',
                'maintenance.airframe.a.cycle_limit',
                'maintenance.airframe.a.duration_hours',
                'maintenance.airframe.b.time_limit_hours',
                'maintenance.airframe.b.cycle_limit',
                'maintenance.airframe.b.duration_hours',
                'maintenance.airframe.c.time_limit_hours',
                'maintenance.airframe.c.cycle_limit',
                'maintenance.airframe.c.duration_hours',
                'maintenance.airframe.warning_percent',
                'regional.rotation_airframe_bias_percent',
            ])->delete();
        }

        Schema::dropIfExists('promethee_airframe_maintenance_events');
        Schema::dropIfExists('promethee_airframe_usage_events');
        Schema::dropIfExists('promethee_airframe_maintenance');
    }
};
