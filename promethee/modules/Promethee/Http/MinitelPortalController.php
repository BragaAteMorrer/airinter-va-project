<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\{Airline, Aircraft, Pirep};
use App\Models\Enums\PirepState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonImmutable;

class MinitelPortalController extends Controller
{
    private const PER_PAGE = 7;

    public function dashboard(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $dayStart = now('Europe/Paris')->startOfDay()->utc();
        $dayEnd = now('Europe/Paris')->endOfDay()->utc();

        $payload = Cache::remember('promethee:minitel:dashboard:v1:'.$userId, 45, function () use ($userId, $dayStart, $dayEnd) {
            return [
                'active_flights' => Pirep::whereIn('state', [PirepState::IN_PROGRESS, PirepState::PAUSED])->count(),
                'today_arrivals' => Pirep::where('state', PirepState::ACCEPTED)->whereBetween('submitted_at', [$dayStart, $dayEnd])->count(),
                'my_reports' => Pirep::where('user_id', $userId)->where('state', PirepState::ACCEPTED)->count(),
                'missions' => DB::table('promethee_missions')->where('active', true)
                    ->where(fn ($q) => $q->whereNull('starts_on')->orWhere('starts_on', '<=', today('Europe/Paris')->toDateString()))
                    ->where(fn ($q) => $q->whereNull('ends_on')->orWhere('ends_on', '>=', today('Europe/Paris')->toDateString()))
                    ->count(),
                'fleet_available' => Aircraft::where('state', 1)->count(),
                'events' => DB::table('promethee_events')->where('ends_at', '>=', now())->count(),
            ];
        });

        return response()->json(['stats' => $payload, 'updated_at' => now()->toIso8601String()]);
    }

    public function pireps(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'mine' => 'nullable|boolean',
            'page' => 'nullable|integer|min:1|max:999',
        ]);

        $query = Pirep::query()
            ->with(['user:id,name,pilot_id', 'aircraft:id,registration,icao'])
            ->when(($filters['mine'] ?? false), fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest('submitted_at');

        $page = $query->paginate(self::PER_PAGE)->withQueryString();

        return response()->json([
            'items' => collect($page->items())->map(fn (Pirep $pirep) => [
                'id' => (string) $pirep->id,
                'date' => $pirep->submitted_at ? CarbonImmutable::parse($pirep->submitted_at)->setTimezone('Europe/Paris')->format('d/m') : '--/--',
                'flight' => trim((string) ($pirep->flight_number ?: $pirep->route_code ?: $pirep->flight_id)),
                'departure' => $pirep->dpt_airport_id,
                'arrival' => $pirep->arr_airport_id,
                'pilot' => $pirep->user?->pilot_id ?: $pirep->user?->name,
                'aircraft' => $pirep->aircraft?->registration ?: $pirep->aircraft?->icao,
                'state' => (int) $pirep->state,
                'status' => $this->pirepStatus((int) $pirep->state),
                'block_minutes' => (int) ($pirep->flight_time ?? 0),
                'distance' => (int) ($pirep->distance ?? 0),
            ])->values(),
            'pagination' => $this->paginationPayload($page),
        ]);
    }

    public function missions(Request $request): JsonResponse
    {
        $today = today('Europe/Paris')->toDateString();
        $userId = (int) $request->user()->id;

        $page = DB::table('promethee_missions as m')
            ->leftJoin('aircraft as a', 'a.id', '=', 'm.aircraft_id')
            ->leftJoin('promethee_mission_bookings as b', function ($join) use ($userId) {
                $join->on('b.mission_id', '=', 'm.id')
                    ->where('b.user_id', '=', $userId)
                    ->where('b.status', '=', 'reserved');
            })
            ->where('m.active', true)
            ->where(fn ($q) => $q->whereNull('m.starts_on')->orWhere('m.starts_on', '<=', $today))
            ->where(fn ($q) => $q->whereNull('m.ends_on')->orWhere('m.ends_on', '>=', $today))
            ->select('m.*', 'a.registration as aircraft_registration', 'b.id as booking_id')
            ->orderByRaw('CASE WHEN m.ends_on IS NULL THEN 1 ELSE 0 END')
            ->orderBy('m.ends_on')
            ->paginate(6)
            ->withQueryString();

        return response()->json([
            'items' => collect($page->items())->map(fn ($mission) => [
                'id' => (int) $mission->id,
                'title' => $mission->title,
                'type' => $mission->mission_type ?? 'mission',
                'departure' => $mission->dpt_airport_id,
                'arrival' => $mission->arr_airport_id,
                'aircraft' => $mission->aircraft_registration,
                'ends_on' => $mission->ends_on,
                'reward_multiplier' => $mission->reward_multiplier ?? null,
                'reserved' => (bool) $mission->booking_id,
            ])->values(),
            'pagination' => $this->paginationPayload($page),
        ]);
    }

    public function passport(Request $request): JsonResponse
    {
        $pilot = $request->user();
        $cacheKey = 'promethee:minitel:passport:v1:'.(int) $pilot->id;

        $payload = Cache::remember($cacheKey, 120, function () use ($pilot) {
            $airports = DB::query()->fromSub(
                Pirep::query()->select('dpt_airport_id as airport_id')->where('user_id', $pilot->id)->where('state', PirepState::ACCEPTED)
                    ->unionAll(Pirep::query()->select('arr_airport_id as airport_id')->where('user_id', $pilot->id)->where('state', PirepState::ACCEPTED)),
                'minitel_passport_airports'
            )->join('airports', 'airports.id', '=', 'minitel_passport_airports.airport_id')
                ->whereNotNull('airports.country')->where('airports.country', '!=', '');

            $countries = (clone $airports)->distinct()->count('airports.country');
            $airportCount = (clone $airports)->distinct()->count('airports.id');

            $destinations = Pirep::where('user_id', $pilot->id)->where('state', PirepState::ACCEPTED)
                ->latest('submitted_at')->limit(6)->get(['arr_airport_id'])
                ->pluck('arr_airport_id')->filter()->values();

            return [
                'pilot_id' => $pilot->pilot_id,
                'name' => $pilot->name,
                'flights' => Pirep::where('user_id', $pilot->id)->where('state', PirepState::ACCEPTED)->count(),
                'flight_time' => (int) ($pilot->flight_time ?? 0),
                'countries' => $countries,
                'airports' => $airportCount,
                'destinations' => $destinations,
            ];
        });

        return response()->json(['pilot' => $payload]);
    }

    public function finances(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'period' => 'nullable|in:month,6months,year',
            'airline' => 'nullable|integer',
        ]);
        $period = $filters['period'] ?? 'month';
        $months = $period === 'month' ? 1 : ($period === '6months' ? 6 : 12);
        $start = now('Europe/Paris')->subMonths($months - 1)->startOfMonth()->utc();
        $airlineId = isset($filters['airline']) ? (int) $filters['airline'] : null;
        $cacheKey = 'promethee:minitel:finances:v1:'.$period.':'.($airlineId ?: 'all');

        $rows = Cache::remember($cacheKey, 60, function () use ($start, $airlineId) {
            return Airline::where('active', 1)
                ->with('journal')
                ->when($airlineId, fn ($q) => $q->where('id', $airlineId))
                ->orderBy('name')->get()
                ->map(function (Airline $airline) use ($start) {
                    $transactions = $airline->journal
                        ? $airline->journal->transactions()->where('post_date', '>=', $start)->get(['credit', 'debit'])
                        : collect();
                    $credits = (int) $transactions->sum('credit');
                    $debits = (int) $transactions->sum('debit');

                    return [
                        'id' => (int) $airline->id,
                        'code' => $airline->icao ?: $airline->iata ?: $airline->name,
                        'name' => $airline->name,
                        'credits' => $credits,
                        'debits' => $debits,
                        'result' => $credits - $debits,
                        'balance' => $airline->journal ? (int) $airline->journal->getBalance()->getAmount() : 0,
                    ];
                })->values();
        });

        return response()->json(['period' => $period, 'items' => $rows]);
    }

    public function regional(Request $request): JsonResponse
    {
        abort_unless($request->user()->ability('admin', 'admin-access'), 403);

        $bases = Cache::remember('promethee:minitel:regional:v1', 45, function () {
            return DB::table('promethee_operational_bases as b')
                ->leftJoin('airports as ap', 'ap.id', '=', 'b.airport_id')
                ->leftJoin('promethee_aircraft_bases as ab', 'ab.base_airport_id', '=', 'b.airport_id')
                ->leftJoin('aircraft as a', 'a.id', '=', 'ab.aircraft_id')
                ->where('b.active', true)
                ->groupBy('b.airport_id', 'b.is_hub', 'b.is_regional_platform', 'b.is_technical_stop', 'b.check_a', 'b.check_b', 'b.check_c', 'ap.name')
                ->select(
                    'b.airport_id', 'ap.name',
                    'b.is_hub', 'b.is_regional_platform', 'b.is_technical_stop',
                    'b.check_a', 'b.check_b', 'b.check_c',
                    DB::raw('COUNT(a.id) as aircraft_count')
                )
                ->orderByDesc('b.is_hub')->orderBy('b.airport_id')->get();
        });

        return response()->json(['items' => $bases]);
    }

    public function admin(Request $request): JsonResponse
    {
        abort_unless($request->user()->ability('admin', 'admin-access'), 403);

        $stats = Cache::remember('promethee:minitel:admin:v1', 30, fn () => [
            'missions' => DB::table('promethee_missions')->where('active', true)->count(),
            'events' => DB::table('promethee_events')->where('ends_at', '>=', now())->count(),
            'bases' => DB::table('promethee_operational_bases')->where('active', true)->count(),
            'crm_campaigns' => DB::table('promethee_crm_campaigns')->count(),
            'aircraft' => Aircraft::count(),
        ]);

        return response()->json(['stats' => $stats]);
    }

    private function pirepStatus(int $state): string
    {
        return match ($state) {
            (int) PirepState::ACCEPTED => 'OK',
            (int) PirepState::IN_PROGRESS => 'VOL',
            (int) PirepState::PAUSED => 'PAUSE',
            default => 'ATT',
        };
    }

    private function paginationPayload($paginator): array
    {
        return [
            'page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'has_more' => $paginator->hasMorePages(),
        ];
    }
}
