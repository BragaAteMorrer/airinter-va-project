<?php

namespace Modules\Promethee\Services;

use App\Models\{Award, Pirep, Rank, User, UserAward};
use App\Models\Enums\PirepState;
use Illuminate\Support\Facades\DB;

/** Evaluates Prométhée's configurable awards and rank policies. */
class ProgressionService
{
    public function eligible(User $user, array $rule): bool
    {
        $criteria = $rule['criteria'] ?? [];
        if (!$criteria) return false;
        $checks = collect($criteria)->map(fn ($criterion) => $this->check($user, (array) $criterion));
        return ($rule['operator'] ?? 'and') === 'or' ? $checks->contains(true) : $checks->every(fn ($value) => $value);
    }

    public function recalculate(?User $only = null, string $origin = 'scheduled'): array
    {
        $awards = 0; $promotions = 0;
        $users = $only ? collect([$only]) : User::where('state', 1)->cursor();
        foreach ($users as $user) {
            foreach (DB::table('promethee_badge_rules')->where('active', true)->get() as $rule) {
                $criteria = json_decode($rule->criteria, true) ?: [];
                if ($this->eligible($user, ['operator' => $rule->operator, 'criteria' => $criteria]) && !UserAward::where(['user_id' => $user->id, 'award_id' => $rule->award_id])->exists()) {
                    UserAward::create(['user_id' => $user->id, 'award_id' => $rule->award_id]);
                    DB::table('promethee_progression_history')->insert(['user_id'=>$user->id, 'kind'=>'badge', 'subject_id'=>(string) $rule->award_id, 'before_value'=>null, 'after_value'=>(string) $rule->award_id, 'origin'=>$origin, 'reason'=>'Rule #'.$rule->id, 'created_at'=>now(), 'updated_at'=>now()]);
                    $awards++;
                }
            }
            $rankRules = DB::table('promethee_rank_rules')->where('active', true)->get();
            // Highest eligible rank wins. This makes overlapping rules
            // deterministic and prevents a lower rule from undoing a promotion.
            foreach ($rankRules->sortByDesc(fn ($rule) => Rank::find($rule->rank_id)?->hours ?? 0) as $rule) {
                $criteria = json_decode($rule->criteria, true) ?: [];
                if (!$this->eligible($user, ['operator'=>$rule->operator, 'criteria'=>$criteria])) continue;
                $rank = Rank::find($rule->rank_id);
                $currentHours = $user->rank?->hours;
                $isPromotion = $currentHours === null || $rank->hours >= $currentHours;
                if ($rank && ($isPromotion || $rule->allow_demotion)) {
                    if ($user->rank_id != $rank->id) {
                        $before = $user->rank_id; $user->rank_id = $rank->id; $user->save();
                        DB::table('promethee_progression_history')->insert(['user_id'=>$user->id, 'kind'=>'rank', 'subject_id'=>(string)$rank->id, 'before_value'=>(string)$before, 'after_value'=>(string)$rank->id, 'origin'=>$origin, 'reason'=>'Rule #'.$rule->id, 'created_at'=>now(), 'updated_at'=>now()]);
                        $promotions++;
                    }
                }
                // A promotion has been selected; do not let a lower eligible
                // rule replace it later in the same evaluation.
                if ($isPromotion) break;
            }
        }
        return compact('awards', 'promotions');
    }

    private function check(User $user, array $criterion): bool
    {
        $value = (float) ($criterion['value'] ?? 0); $metric = $criterion['metric'] ?? '';
        return match ($metric) {
            'validated_flights' => Pirep::where('user_id',$user->id)->where('state',PirepState::ACCEPTED)->count() >= $value,
            'flight_minutes' => (float) $user->flight_time >= $value,
            'total_distance' => (float) Pirep::where('user_id',$user->id)->where('state',PirepState::ACCEPTED)->sum('distance') >= $value,
            'visited_airports' => $this->visitedAirports($user) >= $value,
            'visited_countries' => $this->visitedCountries($user) >= $value,
            'seniority_days' => $user->created_at && $user->created_at->diffInDays(now()) >= $value,
            'required_badge' => UserAward::where(['user_id'=>$user->id, 'award_id'=>(int)$value])->exists(),
            'route' => Pirep::where('user_id',$user->id)->where('state',PirepState::ACCEPTED)->where('route_code',(string)($criterion['route'] ?? ''))->exists(),
            'airline' => Pirep::where('user_id',$user->id)->where('state',PirepState::ACCEPTED)->where('airline_id',(int)$value)->exists(),
            'aircraft_icao' => Pirep::where('pireps.user_id',$user->id)->where('pireps.state',PirepState::ACCEPTED)->whereHas('aircraft', fn ($aircraft) => $aircraft->where('icao', strtoupper((string) ($criterion['text'] ?? ''))))->exists(),
            'event_completed' => Pirep::where('user_id',$user->id)->where('state',PirepState::ACCEPTED)->where('event_id',(int)$value)->exists(),
            'night_flights' => Pirep::where('user_id',$user->id)->where('state',PirepState::ACCEPTED)->whereNotNull('block_off_time')->whereRaw('HOUR(block_off_time) BETWEEN 0 AND 5')->count() >= $value,
            default => false,
        };
    }

    private function visitedAirports(User $user): int
    {
        return Pirep::where('user_id', $user->id)->where('state', PirepState::ACCEPTED)->select('dpt_airport_id as airport_id')->union(Pirep::where('user_id', $user->id)->where('state', PirepState::ACCEPTED)->select('arr_airport_id as airport_id'))->distinct()->count('airport_id');
    }

    private function visitedCountries(User $user): int
    {
        $airportIds = Pirep::where('user_id', $user->id)->where('state', PirepState::ACCEPTED)->select('dpt_airport_id as airport_id')->union(Pirep::where('user_id', $user->id)->where('state', PirepState::ACCEPTED)->select('arr_airport_id as airport_id'));
        return DB::table('airports')->whereIn('id', $airportIds)->distinct()->count('country');
    }
}
