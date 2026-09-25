<?php

namespace Modules\Promethee\Services;

use App\Models\Pirep;
use App\Models\User;
use App\Models\Enums\PirepState;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompanyAccessService
{
    public function flightMinutes(User $user): int
    {
        return (int) Pirep::query()
            ->where('user_id', $user->id)
            ->where('state', PirepState::ACCEPTED)
            ->sum('flight_time');
    }

    public function flightHours(User $user): float
    {
        return round($this->flightMinutes($user) / 60, 1);
    }

    public function minimumHoursForAirline(int $airlineId): int
    {
        return (int) (DB::table('promethee_airline_access_rules')
            ->where('airline_id', $airlineId)
            ->value('min_flight_hours') ?? 0);
    }

    public function canAccessAirline(User $user, int $airlineId): bool
    {
        return $this->flightMinutes($user) >= ($this->minimumHoursForAirline($airlineId) * 60);
    }

    public function allowedAirlineIds(User $user): Collection
    {
        $minutes = $this->flightMinutes($user);

        return DB::table('airlines')
            ->leftJoin('promethee_airline_access_rules as access', 'access.airline_id', '=', 'airlines.id')
            ->where('airlines.active', true)
            ->whereRaw('COALESCE(access.min_flight_hours, 0) * 60 <= ?', [$minutes])
            ->pluck('airlines.id')
            ->map(fn ($id) => (int) $id);
    }

    public function remainingHours(User $user, int $airlineId): float
    {
        $required = $this->minimumHoursForAirline($airlineId);
        return max(0, round($required - $this->flightHours($user), 1));
    }
}
