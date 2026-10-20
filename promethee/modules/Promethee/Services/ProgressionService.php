<?php

namespace Modules\Promethee\Services;

use App\Models\Pirep;
use App\Models\Rank;
use App\Models\User;
use App\Models\UserAward;
use App\Models\Enums\PirepState;
use App\Models\Enums\UserState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Evaluates Prométhée's configurable awards and rank policies. */
class ProgressionService
{
    public function eligible(User $user, array $rule): bool
    {
        $criteria = $rule['criteria'] ?? [];
        if (!$criteria) {
            return false;
        }

        $checks = collect($criteria)->map(fn ($criterion) => $this->check($user, (array) $criterion));

        return ($rule['operator'] ?? 'and') === 'or'
            ? $checks->contains(true)
            : $checks->every(fn ($value) => $value);
    }

    public function recalculate(?User $only = null, string $origin = 'scheduled'): array
    {
        $awards = 0;
        $promotions = 0;

        // Rules are global configuration: load them once, not once per pilot.
        $badgeRules = DB::table('promethee_badge_rules')
            ->where('active', true)
            ->orderBy('id')
            ->get();

        $rankRules = DB::table('promethee_rank_rules')
            ->where('active', true)
            ->get();

        $ranks = Rank::query()
            ->whereIn('id', $rankRules->pluck('rank_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $rankRules = $rankRules->sortByDesc(
            fn ($rule) => $ranks->get($rule->rank_id)?->hours ?? -1
        );

        $users = $only
            ? collect([$only])
            : User::where('state', UserState::ACTIVE)->cursor();

        foreach ($users as $user) {
            // Resolve badge dependencies in the same transaction cycle. A badge
            // which requires another badge can therefore be granted immediately
            // after its prerequisite is granted.
            $pendingBadgeRules = $badgeRules->keyBy('id');
            do {
                $awardedThisPass = 0;

                foreach ($pendingBadgeRules as $ruleId => $rule) {
                    if (UserAward::where([
                        'user_id' => $user->id,
                        'award_id' => $rule->award_id,
                    ])->exists()) {
                        $pendingBadgeRules->forget($ruleId);
                        continue;
                    }

                    $criteria = json_decode($rule->criteria, true) ?: [];
                    if (!$this->eligible($user, [
                        'operator' => $rule->operator,
                        'criteria' => $criteria,
                    ])) {
                        continue;
                    }

                    $userAward = UserAward::firstOrCreate([
                        'user_id' => $user->id,
                        'award_id' => $rule->award_id,
                    ]);

                    $pendingBadgeRules->forget($ruleId);
                    if (!$userAward->wasRecentlyCreated) {
                        continue;
                    }

                    DB::table('promethee_progression_history')->insert([
                        'user_id' => $user->id,
                        'kind' => 'badge',
                        'subject_id' => (string) $rule->award_id,
                        'before_value' => null,
                        'after_value' => (string) $rule->award_id,
                        'origin' => $origin,
                        'reason' => 'Rule #'.$rule->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $awards++;
                    $awardedThisPass++;
                }
            } while ($awardedThisPass > 0 && $pendingBadgeRules->isNotEmpty());

            // Awards explicitly associated with missions and circuits are
            // granted by the same progression engine after accepted PIREPs.
            $awards += $this->grantCompletionAwards($user, $origin);

            // Highest eligible rank wins. This makes overlapping rules
            // deterministic and prevents a lower rule from undoing a promotion.
            foreach ($rankRules as $rule) {
                $rank = $ranks->get($rule->rank_id);
                if (!$rank) {
                    continue;
                }

                $criteria = json_decode($rule->criteria, true) ?: [];
                if (!$this->eligible($user, [
                    'operator' => $rule->operator,
                    'criteria' => $criteria,
                ])) {
                    continue;
                }

                $currentHours = $user->rank?->hours;
                $isPromotion = $currentHours === null || $rank->hours >= $currentHours;

                if ($isPromotion || $rule->allow_demotion) {
                    if ((int) $user->rank_id !== (int) $rank->id) {
                        $before = $user->rank_id;
                        $user->rank_id = $rank->id;
                        $user->save();
                        $user->setRelation('rank', $rank);

                        DB::table('promethee_progression_history')->insert([
                            'user_id' => $user->id,
                            'kind' => 'rank',
                            'subject_id' => (string) $rank->id,
                            'before_value' => $before === null ? null : (string) $before,
                            'after_value' => (string) $rank->id,
                            'origin' => $origin,
                            'reason' => 'Rule #'.$rule->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $promotions++;
                    }
                }

                // A promotion has been selected; do not let a lower eligible
                // rule replace it later in the same evaluation.
                if ($isPromotion) {
                    break;
                }
            }
        }

        return compact('awards', 'promotions');
    }

    private function check(User $user, array $criterion): bool
    {
        $value = (float) ($criterion['value'] ?? 0);
        $metric = $criterion['metric'] ?? '';

        return match ($metric) {
            'validated_flights' => Pirep::where('user_id', $user->id)->where('state', PirepState::ACCEPTED)->count() >= $value,
            'flight_minutes' => (float) $user->flight_time >= $value,
            'total_distance' => (float) Pirep::where('user_id', $user->id)->where('state', PirepState::ACCEPTED)->sum('distance') >= $value,
            'visited_airports' => $this->visitedAirports($user) >= $value,
            'visited_countries' => $this->visitedCountries($user) >= $value,
            'seniority_days' => $user->created_at && $user->created_at->diffInDays(now()) >= $value,
            'required_badge' => UserAward::where(['user_id' => $user->id, 'award_id' => (int) $value])->exists(),
            'route' => Pirep::where('user_id', $user->id)->where('state', PirepState::ACCEPTED)->where('route_code', (string) ($criterion['route'] ?? ''))->exists(),
            'airline' => Pirep::where('user_id', $user->id)->where('state', PirepState::ACCEPTED)->where('airline_id', (int) $value)->exists(),
            'aircraft_icao' => Pirep::where('pireps.user_id', $user->id)
                ->where('pireps.state', PirepState::ACCEPTED)
                ->whereHas('aircraft', fn ($aircraft) => $aircraft->where('icao', strtoupper((string) ($criterion['text'] ?? ''))))
                ->exists(),
            'event_completed' => Pirep::where('user_id', $user->id)->where('state', PirepState::ACCEPTED)->where('event_id', (int) $value)->exists(),
            'mission_completed' => $this->missionCompleted($user, (int) $value),
            'circuit_completed' => $this->circuitCompleted($user, (int) $value),
            'night_flights' => Pirep::where('user_id', $user->id)
                ->where('state', PirepState::ACCEPTED)
                ->whereNotNull('block_off_time')
                ->whereRaw('HOUR(block_off_time) BETWEEN 0 AND 5')
                ->count() >= $value,
            default => false,
        };
    }

    private function missionCompleted(User $user, int $missionId): bool
    {
        if ($missionId < 1 || !Schema::hasTable('promethee_missions')) return false;
        $mission = DB::table('promethee_missions')->where('id', $missionId)->first();
        return $mission && $this->matchingAcceptedPireps($user, $mission)->exists();
    }

    private function circuitCompleted(User $user, int $circuitId): bool
    {
        if ($circuitId < 1 || !Schema::hasTable('promethee_circuits')
            || !Schema::hasTable('promethee_circuit_legs')) return false;
        $circuit = DB::table('promethee_circuits')->where('id', $circuitId)->first();
        if (!$circuit) return false;
        $legs = DB::table('promethee_circuit_legs')->where('circuit_id', $circuitId)->get();
        if ($legs->isEmpty()) return false;
        foreach ($legs as $leg) {
            $leg->created_at = $circuit->created_at;
            $leg->starts_on = $circuit->starts_on;
            if (!$this->matchingAcceptedPireps($user, $leg)->exists()) return false;
        }
        return true;
    }

    private function matchingAcceptedPireps(User $user, object $leg): \Illuminate\Database\Eloquent\Builder
    {
        $query = Pirep::where('user_id', $user->id)->where('state', PirepState::ACCEPTED);
        if (!empty($leg->flight_id)) $query->where('flight_id', $leg->flight_id);
        if (!empty($leg->dpt_airport_id)) $query->where('dpt_airport_id', $leg->dpt_airport_id);
        if (!empty($leg->arr_airport_id)) $query->where('arr_airport_id', $leg->arr_airport_id);
        $start = $leg->starts_on ?? $leg->created_at ?? null;
        if ($start) $query->where('submitted_at', '>=', $start);
        return $query;
    }

    private function grantCompletionAwards(User $user, string $origin): int
    {
        $granted = 0;
        foreach ([
            ['table' => 'promethee_missions', 'type' => 'mission'],
            ['table' => 'promethee_circuits', 'type' => 'circuit'],
        ] as $source) {
            if (!Schema::hasTable($source['table']) || !Schema::hasColumn($source['table'], 'award_id')) {
                continue;
            }
            $items = DB::table($source['table'])->whereNotNull('award_id')->get(['id', 'award_id']);
            foreach ($items as $item) {
                $eligible = $source['type'] === 'mission'
                    ? $this->missionCompleted($user, (int) $item->id)
                    : $this->circuitCompleted($user, (int) $item->id);
                if (!$eligible) {
                    continue;
                }
                // Award uniqueness is per pilot/badge, including other progression rules.
                $existing = UserAward::where('user_id', $user->id)
                    ->where('award_id', $item->award_id)->exists();
                if ($existing) {
                    continue;
                }
                $created = UserAward::firstOrCreate([
                    'user_id' => $user->id,
                    'award_id' => $item->award_id,
                ]);
                if (!$created->wasRecentlyCreated) {
                    continue;
                }
                DB::table('promethee_progression_history')->insert([
                    'user_id' => $user->id,
                    'kind' => 'badge',
                    'subject_id' => (string) $item->award_id,
                    'before_value' => null,
                    'after_value' => (string) $item->award_id,
                    'origin' => $origin,
                    'reason' => ucfirst($source['type']).' #'.$item->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $granted++;
            }
        }
        return $granted;
    }

    private function visitedAirports(User $user): int
    {
        return Pirep::where('user_id', $user->id)
            ->where('state', PirepState::ACCEPTED)
            ->select('dpt_airport_id as airport_id')
            ->union(
                Pirep::where('user_id', $user->id)
                    ->where('state', PirepState::ACCEPTED)
                    ->select('arr_airport_id as airport_id')
            )
            ->distinct()
            ->count('airport_id');
    }

    private function visitedCountries(User $user): int
    {
        $airportIds = Pirep::where('user_id', $user->id)
            ->where('state', PirepState::ACCEPTED)
            ->select('dpt_airport_id as airport_id')
            ->union(
                Pirep::where('user_id', $user->id)
                    ->where('state', PirepState::ACCEPTED)
                    ->select('arr_airport_id as airport_id')
            );

        return DB::table('airports')
            ->whereIn('id', $airportIds)
            ->distinct()
            ->count('country');
    }
}
