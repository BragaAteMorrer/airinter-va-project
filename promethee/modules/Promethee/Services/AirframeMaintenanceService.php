<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Pirep;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class AirframeMaintenanceService
{
    public const CHECKS = ['a', 'b', 'c'];

    public function settings(): array
    {
        $keys = ['maintenance.airframe.warning_percent'];
        foreach (self::CHECKS as $check) {
            $keys[] = 'maintenance.airframe.'.$check.'.time_limit_hours';
            $keys[] = 'maintenance.airframe.'.$check.'.cycle_limit';
            $keys[] = 'maintenance.airframe.'.$check.'.duration_hours';
        }

        $values = Schema::hasTable('promethee_settings')
            ? DB::table('promethee_settings')->whereIn('key', $keys)->pluck('value', 'key')
            : collect();

        $fallback = [
            'a' => ['time_limit_hours' => 20.0, 'cycle_limit' => 20, 'duration_hours' => 20.0],
            'b' => ['time_limit_hours' => 60.0, 'cycle_limit' => 60, 'duration_hours' => 96.0],
            'c' => ['time_limit_hours' => 180.0, 'cycle_limit' => 180, 'duration_hours' => 120.0],
        ];

        $checks = [];
        foreach (self::CHECKS as $check) {
            $checks[$check] = [
                'time_limit_hours' => max(0.1, (float) ($values['maintenance.airframe.'.$check.'.time_limit_hours'] ?? $fallback[$check]['time_limit_hours'])),
                'cycle_limit' => max(1, (int) ($values['maintenance.airframe.'.$check.'.cycle_limit'] ?? $fallback[$check]['cycle_limit'])),
                'duration_hours' => max(0, (float) ($values['maintenance.airframe.'.$check.'.duration_hours'] ?? $fallback[$check]['duration_hours'])),
            ];
        }

        return [
            'checks' => $checks,
            'warning_percent' => max(0, min(100, (float) ($values['maintenance.airframe.warning_percent'] ?? 10))),
        ];
    }

    public function saveSettings(array $values): void
    {
        if (!Schema::hasTable('promethee_settings')) {
            throw new RuntimeException('La table de paramètres Prométhée n’est pas disponible.');
        }

        $map = [
            'a_time_limit_hours' => 'maintenance.airframe.a.time_limit_hours',
            'a_cycle_limit' => 'maintenance.airframe.a.cycle_limit',
            'a_duration_hours' => 'maintenance.airframe.a.duration_hours',
            'b_time_limit_hours' => 'maintenance.airframe.b.time_limit_hours',
            'b_cycle_limit' => 'maintenance.airframe.b.cycle_limit',
            'b_duration_hours' => 'maintenance.airframe.b.duration_hours',
            'c_time_limit_hours' => 'maintenance.airframe.c.time_limit_hours',
            'c_cycle_limit' => 'maintenance.airframe.c.cycle_limit',
            'c_duration_hours' => 'maintenance.airframe.c.duration_hours',
            'warning_percent' => 'maintenance.airframe.warning_percent',
        ];

        foreach ($map as $field => $key) {
            DB::table('promethee_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => (string) $values[$field], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function syncFleet(): int
    {
        if (!Schema::hasTable('promethee_airframe_maintenance')) {
            return 0;
        }

        $created = 0;
        foreach (Aircraft::query()->pluck('id') as $aircraftId) {
            $created += DB::table('promethee_airframe_maintenance')->insertOrIgnore([
                'aircraft_id' => (int) $aircraftId,
                'a_minutes' => 0,
                'a_cycles' => 0,
                'b_minutes' => 0,
                'b_cycles' => 0,
                'c_minutes' => 0,
                'c_cycles' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $created;
    }

    public function recordAcceptedPirep(Pirep $pirep): array
    {
        if (!Schema::hasTable('promethee_airframe_maintenance') || !Schema::hasTable('promethee_airframe_usage_events')) {
            return ['updated' => 0, 'reason' => 'SCHEMA_NOT_READY'];
        }

        $pirep->loadMissing('aircraft');
        if (!$pirep->aircraft) {
            return ['updated' => 0, 'reason' => 'NO_AIRCRAFT'];
        }

        $minutes = max(0, (int) ($pirep->flight_time ?: $pirep->block_time ?: 0));
        if ($minutes <= 0) {
            return ['updated' => 0, 'reason' => 'NO_FLIGHT_TIME'];
        }

        $aircraftId = (int) $pirep->aircraft->id;
        $this->ensureAircraft($aircraftId);

        $updated = DB::transaction(function () use ($pirep, $aircraftId, $minutes) {
            $inserted = DB::table('promethee_airframe_usage_events')->insertOrIgnore([
                'pirep_id' => (string) $pirep->id,
                'aircraft_id' => $aircraftId,
                'flight_minutes' => $minutes,
                'cycles' => 1,
                'recorded_at' => $pirep->submitted_at ?: now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!$inserted) {
                return 0;
            }

            $state = DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraftId)->lockForUpdate()->first();
            if (!$state) {
                return 0;
            }

            DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update([
                'a_minutes' => (int) $state->a_minutes + $minutes,
                'a_cycles' => (int) $state->a_cycles + 1,
                'b_minutes' => (int) $state->b_minutes + $minutes,
                'b_cycles' => (int) $state->b_cycles + 1,
                'c_minutes' => (int) $state->c_minutes + $minutes,
                'c_cycles' => (int) $state->c_cycles + 1,
                'updated_at' => now(),
            ]);

            return 1;
        });

        return ['updated' => $updated, 'reason' => $updated ? 'RECORDED' : 'ALREADY_RECORDED'];
    }


    public function placeSafetyHold(Pirep $pirep, float $gForce, ?string $recordedAt = null): array
    {
        if (!Schema::hasTable('promethee_airframe_maintenance')
            || !Schema::hasColumn('promethee_airframe_maintenance', 'safety_hold_at')) {
            return ['updated' => 0, 'reason' => 'SCHEMA_NOT_READY'];
        }

        $pirep->loadMissing('aircraft');
        if (!$pirep->aircraft) {
            return ['updated' => 0, 'reason' => 'NO_AIRCRAFT'];
        }

        $aircraftId = (int) $pirep->aircraft->id;
        $this->ensureAircraft($aircraftId);
        $occurredAt = $recordedAt ? \Carbon\Carbon::parse($recordedAt)->utc() : now();
        $reason = sprintf(
            'Facteur de charge structurel %.2f g détecté par Hermès (limites +2.9 g / -1.2 g). Inspection technique requise.',
            $gForce
        );

        return DB::transaction(function () use ($pirep, $aircraftId, $gForce, $occurredAt, $reason) {
            $state = DB::table('promethee_airframe_maintenance')
                ->where('aircraft_id', $aircraftId)
                ->lockForUpdate()
                ->first();
            $aircraft = Aircraft::query()->lockForUpdate()->find($aircraftId);

            if (!$state || !$aircraft) {
                return ['updated' => 0, 'reason' => 'AIRCRAFT_NOT_FOUND'];
            }

            if ((string) ($state->safety_hold_pirep_id ?? '') === (string) $pirep->id) {
                return ['updated' => 0, 'reason' => 'ALREADY_HELD'];
            }

            DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update([
                'safety_hold_reason' => $reason,
                'safety_hold_at' => $occurredAt,
                'safety_hold_pirep_id' => (string) $pirep->id,
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('promethee_airframe_maintenance_events')) {
                DB::table('promethee_airframe_maintenance_events')->insert([
                    'aircraft_id' => $aircraftId,
                    'check_type' => 'g',
                    'event_type' => 'safety_hold',
                    'airport_id' => $aircraft->airport_id,
                    'minutes_before' => null,
                    'cycles_before' => null,
                    'created_by' => null,
                    'notes' => $reason.' PIREP '.$pirep->id,
                    'occurred_at' => $occurredAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $aircraft->update(['status' => AircraftStatus::MAINTENANCE]);

            return [
                'updated' => 1,
                'reason' => 'SAFETY_HOLD',
                'aircraft_id' => $aircraftId,
                'g_force' => $gForce,
            ];
        });
    }

    public function releaseSafetyHold(int $aircraftId, ?int $userId = null, ?string $notes = null): bool
    {
        if (!Schema::hasTable('promethee_airframe_maintenance')
            || !Schema::hasColumn('promethee_airframe_maintenance', 'safety_hold_at')) {
            throw new RuntimeException('Migration de sécurité FDM non appliquée.');
        }

        return DB::transaction(function () use ($aircraftId, $userId, $notes) {
            $state = DB::table('promethee_airframe_maintenance')
                ->where('aircraft_id', $aircraftId)
                ->lockForUpdate()
                ->first();
            $aircraft = Aircraft::query()->lockForUpdate()->find($aircraftId);

            if (!$state || !$aircraft || !$state->safety_hold_at) return false;

            $previousReason = (string) ($state->safety_hold_reason ?? '');
            DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update([
                'safety_hold_reason' => null,
                'safety_hold_at' => null,
                'safety_hold_pirep_id' => null,
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('promethee_airframe_maintenance_events')) {
                DB::table('promethee_airframe_maintenance_events')->insert([
                    'aircraft_id' => $aircraftId,
                    'check_type' => 'g',
                    'event_type' => 'safety_release',
                    'airport_id' => $aircraft->airport_id,
                    'minutes_before' => null,
                    'cycles_before' => null,
                    'created_by' => $userId,
                    'notes' => trim(($notes ?: 'Inspection technique validée.').' '.($previousReason ? 'Hold précédent : '.$previousReason : '')),
                    'occurred_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!$state->active_check && $aircraft->status === AircraftStatus::MAINTENANCE) {
                $aircraft->update(['status' => AircraftStatus::ACTIVE]);
            }

            return true;
        });
    }

    public function rollbackPirep(Pirep $pirep): array
    {
        if (!Schema::hasTable('promethee_airframe_maintenance') || !Schema::hasTable('promethee_airframe_usage_events')) {
            return ['updated' => 0, 'reason' => 'SCHEMA_NOT_READY'];
        }

        $event = DB::table('promethee_airframe_usage_events')->where('pirep_id', (string) $pirep->id)->first();
        if (!$event) {
            return ['updated' => 0, 'reason' => 'NOT_RECORDED'];
        }

        DB::transaction(function () use ($event) {
            $state = DB::table('promethee_airframe_maintenance')->where('aircraft_id', $event->aircraft_id)->lockForUpdate()->first();
            if ($state) {
                $minutes = max(0, (int) $event->flight_minutes);
                $cycles = max(0, (int) $event->cycles);
                DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update([
                    'a_minutes' => max(0, (int) $state->a_minutes - $minutes),
                    'a_cycles' => max(0, (int) $state->a_cycles - $cycles),
                    'b_minutes' => max(0, (int) $state->b_minutes - $minutes),
                    'b_cycles' => max(0, (int) $state->b_cycles - $cycles),
                    'c_minutes' => max(0, (int) $state->c_minutes - $minutes),
                    'c_cycles' => max(0, (int) $state->c_cycles - $cycles),
                    'updated_at' => now(),
                ]);
            }

            DB::table('promethee_airframe_usage_events')->where('id', $event->id)->delete();
        });

        return ['updated' => 1, 'reason' => 'ROLLED_BACK'];
    }

    public function fleetStatus(): Collection
    {
        if (!Schema::hasTable('promethee_airframe_maintenance')) {
            return collect();
        }

        $this->syncFleet();
        $settings = $this->settings();

        return DB::table('promethee_airframe_maintenance as maintenance')
            ->join('aircraft', 'aircraft.id', '=', 'maintenance.aircraft_id')
            ->leftJoin('subfleets', 'subfleets.id', '=', 'aircraft.subfleet_id')
            ->leftJoin('airlines', 'airlines.id', '=', 'subfleets.airline_id')
            ->select([
                'maintenance.*',
                'aircraft.registration',
                'aircraft.airport_id',
                'aircraft.status as aircraft_status',
                'aircraft.state as aircraft_state',
                'subfleets.name as subfleet_name',
                'subfleets.type as subfleet_type',
                'airlines.icao as airline_icao',
            ])
            ->orderBy('airlines.icao')
            ->orderBy('subfleets.name')
            ->orderBy('aircraft.registration')
            ->get()
            ->map(fn ($row) => $this->decorate($row, $settings));
    }

    public function rotationPriorities(float $biasPercent): Collection
    {
        $threshold = max(0, min(100, 100 - $biasPercent));

        return $this->fleetStatus()
            ->filter(fn ($row) => !$row->active_check && !$row->safety_hold_at)
            ->mapWithKeys(function ($row) use ($threshold) {
                foreach (['c', 'b', 'a'] as $check) {
                    $status = $row->checks[$check];
                    if ($status['due'] || $status['progress_percent'] >= $threshold) {
                        return [(int) $row->aircraft_id => (object) [
                            'aircraft_id' => (int) $row->aircraft_id,
                            'check' => $check,
                            'due' => $status['due'],
                            'progress_percent' => $status['progress_percent'],
                        ]];
                    }
                }

                return [];
            });
    }

    public function startCheck(int $aircraftId, string $check, ?int $userId = null): void
    {
        $check = strtolower(trim($check));
        if (!in_array($check, self::CHECKS, true)) {
            throw new RuntimeException('Type de check invalide.');
        }
        if (!Schema::hasTable('promethee_airframe_maintenance')) {
            throw new RuntimeException('Migration maintenance cellule non appliquée.');
        }

        $settings = $this->settings();
        $durationHours = (float) $settings['checks'][$check]['duration_hours'];

        DB::transaction(function () use ($aircraftId, $check, $userId, $durationHours) {
            $aircraft = Aircraft::query()->lockForUpdate()->find($aircraftId);
            if (!$aircraft) {
                throw new RuntimeException('Appareil introuvable.');
            }
            if ((int) $aircraft->state !== AircraftState::PARKED) {
                throw new RuntimeException('L’appareil doit être au parking pour démarrer un check.');
            }

            $this->ensureAircraft($aircraftId);
            $state = DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraftId)->lockForUpdate()->first();
            if (!$state) {
                throw new RuntimeException('État de maintenance cellule introuvable.');
            }
            if ($state->active_check) {
                throw new RuntimeException('Un check cellule est déjà en cours sur cet appareil.');
            }

            $column = 'check_'.$check;
            $site = Schema::hasTable('promethee_operational_bases') && Schema::hasColumn('promethee_operational_bases', $column)
                ? DB::table('promethee_operational_bases')
                    ->where('airport_id', strtoupper((string) $aircraft->airport_id))
                    ->where('active', true)
                    ->where($column, true)
                    ->first()
                : null;
            if (!$site) {
                throw new RuntimeException('La position actuelle de l’appareil ne permet pas un '.strtoupper($check).' Check.');
            }

            $startedAt = now();
            $dueAt = $startedAt->copy()->addMinutes((int) round($durationHours * 60));
            $minutesColumn = $check.'_minutes';
            $cyclesColumn = $check.'_cycles';

            DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update([
                'active_check' => $check,
                'active_started_at' => $startedAt,
                'active_due_at' => $dueAt,
                'active_started_by' => $userId,
                'updated_at' => now(),
            ]);

            DB::table('promethee_airframe_maintenance_events')->insert([
                'aircraft_id' => $aircraftId,
                'check_type' => $check,
                'event_type' => 'started',
                'airport_id' => $aircraft->airport_id,
                'minutes_before' => (int) $state->{$minutesColumn},
                'cycles_before' => (int) $state->{$cyclesColumn},
                'created_by' => $userId,
                'notes' => 'Durée planifiée : '.number_format($durationHours, 1, '.', '').' h.',
                'occurred_at' => $startedAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $aircraft->update(['status' => AircraftStatus::MAINTENANCE]);
        });
    }

    public function releaseCompletedChecks(): int
    {
        if (!Schema::hasTable('promethee_airframe_maintenance')) {
            return 0;
        }

        $aircraftIds = DB::table('promethee_airframe_maintenance')
            ->whereNotNull('active_check')
            ->whereNotNull('active_due_at')
            ->where('active_due_at', '<=', now())
            ->pluck('aircraft_id');

        $completed = 0;
        foreach ($aircraftIds as $aircraftId) {
            if ($this->completeCheck((int) $aircraftId)) {
                $completed++;
            }
        }

        return $completed;
    }

    public function completeCheck(int $aircraftId, bool $force = false, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($aircraftId, $force, $userId) {
            $state = DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraftId)->lockForUpdate()->first();
            $aircraft = Aircraft::query()->lockForUpdate()->find($aircraftId);
            if (!$state || !$aircraft || !$state->active_check) {
                return false;
            }
            if (!$force && $state->active_due_at && now()->lt($state->active_due_at)) {
                return false;
            }

            $check = strtolower((string) $state->active_check);
            if (!in_array($check, self::CHECKS, true)) {
                return false;
            }

            $minutesColumn = $check.'_minutes';
            $cyclesColumn = $check.'_cycles';
            $updates = [
                'active_check' => null,
                'active_started_at' => null,
                'active_due_at' => null,
                'active_started_by' => null,
                'updated_at' => now(),
            ];

            $resetChecks = $check === 'c' ? ['a', 'b', 'c'] : ($check === 'b' ? ['a', 'b'] : ['a']);
            foreach ($resetChecks as $reset) {
                $updates[$reset.'_minutes'] = 0;
                $updates[$reset.'_cycles'] = 0;
                $updates['last_'.$reset.'_at'] = now();
            }

            DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update($updates);
            DB::table('promethee_airframe_maintenance_events')->insert([
                'aircraft_id' => $aircraftId,
                'check_type' => $check,
                'event_type' => 'completed',
                'airport_id' => $aircraft->airport_id,
                'minutes_before' => (int) $state->{$minutesColumn},
                'cycles_before' => (int) $state->{$cyclesColumn},
                'created_by' => $userId ?: $state->active_started_by,
                'notes' => null,
                'occurred_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!$state->safety_hold_at && $aircraft->status === AircraftStatus::MAINTENANCE) {
                $aircraft->update(['status' => AircraftStatus::ACTIVE]);
            }

            return true;
        });
    }

    private function ensureAircraft(int $aircraftId): void
    {
        DB::table('promethee_airframe_maintenance')->insertOrIgnore([
            'aircraft_id' => $aircraftId,
            'a_minutes' => 0,
            'a_cycles' => 0,
            'b_minutes' => 0,
            'b_cycles' => 0,
            'c_minutes' => 0,
            'c_cycles' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function decorate(object $row, array $settings): object
    {
        $row->checks = [];
        $mostUrgent = null;

        foreach (self::CHECKS as $check) {
            $config = $settings['checks'][$check];
            $usedMinutes = (int) $row->{$check.'_minutes'};
            $usedCycles = (int) $row->{$check.'_cycles'};
            $limitMinutes = max(1, (int) round($config['time_limit_hours'] * 60));
            $limitCycles = max(1, (int) $config['cycle_limit']);
            $remainingMinutes = $limitMinutes - $usedMinutes;
            $remainingCycles = $limitCycles - $usedCycles;
            $timeProgress = ($usedMinutes / $limitMinutes) * 100;
            $cycleProgress = ($usedCycles / $limitCycles) * 100;
            $progress = max($timeProgress, $cycleProgress);
            $due = $remainingMinutes <= 0 || $remainingCycles <= 0;
            $warning = $due || $progress >= (100 - $settings['warning_percent']);

            $row->checks[$check] = [
                'time_limit_hours' => $config['time_limit_hours'],
                'cycle_limit' => $config['cycle_limit'],
                'duration_hours' => $config['duration_hours'],
                'used_hours' => round($usedMinutes / 60, 2),
                'used_cycles' => $usedCycles,
                'remaining_hours' => round($remainingMinutes / 60, 2),
                'remaining_cycles' => $remainingCycles,
                'progress_percent' => round($progress, 1),
                'due' => $due,
                'warning' => $warning,
            ];
        }

        foreach (['c', 'b', 'a'] as $check) {
            if ($row->checks[$check]['due']) {
                $mostUrgent = $check;
                break;
            }
        }
        if (!$mostUrgent) {
            foreach (['c', 'b', 'a'] as $check) {
                if ($row->checks[$check]['warning']) {
                    $mostUrgent = $check;
                    break;
                }
            }
        }

        $row->next_check = $mostUrgent;
        $row->maintenance_state = ($row->active_check || $row->safety_hold_at)
            ? 'maintenance'
            : ($mostUrgent && $row->checks[$mostUrgent]['due'] ? 'due' : ($mostUrgent ? 'warning' : 'serviceable'));

        return $row;
    }
}
