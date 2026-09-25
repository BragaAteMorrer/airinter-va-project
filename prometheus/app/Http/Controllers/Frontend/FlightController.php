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
use App\Services\ModuleService;
use App\Services\UserService;
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
    public function __construct(
        private readonly AirlineRepository $airlineRepo,
        private readonly AirportService $airportSvc,
        private readonly AirportRepository $airportRepo,
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

        return view('flights.index', [
            'user'          => $user,
            'ac_counts'     => $ac_counts,
            'airlines'      => $this->airlineRepo->selectBoxList(true),
            'airports'      => [],
            'flights'       => $flights,
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
