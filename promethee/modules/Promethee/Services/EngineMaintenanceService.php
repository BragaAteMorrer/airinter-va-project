<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Pirep;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class EngineMaintenanceService
{
    public function recordAcceptedPirep(Pirep $pirep): array
    {
        if (!Schema::hasTable('promethee_engine_profiles') || !Schema::hasTable('promethee_engine_usage_events')) {
            return ['updated' => 0, 'reason' => 'SCHEMA_NOT_READY'];
        }

        $pirep->loadMissing('aircraft');
        $aircraft = $pirep->aircraft;
        if (!$aircraft) {
            return ['updated' => 0, 'reason' => 'NO_AIRCRAFT'];
        }

        $minutes = max(0, (int) ($pirep->flight_time ?: $pirep->block_time ?: 0));
        if ($minutes <= 0) {
            return ['updated' => 0, 'reason' => 'NO_FLIGHT_TIME'];
        }

        $engines = $this->syncAircraft($aircraft);
        if ($engines->isEmpty()) {
            return ['updated' => 0, 'reason' => 'NO_ENGINE_PROFILE'];
        }

        $updated = 0;
        foreach ($engines as $engine) {
            DB::transaction(function () use ($pirep, $engine, $minutes, &$updated) {
                $inserted = DB::table('promethee_engine_usage_events')->insertOrIgnore([
                    'pirep_id' => (string) $pirep->id,
                    'engine_id' => $engine->id,
                    'flight_minutes' => $minutes,
                    'cycles' => 1,
                    'recorded_at' => $pirep->submitted_at ?: now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (!$inserted) {
                    return;
                }

                $fresh = DB::table('promethee_engines')->where('id', $engine->id)->lockForUpdate()->first();
                if (!$fresh) {
                    return;
                }

                DB::table('promethee_engines')->where('id', $fresh->id)->update([
                    'hours_since_overhaul' => round((float) $fresh->hours_since_overhaul + ($minutes / 60), 2),
                    'cycles_since_overhaul' => (int) $fresh->cycles_since_overhaul + 1,
                    'updated_at' => now(),
                ]);

                $this->refreshStatus((int) $fresh->id);
                $updated++;
            });
        }

        return ['updated' => $updated, 'reason' => 'RECORDED'];
    }

    public function syncSubfleet(int $subfleetId): int
    {
        if (!Schema::hasTable('promethee_engine_profiles')) {
            return 0;
        }

        $count = 0;
        Aircraft::where('subfleet_id', $subfleetId)->orderBy('id')->get()->each(function (Aircraft $aircraft) use (&$count) {
            $count += $this->syncAircraft($aircraft)->count();
        });

        return $count;
    }

    public function syncAircraft(Aircraft $aircraft): Collection
    {
        if (!Schema::hasTable('promethee_engine_profiles') || !Schema::hasTable('promethee_aircraft_engines')) {
            return collect();
        }

        $profile = DB::table('promethee_engine_profiles')
            ->where('subfleet_id', $aircraft->subfleet_id)
            ->where('active', true)
            ->first();

        if (!$profile) {
            return collect();
        }

        $engineCount = max(1, min(4, (int) $profile->engine_count));
        $installed = DB::table('promethee_aircraft_engines')
            ->where('aircraft_id', $aircraft->id)
            ->get()
            ->keyBy('position');

        for ($position = 1; $position <= $engineCount; $position++) {
            if ($installed->has($position)) {
                continue;
            }

            $serial = 'AUTO-'.$aircraft->id.'-'.$position;
            $engine = DB::table('promethee_engines')->where('serial_number', $serial)->first();

            if (!$engine) {
                $engineId = DB::table('promethee_engines')->insertGetId([
                    'engine_profile_id' => $profile->id,
                    'serial_number' => $serial,
                    'engine_type' => $profile->engine_type,
                    'tbo_hours' => $profile->tbo_hours,
                    'tbo_cycles' => $profile->tbo_cycles,
                    'hours_since_overhaul' => 0,
                    'cycles_since_overhaul' => 0,
                    'status' => 'serviceable',
                    'last_overhaul_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $engine = DB::table('promethee_engines')->where('id', $engineId)->first();
            } elseif (!DB::table('promethee_aircraft_engines')->where('engine_id', $engine->id)->exists()) {
                DB::table('promethee_engines')->where('id', $engine->id)->update([
                    'engine_profile_id' => $profile->id,
                    'engine_type' => $profile->engine_type,
                    'tbo_hours' => $profile->tbo_hours,
                    'tbo_cycles' => $profile->tbo_cycles,
                    'updated_at' => now(),
                ]);
            } else {
                continue;
            }

            DB::table('promethee_aircraft_engines')->insert([
                'aircraft_id' => $aircraft->id,
                'engine_id' => $engine->id,
                'position' => $position,
                'installed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('promethee_engine_maintenance_events')->insert([
                'engine_id' => $engine->id,
                'aircraft_id' => $aircraft->id,
                'event_type' => 'install',
                'airport_id' => $aircraft->airport_id,
                'hours_before' => $engine->hours_since_overhaul,
                'cycles_before' => $engine->cycles_since_overhaul,
                'created_by' => null,
                'notes' => 'Installation automatique lors de l’initialisation du profil moteur.',
                'occurred_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $engines = $this->installedForAircraft((int) $aircraft->id);
        foreach ($engines as $engine) {
            if (str_starts_with((string) $engine->serial_number, 'AUTO-')) {
                DB::table('promethee_engines')->where('id', $engine->id)->update([
                    'engine_profile_id' => $profile->id,
                    'engine_type' => $profile->engine_type,
                    'tbo_hours' => $profile->tbo_hours,
                    'tbo_cycles' => $profile->tbo_cycles,
                    'updated_at' => now(),
                ]);
            }
            $this->refreshStatus((int) $engine->id);
        }

        return $this->installedForAircraft((int) $aircraft->id);
    }

    public function installedForAircraft(int $aircraftId): Collection
    {
        if (!Schema::hasTable('promethee_aircraft_engines')) {
            return collect();
        }

        return DB::table('promethee_aircraft_engines as installation')
            ->join('promethee_engines as engine', 'engine.id', '=', 'installation.engine_id')
            ->leftJoin('promethee_engine_profiles as profile', 'profile.id', '=', 'engine.engine_profile_id')
            ->where('installation.aircraft_id', $aircraftId)
            ->select([
                'engine.*',
                'installation.position',
                'installation.installed_at',
                'profile.warning_hours',
                'profile.warning_cycles',
            ])
            ->orderBy('installation.position')
            ->get()
            ->map(function ($engine) {
                $engine->remaining_hours = $engine->tbo_hours !== null
                    ? round((float) $engine->tbo_hours - (float) $engine->hours_since_overhaul, 2)
                    : null;
                $engine->remaining_cycles = $engine->tbo_cycles !== null
                    ? (int) $engine->tbo_cycles - (int) $engine->cycles_since_overhaul
                    : null;
                return $engine;
            });
    }

    public function refreshStatus(int $engineId): string
    {
        $engine = DB::table('promethee_engines as engine')
            ->leftJoin('promethee_engine_profiles as profile', 'profile.id', '=', 'engine.engine_profile_id')
            ->where('engine.id', $engineId)
            ->select('engine.*', 'profile.warning_hours', 'profile.warning_cycles')
            ->first();

        if (!$engine) {
            return 'unknown';
        }

        $remainingHours = $engine->tbo_hours !== null
            ? (float) $engine->tbo_hours - (float) $engine->hours_since_overhaul
            : null;
        $remainingCycles = $engine->tbo_cycles !== null
            ? (int) $engine->tbo_cycles - (int) $engine->cycles_since_overhaul
            : null;

        $due = ($remainingHours !== null && $remainingHours <= 0)
            || ($remainingCycles !== null && $remainingCycles <= 0);

        $warning = ($remainingHours !== null && $remainingHours <= (float) ($engine->warning_hours ?? 100))
            || ($remainingCycles !== null && $engine->warning_cycles !== null && $remainingCycles <= (int) $engine->warning_cycles);

        $status = $due ? 'due' : ($warning ? 'warning' : 'serviceable');

        DB::table('promethee_engines')->where('id', $engineId)->update([
            'status' => $status,
            'updated_at' => now(),
        ]);

        return $status;
    }

    public function overhaul(int $engineId, ?int $userId = null, ?string $notes = null): void
    {
        DB::transaction(function () use ($engineId, $userId, $notes) {
            $engine = DB::table('promethee_engines')->where('id', $engineId)->lockForUpdate()->first();
            if (!$engine) {
                throw new RuntimeException('Moteur introuvable.');
            }

            $installation = DB::table('promethee_aircraft_engines')->where('engine_id', $engineId)->first();
            if (!$installation) {
                throw new RuntimeException('Le moteur doit être monté sur un appareil afin de déterminer le site technique.');
            }

            $aircraft = Aircraft::find($installation->aircraft_id);
            if (!$aircraft) {
                throw new RuntimeException('Appareil introuvable.');
            }

            $site = DB::table('promethee_operational_bases')
                ->where('airport_id', strtoupper((string) $aircraft->airport_id))
                ->where('engine_overhaul', true)
                ->where('active', true)
                ->first();

            if (!$site) {
                throw new RuntimeException('La position actuelle de l’appareil ne permet pas une révision moteur.');
            }

            DB::table('promethee_engine_maintenance_events')->insert([
                'engine_id' => $engine->id,
                'aircraft_id' => $aircraft->id,
                'event_type' => 'overhaul',
                'airport_id' => $aircraft->airport_id,
                'hours_before' => $engine->hours_since_overhaul,
                'cycles_before' => $engine->cycles_since_overhaul,
                'created_by' => $userId,
                'notes' => $notes,
                'occurred_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('promethee_engines')->where('id', $engine->id)->update([
                'hours_since_overhaul' => 0,
                'cycles_since_overhaul' => 0,
                'status' => 'serviceable',
                'last_overhaul_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function install(int $engineId, int $aircraftId, int $position, ?int $userId = null): void
    {
        DB::transaction(function () use ($engineId, $aircraftId, $position, $userId) {
            $engine = DB::table('promethee_engines')->where('id', $engineId)->lockForUpdate()->first();
            $aircraft = Aircraft::lockForUpdate()->find($aircraftId);

            if (!$engine || !$aircraft) {
                throw new RuntimeException('Moteur ou appareil introuvable.');
            }

            $profile = DB::table('promethee_engine_profiles')->where('id', $engine->engine_profile_id)->first();
            if (!$profile || (int) $profile->subfleet_id !== (int) $aircraft->subfleet_id) {
                throw new RuntimeException('Ce moteur n’est pas compatible avec la sous-flotte de cet appareil.');
            }

            if ($position < 1 || $position > (int) $profile->engine_count) {
                throw new RuntimeException('Position moteur invalide pour cette sous-flotte.');
            }

            if (DB::table('promethee_aircraft_engines')->where('engine_id', $engineId)->exists()) {
                throw new RuntimeException('Ce moteur est déjà monté sur un appareil. Déposez-le d’abord.');
            }

            $current = DB::table('promethee_aircraft_engines')
                ->where('aircraft_id', $aircraftId)
                ->where('position', $position)
                ->lockForUpdate()
                ->first();

            if ($current) {
                $oldEngine = DB::table('promethee_engines')->where('id', $current->engine_id)->first();
                DB::table('promethee_aircraft_engines')->where('id', $current->id)->delete();
                DB::table('promethee_engine_maintenance_events')->insert([
                    'engine_id' => $current->engine_id,
                    'aircraft_id' => $aircraft->id,
                    'event_type' => 'remove',
                    'airport_id' => $aircraft->airport_id,
                    'hours_before' => $oldEngine?->hours_since_overhaul,
                    'cycles_before' => $oldEngine?->cycles_since_overhaul,
                    'created_by' => $userId,
                    'notes' => 'Dépose moteur avant remplacement.',
                    'occurred_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('promethee_aircraft_engines')->insert([
                'aircraft_id' => $aircraft->id,
                'engine_id' => $engine->id,
                'position' => $position,
                'installed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('promethee_engine_maintenance_events')->insert([
                'engine_id' => $engine->id,
                'aircraft_id' => $aircraft->id,
                'event_type' => 'install',
                'airport_id' => $aircraft->airport_id,
                'hours_before' => $engine->hours_since_overhaul,
                'cycles_before' => $engine->cycles_since_overhaul,
                'created_by' => $userId,
                'notes' => 'Installation manuelle depuis le stock moteur.',
                'occurred_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->refreshStatus((int) $engine->id);
        });
    }
}
