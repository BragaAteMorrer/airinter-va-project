<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Enums\PirepState;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FleetRotationService
{
    public function settings(): array
    {
        $values = Schema::hasTable('promethee_settings')
            ? DB::table('promethee_settings')->whereIn('key', [
                'regional.rotation_enabled',
                'regional.rotation_frequency',
                'regional.rotation_percent',
                'regional.rotation_min_idle_hours',
                'regional.rotation_cooldown_days',
                'regional.rotation_maintenance_bias_hours',
                'regional.rotation_airframe_bias_percent',
                'regional.rotation_last_run_at',
            ])->pluck('value', 'key')
            : collect();

        $frequency = (string) ($values['regional.rotation_frequency'] ?? 'daily');
        if (!in_array($frequency, ['daily', 'weekly'], true)) {
            $frequency = 'daily';
        }

        return [
            'enabled' => ($values['regional.rotation_enabled'] ?? '0') === '1',
            'frequency' => $frequency,
            'percent' => max(0, min(100, (int) ($values['regional.rotation_percent'] ?? 20))),
            'min_idle_hours' => max(0, min(720, (int) ($values['regional.rotation_min_idle_hours'] ?? 8))),
            'cooldown_days' => max(0, min(365, (int) ($values['regional.rotation_cooldown_days'] ?? 3))),
            'maintenance_bias_hours' => max(0, min(5000, (int) ($values['regional.rotation_maintenance_bias_hours'] ?? 50))),
            'airframe_bias_percent' => max(0, min(100, (float) ($values['regional.rotation_airframe_bias_percent'] ?? 10))),
            'last_run_at' => trim((string) ($values['regional.rotation_last_run_at'] ?? '')) ?: null,
        ];
    }

    public function rotate(bool $force = false): array
    {
        if (!Schema::hasTable('promethee_aircraft_bases') || !Schema::hasTable('promethee_fleet_rotation_log')) {
            return ['pairs' => 0, 'aircraft' => 0, 'maintenance_priority' => 0, 'airframe_maintenance_priority' => 0, 'reason' => 'SCHEMA_NOT_READY'];
        }

        $settings = $this->settings();
        if (!$force && !$settings['enabled']) {
            return ['pairs' => 0, 'aircraft' => 0, 'maintenance_priority' => 0, 'airframe_maintenance_priority' => 0, 'reason' => 'DISABLED'];
        }
        if (!$force && !$this->isDue($settings)) {
            return ['pairs' => 0, 'aircraft' => 0, 'maintenance_priority' => 0, 'airframe_maintenance_priority' => 0, 'reason' => 'NOT_DUE'];
        }

        $candidates = $this->eligibleAircraft($settings);
        if ($candidates->count() < 2 || $settings['percent'] <= 0) {
            $this->markRun();
            return ['pairs' => 0, 'aircraft' => 0, 'maintenance_priority' => 0, 'airframe_maintenance_priority' => 0, 'reason' => 'NO_CANDIDATES'];
        }

        $maxAircraft = min(
            $candidates->count(),
            max(2, (int) floor($candidates->count() * ($settings['percent'] / 100)))
        );
        $pairBudget = intdiv($maxAircraft, 2);
        if ($pairBudget < 1) {
            $this->markRun();
            return ['pairs' => 0, 'aircraft' => 0, 'maintenance_priority' => 0, 'airframe_maintenance_priority' => 0, 'reason' => 'NO_BUDGET'];
        }

        $used = [];
        $pairs = 0;
        $maintenancePriority = 0;
        $airframeMaintenancePriority = 0;
        $overhaulBases = Schema::hasColumn('promethee_operational_bases', 'engine_overhaul')
            ? DB::table('promethee_operational_bases')->where('active', true)->where('engine_overhaul', true)->pluck('airport_id')->map(fn ($id) => strtoupper((string) $id))->all()
            : ['LFPO', 'LFPG'];

        $checkBases = [];
        foreach (['a', 'b', 'c'] as $check) {
            $column = 'check_'.$check;
            $checkBases[$check] = Schema::hasTable('promethee_operational_bases') && Schema::hasColumn('promethee_operational_bases', $column)
                ? DB::table('promethee_operational_bases')->where('active', true)->where($column, true)->pluck('airport_id')->map(fn ($id) => strtoupper((string) $id))->all()
                : [];
        }

        $groups = $candidates->groupBy('subfleet_id');

        // First use invisible rotation to bring an engine nearing TBO to a
        // capable maintenance station, without changing the base quota.
        foreach ($groups as $group) {
            if ($pairs >= $pairBudget) {
                break;
            }

            foreach ($group->where('engine_priority', true) as $priority) {
                if ($pairs >= $pairBudget || isset($used[$priority->aircraft_id]) || in_array($priority->base, $overhaulBases, true)) {
                    continue;
                }

                $partner = $group->first(function ($candidate) use ($priority, $used, $overhaulBases) {
                    return $candidate->aircraft_id !== $priority->aircraft_id
                        && !isset($used[$candidate->aircraft_id])
                        && $candidate->base !== $priority->base
                        && in_array($candidate->base, $overhaulBases, true)
                        && !$candidate->engine_priority
                        && !$candidate->airframe_check;
                });

                if ($partner && $this->swap($priority, $partner, 'engine_maintenance_bias')) {
                    $used[$priority->aircraft_id] = true;
                    $used[$partner->aircraft_id] = true;
                    $pairs++;
                    $maintenancePriority++;
                }
            }
        }

        // Then preserve the same base quotas while steering airframes nearing
        // an A/B/C limit toward a station capable of the required check.
        foreach ($groups as $group) {
            if ($pairs >= $pairBudget) {
                break;
            }

            foreach ($group->filter(fn ($candidate) => $candidate->airframe_check !== null) as $priority) {
                if ($pairs >= $pairBudget || isset($used[$priority->aircraft_id])) {
                    continue;
                }

                $capableBases = $checkBases[$priority->airframe_check] ?? [];
                if (!$capableBases || in_array($priority->base, $capableBases, true)) {
                    continue;
                }

                $partner = $group->first(function ($candidate) use ($priority, $used, $capableBases) {
                    return $candidate->aircraft_id !== $priority->aircraft_id
                        && !isset($used[$candidate->aircraft_id])
                        && $candidate->base !== $priority->base
                        && in_array($candidate->base, $capableBases, true)
                        && !$candidate->engine_priority
                        && !$candidate->airframe_check;
                });

                if ($partner && $this->swap($priority, $partner, 'airframe_maintenance_bias_'.$priority->airframe_check)) {
                    $used[$priority->aircraft_id] = true;
                    $used[$partner->aircraft_id] = true;
                    $pairs++;
                    $airframeMaintenancePriority++;
                }
            }
        }

        // Finally rotate the oldest eligible airframes between different bases.
        foreach ($groups as $group) {
            if ($pairs >= $pairBudget) {
                break;
            }

            $available = $group
                // Aircraft nearing engine TBO or an A/B/C limit may only move
                // toward maintenance in the priority passes above.
                ->filter(fn ($candidate) => !isset($used[$candidate->aircraft_id]) && !$candidate->engine_priority && !$candidate->airframe_check)
                ->sortBy(fn ($candidate) => $candidate->last_rotated_at ?: '1970-01-01 00:00:00')
                ->values();

            while ($available->count() >= 2 && $pairs < $pairBudget) {
                $first = $available->shift();
                $partnerIndex = $available->search(fn ($candidate) => $candidate->base !== $first->base);
                if ($partnerIndex === false) {
                    continue;
                }

                $second = $available->get($partnerIndex);
                $available->forget($partnerIndex);
                $available = $available->values();

                if ($this->swap($first, $second, 'routine')) {
                    $used[$first->aircraft_id] = true;
                    $used[$second->aircraft_id] = true;
                    $pairs++;
                }
            }
        }

        $this->markRun();

        return [
            'pairs' => $pairs,
            'aircraft' => $pairs * 2,
            'maintenance_priority' => $maintenancePriority,
            'airframe_maintenance_priority' => $airframeMaintenancePriority,
            'reason' => $pairs > 0 ? 'ROTATED' : 'NO_COMPATIBLE_PAIR',
        ];
    }

    private function eligibleAircraft(array $settings): Collection
    {
        $assignments = DB::table('promethee_aircraft_bases')->get()->keyBy('aircraft_id');
        $bidAircraft = Schema::hasTable('bids')
            ? DB::table('bids')->whereNotNull('aircraft_id')->pluck('aircraft_id')->map(fn ($id) => (int) $id)->flip()
            : collect();
        $busyPireps = Schema::hasTable('pireps')
            ? DB::table('pireps')->whereIn('state', [PirepState::IN_PROGRESS, PirepState::PAUSED, PirepState::PENDING, PirepState::DRAFT])->whereNotNull('aircraft_id')->pluck('aircraft_id')->map(fn ($id) => (int) $id)->flip()
            : collect();
        $missionAircraft = Schema::hasTable('promethee_missions')
            ? DB::table('promethee_missions')->where('active', true)->whereNotNull('aircraft_id')->pluck('aircraft_id')->map(fn ($id) => (int) $id)->flip()
            : collect();
        $legacyMaintenanceAircraft = Schema::hasTable('disposable_maintenance')
            ? DB::table('disposable_maintenance')->whereNotNull('act_note')->pluck('aircraft_id')->map(fn ($id) => (int) $id)->flip()
            : collect();
        $airframeMaintenanceAircraft = Schema::hasTable('promethee_airframe_maintenance')
            ? DB::table('promethee_airframe_maintenance')->whereNotNull('active_check')->pluck('aircraft_id')->map(fn ($id) => (int) $id)->flip()
            : collect();
        $enginePriority = $this->enginePriorityAircraft((float) $settings['maintenance_bias_hours']);
        $airframePriority = Schema::hasTable('promethee_airframe_maintenance')
            ? app(AirframeMaintenanceService::class)->rotationPriorities((float) $settings['airframe_bias_percent'])
            : collect();

        $idleBefore = now()->subHours($settings['min_idle_hours']);
        $rotatedBefore = now()->subDays($settings['cooldown_days']);

        return Aircraft::query()
            ->where('status', AircraftStatus::ACTIVE)
            ->where('state', AircraftState::PARKED)
            ->whereNotNull('subfleet_id')
            ->orderBy('id')
            ->get(['id', 'subfleet_id', 'registration', 'airport_id', 'hub_id', 'landing_time', 'status', 'state'])
            ->map(function (Aircraft $aircraft) use ($assignments, $bidAircraft, $busyPireps, $missionAircraft, $legacyMaintenanceAircraft, $airframeMaintenanceAircraft, $enginePriority, $airframePriority, $idleBefore, $rotatedBefore, $settings) {
                $assignment = $assignments->get($aircraft->id);
                if (!$assignment) {
                    return null;
                }

                $base = strtoupper((string) $assignment->base_airport_id);
                $current = strtoupper((string) $aircraft->airport_id);
                if ($base === '' || $current !== $base) {
                    return null;
                }
                if (!empty($assignment->rotation_locked) || $assignment->away_since || $assignment->repatriation_mission_id) {
                    return null;
                }
                if (
                    $bidAircraft->has((int) $aircraft->id)
                    || $busyPireps->has((int) $aircraft->id)
                    || $missionAircraft->has((int) $aircraft->id)
                    || $legacyMaintenanceAircraft->has((int) $aircraft->id)
                    || $airframeMaintenanceAircraft->has((int) $aircraft->id)
                ) {
                    return null;
                }
                if ($aircraft->landing_time && CarbonImmutable::parse($aircraft->landing_time)->greaterThan($idleBefore)) {
                    return null;
                }
                if ($settings['cooldown_days'] > 0 && $assignment->last_rotated_at && CarbonImmutable::parse($assignment->last_rotated_at)->greaterThan($rotatedBefore)) {
                    return null;
                }

                return (object) [
                    'aircraft_id' => (int) $aircraft->id,
                    'subfleet_id' => (int) $aircraft->subfleet_id,
                    'registration' => $aircraft->registration,
                    'base' => $base,
                    'last_rotated_at' => $assignment->last_rotated_at,
                    'engine_priority' => $enginePriority->has((int) $aircraft->id),
                    'airframe_check' => $airframePriority->get((int) $aircraft->id)?->check,
                ];
            })
            ->filter()
            ->values();
    }

    private function enginePriorityAircraft(float $biasHours): Collection
    {
        if ($biasHours <= 0 || !Schema::hasTable('promethee_aircraft_engines')) {
            return collect();
        }

        $rows = DB::table('promethee_aircraft_engines as installation')
            ->join('promethee_engines as engine', 'engine.id', '=', 'installation.engine_id')
            ->leftJoin('promethee_engine_profiles as profile', 'profile.id', '=', 'engine.engine_profile_id')
            ->select([
                'installation.aircraft_id',
                'engine.tbo_hours',
                'engine.hours_since_overhaul',
                'engine.tbo_cycles',
                'engine.cycles_since_overhaul',
                'profile.warning_cycles',
            ])->get();

        return $rows->filter(function ($row) use ($biasHours) {
            $hoursDue = $row->tbo_hours !== null
                && ((float) $row->tbo_hours - (float) $row->hours_since_overhaul) <= $biasHours;
            $cyclesDue = $row->tbo_cycles !== null && $row->warning_cycles !== null
                && ((int) $row->tbo_cycles - (int) $row->cycles_since_overhaul) <= (int) $row->warning_cycles;
            return $hoursDue || $cyclesDue;
        })->pluck('aircraft_id')->map(fn ($id) => (int) $id)->unique()->flip();
    }

    private function swap(object $first, object $second, string $reason): bool
    {
        if ($first->subfleet_id !== $second->subfleet_id || $first->base === $second->base) {
            return false;
        }

        return DB::transaction(function () use ($first, $second, $reason) {
            $firstAssignment = DB::table('promethee_aircraft_bases')->where('aircraft_id', $first->aircraft_id)->lockForUpdate()->first();
            $secondAssignment = DB::table('promethee_aircraft_bases')->where('aircraft_id', $second->aircraft_id)->lockForUpdate()->first();
            $firstPlane = Aircraft::where('id', $first->aircraft_id)->lockForUpdate()->first();
            $secondPlane = Aircraft::where('id', $second->aircraft_id)->lockForUpdate()->first();

            if (!$firstAssignment || !$secondAssignment || !$firstPlane || !$secondPlane) {
                return false;
            }

            $firstBase = strtoupper((string) $firstAssignment->base_airport_id);
            $secondBase = strtoupper((string) $secondAssignment->base_airport_id);
            if (
                $firstBase === $secondBase
                || strtoupper((string) $firstPlane->airport_id) !== $firstBase
                || strtoupper((string) $secondPlane->airport_id) !== $secondBase
                || $firstAssignment->away_since
                || $secondAssignment->away_since
                || $firstAssignment->repatriation_mission_id
                || $secondAssignment->repatriation_mission_id
                || !empty($firstAssignment->rotation_locked)
                || !empty($secondAssignment->rotation_locked)
            ) {
                return false;
            }

            $now = now();

            $firstPlane->update(['airport_id' => $secondBase, 'hub_id' => $secondBase]);
            $secondPlane->update(['airport_id' => $firstBase, 'hub_id' => $firstBase]);

            DB::table('promethee_aircraft_bases')->where('aircraft_id', $firstPlane->id)->update([
                'base_airport_id' => $secondBase,
                'assigned_at' => $now,
                'away_since' => null,
                'repatriation_mission_id' => null,
                'last_rotated_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('promethee_aircraft_bases')->where('aircraft_id', $secondPlane->id)->update([
                'base_airport_id' => $firstBase,
                'assigned_at' => $now,
                'away_since' => null,
                'repatriation_mission_id' => null,
                'last_rotated_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('promethee_fleet_rotation_log')->insert([
                'first_aircraft_id' => $firstPlane->id,
                'second_aircraft_id' => $secondPlane->id,
                'first_from_base' => $firstBase,
                'second_from_base' => $secondBase,
                'reason' => $reason,
                'rotated_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return true;
        });
    }

    private function isDue(array $settings): bool
    {
        if (!$settings['last_run_at']) {
            return true;
        }

        try {
            $last = CarbonImmutable::parse($settings['last_run_at'], 'Europe/Paris');
        } catch (\Throwable) {
            return true;
        }

        $now = CarbonImmutable::now('Europe/Paris');
        return $settings['frequency'] === 'weekly'
            ? $last->lessThanOrEqualTo($now->subDays(7))
            : !$last->isSameDay($now);
    }

    private function markRun(): void
    {
        if (!Schema::hasTable('promethee_settings')) {
            return;
        }

        DB::table('promethee_settings')->updateOrInsert(
            ['key' => 'regional.rotation_last_run_at'],
            ['value' => CarbonImmutable::now('Europe/Paris')->toIso8601String(), 'created_at' => now(), 'updated_at' => now()]
        );
    }
}
