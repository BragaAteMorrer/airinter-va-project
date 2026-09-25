<?php

namespace Modules\Promethee\Services;

use App\Models\Pirep;
use App\Models\Rank;
use App\Models\User;
use App\Models\UserAward;
use App\Models\Enums\PirepState;
use App\Models\Enums\UserState;
use Illuminate\Support\Facades\DB;

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
            'night_flights' => Pirep::where('user_id', $user->id)
                ->where('state', PirepState::ACCEPTED)
                ->whereNotNull('block_off_time')
                ->whereRaw('HOUR(block_off_time) BETWEEN 0 AND 5')
                ->count() >= $value,
            default => false,
        };
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
