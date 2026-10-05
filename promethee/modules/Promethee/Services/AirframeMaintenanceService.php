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

    /**
     * Maintenance policy shown by Prométhée.
     *
     * The historical aircraft maintenance subsystem already owns the A/B/C
     * limits through disposable_settings, with optional ICAO overrides in
     * disposable_tech_details. Prométhée must therefore read/write the same
     * values instead of maintaining a second, divergent set of limits.
     */
    public function settings(): array
    {
        $prometheeValues = Schema::hasTable('promethee_settings')
            ? DB::table('promethee_settings')
                ->whereIn('key', [
                    'maintenance.airframe.warning_percent',
                    'maintenance.airframe.a.time_limit_hours',
                    'maintenance.airframe.a.cycle_limit',
                    'maintenance.airframe.a.duration_hours',
                    'maintenance.airframe.b.time_limit_hours',
                    'maintenance.airframe.b.cycle_limit',
                    'maintenance.airframe.b.duration_hours',
                    'maintenance.airframe.c.time_limit_hours',
                    'maintenance.airframe.c.cycle_limit',
                    'maintenance.airframe.c.duration_hours',
                ])
                ->pluck('value', 'key')
            : collect();

        // Keep the historical module fallbacks exactly aligned with
        // DS_Maintenance::getLimitsAttribute().
        $fallback = [
            'a' => ['time_limit_hours' => 500.0, 'cycle_limit' => 250, 'duration_hours' => 10.0],
            'b' => ['time_limit_hours' => 1000.0, 'cycle_limit' => 500, 'duration_hours' => 48.0],
            'c' => ['time_limit_hours' => 5000.0, 'cycle_limit' => 2500, 'duration_hours' => 120.0],
        ];

        $legacyKeys = [
            'a' => [
                'time_limit_hours' => 'turksim.maint_lim_at',
                'cycle_limit' => 'turksim.maint_lim_ac',
                'duration_hours' => 'turksim.maint_hours_a',
            ],
            'b' => [
                'time_limit_hours' => 'turksim.maint_lim_bt',
                'cycle_limit' => 'turksim.maint_lim_bc',
                'duration_hours' => 'turksim.maint_hours_b',
            ],
            'c' => [
                'time_limit_hours' => 'turksim.maint_lim_ct',
                'cycle_limit' => 'turksim.maint_lim_cc',
                'duration_hours' => 'turksim.maint_hours_c',
            ],
        ];

        $checks = [];
        foreach (self::CHECKS as $check) {
            $checks[$check] = [];
            foreach ($legacyKeys[$check] as $field => $legacyKey) {
                $prometheeKey = 'maintenance.airframe.'.$check.'.'.$field;
                $value = $this->disposableSetting(
                    $legacyKey,
                    $prometheeValues[$prometheeKey] ?? $fallback[$check][$field]
                );

                $checks[$check][$field] = $field === 'cycle_limit'
                    ? max(1, (int) round($value))
                    : max($field === 'time_limit_hours' ? 0.1 : 0, (float) $value);
            }
        }

        return [
            'checks' => $checks,
            'warning_percent' => max(0, min(100, (float) ($prometheeValues['maintenance.airframe.warning_percent'] ?? 10))),
            'source' => Schema::hasTable('disposable_settings') ? 'disposable_settings' : 'promethee_fallback',
            'per_type_overrides' => Schema::hasTable('disposable_tech_details'),
        ];
    }

    public function saveSettings(array $values): void
    {
        if (!Schema::hasTable('promethee_settings')) {
            throw new RuntimeException('La table de paramètres Prométhée n’est pas disponible.');
        }

        $prometheeMap = [
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

        $disposableMap = [
            'a_time_limit_hours' => ['turksim.maint_lim_at', 'A Check Time Limit'],
            'a_cycle_limit' => ['turksim.maint_lim_ac', 'A Check Cycle Limit'],
            'a_duration_hours' => ['turksim.maint_hours_a', 'A Check Duration'],
            'b_time_limit_hours' => ['turksim.maint_lim_bt', 'B Check Time Limit'],
            'b_cycle_limit' => ['turksim.maint_lim_bc', 'B Check Cycle Limit'],
            'b_duration_hours' => ['turksim.maint_hours_b', 'B Check Duration'],
            'c_time_limit_hours' => ['turksim.maint_lim_ct', 'C Check Time Limit'],
            'c_cycle_limit' => ['turksim.maint_lim_cc', 'C Check Cycle Limit'],
            'c_duration_hours' => ['turksim.maint_hours_c', 'C Check Duration'],
        ];

        DB::transaction(function () use ($values, $prometheeMap, $disposableMap) {
            // Mirror the values in Prométhée for backwards compatibility, but
            // disposable_settings remains the operational source used by the
            // aircraft maintenance records.
            foreach ($prometheeMap as $field => $key) {
                DB::table('promethee_settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => (string) $values[$field], 'created_at' => now(), 'updated_at' => now()]
                );
            }

            if (Schema::hasTable('disposable_settings')) {
                foreach ($disposableMap as $field => [$key, $name]) {
                    DB::table('disposable_settings')->updateOrInsert(
                        ['key' => $key],
                        [
                            'name' => $name,
                            'value' => (string) $values[$field],
                            'default' => (string) $values[$field],
                            'group' => 'Maintenance',
                            'field_type' => str_contains($field, 'duration') ? 'decimal' : 'numeric',
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }

                // Recalculate existing remaining values from their consumed
                // counters. ICAO-specific overrides stay authoritative.
                $this->recalculateLegacyRemaining();
            }
        });
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
        $reason = sprintf(
            'Facteur de charge structurel %.2f g détecté par Hermès (limites +2.9 g / -1.2 g). Inspection technique requise.',
            $gForce
        );

        return $this->placeFdmSafetyHold(
            $pirep,
            'g',
            $reason,
            $recordedAt,
            ['g_force' => $gForce]
        );
    }

    public function placeRunwayOverrunHold(
        Pirep $pirep,
        ?float $distanceMetres = null,
        ?string $runway = null,
        ?string $recordedAt = null
    ): array {
        $details = ['Runway overrun détecté à partir de la trace Hermès'];

        if (filled($runway)) {
            $details[] = 'piste '.strtoupper(trim((string) $runway));
        }
        if ($distanceMetres !== null) {
            $details[] = sprintf('dépassement estimé %.0f m', max(0, $distanceMetres));
        }

        $reason = implode(' · ', $details).'. Immobilisation et inspection technique obligatoires avant remise en service.';

        return $this->placeFdmSafetyHold(
            $pirep,
            'r',
            $reason,
            $recordedAt,
            array_filter([
                'runway' => $runway,
                'distance_metres' => $distanceMetres,
            ], fn ($value) => $value !== null && $value !== '')
        );
    }

    private function placeFdmSafetyHold(
        Pirep $pirep,
        string $checkType,
        string $reason,
        ?string $recordedAt = null,
        array $context = []
    ): array {
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
        $occurredAt = filled($recordedAt) ? \Carbon\Carbon::parse($recordedAt)->utc() : now();
        $checkType = substr(strtolower(trim($checkType)), 0, 1) ?: 'f';

        return DB::transaction(function () use ($pirep, $aircraftId, $occurredAt, $reason, $checkType, $context) {
            $state = DB::table('promethee_airframe_maintenance')
                ->where('aircraft_id', $aircraftId)
                ->lockForUpdate()
                ->first();
            $aircraft = Aircraft::query()->lockForUpdate()->find($aircraftId);

            if (!$state || !$aircraft) {
                return ['updated' => 0, 'reason' => 'AIRCRAFT_NOT_FOUND'];
            }

            if ($state->safety_hold_at ?? null) {
                return [
                    'updated' => 0,
                    'reason' => 'ALREADY_HELD',
                    'aircraft_id' => $aircraftId,
                    'existing_pirep_id' => $state->safety_hold_pirep_id ?? null,
                    'trigger' => $checkType,
                ] + $context;
            }

            DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update([
                'safety_hold_reason' => $reason,
                'safety_hold_at' => $occurredAt,
                'safety_hold_pirep_id' => (string) $pirep->id,
                'safety_hold_previous_status' => (string) $aircraft->status,
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('promethee_airframe_maintenance_events')) {
                DB::table('promethee_airframe_maintenance_events')->insert([
                    'aircraft_id' => $aircraftId,
                    'check_type' => $checkType,
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
                'trigger' => $checkType,
            ] + $context;
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
            $previousStatus = (string) ($state->safety_hold_previous_status ?? AircraftStatus::ACTIVE);
            $releaseCheckType = str_contains(strtolower($previousReason), 'runway overrun') ? 'r' : 'g';
            DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update([
                'safety_hold_reason' => null,
                'safety_hold_at' => null,
                'safety_hold_pirep_id' => null,
                'safety_hold_previous_status' => null,
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('promethee_airframe_maintenance_events')) {
                DB::table('promethee_airframe_maintenance_events')->insert([
                    'aircraft_id' => $aircraftId,
                    'check_type' => $releaseCheckType,
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
                $aircraft->update(['status' => $previousStatus ?: AircraftStatus::ACTIVE]);
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
        $techProfiles = $this->technicalProfiles();

        $query = DB::table('promethee_airframe_maintenance as maintenance')
            ->join('aircraft', 'aircraft.id', '=', 'maintenance.aircraft_id')
            ->leftJoin('subfleets', 'subfleets.id', '=', 'aircraft.subfleet_id')
            ->leftJoin('airlines', 'airlines.id', '=', 'subfleets.airline_id');

        $select = [
            'maintenance.*',
            'aircraft.registration',
            'aircraft.icao',
            'aircraft.airport_id',
            'aircraft.status as aircraft_status',
            'aircraft.state as aircraft_state',
            'subfleets.name as subfleet_name',
            'subfleets.type as subfleet_type',
            'airlines.icao as airline_icao',
        ];

        if (Schema::hasTable('disposable_maintenance')) {
            $query->leftJoin('disposable_maintenance as legacy', 'legacy.aircraft_id', '=', 'maintenance.aircraft_id');
            $select = array_merge($select, [
                'legacy.aircraft_id as legacy_aircraft_id',
                'legacy.curr_state as legacy_curr_state',
                'legacy.time_a as legacy_time_a',
                'legacy.time_b as legacy_time_b',
                'legacy.time_c as legacy_time_c',
                'legacy.cycle_a as legacy_cycle_a',
                'legacy.cycle_b as legacy_cycle_b',
                'legacy.cycle_c as legacy_cycle_c',
                'legacy.rem_ta as legacy_rem_ta',
                'legacy.rem_tb as legacy_rem_tb',
                'legacy.rem_tc as legacy_rem_tc',
                'legacy.rem_ca as legacy_rem_ca',
                'legacy.rem_cb as legacy_rem_cb',
                'legacy.rem_cc as legacy_rem_cc',
                'legacy.last_a as legacy_last_a',
                'legacy.last_b as legacy_last_b',
                'legacy.last_c as legacy_last_c',
                'legacy.last_note as legacy_last_note',
                'legacy.last_time as legacy_last_time',
                'legacy.act_note as legacy_act_note',
                'legacy.act_start as legacy_act_start',
                'legacy.act_end as legacy_act_end',
            ]);
        }

        return $query
            ->select($select)
            ->orderBy('airlines.icao')
            ->orderBy('subfleets.name')
            ->orderBy('aircraft.registration')
            ->get()
            ->map(function ($row) use ($settings, $techProfiles) {
                $icao = strtoupper(trim((string) ($row->icao ?: $row->subfleet_type)));
                return $this->decorate($row, $settings, $techProfiles->get($icao));
            });
    }

    public function rotationPriorities(float $biasPercent): Collection
    {
        $threshold = max(0, min(100, 100 - $biasPercent));

        return $this->fleetStatus()
            ->filter(fn ($row) => !$row->active_check && !($row->safety_hold_at ?? null))
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

        DB::transaction(function () use ($aircraftId, $check, $userId, $settings) {
            $aircraft = Aircraft::query()->lockForUpdate()->find($aircraftId);
            if (!$aircraft) {
                throw new RuntimeException('Appareil introuvable.');
            }
            if ((int) $aircraft->state !== AircraftState::PARKED) {
                throw new RuntimeException('L’appareil doit être au parking pour démarrer un check.');
            }

            $policy = $this->policyForAircraft($aircraft, $settings);
            $durationHours = (float) $policy[$check]['duration_hours'];

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

            if (Schema::hasTable('disposable_maintenance')) {
                $legacyState = DB::table('disposable_maintenance')
                    ->where('aircraft_id', $aircraftId)
                    ->lockForUpdate()
                    ->first();

                if ($legacyState) {
                    DB::table('disposable_maintenance')->where('id', $legacyState->id)->update([
                        'act_note' => strtoupper($check).' Check',
                        'act_start' => $startedAt,
                        'act_end' => $dueAt,
                        'op_type' => 'PROMETHEE',
                        'updated_at' => now(),
                    ]);
                } else {
                    $legacyInsert = [
                        'aircraft_id' => $aircraftId,
                        'curr_state' => 100,
                        'time_a' => 0, 'time_b' => 0, 'time_c' => 0,
                        'cycle_a' => 0, 'cycle_b' => 0, 'cycle_c' => 0,
                        'act_note' => strtoupper($check).' Check',
                        'act_start' => $startedAt,
                        'act_end' => $dueAt,
                        'op_type' => 'PROMETHEE',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    foreach (self::CHECKS as $legacyCheck) {
                        $legacyInsert['rem_t'.$legacyCheck] = (int) round($policy[$legacyCheck]['time_limit_hours'] * 60);
                        $legacyInsert['rem_c'.$legacyCheck] = (int) $policy[$legacyCheck]['cycle_limit'];
                    }

                    DB::table('disposable_maintenance')->insert($legacyInsert);
                }
            }

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
            $legacy = Schema::hasTable('disposable_maintenance')
                ? DB::table('disposable_maintenance')->where('aircraft_id', $aircraftId)->lockForUpdate()->first()
                : null;
            $minutesBefore = $legacy && is_numeric($legacy->{'time_'.$check} ?? null)
                ? (int) $legacy->{'time_'.$check}
                : (int) $state->{$minutesColumn};
            $cyclesBefore = $legacy && is_numeric($legacy->{'cycle_'.$check} ?? null)
                ? (int) $legacy->{'cycle_'.$check}
                : (int) $state->{$cyclesColumn};
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

            if ($legacy) {
                $policy = $this->policyForAircraft($aircraft);
                $legacyUpdate = [
                    'act_note' => null,
                    'act_start' => null,
                    'act_end' => null,
                    'last_note' => strtoupper($check).' Check',
                    'last_time' => now(),
                    'curr_state' => 100,
                    'op_type' => 'PROMETHEE',
                    'updated_at' => now(),
                ];

                foreach ($resetChecks as $reset) {
                    $legacyUpdate['time_'.$reset] = 0;
                    $legacyUpdate['cycle_'.$reset] = 0;
                    $legacyUpdate['rem_t'.$reset] = (int) round($policy[$reset]['time_limit_hours'] * 60);
                    $legacyUpdate['rem_c'.$reset] = (int) $policy[$reset]['cycle_limit'];
                    $legacyUpdate['last_'.$reset] = now();
                }

                DB::table('disposable_maintenance')->where('id', $legacy->id)->update($legacyUpdate);
            }

            DB::table('promethee_airframe_maintenance_events')->insert([
                'aircraft_id' => $aircraftId,
                'check_type' => $check,
                'event_type' => 'completed',
                'airport_id' => $aircraft->airport_id,
                'minutes_before' => $minutesBefore,
                'cycles_before' => $cyclesBefore,
                'created_by' => $userId ?: $state->active_started_by,
                'notes' => null,
                'occurred_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!($state->safety_hold_at ?? null) && $aircraft->status === AircraftStatus::MAINTENANCE) {
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

    private function decorate(object $row, array $settings, ?object $techProfile = null): object
    {
        $row->checks = [];
        $mostUrgent = null;
        $hasLegacyState = isset($row->legacy_aircraft_id) && $row->legacy_aircraft_id !== null;

        foreach (self::CHECKS as $check) {
            $config = $this->checkPolicy($check, $settings['checks'][$check], $techProfile);
            $usedMinutes = $hasLegacyState
                ? max(0, (int) ($row->{'legacy_time_'.$check} ?? 0))
                : max(0, (int) $row->{$check.'_minutes'});
            $usedCycles = $hasLegacyState
                ? max(0, (int) ($row->{'legacy_cycle_'.$check} ?? 0))
                : max(0, (int) $row->{$check.'_cycles'});

            $limitMinutes = max(1, (int) round($config['time_limit_hours'] * 60));
            $limitCycles = max(1, (int) $config['cycle_limit']);

            $legacyRemainingTimeField = 'legacy_rem_t'.$check;
            $legacyRemainingCycleField = 'legacy_rem_c'.$check;
            $remainingMinutes = $hasLegacyState && is_numeric($row->{$legacyRemainingTimeField} ?? null)
                ? (int) $row->{$legacyRemainingTimeField}
                : $limitMinutes - $usedMinutes;
            $remainingCycles = $hasLegacyState && is_numeric($row->{$legacyRemainingCycleField} ?? null)
                ? (int) $row->{$legacyRemainingCycleField}
                : $limitCycles - $usedCycles;

            $timeProgress = ($usedMinutes / $limitMinutes) * 100;
            $cycleProgress = ($usedCycles / $limitCycles) * 100;
            $progress = max($timeProgress, $cycleProgress);
            $due = $remainingMinutes <= 0 || $remainingCycles <= 0;
            $warning = $due || $progress >= (100 - $settings['warning_percent']);

            $row->checks[$check] = [
                'time_limit_hours' => $config['time_limit_hours'],
                'cycle_limit' => $config['cycle_limit'],
                'duration_hours' => $config['duration_hours'],
                'policy_source' => $config['source'],
                'used_hours' => round($usedMinutes / 60, 2),
                'used_cycles' => $usedCycles,
                'remaining_hours' => round($remainingMinutes / 60, 2),
                'remaining_cycles' => $remainingCycles,
                'last_check_at' => $hasLegacyState ? ($row->{'legacy_last_'.$check} ?? null) : ($row->{'last_'.$check.'_at'} ?? null),
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

        $row->current_state_percent = $hasLegacyState && is_numeric($row->legacy_curr_state ?? null)
            ? (float) $row->legacy_curr_state
            : null;
        $row->last_maintenance_note = $hasLegacyState ? ($row->legacy_last_note ?? null) : null;
        $row->last_maintenance_at = $hasLegacyState ? ($row->legacy_last_time ?? null) : null;
        $row->active_legacy_check = $hasLegacyState ? ($row->legacy_act_note ?? null) : null;
        $row->maintenance_source = $hasLegacyState ? 'disposable_maintenance' : 'promethee_fallback';
        $row->next_check = $mostUrgent;
        $row->maintenance_state = ($row->active_check || ($row->safety_hold_at ?? null) || $row->active_legacy_check)
            ? 'maintenance'
            : ($mostUrgent && $row->checks[$mostUrgent]['due'] ? 'due' : ($mostUrgent ? 'warning' : 'serviceable'));

        return $row;
    }

    private function disposableSetting(string $key, int|float|string $fallback): float
    {
        if (!Schema::hasTable('disposable_settings')) {
            return is_numeric($fallback) ? (float) $fallback : 0.0;
        }

        $row = DB::table('disposable_settings')
            ->where('key', $key)
            ->first(['value', 'default']);

        $value = $row && filled($row->value)
            ? $row->value
            : ($row && filled($row->default) ? $row->default : $fallback);

        return is_numeric($value) ? (float) $value : (float) $fallback;
    }

    private function technicalProfiles(): Collection
    {
        if (!Schema::hasTable('disposable_tech_details')) {
            return collect();
        }

        return DB::table('disposable_tech_details')
            ->where('active', true)
            ->get()
            ->filter(fn ($profile) => filled($profile->icao ?? null))
            ->keyBy(fn ($profile) => strtoupper(trim((string) $profile->icao)));
    }

    private function checkPolicy(string $check, array $fallback, ?object $techProfile = null): array
    {
        $timeField = 'max_time_'.$check;
        $cycleField = 'max_cycle_'.$check;
        $durationField = 'duration_'.$check;
        $hasOverride = $techProfile
            && (
                (is_numeric($techProfile->{$timeField} ?? null) && (float) $techProfile->{$timeField} > 0)
                || (is_numeric($techProfile->{$cycleField} ?? null) && (float) $techProfile->{$cycleField} > 0)
                || (is_numeric($techProfile->{$durationField} ?? null) && (float) $techProfile->{$durationField} > 0)
            );

        return [
            'time_limit_hours' => is_numeric($techProfile->{$timeField} ?? null) && (float) $techProfile->{$timeField} > 0
                ? (float) $techProfile->{$timeField}
                : (float) $fallback['time_limit_hours'],
            'cycle_limit' => is_numeric($techProfile->{$cycleField} ?? null) && (int) $techProfile->{$cycleField} > 0
                ? (int) $techProfile->{$cycleField}
                : (int) $fallback['cycle_limit'],
            'duration_hours' => is_numeric($techProfile->{$durationField} ?? null) && (float) $techProfile->{$durationField} > 0
                ? (float) $techProfile->{$durationField}
                : (float) $fallback['duration_hours'],
            'source' => $hasOverride ? 'type_icao' : 'global',
        ];
    }

    private function policyForAircraft(Aircraft $aircraft, ?array $settings = null): array
    {
        $settings ??= $this->settings();
        $techProfile = null;

        if (Schema::hasTable('disposable_tech_details') && filled($aircraft->icao)) {
            $techProfile = DB::table('disposable_tech_details')
                ->where('active', true)
                ->whereRaw('UPPER(icao) = ?', [strtoupper(trim((string) $aircraft->icao))])
                ->first();
        }

        $policy = [];
        foreach (self::CHECKS as $check) {
            $policy[$check] = $this->checkPolicy($check, $settings['checks'][$check], $techProfile);
        }

        return $policy;
    }

    private function recalculateLegacyRemaining(): void
    {
        if (!Schema::hasTable('disposable_maintenance')) return;

        $settings = $this->settings();
        $techProfiles = $this->technicalProfiles();

        DB::table('disposable_maintenance as legacy')
            ->join('aircraft', 'aircraft.id', '=', 'legacy.aircraft_id')
            ->select([
                'legacy.id',
                'legacy.time_a', 'legacy.time_b', 'legacy.time_c',
                'legacy.cycle_a', 'legacy.cycle_b', 'legacy.cycle_c',
                'aircraft.icao',
            ])
            ->orderBy('legacy.id')
            ->chunk(200, function ($rows) use ($settings, $techProfiles) {
                foreach ($rows as $row) {
                    $icao = strtoupper(trim((string) ($row->icao ?? '')));
                    $techProfile = $techProfiles->get($icao);
                    $update = ['updated_at' => now()];

                    foreach (self::CHECKS as $check) {
                        $policy = $this->checkPolicy($check, $settings['checks'][$check], $techProfile);
                        $limitMinutes = (int) round($policy['time_limit_hours'] * 60);
                        $limitCycles = (int) $policy['cycle_limit'];
                        $update['rem_t'.$check] = $limitMinutes - max(0, (int) ($row->{'time_'.$check} ?? 0));
                        $update['rem_c'.$check] = $limitCycles - max(0, (int) ($row->{'cycle_'.$check} ?? 0));
                    }

                    DB::table('disposable_maintenance')->where('id', $row->id)->update($update);
                }
            });
    }

}
