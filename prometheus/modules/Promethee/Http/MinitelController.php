<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\{Aircraft, Airline, Flight, Pirep, User};
use App\Models\Enums\{AircraftState, AircraftStatus, PirepState, UserState};
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MinitelController extends Controller
{
    private const PER_PAGE = 7;

    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();
        $dayStart = now('Europe/Paris')->startOfDay()->utc();
        $dayEnd = now('Europe/Paris')->endOfDay()->utc();

        return response()->json([
            'service' => '3615 AIRINTER',
            'product' => 'PROMETHEE',
            'pilot' => $this->pilotPayload($user),
            'stats' => [
                'flights' => Flight::where('active', true)->where('visible', true)->count(),
                'pilots' => User::where('state', UserState::ACTIVE)->count(),
                'active' => Pirep::whereIn('state', [PirepState::IN_PROGRESS, PirepState::PAUSED])->count(),
                'today' => Pirep::where('state', PirepState::ACCEPTED)->whereBetween('submitted_at', [$dayStart, $dayEnd])->count(),
                'personal' => Pirep::where('state', PirepState::ACCEPTED)->where('user_id', $user->id)->count(),
            ],
            'endpoints' => [
                'departures' => route('promethee.departure-board.data'),
                'flights' => route('promethee.minitel.flights'),
                'fleet' => route('promethee.minitel.fleet'),
                'pilots' => route('promethee.minitel.pilots'),
                'profile' => route('promethee.minitel.profile'),
            ],
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function flights(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:32',
            'departure' => 'nullable|string|max:10',
            'arrival' => 'nullable|string|max:10',
            'page' => 'nullable|integer|min:1|max:999',
        ]);

        $query = Flight::query()
            ->where('active', true)
            ->where('visible', true)
            ->with(['airline:id,icao,name', 'dpt_airport:id,icao,iata,name,location', 'arr_airport:id,icao,iata,name,location']);

        if (!empty($filters['q'])) {
            $term = '%'.strtoupper(trim($filters['q'])).'%';
            $query->where(function ($flights) use ($term) {
                $flights->where('flight_number', 'like', $term)
                    ->orWhere('callsign', 'like', $term)
                    ->orWhere('route_code', 'like', $term)
                    ->orWhere('dpt_airport_id', 'like', $term)
                    ->orWhere('arr_airport_id', 'like', $term);
            });
        }
        if (!empty($filters['departure'])) $query->where('dpt_airport_id', strtoupper($filters['departure']));
        if (!empty($filters['arrival'])) $query->where('arr_airport_id', strtoupper($filters['arrival']));

        $page = $query->orderBy('dpt_time')->orderBy('route_code')->orderBy('flight_number')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return response()->json([
            'items' => collect($page->items())->map(fn (Flight $flight) => [
                'id' => (string) $flight->id,
                'ident' => $flight->ident,
                'airline' => $flight->airline?->icao ?: $flight->airline?->name,
                'departure' => $flight->dpt_airport_id,
                'departure_name' => $flight->dpt_airport?->location ?: $flight->dpt_airport?->name,
                'arrival' => $flight->arr_airport_id,
                'arrival_name' => $flight->arr_airport?->location ?: $flight->arr_airport?->name,
                'departure_time' => $flight->dpt_time ? substr((string) $flight->dpt_time, 0, 5) : null,
                'arrival_time' => $flight->arr_time ? substr((string) $flight->arr_time, 0, 5) : null,
                'route_code' => $flight->route_code,
            ])->values(),
            'pagination' => $this->paginationPayload($page),
        ]);
    }

    public function fleet(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:32',
            'page' => 'nullable|integer|min:1|max:999',
        ]);

        $query = Aircraft::query()->with(['subfleet.airline', 'airport:id,icao']);
        $user = $request->user();

        if (!$user->ability('admin', 'admin-access')
            && (setting('pireps.restrict_aircraft_to_rank', false) || setting('pireps.restrict_aircraft_to_typerating', false))) {
            $query->whereIn('subfleet_id', app(UserService::class)->getAllowableSubfleets($user)->pluck('id'));
        }

        if (!empty($filters['q'])) {
            $term = '%'.trim($filters['q']).'%';
            $query->where(function ($aircraft) use ($term) {
                $aircraft->where('registration', 'like', $term)
                    ->orWhere('icao', 'like', $term)
                    ->orWhereHas('subfleet', fn ($subfleet) => $subfleet->where('name', 'like', $term));
            });
        }

        $page = $query->orderBy('registration')->paginate(self::PER_PAGE)->withQueryString();

        return response()->json([
            'items' => collect($page->items())->map(fn (Aircraft $aircraft) => [
                'id' => (string) $aircraft->id,
                'registration' => $aircraft->registration,
                'icao' => $aircraft->icao,
                'subfleet' => $aircraft->subfleet?->name,
                'airline' => $aircraft->subfleet?->airline?->icao ?: $aircraft->subfleet?->airline?->name,
                'airport' => $aircraft->airport_id ?: $aircraft->airport?->icao,
                'state' => AircraftState::$labels[$aircraft->state] ?? 'Inconnu',
                'status' => __(AircraftStatus::$labels[$aircraft->status] ?? 'aircraft.status.active'),
            ])->values(),
            'pagination' => $this->paginationPayload($page),
        ]);
    }

    public function pilots(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:80',
            'page' => 'nullable|integer|min:1|max:999',
        ]);

        $query = User::query()
            ->with(['rank:id,name', 'airline:id,icao,name'])
            ->whereIn('state', [UserState::ACTIVE, UserState::ON_LEAVE])
            ->whereNotIn('id', DB::table('promethee_members')->whereIn('status', ['ancien', 'heaven'])->select('user_id'));

        if (!empty($filters['q'])) {
            $term = '%'.trim($filters['q']).'%';
            $query->where(fn ($pilots) => $pilots
                ->where('name', 'like', $term)
                ->orWhere('pilot_id', 'like', $term));
        }

        $page = $query->orderBy('pilot_id')->paginate(self::PER_PAGE)->withQueryString();

        return response()->json([
            'items' => collect($page->items())->map(fn (User $pilot) => [
                'id' => (int) $pilot->id,
                'pilot_id' => $pilot->pilot_id,
                'name' => $pilot->name,
                'rank' => $pilot->rank?->name,
                'airline' => $pilot->airline?->icao ?: $pilot->airline?->name,
                'home_airport' => $pilot->home_airport_id,
                'flight_time' => (int) ($pilot->flight_time ?? 0),
            ])->values(),
            'pagination' => $this->paginationPayload($page),
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $pilot = User::with(['rank', 'airline', 'home_airport', 'current_airport'])
            ->withCount([
                'pireps as accepted_pireps_count' => fn ($query) => $query->where('state', PirepState::ACCEPTED),
                'bids as bids_count',
            ])->findOrFail($request->user()->id);

        return response()->json([
            'pilot' => $this->pilotPayload($pilot) + [
                'rank' => $pilot->rank?->name,
                'airline' => $pilot->airline?->icao ?: $pilot->airline?->name,
                'current_airport' => $pilot->current_airport_id,
                'accepted_pireps' => (int) $pilot->accepted_pireps_count,
                'bookings' => (int) $pilot->bids_count,
                'flight_time' => (int) ($pilot->flight_time ?? 0),
                'vatsim_id' => $pilot->vatsim_id,
                'ivao_id' => $pilot->ivao_id,
            ],
        ]);
    }

    private function pilotPayload(User $pilot): array
    {
        return [
            'id' => (int) $pilot->id,
            'pilot_id' => $pilot->pilot_id,
            'name' => $pilot->name,
            'home_airport' => $pilot->home_airport_id,
        ];
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
