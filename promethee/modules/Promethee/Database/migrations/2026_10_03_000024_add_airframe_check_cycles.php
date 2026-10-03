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
                $table->unsignedInteger('a_minutes')->default(0);
                $table->unsignedInteger('a_cycles')->default(0);
                $table->unsignedInteger('b_minutes')->default(0);
                $table->unsignedInteger('b_cycles')->default(0);
                $table->unsignedInteger('c_minutes')->default(0);
                $table->unsignedInteger('c_cycles')->default(0);
                $table->timestamp('last_a_at')->nullable();
                $table->timestamp('last_b_at')->nullable();
                $table->timestamp('last_c_at')->nullable();
                $table->char('active_check', 1)->nullable();
                $table->timestamp('active_started_at')->nullable();
                $table->timestamp('active_due_at')->nullable();
                $table->unsignedInteger('active_started_by')->nullable();
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
                $table->char('check_type', 1);
                $table->string('event_type', 20);
                $table->string('airport_id', 8)->nullable();
                $table->unsignedInteger('minutes_before')->nullable();
                $table->unsignedInteger('cycles_before')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('occurred_at');
                $table->timestamps();
                $table->index(['aircraft_id', 'occurred_at'], 'prom_airframe_maint_history_idx');
            });
        }

        if (Schema::hasTable('promethee_settings')) {
            $legacyValue = function (string $key, string $fallback): string {
                if (!Schema::hasTable('disposable_settings')) {
                    return $fallback;
                }

                $legacy = DB::table('disposable_settings')->where('key', $key)->first(['value', 'default']);
                if (!$legacy) {
                    return $fallback;
                }

                $value = trim((string) ($legacy->value ?? ''));
                if ($value === '') {
                    $value = trim((string) ($legacy->default ?? ''));
                }

                return is_numeric($value) ? $value : $fallback;
            };

            // Preserve the values already configured in the former maintenance
            // module when they exist. Fresh installations use the values from
            // the current Air Inter administration screen supplied by Ops.
            $defaults = [
                'maintenance.airframe.a.time_limit_hours' => $legacyValue('turksim.maint_lim_at', '20'),
                'maintenance.airframe.a.cycle_limit' => $legacyValue('turksim.maint_lim_ac', '20'),
                'maintenance.airframe.a.duration_hours' => $legacyValue('turksim.maint_hours_a', '20'),
                'maintenance.airframe.b.time_limit_hours' => $legacyValue('turksim.maint_lim_bt', '60'),
                'maintenance.airframe.b.cycle_limit' => $legacyValue('turksim.maint_lim_bc', '60'),
                'maintenance.airframe.b.duration_hours' => $legacyValue('turksim.maint_hours_b', '96'),
                'maintenance.airframe.c.time_limit_hours' => $legacyValue('turksim.maint_lim_ct', '180'),
                'maintenance.airframe.c.cycle_limit' => $legacyValue('turksim.maint_lim_cc', '180'),
                'maintenance.airframe.c.duration_hours' => $legacyValue('turksim.maint_hours_c', '120'),
                'maintenance.airframe.warning_percent' => '10',
                'regional.rotation_airframe_bias_percent' => '10',
            ];

            foreach ($defaults as $key => $value) {
                DB::table('promethee_settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => (string) $value, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        if (Schema::hasTable('aircraft')) {
            $legacy = Schema::hasTable('disposable_maintenance')
                ? DB::table('disposable_maintenance')->get()->keyBy('aircraft_id')
                : collect();

            foreach (DB::table('aircraft')->get(['id']) as $aircraft) {
                $old = $legacy->get($aircraft->id);
                $activeCheck = null;
                if ($old && in_array($old->act_note ?? null, ['A Check', 'B Check', 'C Check'], true)) {
                    $activeCheck = strtolower(substr((string) $old->act_note, 0, 1));
                }

                DB::table('promethee_airframe_maintenance')->updateOrInsert(
                    ['aircraft_id' => $aircraft->id],
                    [
                        'a_minutes' => max(0, (int) ($old->time_a ?? 0)),
                        'a_cycles' => max(0, (int) ($old->cycle_a ?? 0)),
                        'b_minutes' => max(0, (int) ($old->time_b ?? 0)),
                        'b_cycles' => max(0, (int) ($old->cycle_b ?? 0)),
                        'c_minutes' => max(0, (int) ($old->time_c ?? 0)),
                        'c_cycles' => max(0, (int) ($old->cycle_c ?? 0)),
                        'last_a_at' => $old->last_a ?? null,
                        'last_b_at' => $old->last_b ?? null,
                        'last_c_at' => $old->last_c ?? null,
                        'active_check' => $activeCheck,
                        'active_started_at' => $old->act_start ?? null,
                        'active_due_at' => $old->act_end ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_airframe_maintenance_events');
        Schema::dropIfExists('promethee_airframe_usage_events');
        Schema::dropIfExists('promethee_airframe_maintenance');

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
    }
};
