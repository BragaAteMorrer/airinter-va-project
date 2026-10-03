<?php

namespace App\Http\Controllers\Frontend;

use App\Contracts\Controller;
use App\Models\Aircraft;
use App\Models\Bid;
use App\Models\Enums\AircraftStatus;
use App\Models\Enums\AircraftState;
use App\Models\Enums\FlightType;
use App\Models\Flight;
use App\Models\Typerating;
use App\Repositories\AirlineRepository;
use App\Repositories\AirportRepository;
use App\Repositories\Criteria\WhereCriteria;
use App\Repositories\FlightRepository;
use App\Repositories\SubfleetRepository;
use App\Repositories\UserRepository;
use App\Services\FlightService;
use App\Services\GeoService;
use App\Services\AirportService;
use App\Services\BidService;
use App\Services\ModuleService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laracasts\Flash\Flash;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class FlightController extends Controller
{
    private const MAX_ITINERARY_STOPS = 3;
    private const MAX_ITINERARY_LEGS = 4;
    private const MAX_ITINERARY_RESULTS = 3;
    private const MAX_ITINERARY_EXPANSIONS = 6000;

    public function __construct(
        private readonly AirlineRepository $airlineRepo,
        private readonly AirportService $airportSvc,
        private readonly AirportRepository $airportRepo,
        private readonly BidService $bidSvc,
        private readonly FlightRepository $flightRepo,
        private readonly FlightService $flightSvc,
        private readonly GeoService $geoSvc,
        private readonly ModuleService $moduleSvc,
        private readonly SubfleetRepository $subfleetRepo,
        private readonly UserRepository $userRepo,
        private readonly UserService $userSvc
    ) {}

    /**
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $request): View
    {
        return $this->search($request);
    }

    /**
     * Make a search request using the Repository search
     *
     *
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function search(Request $request): View
    {
        $where = [
            'active'  => true,
            'visible' => true,
        ];

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadMissing(['current_airport', 'typeratings']);

        if (setting('pilots.restrict_to_company')) {
            $where['airline_id'] = $user->airline_id;
        }

        // default restrictions on the flights shown. Handle search differently
        if (setting('pilots.only_flights_from_current')) {
            $where['dpt_airport_id'] = $user->curr_airport_id;
        }

        $this->flightRepo->resetCriteria();

        try {
            $this->flightRepo->searchCriteria($request);
            $this->flightRepo->pushCriteria(new WhereCriteria($request, $where, [
                'airline' => ['active' => true],
            ]));

            $this->flightRepo->pushCriteria(new RequestCriteria($request));
        } catch (RepositoryException $e) {
            Log::emergency($e);
        }

        // Filter flights according to user capabilities (by rank or by type rating etc)
        $filter_by_user = (setting('pireps.restrict_aircraft_to_rank', true) || setting('pireps.restrict_aircraft_to_typerating', false)) ? true : false;

        if ($filter_by_user) {
            // Get allowed subfleets for the user
            $user_subfleets = $this->userSvc->getAllowableSubfleets($user)->pluck('id')->toArray();
            // Get flight_id's from relationships (group by flight id to reduce the array size)
            $user_flights = DB::table('flight_subfleet')
                ->select('flight_id')
                ->whereIn('subfleet_id', $user_subfleets)
                ->groupBy('flight_id')
                ->pluck('flight_id')
                ->toArray();
            // Get flight_id's of open (non restricted) flights
            $open_flights = Flight::withCount('subfleets')->whereNull('user_id')->having('subfleets_count', 0)->pluck('id')->toArray();
            $allowed_flights = array_merge($user_flights, $open_flights);
            // Build aircraft icao codes by considering allowed subfleets
            $icao_codes = Aircraft::whereIn('subfleet_id', $user_subfleets)->groupBy('icao')->orderBy('icao')->pluck('icao')->toArray();
            // Build type ratings collection by considering user's capabilities
            $type_ratings = $user->typeratings;
        } else {
            $user_subfleets = null;
            $allowed_flights = [];
            // Build aircraft icao codes array from complete fleet
            $icao_codes = Aircraft::groupBy('icao')->orderBy('icao')->pluck('icao')->toArray();
            // Build type ratings collection from all active ratings
            $type_ratings = Typerating::where('active', 1)->select('id', 'name', 'type')->orderBy('type')->get();
        }

        // Get only used Flight Types for the search form
        // And filter according to settings
        $usedtypes = Flight::select('flight_type')
            ->where($where)
            ->groupby('flight_type')
            ->orderby('flight_type')
            ->get();

        // Build collection with type codes and labels
        $flight_types = collect('', '');
        foreach ($usedtypes as $ftype) {
            $flight_types->put($ftype->flight_type, FlightType::label($ftype->flight_type));
        }

        $flights = $this->flightRepo->searchCriteria($request)
            ->with([
                'airline',
                'alt_airport',
                'arr_airport',
                'dpt_airport',
                'subfleets.airline',
                'simbrief' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
            ])
            ->when($filter_by_user, function ($query) use ($allowed_flights) {
                return $query->whereIn('id', $allowed_flights);
            })
            ->sortable('flight_number')->orderBy('route_code')->orderBy('route_leg')
            ->paginate();

        // phpVMS 7.0.10: expose the number of actually available aircraft per flight.
        $ac_counts = $this->CountAvailableAircraftForFlights($flights, $user_subfleets);

        $saved_flights = [];
        $bids = Bid::where('user_id', Auth::id())->get();
        foreach ($bids as $bid) {
            if (!$bid->flight) {
                $bid->delete();

                continue;
            }

            $saved_flights[$bid->flight_id] = $bid->id;
        }

        $itinerary_search_requested = $this->isItinerarySearchRequest($request);
        $itineraries = collect();

        // Keep the native direct-flight search untouched. Only when a genuine
        // origin/destination search has no direct result do we offer network
        // connections (up to three stopovers / four flight segments).
        if ($itinerary_search_requested && $flights->total() === 0) {
            $itineraries = $this->findAlternativeItineraries(
                $request,
                $user,
                $filter_by_user ? $allowed_flights : null
            );
        }

        return view('flights.index', [
            'user'          => $user,
            'ac_counts'     => $ac_counts,
            'airlines'      => $this->airlineRepo->selectBoxList(true),
            'airports'      => [],
            'flights'       => $flights,
            'itineraries'   => $itineraries,
            'itinerary_search_requested' => $itinerary_search_requested,
            'max_itinerary_stops' => self::MAX_ITINERARY_STOPS,
            'saved'         => $saved_flights,
            'subfleets'     => $this->subfleetRepo->selectBoxList(true),
            'flight_number' => $request->input('flight_number'),
            'flight_types'  => $flight_types,
            'flight_type'   => $request->input('flight_type'),
            'arr_icao'      => $request->input('arr_icao'),
            'dep_icao'      => $request->input('dep_icao'),
            'subfleet_id'   => $request->input('subfleet_id'),
            'simbrief'      => !empty(setting('simbrief.api_key')),
            'simbrief_bids' => setting('simbrief.only_bids'),
            'acars_plugin'  => $this->moduleSvc->isModuleActive('VMSAcars'),
            'icao_codes'    => $icao_codes,
            'type_ratings'  => $type_ratings,
        ]);
    }

    /**
     * Find the user's bids and display them
     */
    public function bids(Request $request): View
    {
        $user = $this->userRepo
            ->with(['bids', 'bids.flight'])
            ->find(Auth::user()->id);

        $flights = collect();
        $saved_flights = [];
        foreach ($user->bids as $bid) {
            // Remove any invalid bids (flight doesn't exist or something)
            if (!$bid->flight) {
                $bid->delete();

                continue;
            }

            $flights->add($bid->flight);
            $saved_flights[$bid->flight_id] = $bid->id;
        }

        return view('flights.bids', [
            'user'          => $user,
            'airlines'      => $this->airlineRepo->selectBoxList(true),
            'airports'      => [],
            'flights'       => $flights,
            'saved'         => $saved_flights,
            'subfleets'     => $this->subfleetRepo->selectBoxList(true),
            'simbrief'      => !empty(setting('simbrief.api_key')),
            'simbrief_bids' => setting('simbrief.only_bids'),
            'acars_plugin'  => $this->moduleSvc->isModuleActive('VMSAcars'),
        ]);
    }

    /**
     * Show the flight information page
     *
     *
     * @return mixed
     */
    public function show(string $id): View
    {
        $user = Auth::user();
        // Support retrieval of deleted relationships
        $with_flight = [
            'airline' => function ($query) {
                return $query->withTrashed();
            },
            'alt_airport' => function ($query) {
                return $query->withTrashed();
            },
            'arr_airport' => function ($query) {
                return $query->withTrashed();
            },
            'dpt_airport' => function ($query) {
                return $query->withTrashed();
            },
            'subfleets.airline',
            'fares',
            'field_values',
            'simbrief' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            },
        ];

        $flight = $this->flightRepo->with($with_flight)->find($id);
        if (empty($flight)) {
            Flash::error(__('promethee.flight_not_found'));

            return redirect(route('frontend.dashboard.index'));
        }

        if (setting('flights.only_company_aircraft', false)) {
            $flight = $this->flightSvc->filterSubfleets($user, $flight);
        }

        $map_features = $this->geoSvc->flightGeoJson($flight);

        // Keep the operational weather with the flight, including the alternate
        // when one is planned. A missing report must not prevent the sheet from
        // being displayed: the view gives the pilot a clear status instead.
        $weather = collect([
            'departure' => $flight->dpt_airport_id,
            'arrival' => $flight->arr_airport_id,
            'alternate' => $flight->alt_airport_id,
        ])->filter()->mapWithKeys(function (string $icao, string $role) {
            return [$role => [
                'icao' => $icao,
                'metar' => $this->airportSvc->getMetar($icao),
                'taf' => $this->airportSvc->getTaf($icao),
            ]];
        });

        // See if the user has a bid for this flight
        $bid = Bid::where(['user_id' => $user->id, 'flight_id' => $flight->id])->first();

        return view('flights.show', [
            'flight'       => $flight,
            'map_features' => $map_features,
            'weather'      => $weather,
            'bid'          => $bid,
            'acars_plugin' => $this->moduleSvc->isModuleActive('VMSAcars'),
        ]);
    }

    /**
     * Reserve every segment of a proposed itinerary in one atomic operation.
     * Existing bids owned by the pilot are idempotent through BidService.
     */
    public function reserveItinerary(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'flight_ids'   => 'required|array|min:2|max:'.self::MAX_ITINERARY_LEGS,
            'flight_ids.*' => 'required|distinct',
            'dep_icao'     => 'required|string|max:8',
            'arr_icao'     => 'required|string|max:8',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $flight_ids = array_values($validated['flight_ids']);
        $origin = strtoupper(trim($validated['dep_icao']));
        $destination = strtoupper(trim($validated['arr_icao']));

        if (setting('bids.allow_multiple_bids') === false) {
            Flash::error(__('flights.multiple_bids_required'));

            return redirect()->back();
        }

        try {
            DB::transaction(function () use ($flight_ids, $origin, $destination, $user) {
                $flight_map = Flight::with(['airline', 'subfleets'])
                    ->whereIn('id', $flight_ids)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy(fn ($flight) => (string) $flight->id);

                $ordered_flights = collect($flight_ids)->map(function ($flight_id) use ($flight_map) {
                    $flight = $flight_map->get((string) $flight_id);
                    if (!$flight) {
                        throw new \RuntimeException(__('flights.itinerary_invalid'));
                    }

                    return $flight;
                });

                $this->assertItineraryCanBeReserved($ordered_flights, $user, $origin, $destination);

                foreach ($ordered_flights as $flight) {
                    $this->bidSvc->addBid($flight, $user);
                }
            }, 3);
        } catch (\Throwable $e) {
            Log::warning('Unable to reserve multi-leg itinerary', [
                'user_id' => $user->id,
                'flight_ids' => $flight_ids,
                'exception' => $e,
            ]);

            Flash::error(__('flights.itinerary_reserve_failed', ['message' => $e->getMessage()]));

            return redirect()->back();
        }

        Flash::success(trans_choice('flights.itinerary_reserved', count($flight_ids), [
            'count' => count($flight_ids),
        ]));

        return redirect()->route('frontend.flights.bids');
    }

    /**
     * Only origin/destination searches are eligible for the connection fallback.
     * A flight number or route-code search is deliberately kept exact.
     */
    private function isItinerarySearchRequest(Request $request): bool
    {
        if (!$request->filled('dep_icao') || !$request->filled('arr_icao')) {
            return false;
        }

        if ($request->filled('flight_number') || $request->filled('route_code')) {
            return false;
        }

        return strtoupper(trim((string) $request->input('dep_icao')))
            !== strtoupper(trim((string) $request->input('arr_icao')));
    }

    /**
     * Build the active route network as a directed graph and perform a bounded
     * breadth-first search. Up to three stopovers means at most four legs.
     */
    private function findAlternativeItineraries(Request $request, $user, ?array $allowed_flights)
    {
        $origin = strtoupper(trim((string) $request->input('dep_icao')));
        $destination = strtoupper(trim((string) $request->input('arr_icao')));

        if (setting('pilots.only_flights_from_current')
            && strtoupper((string) $user->curr_airport_id) !== $origin) {
            return collect();
        }

        if ($allowed_flights !== null && empty($allowed_flights)) {
            return collect();
        }

        $query = Flight::query()
            ->with(['airline', 'dpt_airport', 'arr_airport', 'subfleets.airline'])
            ->where('active', true)
            ->where('visible', true)
            ->whereNotNull('dpt_airport_id')
            ->whereNotNull('arr_airport_id')
            ->whereColumn('dpt_airport_id', '<>', 'arr_airport_id')
            ->whereHas('airline', fn ($airline) => $airline->where('active', true));

        if (setting('pilots.restrict_to_company')) {
            $query->where('airline_id', $user->airline_id);
        }

        if ($allowed_flights !== null) {
            $query->whereIn('id', $allowed_flights);
        }

        if ($request->filled('airline_id')) {
            $query->where('airline_id', $request->input('airline_id'));
        }

        if ($request->filled('flight_type') && $request->input('flight_type') !== '0') {
            $query->where('flight_type', $request->input('flight_type'));
        }

        $requested_subfleet_ids = null;
        if ($request->filled('subfleet_id')) {
            $requested_subfleet_ids = [(string) $request->input('subfleet_id')];
        }

        if ($request->filled('type_rating_id')) {
            $type_rating = Typerating::with('subfleets')->find($request->input('type_rating_id'));
            $requested_subfleet_ids = filled(optional($type_rating)->subfleets)
                ? $type_rating->subfleets->pluck('id')->map(fn ($id) => (string) $id)->toArray()
                : [];
        }

        if ($request->filled('icao_type')) {
            $requested_subfleet_ids = Aircraft::where('icao', $request->input('icao_type'))
                ->groupBy('subfleet_id')
                ->pluck('subfleet_id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        }

        if ($requested_subfleet_ids !== null) {
            if (empty($requested_subfleet_ids)) {
                return collect();
            }

            $query->whereHas('subfleets', function ($subfleets) use ($requested_subfleet_ids) {
                $subfleets->whereIn('subfleets.id', $requested_subfleet_ids);
            });
        }

        $candidate_flights = $query->get();
        if ($candidate_flights->isEmpty()) {
            return collect();
        }

        // Multiple timetable entries can represent the same network edge. Keep
        // one deterministic representative per airport pair so the path search
        // remains fast and the pilot sees distinct routing alternatives.
        $network_flights = $candidate_flights
            ->groupBy(fn ($flight) => strtoupper((string) $flight->dpt_airport_id)
                .'>'.
                strtoupper((string) $flight->arr_airport_id))
            ->map(function ($same_route) {
                return $same_route->sortBy(function ($flight) {
                    $time = $flight->flight_time ?? 999999;
                    $distance = $flight->distance ?? 999999;

                    return sprintf('%09d:%09d:%s', (int) $time, (int) $distance, (string) $flight->ident);
                })->first();
            })
            ->values();

        $by_departure = $network_flights->groupBy(
            fn ($flight) => strtoupper((string) $flight->dpt_airport_id)
        );

        $queue = [[
            'airport' => $origin,
            'legs' => [],
            'visited' => [$origin => true],
            'distance' => 0.0,
            'flight_time' => 0,
        ]];
        $cursor = 0;
        $expansions = 0;
        $found = [];
        $signatures = [];

        while (isset($queue[$cursor]) && $expansions < self::MAX_ITINERARY_EXPANSIONS) {
            $state = $queue[$cursor++];
            if (count($state['legs']) >= self::MAX_ITINERARY_LEGS) {
                continue;
            }

            $outgoing = $by_departure->get($state['airport'], collect())
                ->sortBy(fn ($flight) => (float) ($flight->distance ?? 999999));

            foreach ($outgoing as $flight) {
                $expansions++;
                if ($expansions > self::MAX_ITINERARY_EXPANSIONS) {
                    break 2;
                }

                $next_airport = strtoupper((string) $flight->arr_airport_id);
                if (isset($state['visited'][$next_airport])) {
                    continue;
                }

                $legs = [...$state['legs'], $flight];
                $distance = $state['distance'] + (float) ($flight->distance ?? 0);
                $flight_time = $state['flight_time'] + (int) ($flight->flight_time ?? 0);

                if ($next_airport === $destination) {
                    if (count($legs) < 2) {
                        continue;
                    }

                    $airports = [$origin];
                    foreach ($legs as $leg) {
                        $airports[] = strtoupper((string) $leg->arr_airport_id);
                    }
                    $signature = implode('>', $airports);

                    if (!isset($signatures[$signature])) {
                        $signatures[$signature] = true;
                        $found[] = [
                            'legs' => $legs,
                            'stops' => count($legs) - 1,
                            'distance' => $distance,
                            'flight_time' => $flight_time,
                            'airports' => $airports,
                        ];
                    }

                    if (count($found) >= 24) {
                        break 2;
                    }

                    continue;
                }

                if (count($legs) < self::MAX_ITINERARY_LEGS) {
                    $visited = $state['visited'];
                    $visited[$next_airport] = true;

                    $queue[] = [
                        'airport' => $next_airport,
                        'legs' => $legs,
                        'visited' => $visited,
                        'distance' => $distance,
                        'flight_time' => $flight_time,
                    ];
                }
            }
        }

        usort($found, static function (array $a, array $b): int {
            $by_legs = count($a['legs']) <=> count($b['legs']);
            if ($by_legs !== 0) {
                return $by_legs;
            }

            $by_distance = $a['distance'] <=> $b['distance'];
            if ($by_distance !== 0) {
                return $by_distance;
            }

            return $a['flight_time'] <=> $b['flight_time'];
        });

        return collect(array_slice($found, 0, self::MAX_ITINERARY_RESULTS));
    }

    /**
     * Revalidate the complete chain while rows are locked. This keeps the
     * "reserve all" action all-or-nothing and prevents stale search results
     * from bypassing company, qualification or current-airport rules.
     */
    private function assertItineraryCanBeReserved($flights, $user, string $origin, string $destination): void
    {
        if ($flights->count() < 2 || $flights->count() > self::MAX_ITINERARY_LEGS) {
            throw new \RuntimeException(__('flights.itinerary_invalid'));
        }

        $expected_airport = $origin;
        $visited = [$origin => true];

        $filter_by_user = setting('pireps.restrict_aircraft_to_rank', true)
            || setting('pireps.restrict_aircraft_to_typerating', false);
        $allowed_flight_ids = $filter_by_user
            ? array_map('strval', $this->getUserAllowedFlightIds($user))
            : null;

        foreach ($flights as $flight) {
            if (!$flight->active || !$flight->visible || !$flight->airline || !$flight->airline->active) {
                throw new \RuntimeException(__('flights.itinerary_invalid'));
            }

            if (setting('pilots.restrict_to_company')
                && (string) $flight->airline_id !== (string) $user->airline_id) {
                throw new \RuntimeException(__('flights.itinerary_invalid'));
            }

            if ($allowed_flight_ids !== null
                && !in_array((string) $flight->id, $allowed_flight_ids, true)) {
                throw new \RuntimeException(__('flights.itinerary_invalid'));
            }

            $departure = strtoupper((string) $flight->dpt_airport_id);
            $arrival = strtoupper((string) $flight->arr_airport_id);

            if ($departure !== $expected_airport || isset($visited[$arrival])) {
                throw new \RuntimeException(__('flights.itinerary_invalid'));
            }

            $visited[$arrival] = true;
            $expected_airport = $arrival;
        }

        if ($expected_airport !== $destination) {
            throw new \RuntimeException(__('flights.itinerary_invalid'));
        }

        if (setting('pilots.only_flights_from_current')
            && strtoupper((string) $user->curr_airport_id) !== $origin) {
            throw new \RuntimeException(__('flights.itinerary_invalid'));
        }
    }

    private function getUserAllowedFlightIds($user): array
    {
        $user_subfleets = $this->userSvc->getAllowableSubfleets($user)->pluck('id')->toArray();

        $user_flights = DB::table('flight_subfleet')
            ->select('flight_id')
            ->whereIn('subfleet_id', $user_subfleets)
            ->groupBy('flight_id')
            ->pluck('flight_id')
            ->toArray();

        $open_flights = Flight::withCount('subfleets')
            ->whereNull('user_id')
            ->having('subfleets_count', 0)
            ->pluck('id')
            ->toArray();

        return array_values(array_unique(array_merge($user_flights, $open_flights)));
    }

    /**
     * Count aircraft which are genuinely selectable for each displayed flight.
     * Imported from phpVMS 7.0.10 and kept here while the legacy flight screen exists.
     */
    private function CountAvailableAircraftForFlights($flights, $user_subfleets = null): array
    {
        $counts = [];
        $batchSize = 50;

        foreach ($flights->chunk($batchSize) as $batch) {
            $airportIds = $batch->pluck('dpt_airport_id')->unique()->toArray();
            $airlineIds = collect($batch)->pluck('airline_id')->unique()->toArray();

            $explicitSubfleetIds = collect($batch)
                ->flatMap(fn($f) => $f->subfleets->pluck('id'))
                ->unique()
                ->toArray();

            $hasOpenFlights = collect($batch)->contains(fn($f) => $f->subfleets->isEmpty());
            $fallbackSubfleetIds = [];
            if ($hasOpenFlights) {
                $fallbackSubfleetIds = DB::table('subfleets')
                    ->when(setting('flights.only_company_aircraft', false), function ($query) use ($airlineIds) {
                        return $query->whereIn('airline_id', $airlineIds);
                    })
                    ->pluck('id')
                    ->toArray();
            }

            $subfleetIds = array_unique(array_merge($explicitSubfleetIds, $fallbackSubfleetIds));
            if ($user_subfleets !== null) {
                $subfleetIds = array_intersect($subfleetIds, $user_subfleets);
            }

            $aircraftQuery = Aircraft::query()
                ->where('status', AircraftStatus::ACTIVE)
                ->where('state', AircraftState::PARKED)
                ->whereIn('airport_id', $airportIds);

            if (!empty($subfleetIds)) {
                $aircraftQuery->whereIn('subfleet_id', $subfleetIds);
            }

            $aircraftData = $aircraftQuery
                ->select('airport_id', 'subfleet_id', DB::raw('COUNT(*) as count'))
                ->groupBy('airport_id', 'subfleet_id')
                ->get()
                ->groupBy('airport_id')
                ->map(fn($airportAircrafts) => $airportAircrafts
                    ->groupBy('subfleet_id')
                    ->map(fn($subfleetGroup) => collect($subfleetGroup)->sum('count')))
                ->toArray();

            $onlyCompanyAircraft = setting('flights.only_company_aircraft', false);
            $airlineSubfleetIdsCache = [];
            $uniqueAirlineIds = $batch->pluck('airline_id')->unique()->toArray();
            $subfleetRows = DB::table('subfleets')
                ->when($onlyCompanyAircraft, fn($q) => $q->whereIn('airline_id', $uniqueAirlineIds))
                ->select('id', 'airline_id')
                ->get();

            foreach ($uniqueAirlineIds as $airlineId) {
                $airlineSubfleetIdsCache[$airlineId] = $onlyCompanyAircraft
                    ? $subfleetRows->where('airline_id', $airlineId)->pluck('id')->toArray()
                    : $subfleetRows->pluck('id')->toArray();
            }

            foreach ($batch as $flight) {
                $count = 0;
                if ($flight->subfleets->isEmpty()) {
                    foreach ($airlineSubfleetIdsCache[$flight->airline_id] ?? [] as $sfId) {
                        $count += $aircraftData[$flight->dpt_airport_id][$sfId] ?? 0;
                    }
                } else {
                    foreach ($flight->subfleets as $subfleet) {
                        $count += $aircraftData[$flight->dpt_airport_id][$subfleet->id] ?? 0;
                    }
                }

                $counts[$flight->id] = $count;
            }
        }

        return $counts;
    }

}
