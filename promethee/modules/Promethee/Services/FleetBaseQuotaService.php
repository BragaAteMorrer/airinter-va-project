<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Enums\PirepState;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FleetBaseQuotaService
{
    public const AWAY_GRACE_DAYS = 5;

    /**
     * During this grace window an aircraft away from its assigned base is
     * treated as a pilot-operated temporary absence. It must not trigger a
     * compensating base reassignment or an automatic repatriation decision.
     */
    public function graceDays(): int
    {
        return self::AWAY_GRACE_DAYS;
    }

    public function overview(): array
    {
        if (!Schema::hasTable('promethee_fleet_base_targets')) {
            return [
                'available' => false,
                'grace_days' => self::AWAY_GRACE_DAYS,
                'target_total' => 0,
                'assigned_total' => 0,
                'effective_total' => 0,
                'actionable_away_total' => 0,
                'base_totals' => collect(),
                'groups' => collect(),
            ];
        }

        $targets = $this->targetGroups();
        $aircraft = $this->aircraftRows()->groupBy(fn ($row) => $this->key($row->airline_icao, $row->subfleet_type));
        $assignments = Schema::hasTable('promethee_aircraft_bases')
            ? DB::table('promethee_aircraft_bases')->get()->keyBy('aircraft_id')
            : collect();

        $baseTotals = collect();
        $groups = collect();
        $targetTotal = 0;
        $assignedTotal = 0;
        $effectiveTotal = 0;
        $actionableAwayTotal = 0;

        foreach ($targets as $key => $rows) {
            $first = $rows->first();
            $planes = $aircraft->get($key, collect());
            $targetByBase = $rows->pluck('target_count', 'base_airport_id')->map(fn ($value) => (int) $value);
            $groupTargetTotal = (int) $targetByBase->sum();
            $groupAssignedTotal = 0;
            $groupEffectiveTotal = 0;
            $groupActionable = 0;
            $baseCells = [];

            foreach ($targetByBase as $base => $target) {
                $assigned = 0;
                $onStation = 0;
                $protectedAway = 0;
                $actionableAway = 0;

                foreach ($planes as $plane) {
                    $assignment = $assignments->get((int) $plane->id);
                    if (!$assignment || strtoupper((string) $assignment->base_airport_id) !== $base) {
                        continue;
                    }

                    $assigned++;
                    $current = strtoupper((string) $plane->airport_id);
                    if ($current === $base) {
                        $onStation++;
                    } elseif ($this->isGraceProtected($plane, $assignment)) {
                        $protectedAway++;
                    } else {
                        $actionableAway++;
                    }
                }

                $effective = $onStation + $protectedAway;
                $baseCells[$base] = [
                    'target' => $target,
                    'assigned' => $assigned,
                    'on_station' => $onStation,
                    'protected_away' => $protectedAway,
                    'actionable_away' => $actionableAway,
                    'effective' => $effective,
                    'assignment_delta' => $assigned - $target,
                    'operational_delta' => $effective - $target,
                ];

                $groupAssignedTotal += $assigned;
                $groupEffectiveTotal += $effective;
                $groupActionable += $actionableAway;
                $targetTotal += $target;
                $assignedTotal += $assigned;
                $effectiveTotal += $effective;
                $actionableAwayTotal += $actionableAway;

                $aggregate = $baseTotals->get($base, [
                    'base' => $base,
                    'target' => 0,
                    'assigned' => 0,
                    'effective' => 0,
                    'protected_away' => 0,
                    'actionable_away' => 0,
                ]);
                $aggregate['target'] += $target;
                $aggregate['assigned'] += $assigned;
                $aggregate['effective'] += $effective;
                $aggregate['protected_away'] += $protectedAway;
                $aggregate['actionable_away'] += $actionableAway;
                $baseTotals->put($base, $aggregate);
            }

            $groups->push([
                'airline' => strtoupper((string) $first->airline_icao),
                'type' => (string) $first->subfleet_type,
                'target_total' => $groupTargetTotal,
                'fleet_total' => $planes->count(),
                'assigned_total' => $groupAssignedTotal,
                'effective_total' => $groupEffectiveTotal,
                'actionable_away' => $groupActionable,
                'fleet_size_ok' => $planes->count() === $groupTargetTotal,
                'assignment_ok' => $groupAssignedTotal === $groupTargetTotal
                    && collect($baseCells)->every(fn ($cell) => $cell['assignment_delta'] === 0),
                'bases' => $baseCells,
            ]);
        }

        return [
            'available' => true,
            'grace_days' => self::AWAY_GRACE_DAYS,
            'target_total' => $targetTotal,
            'assigned_total' => $assignedTotal,
            'effective_total' => $effectiveTotal,
            'actionable_away_total' => $actionableAwayTotal,
            'base_totals' => $baseTotals->sortKeys()->values(),
            'groups' => $groups->sortBy(fn ($group) => $group['airline'].'|'.$group['type'])->values(),
        ];
    }

    /**
     * Restore the base assignment matrix to the Excel reference without ever
     * teleporting an aircraft. Physical airport_id stays untouched; only the
     * home/base assignment is corrected. Aircraft in the 5-day pilot grace
     * window, reserved/in flight, under maintenance, or rotation-locked are
     * never used as donors for a correction.
     */
    public function reconcileAssignments(): array
    {
        if (!Schema::hasTable('promethee_fleet_base_targets') || !Schema::hasTable('promethee_aircraft_bases')) {
            return ['moved' => 0, 'blocked_groups' => 0, 'fleet_mismatch_groups' => 0, 'reason' => 'SCHEMA_NOT_READY'];
        }

        $targets = $this->targetGroups();
        $aircraftGroups = $this->aircraftRows()->groupBy(fn ($row) => $this->key($row->airline_icao, $row->subfleet_type));
        $assignments = DB::table('promethee_aircraft_bases')->get()->keyBy('aircraft_id');
        $protectedIds = $this->protectedAircraftIds();

        $moved = 0;
        $blockedGroups = 0;
        $fleetMismatchGroups = 0;

        foreach ($targets as $key => $targetRows) {
            $planes = $aircraftGroups->get($key, collect())->values();
            $targetByBase = $targetRows->pluck('target_count', 'base_airport_id')->map(fn ($value) => (int) $value);
            $targetTotal = (int) $targetByBase->sum();

            if ($planes->count() !== $targetTotal) {
                $fleetMismatchGroups++;
                continue;
            }

            $counts = collect($targetByBase->keys())->mapWithKeys(fn ($base) => [$base => 0]);
            foreach ($planes as $plane) {
                $assignment = $assignments->get((int) $plane->id);
                $base = strtoupper((string) ($assignment->base_airport_id ?? ''));
                if ($counts->has($base)) {
                    $counts[$base] = (int) $counts[$base] + 1;
                }
            }

            $groupBlocked = false;
            foreach ($targetByBase as $destination => $targetCount) {
                while ((int) $counts[$destination] < $targetCount) {
                    $candidate = $planes
                        ->filter(function ($plane) use ($assignments, $counts, $targetByBase, $protectedIds) {
                            $assignment = $assignments->get((int) $plane->id);
                            $source = strtoupper((string) ($assignment->base_airport_id ?? ''));

                            if ($assignment && (!empty($assignment->rotation_locked) || $assignment->repatriation_mission_id)) {
                                return false;
                            }
                            if ($protectedIds->has((int) $plane->id)) {
                                return false;
                            }
                            if ($assignment && $this->isGraceProtected($plane, $assignment)) {
                                return false;
                            }

                            return !$targetByBase->has($source)
                                || (int) $counts[$source] > (int) $targetByBase[$source];
                        })
                        ->sortBy(function ($plane) use ($destination, $assignments, $targetByBase) {
                            $assignment = $assignments->get((int) $plane->id);
                            $source = strtoupper((string) ($assignment->base_airport_id ?? ''));
                            $current = strtoupper((string) $plane->airport_id);

                            return sprintf(
                                '%d|%d|%s',
                                $current === $destination ? 0 : 1,
                                $targetByBase->has($source) ? 1 : 0,
                                strtoupper((string) $plane->registration)
                            );
                        })
                        ->first();

                    if (!$candidate) {
                        $groupBlocked = true;
                        break;
                    }

                    $assignment = $assignments->get((int) $candidate->id);
                    $source = strtoupper((string) ($assignment->base_airport_id ?? ''));
                    $current = strtoupper((string) $candidate->airport_id);
                    $awaySince = $current === $destination
                        ? null
                        : ($candidate->landing_time ?: now());

                    DB::transaction(function () use ($candidate, $destination, $awaySince, $assignment) {
                        $values = [
                            'base_airport_id' => $destination,
                            'assigned_at' => now(),
                            'away_since' => $awaySince,
                            'repatriation_mission_id' => null,
                            'updated_at' => now(),
                        ];
                        if ($assignment) {
                            DB::table('promethee_aircraft_bases')->where('aircraft_id', (int) $candidate->id)->update($values);
                        } else {
                            DB::table('promethee_aircraft_bases')->insert($values + [
                                'aircraft_id' => (int) $candidate->id,
                                'created_at' => now(),
                            ]);
                        }
                        DB::table('aircraft')->where('id', (int) $candidate->id)->update([
                            'hub_id' => $destination,
                            'updated_at' => now(),
                        ]);
                    });

                    if ($counts->has($source)) {
                        $counts[$source] = max(0, (int) $counts[$source] - 1);
                    }
                    $counts[$destination] = (int) $counts[$destination] + 1;
                    $assignments->put((int) $candidate->id, (object) [
                        'aircraft_id' => (int) $candidate->id,
                        'base_airport_id' => $destination,
                        'assigned_at' => now(),
                        'away_since' => $awaySince,
                        'repatriation_mission_id' => null,
                        'rotation_locked' => false,
                        'last_rotated_at' => $assignment->last_rotated_at ?? null,
                    ]);
                    $moved++;
                }

                if ($groupBlocked) {
                    break;
                }
            }

            if ($groupBlocked || collect($targetByBase)->contains(function ($target, $base) use ($counts) {
                return (int) ($counts[$base] ?? 0) !== (int) $target;
            })) {
                $blockedGroups++;
            }
        }

        return [
            'moved' => $moved,
            'blocked_groups' => $blockedGroups,
            'fleet_mismatch_groups' => $fleetMismatchGroups,
            'reason' => $moved > 0 ? 'RECONCILED' : 'NO_CHANGE',
        ];
    }

    public function assignmentDecision(Aircraft $aircraft, string $newBase): array
    {
        $newBase = strtoupper($newBase);
        $aircraft->loadMissing('subfleet.airline');
        $airline = strtoupper((string) $aircraft->subfleet?->airline?->icao);
        $type = (string) $aircraft->subfleet?->type;

        if ($airline === '' || $type === '' || !Schema::hasTable('promethee_fleet_base_targets')) {
            return ['allowed' => true, 'message' => null];
        }

        $targets = DB::table('promethee_fleet_base_targets')
            ->where('airline_icao', $airline)
            ->where('subfleet_type', $type)
            ->pluck('target_count', 'base_airport_id')
            ->map(fn ($value) => (int) $value);

        if ($targets->isEmpty()) {
            return ['allowed' => true, 'message' => null];
        }
        if (!$targets->has($newBase) || (int) $targets[$newBase] <= 0) {
            return ['allowed' => false, 'message' => 'Cette base n’est pas prévue pour ce type dans la répartition flotte Excel.'];
        }

        $assignment = DB::table('promethee_aircraft_bases')->where('aircraft_id', $aircraft->id)->first();
        $currentBase = strtoupper((string) ($assignment->base_airport_id ?? ''));
        if ($currentBase === $newBase) {
            return ['allowed' => true, 'message' => null];
        }
        if ($assignment && $this->isGraceProtected((object) [
            'airport_id' => $aircraft->airport_id,
            'landing_time' => $aircraft->landing_time,
        ], $assignment)) {
            return [
                'allowed' => false,
                'message' => 'Appareil protégé par la fenêtre pilote de '.self::AWAY_GRACE_DAYS.' jours : sa base ne peut pas être recalculée pour le moment.',
            ];
        }

        $groupIds = DB::table('aircraft as ac')
            ->join('subfleets as sf', 'sf.id', '=', 'ac.subfleet_id')
            ->join('airlines as al', 'al.id', '=', 'sf.airline_id')
            ->where('al.icao', $airline)
            ->where('sf.type', $type)
            ->when(Schema::hasColumn('aircraft', 'deleted_at'), fn ($query) => $query->whereNull('ac.deleted_at'))
            ->pluck('ac.id')->map(fn ($id) => (int) $id);

        $groupAssignments = DB::table('promethee_aircraft_bases')
            ->whereIn('aircraft_id', $groupIds)
            ->get();
        $counts = collect($targets->keys())->mapWithKeys(fn ($base) => [$base => 0]);
        foreach ($groupAssignments as $row) {
            $base = strtoupper((string) $row->base_airport_id);
            if ($counts->has($base)) {
                $counts[$base] = (int) $counts[$base] + 1;
            }
        }

        $before = $this->distributionScore($counts, $targets);
        $after = clone $counts;
        if ($after->has($currentBase)) {
            $after[$currentBase] = max(0, (int) $after[$currentBase] - 1);
        }
        $after[$newBase] = (int) $after[$newBase] + 1;
        $afterScore = $this->distributionScore($after, $targets);

        if ($afterScore >= $before) {
            return [
                'allowed' => false,
                'message' => 'Affectation refusée : elle ferait dériver la répartition de référence Excel. Utilisez une permutation de flotte ou corrigez d’abord un déficit réel.',
            ];
        }

        return ['allowed' => true, 'message' => null];
    }

    private function distributionScore(Collection $counts, Collection $targets): int
    {
        $score = 0;
        foreach ($targets as $base => $target) {
            $score += abs((int) ($counts[$base] ?? 0) - (int) $target);
        }

        return $score;
    }

    private function targetGroups(): Collection
    {
        return DB::table('promethee_fleet_base_targets')
            ->orderBy('airline_icao')
            ->orderBy('subfleet_type')
            ->orderBy('base_airport_id')
            ->get()
            ->groupBy(fn ($row) => $this->key($row->airline_icao, $row->subfleet_type));
    }

    private function aircraftRows(): Collection
    {
        if (!Schema::hasTable('aircraft') || !Schema::hasTable('subfleets') || !Schema::hasTable('airlines')) {
            return collect();
        }

        return DB::table('aircraft as ac')
            ->join('subfleets as sf', 'sf.id', '=', 'ac.subfleet_id')
            ->join('airlines as al', 'al.id', '=', 'sf.airline_id')
            ->select([
                'ac.id',
                'ac.registration',
                'ac.airport_id',
                'ac.hub_id',
                'ac.landing_time',
                'sf.type as subfleet_type',
                'al.icao as airline_icao',
            ])
            ->when(Schema::hasColumn('aircraft', 'deleted_at'), fn ($query) => $query->whereNull('ac.deleted_at'))
            ->get();
    }

    private function protectedAircraftIds(): Collection
    {
        $ids = collect();

        if (Schema::hasTable('bids')) {
            $ids = $ids->merge(DB::table('bids')->whereNotNull('aircraft_id')->pluck('aircraft_id'));
        }
        if (Schema::hasTable('pireps')) {
            $ids = $ids->merge(DB::table('pireps')
                ->whereIn('state', [PirepState::IN_PROGRESS, PirepState::PAUSED, PirepState::PENDING, PirepState::DRAFT])
                ->whereNotNull('aircraft_id')->pluck('aircraft_id'));
        }
        if (Schema::hasTable('promethee_missions')) {
            $ids = $ids->merge(DB::table('promethee_missions')->where('active', true)->whereNotNull('aircraft_id')->pluck('aircraft_id'));
        }
        if (Schema::hasTable('disposable_maintenance')) {
            $ids = $ids->merge(DB::table('disposable_maintenance')->whereNotNull('act_note')->pluck('aircraft_id'));
        }
        if (Schema::hasTable('promethee_airframe_maintenance')) {
            $ids = $ids->merge(DB::table('promethee_airframe_maintenance')->whereNotNull('active_check')->pluck('aircraft_id'));
        }

        return $ids->map(fn ($id) => (int) $id)->unique()->flip();
    }

    private function isGraceProtected(object $plane, object $assignment): bool
    {
        $base = strtoupper((string) ($assignment->base_airport_id ?? ''));
        $current = strtoupper((string) ($plane->airport_id ?? ''));
        if ($base === '' || $current === '' || $base === $current) {
            return false;
        }

        $awaySince = $assignment->away_since ?? null;
        if (!$awaySince && !empty($plane->landing_time)) {
            $awaySince = $plane->landing_time;
        }
        if (!$awaySince) {
            return true;
        }

        try {
            return (int) CarbonImmutable::parse($awaySince)->diffInDays(now()) <= self::AWAY_GRACE_DAYS;
        } catch (\Throwable) {
            return true;
        }
    }

    private function key(?string $airline, ?string $type): string
    {
        return strtoupper(trim((string) $airline)).'|'.trim((string) $type);
    }
}
