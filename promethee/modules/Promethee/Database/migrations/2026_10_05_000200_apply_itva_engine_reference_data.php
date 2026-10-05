<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_engine_profiles') || !Schema::hasTable('promethee_engines')) {
            return;
        }

        if (!Schema::hasColumn('promethee_engine_profiles', 'itva_overhaul_cost')) {
            Schema::table('promethee_engine_profiles', function (Blueprint $table) {
                $table->decimal('itva_overhaul_cost', 14, 2)->nullable()->after('warning_cycles');
            });
        }

        $references = require dirname(__DIR__, 2).'/Config/engine-profiles.php';

        $subfleets = DB::table('subfleets as subfleet')
            ->leftJoin('airlines as airline', 'airline.id', '=', 'subfleet.airline_id')
            ->select([
                'subfleet.id',
                'subfleet.type',
                'airline.icao as airline_icao',
            ])
            ->orderBy('subfleet.id')
            ->get();

        foreach ($subfleets as $subfleet) {
            $key = strtoupper((string) ($subfleet->airline_icao ?: '')).'|'.(string) $subfleet->type;
            $reference = $references[$key] ?? null;

            if (!is_array($reference)) {
                continue;
            }

            $profile = DB::table('promethee_engine_profiles')
                ->where('subfleet_id', $subfleet->id)
                ->first();

            $profileValues = [
                'engine_type' => (string) $reference['engine_type'],
                'engine_count' => (int) $reference['engine_count'],
                // Historical column name retained for compatibility:
                // it stores the Air Inter VA gameplay potential, not real TBO.
                'tbo_hours' => $reference['tbo_hours'] ?? null,
                'warning_hours' => (float) ($reference['warning_hours'] ?? 100),
                'itva_overhaul_cost' => $reference['itva_tbo_cost'] ?? null,
                'updated_at' => now(),
            ];

            if (array_key_exists('tbo_cycles', $reference)) {
                $profileValues['tbo_cycles'] = $reference['tbo_cycles'];
            }
            if (array_key_exists('warning_cycles', $reference)) {
                $profileValues['warning_cycles'] = $reference['warning_cycles'];
            }

            if ($profile) {
                DB::table('promethee_engine_profiles')
                    ->where('id', $profile->id)
                    ->update($profileValues);
                $profileId = (int) $profile->id;
            } else {
                $profileId = (int) DB::table('promethee_engine_profiles')->insertGetId($profileValues + [
                    'subfleet_id' => $subfleet->id,
                    'tbo_cycles' => $reference['tbo_cycles'] ?? null,
                    'warning_cycles' => $reference['warning_cycles'] ?? null,
                    'active' => true,
                    'created_at' => now(),
                ]);
            }

            $profile = DB::table('promethee_engine_profiles')
                ->where('id', $profileId)
                ->first();

            DB::table('promethee_engines')
                ->where('engine_profile_id', $profileId)
                ->update([
                    'engine_type' => (string) $reference['engine_type'],
                    'tbo_hours' => $reference['tbo_hours'] ?? null,
                    'tbo_cycles' => $profile?->tbo_cycles,
                    'updated_at' => now(),
                ]);

            $engines = DB::table('promethee_engines')
                ->where('engine_profile_id', $profileId)
                ->get();

            foreach ($engines as $engine) {
                $remainingHours = $engine->tbo_hours !== null
                    ? (float) $engine->tbo_hours - (float) $engine->hours_since_overhaul
                    : null;
                $remainingCycles = $engine->tbo_cycles !== null
                    ? (int) $engine->tbo_cycles - (int) $engine->cycles_since_overhaul
                    : null;

                $due = ($remainingHours !== null && $remainingHours <= 0)
                    || ($remainingCycles !== null && $remainingCycles <= 0);

                $warning = ($remainingHours !== null
                        && $remainingHours <= (float) ($profile?->warning_hours ?? 100))
                    || ($remainingCycles !== null
                        && $profile?->warning_cycles !== null
                        && $remainingCycles <= (int) $profile->warning_cycles);

                DB::table('promethee_engines')
                    ->where('id', $engine->id)
                    ->update([
                        'status' => $due ? 'due' : ($warning ? 'warning' : 'serviceable'),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Do not restore real-world TBO values: they were never intended to be
        // operational maintenance limits in Prométhée. Only remove the added
        // reference-cost column on rollback.
        if (Schema::hasTable('promethee_engine_profiles')
            && Schema::hasColumn('promethee_engine_profiles', 'itva_overhaul_cost')) {
            Schema::table('promethee_engine_profiles', function (Blueprint $table) {
                $table->dropColumn('itva_overhaul_cost');
            });
        }
    }
};
