<?php
namespace Modules\Promethee\Http;
use App\Models\{Aircraft,Airline,Airport,Bid,File,Flight,Pirep,SimBrief,User,Fare,Subfleet,Rank};
use App\Models\Enums\{AircraftState,AircraftStatus,FareType,FlightType,PirepState,PirepStatus,UserState};
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File as Filesystem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Services\AirportService;
use App\Services\FinanceService;
use App\Services\FileService;
use App\Services\UserService;
use App\Support\Money;
use App\Support\Countries;
use Modules\Promethee\Services\{AirframeMaintenanceService,BrandingService,BulletinService,CompanyAccessService,DemandProfileService,EconomyFareResolver,EconomyService,EngineMaintenanceService,FleetRotationService,FlightOpsService,LegacyPirepScoringService,OperationalWeatherService,PilotPirepDeletionService,PirepJournalService,RegionalOperationsService,SafetyAnalyzer};


class FlightProgrammeController extends PrometheeWebController
{
    private const MAX_ITINERARY_STOPS = 3;
    private const MAX_ITINERARY_LEGS = 4;
    private const MAX_ITINERARY_RESULTS = 3;
    private const MAX_ITINERARY_EXPANSIONS = 12000;

private function decorateProgrammeTimetable($flights): void
    {
        $now=CarbonImmutable::now('Europe/Paris');
        $flights->each(function(Flight $flight) use($now) {
            $departure=$this->nextDeparture($flight);
            $arrival=$departure && trim((string)$flight->arr_time)!==''
                ? $this->scheduledDateTime((string)$flight->arr_time,$departure) : null;
            if($arrival && $arrival->lte($departure)) $arrival=$arrival->addDay();
            $arrival=$arrival?->setTimezone('Europe/Paris');
            $minutes=$departure ? max(0,(int)ceil($now->diffInSeconds($departure,false)/60)) : null;
            $relative=$departure ? match(true) {
                $minutes<=1 => 'Départ imminent',
                $minutes<60 => 'Dans '.$minutes.' min',
                $minutes<180 => 'Dans '.intdiv($minutes,60).' h'.($minutes%60?' '.str_pad((string)($minutes%60),2,'0',STR_PAD_LEFT):''),
                $departure->isSameDay($now) => 'Aujourd’hui',
                $departure->isSameDay($now->addDay()) => 'Demain',
                default => 'Le '.$departure->format('d/m'),
            } : null;
            foreach([
                'next_departure_time'=>$departure?->format('H:i') ?: trim((string)$flight->dpt_time),
                'next_arrival_time'=>$arrival?->format('H:i') ?: trim((string)$flight->arr_time),
                'next_departure_iso'=>$departure?->toIso8601String(),
                'next_arrival_iso'=>$arrival?->toIso8601String(),
                'next_departure_relative'=>$relative,
                'next_departure_soon'=>$minutes!==null && $minutes<=90,
                'next_departure_sort'=>$departure?->getTimestamp(),
            ] as $key=>$value) $flight->setAttribute($key,$value);
        });
    }

public function flights(Request $r) {
        $filters = $r->validate([
            'departure'   => 'nullable|string|max:80',
            'arrival'     => 'nullable|string|max:80',
            'airline_id'  => 'nullable|integer|exists:airlines,id',
            'subfleet_id' => 'nullable|integer|exists:subfleets,id',
            'flight_type' => 'nullable|string|size:1',
            'q'           => 'nullable|string|max:80',
            'min_distance'=> 'nullable|numeric|min:0',
            'max_distance'=> 'nullable|numeric|gte:min_distance',
            'time_from'   => 'nullable|date_format:H:i',
            'time_to'     => 'nullable|date_format:H:i',
            'sort'        => 'nullable|in:departure,ident,distance',
        ]);

        $resolveAirport = function (?string $value): ?string {
            $value = trim((string) $value);
            if ($value === '') return null;
            $upper = strtoupper($value);

            return Airport::query()
                ->where(function ($airports) use ($value, $upper) {
                    $airports->where('id', $upper)
                        ->orWhere('icao', $upper)
                        ->orWhere('iata', $upper)
                        ->orWhere('name', 'like', '%'.$value.'%')
                        ->orWhere('location', 'like', '%'.$value.'%');
                })
                ->orderByRaw('CASE WHEN id = ? OR icao = ? OR iata = ? THEN 0 ELSE 1 END', [$upper, $upper, $upper])
                ->value('id');
        };

        $selectedDeparture = $resolveAirport($filters['departure'] ?? null);
        $selectedArrival = $resolveAirport($filters['arrival'] ?? null);

        $explicitFilterKeys=['departure','arrival','airline_id','subfleet_id','flight_type','q','min_distance','max_distance','time_from','time_to'];
        $personalizedDefault=collect($explicitFilterKeys)->every(fn($key)=>!$r->filled($key))
            && (!$r->filled('sort') || ($filters['sort']??'departure')==='departure');
        $programmeBaseId=$r->user()?->home_airport_id ?: $r->user()?->curr_airport_id;
        $programmeBase=$programmeBaseId ? Airport::find($programmeBaseId) : null;

        $q = Flight::where('active', true)
            ->where('visible', true)
            ->with(['airline', 'fares', 'dpt_airport', 'arr_airport', 'subfleets']);

        if($personalizedDefault) $programmeBaseId
            ? $q->where('dpt_airport_id',$programmeBaseId)
            : $q->whereRaw('1 = 0');

        if ($r->user() && !$r->user()->ability('admin', 'admin-access')) {
            $q->whereIn('airline_id', app(CompanyAccessService::class)->allowedAirlineIds($r->user()));
        }

        if ($r->filled('departure')) $selectedDeparture ? $q->where('dpt_airport_id', $selectedDeparture) : $q->whereRaw('1 = 0');
        if ($r->filled('arrival')) $selectedArrival ? $q->where('arr_airport_id', $selectedArrival) : $q->whereRaw('1 = 0');
        if ($r->filled('airline_id')) $q->where('airline_id', $filters['airline_id']);
        if ($r->filled('subfleet_id')) $q->whereHas('subfleets', fn ($subfleets) => $subfleets->where('subfleets.id', $filters['subfleet_id']));
        if ($r->filled('flight_type')) $q->where('flight_type', $filters['flight_type']);
        if ($r->filled('min_distance')) $q->where('distance', '>=', $filters['min_distance']);
        if ($r->filled('max_distance')) $q->where('distance', '<=', $filters['max_distance']);
        if ($r->filled('time_from')) $q->where('dpt_time', '>=', $filters['time_from']);
        if ($r->filled('time_to')) $q->where('dpt_time', '<=', $filters['time_to']);
        if ($r->filled('q')) {
            $raw=trim((string)$filters['q']); $term='%'.strtoupper($raw).'%';
            $ids=Airport::query()->where(fn($a)=>$a->where('id','like',$term)->orWhere('icao','like',$term)->orWhere('iata','like',$term)->orWhere('name','like','%'.$raw.'%')->orWhere('location','like','%'.$raw.'%'))->pluck('id');
            $q->where(fn($f)=>$f->where('flight_number','like',$term)->orWhere('callsign','like',$term)->orWhere('route_code','like',$term)->orWhereIn('dpt_airport_id',$ids)->orWhereIn('arr_airport_id',$ids));
        }

        if ($personalizedDefault) {
            $upcoming=$q->get();
            $this->decorateProgrammeTimetable($upcoming);
            $upcoming=$upcoming->filter(fn(Flight $flight)=>$flight->next_departure_sort!==null)
                ->sortBy('next_departure_sort')->take(10)->values();
            $flights=new \Illuminate\Pagination\LengthAwarePaginator(
                $upcoming,$upcoming->count(),10,1,['path'=>$r->url(),'query'=>$r->query()]
            );
        } else {
            $sort = $filters['sort'] ?? 'departure';
            if ($sort === 'ident') $q->orderBy('route_code')->orderBy('flight_number');
            elseif ($sort === 'distance') $q->orderBy('distance');
            else $q->orderBy('dpt_time')->orderBy('route_code')->orderBy('flight_number');

            $flights = $q->paginate(24)->withQueryString();
            $this->decorateProgrammeTimetable($flights->getCollection());
        }

        // The public programme is not the legacy phpVMS flight screen. When a
        // real origin/destination search returns no direct line, build possible
        // connections from the same Prométhée catalogue instead of stopping at
        // "no result".
        $itineraries = collect();
        if ($selectedDeparture
            && $selectedArrival
            && $selectedDeparture !== $selectedArrival
            && $flights->total() === 0
            && !$r->filled('q')) {
            $itineraries = $this->findProgrammeItineraries(
                $r,
                $filters,
                $selectedDeparture,
                $selectedArrival
            );
        }

        $airportIds = Flight::where('active', true)->where('visible', true)
            ->select('dpt_airport_id as id')->union(
                Flight::where('active', true)->where('visible', true)->select('arr_airport_id as id')
            );
        $mapAirports = Airport::whereIn('id', $airportIds)->orderBy('icao')->get(['id', 'icao', 'iata', 'name', 'location', 'lat', 'lon']);
        $bounds = [
            'west' => $mapAirports->min('lon') ?? -10,
            'east' => $mapAirports->max('lon') ?? 10,
            'north' => $mapAirports->max('lat') ?? 55,
            'south' => $mapAirports->min('lat') ?? 40,
        ];
        $lonSpan = max(1, $bounds['east'] - $bounds['west']);
        $latSpan = max(1, $bounds['north'] - $bounds['south']);
        $mapAirports = $mapAirports->map(fn ($airport) => [
            'code' => $airport->id,
            'iata' => $airport->iata,
            'name' => $airport->name,
            'location' => $airport->location,
            'lat' => (float) $airport->lat,
            'lon' => (float) $airport->lon,
            'x' => round(35 + (($airport->lon - $bounds['west']) / $lonSpan) * 930, 1),
            'y' => round(35 + (($bounds['north'] - $airport->lat) / $latSpan) * 470, 1),
        ]);
        $mapByCode = $mapAirports->keyBy('code');

        $mapRouteFlights = collect();
        if ($itineraries->isNotEmpty()) {
            $mapRouteFlights = $itineraries
                ->flatMap(fn ($itinerary) => $itinerary['legs'])
                ->unique(fn ($flight) => $flight->dpt_airport_id.'>'.$flight->arr_airport_id.'>'.$flight->airline_id)
                ->values();
        } elseif ($selectedDeparture || $selectedArrival) {
            $mapRouteFlights = (clone $q)->reorder()
                ->with('airline')
                ->select('id', 'dpt_airport_id', 'arr_airport_id', 'airline_id')
                ->distinct()
                ->limit(160)
                ->get();
        }

        $mapRoutes = $mapRouteFlights
            ->map(function ($flight) use ($mapByCode) {
                $airlineName = strtolower($flight->airline?->name ?? 'Air Inter');
                $airline = str_contains($airlineName, 'air charter')
                    ? 'air-charter'
                    : (str_contains($airlineName, 'inter cargo') ? 'ics' : 'air-inter');

                return [
                    'from' => $mapByCode->get($flight->dpt_airport_id),
                    'to' => $mapByCode->get($flight->arr_airport_id),
                    'airline' => $airline,
                    'airline_name' => $flight->airline?->name ?? 'Air Inter',
                ];
            })
            ->filter(fn ($route) => $route['from'] && $route['to'])
            ->values();

        $itineraryFlightIds = $itineraries
            ->flatMap(fn ($itinerary) => collect($itinerary['legs'])->pluck('id'))
            ->unique()
            ->values()
            ->all();
        $reservedFlightIds = Bid::with(['flight', 'aircraft'])
            ->where('user_id', $r->user()->id)
            ->whereIn('flight_id', $itineraryFlightIds)
            ->get()
            ->filter(function (Bid $bid) {
                $operation = $this->bookingOperation($bid);

                return !in_array($operation->operation_status, ['COMPLETED', 'CANCELLED'], true);
            })
            ->pluck('flight_id')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        return $this->page('flights', [
            'flights' => $flights,
            'itineraries' => $itineraries,
            'maxItineraryStops' => self::MAX_ITINERARY_STOPS,
            'reservedFlightIds' => $reservedFlightIds,
            'airlines' => Airline::where('active', true)->orderBy('name')->get(['id', 'name', 'icao']),
            'subfleets' => Subfleet::with('airline')->orderBy('name')->get(['id', 'name', 'type', 'airline_id']),
            'flightTypes' => Flight::where('active', true)->where('visible', true)->distinct()->orderBy('flight_type')->pluck('flight_type')
                ->mapWithKeys(fn ($type) => [$type => FlightType::label($type)]),
            'mapAirports' => $mapAirports,
            'mapRoutes' => $mapRoutes,
            'selectedDeparture' => $selectedDeparture,
            'selectedArrival' => $selectedArrival,
            'personalizedDefault' => $personalizedDefault,
            'programmeBase' => $programmeBase,
        ]);
    }

private function findProgrammeItineraries(Request $r, array $filters, string $origin, string $destination)
    {
        $query = Flight::query()
            ->where('active', true)
            ->where('visible', true)
            ->whereNotNull('dpt_airport_id')
            ->whereNotNull('arr_airport_id')
            ->whereColumn('dpt_airport_id', '<>', 'arr_airport_id')
            ->whereHas('airline', fn ($airline) => $airline->where('active', true))
            ->with(['airline', 'dpt_airport', 'arr_airport', 'subfleets']);

        if ($r->user() && !$r->user()->ability('admin', 'admin-access')) {
            $query->whereIn('airline_id', app(CompanyAccessService::class)->allowedAirlineIds($r->user()));
        }
        if ($r->filled('airline_id')) $query->where('airline_id', $filters['airline_id']);
        if ($r->filled('subfleet_id')) $query->whereHas('subfleets', fn ($subfleets) => $subfleets->where('subfleets.id', $filters['subfleet_id']));
        if ($r->filled('flight_type')) $query->where('flight_type', $filters['flight_type']);
        if ($r->filled('min_distance')) $query->where('distance', '>=', $filters['min_distance']);
        if ($r->filled('max_distance')) $query->where('distance', '<=', $filters['max_distance']);
        if ($r->filled('time_from')) $query->where('dpt_time', '>=', $filters['time_from']);
        if ($r->filled('time_to')) $query->where('dpt_time', '<=', $filters['time_to']);

        $candidates = $query->get();
        if ($candidates->isEmpty()) return collect();

        // Several timetable rows can describe the same network edge. One
        // deterministic representative per airport pair keeps the graph small
        // and prevents visually duplicate proposals.
        $network = $candidates
            ->groupBy(fn ($flight) => strtoupper((string) $flight->dpt_airport_id).'>'.strtoupper((string) $flight->arr_airport_id))
            ->map(function ($sameRoute) {
                return $sameRoute->sortBy(function ($flight) {
                    return sprintf(
                        '%09d:%012.2f:%s',
                        (int) ($flight->flight_time ?? 999999),
                        $this->flightDistanceNm($flight) ?: 999999,
                        (string) $flight->ident
                    );
                })->first();
            })
            ->values();

        $byDeparture = $network->groupBy(fn ($flight) => strtoupper((string) $flight->dpt_airport_id));
        $queue = [[
            'airport' => strtoupper($origin),
            'legs' => [],
            'visited' => [strtoupper($origin) => true],
            'distance_nm' => 0.0,
            'flight_time' => 0,
        ]];

        $cursor = 0;
        $expansions = 0;
        $found = [];
        $signatures = [];

        while (isset($queue[$cursor]) && $expansions < self::MAX_ITINERARY_EXPANSIONS) {
            $state = $queue[$cursor++];
            if (count($state['legs']) >= self::MAX_ITINERARY_LEGS) continue;

            $outgoing = $byDeparture->get($state['airport'], collect())
                ->sortBy(fn ($flight) => $this->flightDistanceNm($flight) ?: PHP_FLOAT_MAX);

            foreach ($outgoing as $flight) {
                if (++$expansions > self::MAX_ITINERARY_EXPANSIONS) break 2;

                $next = strtoupper((string) $flight->arr_airport_id);
                if (isset($state['visited'][$next])) continue;

                $legs = [...$state['legs'], $flight];
                $distance = $state['distance_nm'] + $this->flightDistanceNm($flight);
                $flightTime = $state['flight_time'] + (int) ($flight->flight_time ?? 0);

                if ($next === strtoupper($destination)) {
                    if (count($legs) < 2) continue;

                    $airports = [strtoupper($origin)];
                    foreach ($legs as $leg) $airports[] = strtoupper((string) $leg->arr_airport_id);
                    $signature = implode('>', $airports);

                    if (!isset($signatures[$signature])) {
                        $signatures[$signature] = true;
                        $found[] = [
                            'legs' => $legs,
                            'stops' => count($legs) - 1,
                            'distance_nm' => $distance,
                            'flight_time' => $flightTime,
                            'airports' => $airports,
                        ];
                    }

                    if (count($found) >= 30) break 2;
                    continue;
                }

                if (count($legs) < self::MAX_ITINERARY_LEGS) {
                    $visited = $state['visited'];
                    $visited[$next] = true;
                    $queue[] = [
                        'airport' => $next,
                        'legs' => $legs,
                        'visited' => $visited,
                        'distance_nm' => $distance,
                        'flight_time' => $flightTime,
                    ];
                }
            }
        }

        usort($found, static function (array $left, array $right): int {
            $legs = count($left['legs']) <=> count($right['legs']);
            if ($legs !== 0) return $legs;

            $distance = $left['distance_nm'] <=> $right['distance_nm'];
            if ($distance !== 0) return $distance;

            return $left['flight_time'] <=> $right['flight_time'];
        });

        return collect(array_slice($found, 0, self::MAX_ITINERARY_RESULTS));
    }

private function flightDistanceNm(Flight $flight): float
    {
        try {
            return $flight->distance ? (float) $flight->distance->toUnit('nmi') : 0.0;
        } catch (\Throwable) {
            return 0.0;
        }
    }

public function reserveItinerary(Request $r, \App\Services\BidService $bids)
    {
        $data = $r->validate([
            'flight_ids' => 'required|array|min:2|max:'.self::MAX_ITINERARY_LEGS,
            'flight_ids.*' => 'required|distinct|string',
            'departure' => 'nullable|string|max:8',
            'arrival' => 'nullable|string|max:8',
            'dep_icao' => 'nullable|string|max:8',
            'arr_icao' => 'nullable|string|max:8',
        ]);

        $origin = strtoupper(trim((string) ($data['departure'] ?? $data['dep_icao'] ?? '')));
        $destination = strtoupper(trim((string) ($data['arrival'] ?? $data['arr_icao'] ?? '')));
        if ($origin === '' || $destination === '' || $origin === $destination) {
            return back()->withErrors(['reservation' => 'L’itinéraire demandé est invalide.']);
        }

        if (setting('bids.allow_multiple_bids') === false) {
            return back()->withErrors([
                'reservation' => 'La réservation groupée nécessite l’activation des réservations multiples dans phpVMS.',
            ]);
        }

        $flightIds = array_values($data['flight_ids']);
        $user = $r->user();

        try {
            DB::transaction(function () use ($flightIds, $origin, $destination, $user, $bids) {
                $byId = Flight::query()
                    ->with(['airline', 'subfleets'])
                    ->whereIn('id', $flightIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy(fn ($flight) => (string) $flight->id);

                if ($byId->count() !== count($flightIds)) {
                    throw new \RuntimeException('Un des vols proposés n’existe plus.');
                }

                $ordered = collect($flightIds)->map(function ($flightId) use ($byId) {
                    return $byId->get((string) $flightId);
                });

                $expected = $origin;
                $visited = [$origin => true];
                $access = app(CompanyAccessService::class);

                foreach ($ordered as $flight) {
                    if (!$flight || !$flight->active || !$flight->visible || !$flight->airline?->active) {
                        throw new \RuntimeException('Un des vols de cet itinéraire n’est plus disponible.');
                    }
                    if (!$access->canAccessAirline($user, (int) $flight->airline_id)) {
                        throw new \RuntimeException('Votre profil ne permet plus de réserver une des compagnies de cet itinéraire.');
                    }

                    $departure = strtoupper((string) $flight->dpt_airport_id);
                    $arrival = strtoupper((string) $flight->arr_airport_id);
                    if ($departure !== $expected || isset($visited[$arrival])) {
                        throw new \RuntimeException('La chaîne d’escales proposée n’est plus cohérente.');
                    }

                    $visited[$arrival] = true;
                    $expected = $arrival;
                }

                if ($expected !== $destination) {
                    throw new \RuntimeException('La destination finale de cet itinéraire a changé.');
                }

                // A segment already reserved by this pilot is valid and should
                // not make the whole operation fail. Terminal/ghost operations
                // are cleaned using the same Prométhée lifecycle as a normal
                // single-flight reservation.
                foreach ($ordered as $flight) {
                    $active = $this->releaseTerminalReservationsForFlight($flight, $user);
                    if ($active) continue;
                    $bids->addBid($flight, $user);
                }
            }, 3);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'reservation' => $e->getMessage() ?: 'Impossible de réserver cet itinéraire.',
            ]);
        }

        return redirect()->route('promethee.bookings')
            ->with('success', count($flightIds).' vols réservés en une seule opération.');
    }

public function flight(string $id) {
        $flight = Flight::with(['airline','fares','subfleets','dpt_airport','arr_airport','alt_airport'])->findOrFail($id);
        $stats = [
            'pireps'=>Pirep::where('flight_id',$flight->id)->where('state',PirepState::ACCEPTED)->count(),
            'last'=>Pirep::where('flight_id',$flight->id)->where('state',PirepState::ACCEPTED)->orderByDesc('submitted_at')->first(),
        ];
        $history=Pirep::where('flight_id',$flight->id)->where('state',PirepState::ACCEPTED);
        $routeHistory=['flights'=>(clone $history)->count(),'average_time'=>(int) (clone $history)->avg('flight_time'),'best_time'=>(int) (clone $history)->min('flight_time')];
        try {
            $weather = app(OperationalWeatherService::class)->forFlight($flight);
        } catch (\Throwable) {
            $weather = ['status' => 'DEGRADED', 'stations' => [], 'sigmets' => [], 'summary' => [], 'note' => 'Météo opérationnelle temporairement indisponible.'];
        }
        $reservation = Bid::with(['flight','aircraft'])
            ->where(['flight_id'=>$flight->id,'user_id'=>auth()->id()])
            ->latest()
            ->first();
        if ($reservation) {
            $reservation = $this->bookingOperation($reservation);
            // A stale terminal bid must never make a completed line look
            // permanently reserved. The actual cleanup happens only on POST
            // when the pilot explicitly reserves/repeats the flight.
            if (in_array($reservation->operation_status, ['COMPLETED', 'CANCELLED'], true)) {
                $reservation = null;
            }
        }

        return $this->page('flight',[
            'flight'=>$flight,
            'stats'=>$stats,
            'routeHistory'=>$routeHistory,
            'weather'=>$weather,
            'recentPireps'=>Pirep::with(['user','aircraft'])->where('flight_id',$flight->id)->where('state',PirepState::ACCEPTED)->latest('submitted_at')->limit(12)->get(),
            'reservation'=>$reservation,
        ]);
    }

public function reserveFlight(string $id, Request $r, \App\Services\BidService $bids) {
        $flight=Flight::where(['id'=>$id,'active'=>true,'visible'=>true])->firstOrFail();
        abort_unless(app(CompanyAccessService::class)->canAccessAirline($r->user(), (int) $flight->airline_id), 403, 'Cette compagnie n’est pas encore accessible avec votre nombre d’heures de vol.');

        $existing = $this->releaseTerminalReservationsForFlight($flight, $r->user());
        if ($existing) {
            return redirect()->route('promethee.flights.show', $flight->id)
                ->with('success', 'Ce vol possède déjà une opération active ('.$existing->operation_id.').');
        }

        try { $bids->addBid($flight,$r->user()); return back()->with('success','Vol '.$flight->ident.' réservé.'); }
        catch (\Throwable $e) { return back()->withErrors(['reservation'=>$e->getMessage() ?: 'Cette réservation ne peut pas être créée.']); }
    }

public function briefing(string $id, Request $r, DemandProfileService $demand) {
        $flight=Flight::with(['airline','dpt_airport','arr_airport','alt_airport','subfleets'])->findOrFail($id);
        $distance=(float) $flight->distance->toUnit('nmi');
        $fuelUnit=setting('units.fuel', 'kg');
        // The dispatch rule is calculated in kilograms, then converted to the
        // unit selected for this installation before it is shown to the pilot.
        $suggestedFuel=(int) round(\App\Support\Units\Fuel::make(ceil(max(250,$distance*3.2)), 'kg')->toUnit($fuelUnit));
        $briefing=DB::table('promethee_briefings')->where(['user_id'=>$r->user()->id,'flight_id'=>$flight->id])->first();
        try {
            $weather = app(OperationalWeatherService::class)->forFlight($flight, $briefing?->alternate);
        } catch (\Throwable) {
            $weather = ['status' => 'DEGRADED', 'stations' => [], 'sigmets' => [], 'summary' => [], 'note' => 'Météo opérationnelle temporairement indisponible.'];
        }
        $bid=Bid::with(['aircraft.subfleet'])->where(['user_id'=>$r->user()->id,'flight_id'=>$flight->id])->latest()->first();
        $loadProfile = $bid?->aircraft
            ? $demand->profile(
                $bid->aircraft,
                $flight,
                app(\Modules\Promethee\Services\OperationIdentityService::class)->id($bid)
            )
            : null;
        $simbrief=SimBrief::with('aircraft')->where('user_id',$r->user()->id)
            ->where('flight_id',$flight->id)->latest('updated_at')->first();

        // An empty flight/subfleet pivot means "no line restriction" in phpVMS,
        // not "zero compatible fleets". Keep the briefing consistent with the
        // operation dispatch used by Hermes.
        $allowedSubfleetIds = app(UserService::class)->getAllowableSubfleets($r->user())->pluck('id')
            ->merge(app(\Modules\Promethee\Services\ShopEntitlementService::class)->subfleetIds($r->user()))->unique()->values();
        $flightSubfleetIds = $flight->subfleets->pluck('id');
        $compatibleSubfleetIds = $flightSubfleetIds->isEmpty()
            ? $allowedSubfleetIds
            : $allowedSubfleetIds->intersect($flightSubfleetIds)->values();
        $compatibleAircraftCount = Aircraft::query()
            ->whereIn('subfleet_id', $compatibleSubfleetIds)
            ->where('status', AircraftStatus::ACTIVE)
            ->count();

        return $this->page('briefing',[
            'flight'=>$flight,'weather'=>$weather,'briefing'=>$briefing,
            'suggestedFuel'=>$suggestedFuel,'fuelUnit'=>$fuelUnit,
            'bid'=>$bid,'simbrief'=>$simbrief,'loadProfile'=>$loadProfile,
            'lineFleetRestricted'=>$flightSubfleetIds->isNotEmpty(),
            'compatibleSubfleetCount'=>$compatibleSubfleetIds->count(),
            'compatibleAircraftCount'=>$compatibleAircraftCount,
        ]);
    }

public function simbrief(string $id, Request $r) {
        $simbrief=SimBrief::with(['flight.airline','aircraft.subfleet','pirep.flight.airline'])->where('user_id',$r->user()->id)->findOrFail($id);
        abort_unless($simbrief->xml, 404, 'OFP SimBrief indisponible.');
        $fares=collect();
        if (!empty($simbrief->fare_data)) {
            $decoded=json_decode($simbrief->fare_data, true);
            if (is_array($decoded)) $fares=collect($decoded);
        }

        $flight=$simbrief->flight ?: $simbrief->pirep?->flight;

        return $this->page('simbrief', ['simbrief'=>$simbrief,'ofp'=>$simbrief->xml,'flight'=>$flight,'aircraft'=>$simbrief->aircraft,'fares'=>$fares]);
    }

public function saveBriefing(string $id, Request $r) {
        Flight::findOrFail($id);
        $data=$r->validate(['ofp_reference'=>'nullable|string|max:120','alternate'=>'nullable|string|max:8','planned_fuel'=>'nullable|integer|min:0|max:1000000','notes'=>'nullable|string|max:4000']);
        if (!empty($data['alternate'])) $data['alternate']=strtoupper($data['alternate']);
        DB::table('promethee_briefings')->updateOrInsert(['user_id'=>$r->user()->id,'flight_id'=>$id],$data+['updated_at'=>now(),'created_at'=>now()]);
        DB::table('promethee_audit_logs')->insert(['actor_id'=>$r->user()->id,'action'=>'briefing.saved','subject_type'=>'flight','subject_id'=>$id,'context'=>json_encode(['ofp_reference'=>$data['ofp_reference']??null]),'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Briefing enregistré. L’OFP reste une référence pilote : il n’est pas transmis à un service tiers.');
    }

public function replay(string $id) {
        $pirep=Pirep::with(['user','aircraft','flight'])->findOrFail($id);
        $samples=DB::table('promethee_telemetry')->where('pirep_id',$id)->orderBy('recorded_at')->get()->map(fn ($row) => array_merge(json_decode($row->payload,true),['recorded_at'=>$row->recorded_at]))->values();
        $score=(new SafetyAnalyzer())->score($pirep->landing_rate === null ? null : (float) $pirep->landing_rate,$samples->all());
        return $this->page('replay',['pirep'=>$pirep,'samples'=>$samples,'analysis'=>$score['analysis'],'score'=>$score]);
    }
}
