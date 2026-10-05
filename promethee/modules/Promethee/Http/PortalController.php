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
use Modules\Promethee\Models\RealSimulatorCertification;
use Modules\Promethee\Services\{AirframeMaintenanceService,BrandingService,BulletinService,CompanyAccessService,DemandProfileService,EconomyFareResolver,EconomyService,EngineMaintenanceService,FleetRotationService,FlightOpsService,LegacyPirepScoringService,OperationalWeatherService,PilotPirepDeletionService,PirepJournalService,RegionalOperationsService,SafetyAnalyzer};

class PortalController extends PrometheeWebController
{
    
    /** Public, read-only OCC overview. No pilot-only fields are exposed here. */
    public function occ() {
        return view('promethee::occ', [
            'pilots' => User::where('state', UserState::ACTIVE)->orderByDesc('created_at')->limit(8)->get(['id','name','pilot_id','home_airport_id','created_at']),
            'completed' => Pirep::where('state', PirepState::ACCEPTED)->with('user:id,name,pilot_id')->latest('submitted_at')->limit(8)->get(),
            'active' => Pirep::whereIn('state', [PirepState::IN_PROGRESS, PirepState::PAUSED])->with('user:id,name,pilot_id')->latest('updated_at')->limit(8)->get(),
        ]);
    }
    public function publicPilots(Request $r) {
        $term = trim((string) $r->query('q'));
        $pilots = User::with(['rank','home_airport'])->whereIn('state',[UserState::ACTIVE,UserState::ON_LEAVE])->when($term,fn($q)=>$q->where(fn($q)=>$q->where('name','like','%'.$term.'%')->orWhere('pilot_id','like','%'.$term.'%')))->orderBy('pilot_id')->paginate(30)->withQueryString();
        $latest = Pirep::where('state', PirepState::ACCEPTED)->whereIn('user_id', $pilots->getCollection()->pluck('id'))->latest('submitted_at')->get()->unique('user_id')->keyBy('user_id');
        $pilots->getCollection()->each(fn (User $pilot) => $pilot->setRelation('latest_public_pirep', $latest->get($pilot->id)));
        return view('promethee::public-pilots', compact('pilots'));
    }
    

    

    
    /** The branded, public replacement for /legacy/pireps/{id}. */
    

    /**
     * Keep the historical controller seam while delegating the increasingly
     * rich journal reconstruction to its dedicated service.
     */
    

    

    /**
     * Start a fresh operation from a genuinely finished PIREP.
     *
     * Repeating a flight must never reuse the old Bid/operation id: Hermès
     * deliberately scopes duplicate detection and PIREP correlation to that
     * immutable id. Reusing it would make the new attempt inherit the terminal
     * state of the previous flight.
     */
    
    public function publicLive() { return view('promethee::public-live'); }

    /** Native company pages replacing the disabled Disposable module. */
    

    

    

    

    /** Operational record for one aircraft, including type-specific downloads. */
    

    /**
     * Return the next departure occurrence in Paris time.
     *
     * phpVMS schedules are recurring rather than dated.  Sorting their raw
     * HH:MM values made the dispatch board call flights from this morning
     * "upcoming" for the rest of the day.  A trailing Z explicitly denotes a
     * UTC time; otherwise the historic Air Inter timetable is Paris local time.
     */
    

    

    /**
     * The phpVMS timetable stores recurring HH:MM values, not dated flights.
     * A Z suffix means UTC; legacy Air Inter timetable values are Paris local
     * time. This is deliberately independent of the server's UTC timezone.
     */
    

    private function boardStatus(?Pirep $pirep, CarbonImmutable $departure, ?CarbonImmutable $arrival, CarbonImmutable $now): string
    {
        if ($pirep) {
            if ($pirep->state === PirepState::CANCELLED || $pirep->status === PirepStatus::CANCELLED) return 'cancelled';

            return match ($pirep->status) {
                PirepStatus::BOARDING => 'boarding',
                PirepStatus::DEPARTED, PirepStatus::PUSHBACK_TOW, PirepStatus::TAXI => 'departed',
                PirepStatus::TAKEOFF, PirepStatus::INIT_CLIM, PirepStatus::AIRBORNE,
                PirepStatus::ENROUTE, PirepStatus::DIVERTED => 'en_route',
                PirepStatus::APPROACH, PirepStatus::APPROACH_ICAO, PirepStatus::ON_FINAL => 'on_approach',
                PirepStatus::LANDING, PirepStatus::LANDED => 'landed',
                PirepStatus::ON_BLOCK, PirepStatus::ARRIVED => 'arrived',
                default => 'on_time',
            };
        }

        // Timetable-only rows use configurable operational windows. These are
        // presentation statuses, not PIREP state mutations.
        $boardingAt=$departure->subMinutes((int) config('departure-board.status.boarding_before_minutes',30));
        $gateClosedAt=$departure->subMinutes((int) config('departure-board.status.gate_closed_before_minutes',10));
        if ($now->lt($boardingAt)) return 'on_time';
        if ($now->lt($gateClosedAt)) return 'boarding';
        if ($now->lt($departure)) return 'gate_closed';

        if (!$arrival) return 'departed';
        $enRouteAt=$departure->addMinutes((int) config('departure-board.status.en_route_after_minutes',15));
        $approachAt=$arrival->subMinutes((int) config('departure-board.status.approach_before_minutes',25));
        $arrivedAt=$arrival->addMinutes((int) config('departure-board.status.arrived_after_minutes',10));

        if ($now->lt($enRouteAt)) return 'departed';
        if ($now->lt($approachAt)) return 'en_route';
        if ($now->lt($arrival)) return 'on_approach';
        if ($now->lt($arrivedAt)) return 'landed';

        return 'arrived';
    }

    private function airportCode(?Airport $airport, string $fallback): string
    {
        return strtoupper((string) ($airport?->iata ?: $airport?->icao ?: $fallback));
    }

    private function airportDestination(?Airport $airport, string $fallback): string
    {
        $label = strtoupper(trim((string) ($airport?->location ?: $airport?->name ?: $airport?->iata ?: $airport?->icao ?: $fallback)));
        // The physical split-flap board has a finite number of palettes.
        // Keep the database name untouched and only shorten its display value.
        $aliases = config('departure-board.airport_labels', []);

        return strtoupper((string) ($aliases[$airport?->id ?? $fallback] ?? \Illuminate\Support\Str::limit($label, 18, '')));
    }

    

    /** Build dated occurrences of recurring phpVMS timetable rows in the configured window. */
    private function departureBoardFlights(?string $homeAirportId, string $board='departures'): \Illuminate\Support\Collection
    {
        $now = CarbonImmutable::now('Europe/Paris');
        $from = $now->subMinutes(config('departure-board.past_minutes'));
        $until = $now->addMinutes(config('departure-board.future_minutes'));
        // A recurring timetable row must not inherit yesterday's state.
        // Only recent, still-open reports can drive today's operational board.
        $activePireps = Pirep::query()->whereIn('state', [PirepState::IN_PROGRESS, PirepState::PAUSED])
            ->whereNotNull('flight_id')->where('updated_at', '>=', $now->subHours(12))
            ->latest('updated_at')->get()->unique('flight_id')->keyBy('flight_id');

        $occurrences = Flight::query()->where('active', true)->where('visible', true)
            ->when($homeAirportId, function ($flights) use ($homeAirportId, $board) {
                $column=$board === 'arrivals' ? 'arr_airport_id' : 'dpt_airport_id';
                $flights->where($column, $homeAirportId);
            }, fn ($flights) => $flights->whereRaw('1 = 0'))
            ->with(['airline', 'dpt_airport', 'arr_airport'])->get()
            ->flatMap(function (Flight $flight) use ($now, $activePireps) {
                $rows = collect();
                // Include the next week so the empty-window fallback can use
                // the same dated, operation-day-aware source.
                for ($offset = -1; $offset <= 7; $offset++) {
                    $date = $now->addDays($offset);
                    $departure = $this->scheduledDateTime((string) $flight->dpt_time, $date);
                    if (!$departure) continue;
                    $dayBit = 1 << ($departure->isoWeekday() - 1);
                    if (($flight->days ?? 0) !== 0 && !($flight->days & $dayBit)) continue;

                    $arrival = $this->scheduledDateTime((string) $flight->arr_time, $date);
                    if ($arrival && $arrival->lte($departure)) $arrival = $arrival->addDay();
                    $pirep = $activePireps->get($flight->id);
                    $row = clone $flight;
                    $parisDeparture=$departure->setTimezone('Europe/Paris');
                    $parisArrival=$arrival?->setTimezone('Europe/Paris');
                    $row->setAttribute('board_departure_at', $parisDeparture);
                    $row->setAttribute('board_arrival_at', $parisArrival);
                    $row->setAttribute('board_departure_time', $parisDeparture->format('H:i'));
                    $row->setAttribute('board_arrival_time', $parisArrival?->format('H:i') ?? '----');
                    $row->setAttribute('board_departure_airport', $this->airportDestination($flight->dpt_airport, $flight->dpt_airport_id));
                    $row->setAttribute('board_destination', $this->airportDestination($flight->arr_airport, $flight->arr_airport_id));
                    $row->setAttribute('board_status', $this->boardStatus($pirep, $parisDeparture, $parisArrival, $now));
                    $row->setAttribute('board_logo_url', $this->airlineLogoUrl($flight->airline));
                    $row->setAttribute('board_airline_code', strtoupper((string) ($flight->airline?->code ?: $flight->airline?->callsign ?: '---')));
                    $rows->push($row);
                }
                return $rows;
            })->sortBy('board_departure_at')->values();

        if ($board === 'arrivals') {
            $arrivalFrom=$now->subMinutes((int) config('departure-board.arrivals_past_minutes',45));
            $arrivalUntil=$now->addMinutes((int) config('departure-board.arrivals_future_minutes',120));
            $inWindow=$occurrences
                ->filter(fn (Flight $flight) => $flight->board_arrival_at
                    && $flight->board_arrival_at->betweenIncluded($arrivalFrom,$arrivalUntil))
                ->sortBy('board_arrival_at')->values();

            return $this->balancedDepartureBoardRows($inWindow, 'board_arrival_at');
        }

        // Once airborne long enough to become EN ROUTE, a flight leaves the
        // departures board and remains visible on the arrivals board instead.
        $departureStatuses=['on_time','boarding','gate_closed','departed'];
        $inWindow=$occurrences
            ->filter(fn (Flight $flight) => $flight->board_departure_at->betweenIncluded($from,$until))
            ->filter(fn (Flight $flight) => in_array($flight->board_status,$departureStatuses,true));

        if ($inWindow->isNotEmpty() || !config('departure-board.future_fallback')) {
            return $this->balancedDepartureBoardRows($inWindow, 'board_departure_at');
        }

        return $this->balancedDepartureBoardRows(
            $occurrences
                ->filter(fn (Flight $flight) => $flight->board_departure_at->gt($until))
                ->filter(fn (Flight $flight) => in_array($flight->board_status,$departureStatuses,true)),
            'board_departure_at'
        );
    }

    private function balancedDepartureBoardRows(\Illuminate\Support\Collection $rows, string $sortKey='board_departure_at'): \Illuminate\Support\Collection
    {
        $limit=max(1,(int) config('departure-board.max_rows',20));
        if ($rows->count() <= $limit) return $rows->sortBy($sortKey)->values();

        $selected=collect();
        // Seed the board with one row from each status represented in the
        // window, then fill remaining slots chronologically.
        foreach ($rows->groupBy('board_status') as $statusRows) {
            if ($selected->count() >= $limit) break;
            $selected->push($statusRows->sortBy($sortKey)->first());
        }

        $selectedIds=$selected->map(fn (Flight $flight) => $flight->id.'-'.$flight->board_departure_at->format('YmdHi'))->all();
        foreach ($rows->sortBy($sortKey) as $flight) {
            if ($selected->count() >= $limit) break;
            $key=$flight->id.'-'.$flight->board_departure_at->format('YmdHi');
            if (!in_array($key,$selectedIds,true)) {
                $selected->push($flight);
                $selectedIds[]=$key;
            }
        }

        return $selected->sortBy($sortKey)->values();
    }

    private function departureBoardPayload(?string $homeAirportId, string $board='departures'): array
    {
        return $this->departureBoardFlights($homeAirportId, $board)->map(fn (Flight $flight) => [
            'id' => $flight->id.'-'.$flight->board_departure_at->format('YmdHi'),
            'url' => route('promethee.flights.show', $flight->id),
            'airline_code' => $flight->board_airline_code,
            'logo_url' => $flight->board_logo_url,
            'flight' => $flight->ident,
            'departure' => $flight->board_departure_airport,
            'departure_time' => $flight->board_departure_time,
            'destination' => $flight->board_destination,
            'arrival_time' => $flight->board_arrival_time,
            'status' => $flight->board_status,
            'status_label' => __('promethee_board.statuses.'.$flight->board_status),
        ])->all();
    }

    
    private function monthlyPilotRankings(): array
    {
        $monthStart = now('Europe/Paris')->startOfMonth()->utc();
        $monthEnd = now('Europe/Paris')->addMonthNoOverflow()->startOfMonth()->utc();

        $reports = Pirep::query()
            ->with('user:id,name,pilot_id')
            ->where('state', PirepState::ACCEPTED)
            ->where('submitted_at', '>=', $monthStart)
            ->where('submitted_at', '<', $monthEnd)
            ->whereNotNull('user_id')
            ->get([
                'id',
                'user_id',
                'flight_time',
                'block_off_time',
                'block_on_time',
                'distance',
                'landing_rate',
                'score',
                'submitted_at',
            ]);

        $pilots = $reports
            ->groupBy('user_id')
            ->map(function ($pireps, $userId) {
                $user = $pireps->first()?->user;
                $landingRates = $pireps->pluck('landing_rate')
                    ->filter(fn ($value) => $value !== null && (float) $value < 0)
                    ->map(fn ($value) => (float) $value);
                $scores = $pireps->pluck('score')
                    ->filter(fn ($value) => $value !== null)
                    ->map(fn ($value) => (float) $value);

                $distanceNmi = $pireps->sum(function (Pirep $pirep) {
                    return $pirep->distance
                        ? (float) $pirep->distance->toUnit('nmi')
                        : 0.0;
                });

                return (object) [
                    'user_id' => (int) $userId,
                    'name' => $user?->name ?: 'Pilote',
                    'pilot_id' => $user?->pilot_id,
                    'flights' => $pireps->count(),
                    'block_minutes' => (int) $pireps->sum(function (Pirep $pirep) {
                        if ($pirep->block_off_time && $pirep->block_on_time) {
                            return max(0, $pirep->block_off_time->diffInMinutes($pirep->block_on_time));
                        }

                        return (int) ($pirep->flight_time ?: 0);
                    }),
                    'soft_landing' => $landingRates->isNotEmpty() ? $landingRates->max() : null,
                    'hard_landing' => $landingRates->isNotEmpty() ? $landingRates->min() : null,
                    'distance_nmi' => $distanceNmi,
                    'score' => $scores->isNotEmpty() ? round($scores->avg()) : null,
                ];
            })
            ->values();

        $rank = fn (string $field, bool $descending = true) => $pilots
            ->filter(fn ($pilot) => $pilot->{$field} !== null)
            ->sortBy($field, SORT_REGULAR, $descending)
            ->take(5)
            ->map(function ($pilot) use ($field) {
                return (object) [
                    'user_id' => $pilot->user_id,
                    'name' => $pilot->name,
                    'pilot_id' => $pilot->pilot_id,
                    'value' => $pilot->{$field},
                ];
            })
            ->values();

        $formatRows = static function ($rows, string $kind) {
            return $rows->map(function ($row) use ($kind) {
                $row->display_value = match ($kind) {
                    'flights' => number_format((int) $row->value, 0, ',', ' '),
                    'block' => floor(((int) $row->value) / 60).'h '.str_pad(((int) $row->value) % 60, 2, '0', STR_PAD_LEFT).'m',
                    'landing' => number_format((int) round($row->value), 0, ',', ' ').' ft/min',
                    'distance' => number_format((int) round($row->value), 0, ',', ' ').' nmi',
                    'score' => number_format((int) round($row->value), 0, ',', ' '),
                    default => (string) $row->value,
                };
                return $row;
            });
        };

        $topPilotsByFlights = $rank('flights');
        $topPilotsByBlockTime = $rank('block_minutes');
        $topPilotsBySoftLanding = $rank('soft_landing');
        $topPilotsByDistance = $rank('distance_nmi');
        $topPilotsByScore = $rank('score');
        $topPilotsByHardLanding = $rank('hard_landing', false);

        return [
            'monthLabel' => now('Europe/Paris')->locale('fr')->isoFormat('MMMM'),
            'topPilotsByFlights' => $topPilotsByFlights,
            'topPilotsByBlockTime' => $topPilotsByBlockTime,
            'topPilotsBySoftLanding' => $topPilotsBySoftLanding,
            'topPilotsByDistance' => $topPilotsByDistance,
            'topPilotsByScore' => $topPilotsByScore,
            'topPilotsByHardLanding' => $topPilotsByHardLanding,
            'leaderboards' => [
                ['title' => 'Vols', 'rows' => $formatRows($topPilotsByFlights, 'flights')],
                ['title' => 'Temps de vol (block)', 'rows' => $formatRows($topPilotsByBlockTime, 'block')],
                ['title' => 'Touché le plus doux', 'rows' => $formatRows($topPilotsBySoftLanding, 'landing')],
                ['title' => 'Distance', 'rows' => $formatRows($topPilotsByDistance, 'distance')],
                ['title' => 'Score moyen', 'rows' => $formatRows($topPilotsByScore, 'score')],
                ['title' => 'Touché le plus dur', 'rows' => $formatRows($topPilotsByHardLanding, 'landing')],
            ],
        ];
    }

    private function operationsData(Request $r): array {
        $dayStart = now('Europe/Paris')->startOfDay()->utc();
        $dayEnd = now('Europe/Paris')->endOfDay()->utc();
        $monthStart = now('Europe/Paris')->startOfMonth()->utc();
        $homeAirportId = $r->user()->home_airport_id;
        $nextOperation = Bid::with(['flight.airline','aircraft.subfleet'])
            ->where('user_id', $r->user()->id)
            ->latest('created_at')
            ->get()
            ->map(fn (Bid $booking) => $this->bookingOperation($booking))
            ->first(fn (Bid $booking) => !in_array($booking->operation_status, ['COMPLETED','CANCELLED'], true));

        return [
            'nextOperation'=>$nextOperation,
            'activeFlights'=>Pirep::whereIn('state',[PirepState::IN_PROGRESS,PirepState::PAUSED])->count(),
            'pendingPireps'=>Pirep::where('state',PirepState::PENDING)->count(),
            'acceptedToday'=>Pirep::where('state',PirepState::ACCEPTED)->whereBetween('submitted_at',[$dayStart,$dayEnd])->count(),
            'acceptedMonth'=>Pirep::where('state',PirepState::ACCEPTED)->where('submitted_at','>=',$monthStart)->count(),
            'telemetrySamples'=>DB::table('promethee_telemetry')->where('created_at','>=',$dayStart)->count(),
            'pricingRules'=>DB::table('promethee_pricing')->count(),
            'lastBulletin'=>DB::table('promethee_bulletins')->orderByDesc('month')->first(),
            // Keep the pilot's departure-base scope, while allowing every
            // airline actually scheduled at that airport to appear.
            'departureBoard'=>$this->departureBoardPayload($homeAirportId, 'departures'),
            'arrivalBoard'=>$this->departureBoardPayload($homeAirportId, 'arrivals'),
            // The operations page still uses its compact "next available"
            // list; it is intentionally separate from the time-window board.
            'upcoming'=>Flight::where('active',true)->where('visible',true)->where('has_bid',false)
                ->when($homeAirportId, fn ($flights) => $flights->where('dpt_airport_id',$homeAirportId), fn ($flights) => $flights->whereRaw('1 = 0'))
                ->with(['airline','fares','arr_airport'])->get()
                ->map(function (Flight $flight) {
                    $nextDeparture = $this->nextDeparture($flight);
                    $flight->setAttribute('next_departure_at', $nextDeparture);
                    $flight->setAttribute('display_departure_time', $nextDeparture?->format('H:i'));
                    return $flight;
                })->filter(fn (Flight $flight) => $flight->next_departure_at !== null)
                ->sortBy('next_departure_at')->take(10)->values(),
            'homeAirportId'=>$homeAirportId,
            'recentPireps'=>Pirep::where('state',PirepState::ACCEPTED)->with(['airline','aircraft','user'])->orderByDesc('submitted_at')->limit(8)->get(),
            'topRoutes'=>Pirep::select('dpt_airport_id','arr_airport_id',DB::raw('COUNT(*) as total'))
                ->where('state',PirepState::ACCEPTED)->where('submitted_at','>=',$monthStart)
                ->groupBy('dpt_airport_id','arr_airport_id')->orderByDesc('total')->limit(8)->get(),
            'events'=>DB::table('promethee_events')->where('ends_at','>=',now())->orderBy('starts_at')->limit(4)->get(),
            'personal'=>Pirep::where('user_id',$r->user()->id)->where('state',PirepState::ACCEPTED)->count(),
        ];
    }
    public function dashboard(Request $r) {
        return $this->page('dashboard',[
            'flightCount'=>Flight::where('active',true)->count(),
            'pilotCount'=>User::where('state',UserState::ACTIVE)->count(),
            'pirepCount'=>Pirep::where('state',PirepState::ACCEPTED)->where('submitted_at','>=',now()->startOfMonth())->count(),
            'flights'=>Flight::where('active',true)->with('airline')->orderBy('dpt_time')->limit(7)->get(),
            'events'=>DB::table('promethee_events')->where('ends_at','>=',now())->orderBy('starts_at')->limit(3)->get(),
            'personal'=>Pirep::where('user_id',$r->user()->id)->where('state',PirepState::ACCEPTED)->count(),
        ] + $this->operationsData($r) + $this->monthlyPilotRankings());
    }
    public function departureBoardData(Request $r) {
        $homeAirportId=$r->user()->home_airport_id;
        $departures=$this->departureBoardPayload($homeAirportId, 'departures');
        $arrivals=$this->departureBoardPayload($homeAirportId, 'arrivals');
        $now=now();
        return response()->json([
            'updated_at' => $now->toIso8601String(),
            'revision' => sha1(json_encode([$departures,$arrivals], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)),
            'refresh_after_seconds' => max(10, 61-(int) $now->format('s')),
            'departures' => $departures,
            'arrivals' => $arrivals,
            'flights' => $departures,
        ]);
    }
    public function operations(Request $r) {
        return $this->page('operations',$this->operationsData($r));
    }
    public function catalogue(Request $r, string $type = 'flights') {
        abort_unless(in_array($type,['flights','airports'],true),404);
        $filters=$r->validate(['country'=>'nullable|string|size:2','region'=>'nullable|string|max:32','airline_id'=>'nullable|integer|exists:airlines,id','status'=>'nullable|in:all,active,inactive','flight_type'=>'nullable|string|size:1','arrival_country'=>'nullable|string|size:2','min_distance'=>'nullable|numeric|min:0','max_distance'=>'nullable|numeric|gte:min_distance','schedule'=>'nullable|in:all,defined,missing','fuel'=>'nullable|in:all,custom,default','coordinates'=>'nullable|in:all,complete,missing','q'=>'nullable|string|max:80']);
        $country=strtoupper($filters['country'] ?? ''); $term=$filters['q'] ?? '';
        $countries=Airport::whereNotNull('country')->where('country','!=','')->distinct()->orderBy('country')->pluck('country');
        $regions=Airport::whereNotNull('region')->where('region','!=','')->select('country','region')->distinct()->orderBy('country')->orderBy('region')->get();
        if ($type==='airports') {
            $items=Airport::query()->when($country,fn($query)=>$query->where('country',$country))->when($r->filled('region'),fn($query)=>$query->where('region',$filters['region']))->when(($filters['fuel'] ?? 'all')==='custom',fn($query)=>$query->where('fuel_jeta_cost','>',0))->when(($filters['fuel'] ?? 'all')==='default',fn($query)=>$query->where(fn($q)=>$q->whereNull('fuel_jeta_cost')->orWhere('fuel_jeta_cost','<=',0)))->when(($filters['coordinates'] ?? 'all')==='complete',fn($query)=>$query->whereNotNull('lat')->whereNotNull('lon'))->when(($filters['coordinates'] ?? 'all')==='missing',fn($query)=>$query->where(fn($q)=>$q->whereNull('lat')->orWhereNull('lon')))->when($term,fn($query)=>$query->where(fn($q)=>$q->where('id','like','%'.$term.'%')->orWhere('icao','like','%'.$term.'%')->orWhere('name','like','%'.$term.'%')->orWhere('location','like','%'.$term.'%')))->orderBy('country')->orderBy('region')->orderBy('location')->orderBy('name')->paginate(40)->withQueryString();
        } else {
            $items=Flight::with('airline')->when($country,fn($query)=>$query->whereHas('dpt_airport',fn($airports)=>$airports->where('country',$country)))->when($r->filled('region'),fn($query)=>$query->whereHas('dpt_airport',fn($airports)=>$airports->where('region',$filters['region'])))->when($r->filled('arrival_country'),fn($query)=>$query->whereHas('arr_airport',fn($airports)=>$airports->where('country',$filters['arrival_country'])))->when($r->filled('airline_id'),fn($query)=>$query->where('airline_id',$filters['airline_id']))->when(($filters['status'] ?? 'all')!=='all',fn($query)=>$query->where('active',$filters['status']==='active'))->when($r->filled('flight_type'),fn($query)=>$query->where('flight_type',$filters['flight_type']))->when($r->filled('min_distance'),fn($query)=>$query->where('distance','>=',$filters['min_distance']))->when($r->filled('max_distance'),fn($query)=>$query->where('distance','<=',$filters['max_distance']))->when(($filters['schedule'] ?? 'all')==='defined',fn($query)=>$query->whereNotNull('dpt_time'))->when(($filters['schedule'] ?? 'all')==='missing',fn($query)=>$query->whereNull('dpt_time'))->when($term,fn($query)=>$query->where(fn($q)=>$q->where('flight_number','like','%'.$term.'%')->orWhere('dpt_airport_id','like','%'.$term.'%')->orWhere('arr_airport_id','like','%'.$term.'%')->orWhere('route_code','like','%'.$term.'%')))->orderBy('dpt_airport_id')->orderBy('arr_airport_id')->paginate(40)->withQueryString();
        }
        return $this->page('catalogue',['type'=>$type,'items'=>$items,'countries'=>$countries,'regions'=>$regions,'airlines'=>Airline::where('active',true)->orderBy('name')->get(['id','name']),'flightTypes'=>Flight::where('active',true)->distinct()->orderBy('flight_type')->pluck('flight_type')]);
    }
    public function live(Request $r) { return $this->page('live'); }
    public function liveData(Request $r, FlightOpsService $ops) {
        $pireps = Pirep::whereIn('state', [PirepState::IN_PROGRESS, PirepState::PAUSED])
            ->with(['user', 'aircraft'])->orderByDesc('updated_at')->limit(50)->get();

        $telemetry = DB::table('promethee_telemetry')
            ->whereIn('pirep_id', $pireps->pluck('id'))
            ->orderBy('recorded_at')
            ->get()
            ->groupBy('pirep_id');

        return response()->json([
            'updated_at' => now()->toIso8601String(),
            'poll_after_seconds' => 5,
            'flights' => $pireps->map(function ($p) use ($ops, $telemetry) {
                $samples = collect($telemetry->get($p->id, []))->map(function ($sample) {
                    $payload = json_decode($sample->payload, true) ?: [];
                    $payload['recorded_at'] = (string) $sample->recorded_at;
                    return $payload;
                });
                $data = $samples->last() ?? [];

                $track=$samples->filter(fn(array $s)=>is_numeric($s['lat']??null)&&is_numeric($s['lon']??null))->map(fn(array $s)=>[(float)$s['lat'],(float)$s['lon']])->values();
                if ($track->count()>400) {
                    $stride=(int)ceil($track->count()/400); $last=$track->last();
                    $track=$track->filter(fn($point,int $i)=>$i%$stride===0)->values();
                    if ($last&&$track->last()!==$last) $track->push($last);
                }

                preg_match('/Hermes ACARS \\[(op_[^\\]]+)\\]/', (string) $p->source_name, $operationMatch);

                $transitions = [];
                $previousPhase = null;
                foreach ($samples as $sample) {
                    $phase = strtoupper((string) ($sample['phase'] ?? ''));
                    if ($phase === '' || $phase === $previousPhase) continue;
                    $transitions[] = ['phase' => $phase, 'at' => $sample['recorded_at']];
                    $previousPhase = $phase;
                }
                $firstAt = function (array $phases) use ($transitions) {
                    foreach ($transitions as $transition) {
                        if (in_array($transition['phase'], $phases, true)) return $transition['at'];
                    }
                    return null;
                };

                $recordedAt = $data['recorded_at'] ?? null;
                $age = $recordedAt ? now()->diffInSeconds(\Carbon\CarbonImmutable::parse($recordedAt)) : null;
                $signal = $age === null ? 'NO_SIGNAL' : ($age > 180 ? 'LOST' : ($age > 60 ? 'STALE' : 'LIVE'));

                return $ops->liveFlight([
                    'id' => $p->id,
                    'operation_id' => $operationMatch[1] ?? null,
                    'ident' => $p->ident,
                    'pilot' => $p->user?->name,
                    'aircraft' => $p->aircraft?->registration,
                    'departure' => $p->dpt_airport_id,
                    'arrival' => $p->arr_airport_id,
                    'state' => $data['phase'] ?? PirepState::label($p->state),
                    'phase' => $data['phase'] ?? null,
                    'recorded_at' => $recordedAt,
                    'signal' => $signal,
                    'milestones' => [
                        'out' => $firstAt(['PUSHBACK', 'TAXI_OUT', 'TAKEOFF', 'CLIMB', 'CRUISE', 'ENROUTE', 'DESCENT', 'APPROACH', 'FINAL', 'LANDING', 'TAXI_IN', 'IN']),
                        'off' => $firstAt(['TAKEOFF', 'CLIMB', 'CRUISE', 'ENROUTE', 'DESCENT', 'APPROACH', 'FINAL', 'LANDING', 'TAXI_IN', 'IN']),
                        'on' => $firstAt(['LANDING', 'TAXI_IN', 'IN']),
                        'in' => $firstAt(['IN']),
                    ],
                    'phase_history' => $transitions,
                    'track' => $track->all(),
                    'lat' => $data['lat'] ?? null, 'lon' => $data['lon'] ?? null,
                    'altitude' => $data['altitude_msl'] ?? null, 'ias' => $data['ias'] ?? null,
                    'gs' => $data['gs'] ?? null, 'vs' => $data['vs'] ?? null,
                    'heading' => $data['heading'] ?? null, 'fuel' => $data['fuel'] ?? null,
                    'on_ground' => $data['on_ground'] ?? null,
                ], $data);
            })->values(),
        ]);
    }
    public function profile(Request $r) {
        return $this->pilot($r->user()->id);
    }
    public function editProfile(Request $r) {
        abort_unless(config('services.airinter_id.enabled'), 503, 'Argos doit être activé pour modifier un profil.');
        return redirect()->away(rtrim((string) config('services.airinter_id.base_url'), '/').'/account#profile');
    }

    public function updateProfile(Request $r) {
        abort_unless(config('services.airinter_id.enabled'), 503, 'Argos doit être activé pour modifier un profil.');
        return redirect()->away(rtrim((string) config('services.airinter_id.base_url'), '/').'/account#profile');
    }

    /**
     * Pilot passport. A country is stamped after an accepted flight touching
     * one of its airports; no editable or duplicate passport data is stored.
     */

   public function passport(Request $r) {
        $viewer = $r->user();

        // Raw aggregates are not handled by Laravel's table-prefix grammar.
        $airportCountry = DB::getTablePrefix().'airports.country';
        $ranking = DB::query()->fromSub(
            Pirep::query()->select('user_id', 'dpt_airport_id as airport_id')->where('state', PirepState::ACCEPTED)
                ->unionAll(Pirep::query()->select('user_id', 'arr_airport_id as airport_id')->where('state', PirepState::ACCEPTED)),
            'passport_legs'
        )->join('airports', 'airports.id', '=', 'passport_legs.airport_id')
            ->join('users', 'users.id', '=', 'passport_legs.user_id')
            ->whereNotNull('airports.country')->where('airports.country', '!=', '')
            ->select('users.id', 'users.name', 'users.pilot_id', DB::raw('COUNT(DISTINCT '.$airportCountry.') as countries'))
            ->groupBy('users.id', 'users.name', 'users.pilot_id')->orderByDesc('countries')->orderBy('users.pilot_id')->limit(20)->get();

        $allowedPilotIds = $ranking->pluck('id')->map(fn ($id) => (int) $id)->push((int) $viewer->id)->unique();
        $requestedPilotId = (int) $r->query('pilot', $viewer->id);
        if (!$allowedPilotIds->contains($requestedPilotId)) $requestedPilotId = (int) $viewer->id;
        $pilot = User::find($requestedPilotId) ?: $viewer;

        $countries = DB::query()->fromSub(
            Pirep::query()->selectRaw('dpt_airport_id as airport_id, submitted_at as stamped_at')
                ->where('user_id', $pilot->id)->where('state', PirepState::ACCEPTED)
                ->unionAll(Pirep::query()->selectRaw('arr_airport_id as airport_id, submitted_at as stamped_at')
                    ->where('user_id', $pilot->id)->where('state', PirepState::ACCEPTED)),
            'stamps'
        )->join('airports', 'airports.id', '=', 'stamps.airport_id')
            ->whereNotNull('airports.country')->where('airports.country', '!=', '')
            ->select('airports.country', DB::raw('MIN(stamped_at) as first_visit'), DB::raw('MAX(stamped_at) as last_visit'), DB::raw('COUNT(*) as legs'))
            ->groupBy('airports.country')->orderBy('airports.country')->get();

        $passportCountries = DB::query()->fromSub(
            DB::table('flights')->select('dpt_airport_id as airport_id')
                ->whereNotNull('dpt_airport_id')->where('dpt_airport_id', '!=', '')
                ->union(DB::table('flights')->select('arr_airport_id as airport_id')
                    ->whereNotNull('arr_airport_id')->where('arr_airport_id', '!=', '')),
            'passport_network_airports'
        )->join('airports', 'airports.id', '=', 'passport_network_airports.airport_id')
            ->whereNotNull('airports.country')->where('airports.country', '!=', '')
            ->select('airports.country')->distinct()->orderBy('airports.country')->pluck('country')
            ->map(fn ($country) => strtoupper(trim((string) $country)))
            ->filter(fn ($country) => preg_match('/^[A-Z]{2}$/', $country) === 1)
            ->values();

        abort_unless(DB::table('promethee_settings')->where('key','passport.enabled')->value('value') !== '0', 404);
        return $this->page('passport', compact('pilot', 'viewer', 'countries', 'ranking', 'passportCountries'));
   }
   /** The pilot's phpVMS bids projected as operational states. */
   public function bookings(Request $r) {
       $bookings = Bid::with(['flight.airline', 'flight.dpt_airport', 'flight.arr_airport', 'aircraft'])
           ->where('user_id', $r->user()->id)->latest()->get()
           ->map(fn (Bid $booking) => $this->bookingOperation($booking));
       return $this->page('bookings', compact('bookings'));
   }

   public function cancelBooking(string $bid, Request $r) {
       $booking = Bid::with(['flight', 'aircraft'])->where('user_id', $r->user()->id)->findOrFail($bid);
       $booking = $this->bookingOperation($booking);
       if (!$booking->operation_can_delete) {
           return back()->withErrors(['booking' => 'Cette opération a déjà commencé et ne peut plus être supprimée.']);
       }
       $ident = $booking->flight?->ident ?? $booking->flight_id;
       $booking->delete();

       return redirect()->route('promethee.bookings')->with('success', 'Réservation '.$ident.' supprimée.');
   }

   public function deleteBookingPirep(string $bid, Request $r)
   {
       $booking = Bid::with(['flight', 'aircraft'])->where('user_id', $r->user()->id)->findOrFail($bid);
       $booking = $this->bookingOperation($booking);
       $pirep = $booking->operation_pirep;

       abort_if(!$pirep, 404, 'Aucun PIREP n’est lié à cette opération.');
       app(PilotPirepDeletionService::class)->deleteOwn($pirep, $r->user(), 'promethee-booking');

       return redirect()->route('promethee.bookings')
           ->with('success', 'PIREP '.$pirep->id.' abandonné. La réservation reste disponible pour recommencer la préparation.');
   }

   

   

   /**
    * Remove only stale terminal reservations for this exact pilot/flight.
    * Any non-terminal operation wins and is returned untouched.
    */
   

   

   
   
   /**
    * Only expose files owned by Promethee or attached to operational catalogue
    * models. The global files table also contains unrelated application assets
    * which must never fall through into the Documents category.
    */
   
   
   

   
   
   
   
   
   
   
   
   
   
   
    /** Current missions and circuits. Completion is derived from accepted PIREPs. */
    

    

    

    public function assignments(Request $r) {
        $month=$r->query('month',now('Europe/Paris')->format('Y-m'));
        abort_unless((bool)preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/',$month),422,'Mois invalide.');
        $assignments=DB::table('promethee_assignments as assignment')->join('flights','flights.id','=','assignment.flight_id')->where('assignment.user_id',$r->user()->id)->where('assignment.month',$month)->select('assignment.*','flights.route_code','flights.flight_number','flights.dpt_airport_id','flights.arr_airport_id')->get();
        $completed=Pirep::where('user_id',$r->user()->id)->where('state',PirepState::ACCEPTED)->whereIn('flight_id',$assignments->pluck('flight_id'))->pluck('flight_id')->flip();
        $assignments->each(fn($assignment)=>$assignment->completed=$completed->has($assignment->flight_id));
        return $this->page('assignments',compact('assignments','month'));
    }
    public function shop(Request $r) { $pilot=$r->user()->fresh('journal'); $journal=$pilot->journal ?: $pilot->initJournal(); $wallet=$journal->getBalance(); $items=DB::table('promethee_shop_items')->where('active',true)->orderBy('price')->get(); $orders=DB::table('promethee_shop_orders as orders')->join('promethee_shop_items as items','items.id','=','orders.item_id')->where('orders.user_id',$pilot->id)->select('orders.*','items.name')->latest('purchased_at')->get(); return $this->page('shop',compact('wallet','items','orders')); }
    public function buyShopItem(int $id, Request $r, FinanceService $finance) { return DB::transaction(function() use($id,$r,$finance) { $item=DB::table('promethee_shop_items')->where('id',$id)->where('active',true)->lockForUpdate()->first(); abort_unless($item,404); $user=$r->user()->fresh('journal'); $journal=$user->journal ?: $user->initJournal(); if((int)$journal->getBalance()->getAmount() < (int)$item->price) return back()->withErrors(['shop'=>'Solde phpVMS insuffisant.']); $finance->debitFromJournal($journal,new Money($item->price),$user,'Boutique : '.$item->name,'shop','shop'); DB::table('promethee_shop_orders')->insert(['user_id'=>$user->id,'item_id'=>$item->id,'price'=>$item->price,'purchased_at'=>now(),'created_at'=>now(),'updated_at'=>now()]); return back()->with('success','Achat enregistré dans votre journal phpVMS.'); }); }
    public function transfers(Request $r) { return redirect()->route('promethee.airlines')->with('success','Les transferts ont été remplacés par l’accès automatique aux compagnies selon vos heures de vol.'); }
    /** Same formula as the historical Prometheus jumpseat: configurable base per nautical mile. */
    private function jumpseatQuote(User $user, Airport $destination): array {
        $originId = $user->curr_airport_id ?: $user->home_airport_id;
        $origin = Airport::findOrFail($originId);
        $distance = app(AirportService::class)->calculateDistance($origin->id, $destination->id)->toUnit('nmi', 2);
        $discount = (float) setting('dbasic.jumpseat_discount', 0);
        $ratio = $discount > 0 && $discount < 100 ? 1 - ($discount / 100) : 1;
        $basePrice = (float) (DB::table('promethee_settings')->where('key', 'jumpseat.base_price')->value('value') ?: 0.13);
        $amount = Money::createFromAmount(round($basePrice * $distance * $ratio, 2));

        return compact('origin', 'distance', 'discount', 'amount');
    }
    public function jumpseat(Request $r) {
        $pilot = $r->user()->load('journal');
        // Older pilot records can predate phpVMS journal creation. Ensure they
        // have a zero-balance journal before rendering the wallet.
        $journal = $pilot->journal ?: $pilot->initJournal();
        $origin = Airport::find($pilot->curr_airport_id ?: $pilot->home_airport_id);
        return $this->page('jumpseat',[
            'airports'=>Airport::orderBy('country')->orderBy('icao')->get(['id','icao','name','location','country']),
            'origin'=>$origin,
            'basePrice'=>(float) (DB::table('promethee_settings')->where('key', 'jumpseat.base_price')->value('value') ?: 0.13),
            'discount'=>(float) setting('dbasic.jumpseat_discount', 0),
            'wallet'=>$journal->getBalance(),
            'orders'=>DB::table('promethee_transfer_requests as request')
                ->leftJoin('airports','airports.id','=','request.target_airport_id')
                ->leftJoin('airlines','airlines.id','=','request.target_airline_id')
                ->where('request.user_id',$pilot->id)->where('request.type','jumpseat')
                ->select('request.*','airports.icao','airports.name as airport_name','airlines.icao as airline_icao','airlines.name as airline_name')
                ->latest('request.created_at')->get()
        ]);
    }
    public function requestJumpseat(Request $r, FinanceService $finance) {
        $data=$r->validate(['target_airport_id'=>'required|string|exists:airports,id','reason'=>'nullable|string|max:2000']);
        return DB::transaction(function() use($data,$r,$finance) {
            $user=User::with(['journal','airline.journal'])->findOrFail($r->user()->id);
            $journal = $user->journal ?: $user->initJournal();
            $airport=Airport::findOrFail($data['target_airport_id']);
            $originId=$user->curr_airport_id ?: $user->home_airport_id;
            if (!$originId) return back()->withErrors(['jumpseat'=>'Votre aéroport actuel est introuvable.']);
            if ($originId === $airport->id) return back()->withErrors(['jumpseat'=>'Vous êtes déjà positionné à cet aéroport.']);
            $quote=$this->jumpseatQuote($user,$airport);
            if ($r->boolean('preview')) return back()->withInput()->with('jumpseat_quote', $quote['origin']->icao.' → '.$airport->icao.' · '.$quote['distance'].' NM · '.$quote['amount']);
            if((int)$journal->getBalance()->getAmount() < (int)$quote['amount']->getAmount()) return back()->withErrors(['jumpseat'=>'Solde phpVMS insuffisant pour ce jumpseat ('.$quote['amount'].').']);
            $finance->debitFromJournal($journal,$quote['amount'],$user,'Jumpseat '.$quote['origin']->icao.' > '.$airport->icao,'jumpseat','jumpseat');
            if ($user->airline?->journal) $finance->creditToJournal($user->airline->journal,$quote['amount'],$user,'Jumpseat de '.$user->name.' ('.$quote['origin']->icao.' > '.$airport->icao.')','jumpseat','jumpseat');
            $user->update(['curr_airport_id'=>$airport->id]);
            DB::table('promethee_transfer_requests')->insert(['user_id'=>$user->id,'type'=>'jumpseat','target_airport_id'=>$airport->id,'reason'=>$data['reason']??null,'status'=>'approved','decision_note'=>'Déplacement automatique : '.$quote['distance'].' NM, remise '.$quote['discount'].' %, montant '.$quote['amount'].'.','decided_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
            return back()->with('success','Jumpseat effectué : '.$quote['origin']->icao.' → '.$airport->icao.' ('.$quote['distance'].' NM, '.$quote['amount'].').');
        });
    }
    public function requestTransfer(Request $r, FinanceService $finance) { $data=$r->validate(['type'=>'required|in:airline,hub,jumpseat','target_airline_id'=>'nullable|required_if:type,airline|required_if:type,jumpseat|exists:airlines,id','target_airport_id'=>'nullable|required_if:type,hub|exists:airports,id','reason'=>'nullable|string|max:2000']); if($data['type']==='jumpseat') return DB::transaction(function() use($data,$r,$finance) { $user=$r->user()->fresh('journal'); $price=(int)(DB::table('promethee_settings')->where('key','jumpseat.price')->value('value') ?: 2500); if((int)$user->journal->getBalance()->getAmount()<$price)return back()->withErrors(['transfer'=>'Solde phpVMS insuffisant pour le jumpseat.']); $airline=Airline::findOrFail($data['target_airline_id']); $finance->debitFromJournal($user->journal,new Money($price),$user,'Jumpseat : '.$airline->name,'jumpseat','jumpseat'); DB::table('promethee_transfer_requests')->insert(['user_id'=>$user->id,'type'=>'jumpseat','target_airline_id'=>$airline->id,'reason'=>$data['reason']??null,'status'=>'approved','decision_note'=>'Accord automatique après paiement.','decided_at'=>now(),'created_at'=>now(),'updated_at'=>now()]); return back()->with('success','Jumpseat accordé et débité de votre journal phpVMS.'); }); DB::table('promethee_transfer_requests')->insert(['user_id'=>$r->user()->id,'type'=>$data['type'],'target_airline_id'=>$data['target_airline_id']??null,'target_airport_id'=>$data['target_airport_id']??null,'reason'=>$data['reason']??null,'status'=>'pending','created_at'=>now(),'updated_at'=>now()]); return back()->with('success','Demande de transfert transmise au staff.'); }
    

    

    

    

    
    
    
    

    
    
    
    
    
    
    
    
    public function pilots(Request $r) {
        $r->validate(['status'=>'nullable|in:actif,ancien,heaven','q'=>'nullable|string|max:80','airline_id'=>'nullable|integer|exists:airlines,id','rank_id'=>'nullable|integer|exists:ranks,id']);
        $status=$r->query('status','actif');
        $q=User::query()->with(['rank','airline']);
        // A pilote en congé reste membre de la communauté : il ne doit pas disparaître de l'annuaire.
        if ($status==='actif') $q->whereIn('state',[UserState::ACTIVE,UserState::ON_LEAVE])->whereNotIn('id',DB::table('promethee_members')->whereIn('status',['ancien','heaven'])->select('user_id'));
        else $q->whereIn('id',DB::table('promethee_members')->where('status',$status)->select('user_id'));
        if ($r->filled('q')) {
            $term='%'.$r->query('q').'%';
            $q->where(fn ($pilots) => $pilots->where('name','like',$term)->orWhere('email','like',$term)->orWhere('pilot_id','like',$term));
        }
        if ($r->filled('airline_id')) $q->where('airline_id',$r->query('airline_id'));
        if ($r->filled('rank_id')) $q->where('rank_id',$r->query('rank_id'));
        $pilots=$q->orderBy('pilot_id')->paginate(24)->withQueryString();
        $memorials=DB::table('promethee_members')->whereIn('user_id',$pilots->getCollection()->pluck('id'))
            ->get(['user_id','status','memorial_portrait_url','memorial_tribute'])->keyBy('user_id');
        $pilots->getCollection()->each(function (User $pilot) use ($memorials) {
            $memorial=$memorials->get($pilot->id);
            $pilot->setAttribute('member_status', $memorial->status ?? 'actif');
            $pilot->setAttribute('memorial_portrait_url', $memorial->memorial_portrait_url ?? null);
            $pilot->setAttribute('memorial_tribute', $memorial->memorial_tribute ?? null);
        });
        return $this->page('pilots',[
            'pilots'=>$pilots,
            'status'=>$status,
            'airlines'=>Airline::orderBy('name')->get(['id','name','icao']),
            'ranks'=>Rank::orderBy('hours')->orderBy('name')->get(['id','name']),
            'communityDocuments'=>app(\Modules\Promethee\Services\DownloadCatalogueService::class)->groups()->get('documents',collect())->take(3),
        ]);
    }
    public function pilot(int $id) {
        $pilot = User::with(['rank','airline','home_airport','current_airport','journal'])->withCount([
            'pireps as accepted_pireps_count' => fn ($q) => $q->where('state',PirepState::ACCEPTED),
            'bids as bids_count',
        ])->findOrFail($id);
        $pireps = Pirep::where('user_id',$pilot->id)->where('state',PirepState::ACCEPTED)
            ->with(['airline','aircraft'])->orderByDesc('submitted_at')->paginate(15)->withQueryString();
        $routes = Pirep::select('dpt_airport_id','arr_airport_id',DB::raw('COUNT(*) as total'))
            ->where('user_id',$pilot->id)->where('state',PirepState::ACCEPTED)
            ->groupBy('dpt_airport_id','arr_airport_id')->orderByDesc('total')->limit(8)->get();
        $monthFlights=Pirep::where('user_id',$pilot->id)->where('state',PirepState::ACCEPTED)->where('submitted_at','>=',now()->startOfMonth())->count();
        $badges=collect([['name'=>'Premier officier','description'=>'10 vols validés','earned'=>$pilot->accepted_pireps_count>=10],['name'=>'Régularité','description'=>'3 vols ce mois','earned'=>$monthFlights>=3],['name'=>'Grand voyageur','description'=>'50 heures de vol','earned'=>($pilot->flight_time??0)>=3000]]);
        $badges=$pilot->awards()->orderBy('name')->get();
        // Older/imported pilot accounts may not yet have a phpVMS journal.
        // A profile remains read-only in that case and reports a zero balance.
        $wallet = $pilot->journal?->getBalance() ?? new Money(0);
        $memberProfile = DB::table('promethee_members')->where('user_id', $pilot->id)->first();
        $pilot->setAttribute('admin_portrait_url', $memberProfile->memorial_portrait_url ?? null);
        $realSimulatorCertifications = RealSimulatorCertification::query()
            ->where('user_id', $pilot->id)
            ->where('status', 'verified')
            ->orderByDesc('completed_on')
            ->orderByDesc('id')
            ->get();

        $myMissions = collect();
        if (auth()->check() && (int) auth()->id() === (int) $pilot->id) {
            $myMissions = DB::table('promethee_mission_bookings as booking')
                ->join('promethee_missions as mission', 'mission.id', '=', 'booking.mission_id')
                ->leftJoin('aircraft', 'aircraft.id', '=', 'mission.aircraft_id')
                ->where('booking.user_id', $pilot->id)
                ->where('booking.status', 'reserved')
                ->select([
                    'booking.id as booking_id',
                    'booking.reserved_at',
                    'booking.jumpseat_amount',
                    'mission.id',
                    'mission.title',
                    'mission.description',
                    'mission.mission_type',
                    'mission.dpt_airport_id',
                    'mission.arr_airport_id',
                    'mission.ends_on',
                    'mission.active',
                    'aircraft.registration as aircraft_registration',
                ])
                ->orderBy('mission.ends_on')
                ->orderByDesc('booking.reserved_at')
                ->get();
        }

        return $this->page('profile',[
            'pilot'=>$pilot,
            'wallet'=>$wallet,
            'pireps'=>$pireps,
            'routes'=>$routes,
            'monthFlights'=>$monthFlights,
            'badges'=>$badges,
            'myMissions'=>$myMissions,
            'realSimulatorCertifications'=>$realSimulatorCertifications,
        ]);
    }
    public function saveMember(int $id,Request $r) {
        User::findOrFail($id);
        $d=$r->validate(['status'=>'required|in:actif,ancien,heaven','memorial_portrait_url'=>'nullable|url|max:2000','memorial_tribute'=>'nullable|string|max:4000','memorial_portrait'=>'nullable|image|max:4096']);
        $current=DB::table('promethee_members')->where('user_id',$id)->first();
        $portrait=$r->hasFile('memorial_portrait') || $r->filled('memorial_portrait_url') ? $this->memorialPortrait($r) : ($current->memorial_portrait_url ?? null);
        if ($d['status'] !== 'heaven') {
            // The portrait also acts as an administrator-defined avatar for
            // active/former pilots. Only the memorial tribute is Heaven-only.
            $d['memorial_tribute']=null;
        }
        unset($d['memorial_portrait'], $d['memorial_portrait_url']);
        $d['memorial_portrait_url']=$portrait;
        DB::table('promethee_members')->updateOrInsert(['user_id'=>$id],$d+['updated_at'=>now(),'created_at'=>now()]);
        return back()->with('success','Classement mis à jour. Le compte et le carnet de vol sont conservés.');
    }
    private function memorialPortrait(Request $r): ?string {
        if ($r->hasFile('memorial_portrait')) {
            $file=$r->file('memorial_portrait'); $directory=storage_path('app/promethee-distinctions');
            Filesystem::ensureDirectoryExists($directory);
            $name=uniqid('memorial_', true).'.'.$file->extension(); $file->move($directory,$name);
            return '/promethee-assets/distinctions/'.$name;
        }
        return $r->filled('memorial_portrait_url') ? $r->string('memorial_portrait_url')->toString() : null;
    }
    

    

    

    

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    public function mailbox(Request $r) {
        $messages=DB::table('promethee_messages')->where(function ($query) use ($r) {$query->where('recipient_id',$r->user()->id)->orWhere('sender_id',$r->user()->id)->orWhere('shared_staff',true);})->latest()->paginate(30);
        return $this->page('mailbox',['messages'=>$messages,'pilots'=>User::whereIn('state',[UserState::ACTIVE,UserState::ON_LEAVE])->orderBy('pilot_id')->get(['id','name','email','pilot_id']),'activeCount'=>User::where('state',UserState::ACTIVE)->count(),'staffCount'=>User::query()->whereHas('roles', fn ($query) => $query->where('name', 'admin'))->count()]);
    }
    public function sendMessage(Request $r) {
        $data=$r->validate(['audience'=>'required|in:user,active,staff','user_id'=>'required_if:audience,user|nullable|exists:users,id','subject'=>'required|string|max:191','body'=>'required|string|max:10000','shared_staff'=>'nullable|boolean']);
        $recipients=match($data['audience']) { 'user'=>User::whereKey($data['user_id'])->get(), 'active'=>User::where('state',UserState::ACTIVE)->get(), 'staff'=>User::query()->whereHas('roles', fn ($query) => $query->where('name', 'admin'))->get() };
        abort_if($recipients->isEmpty(),422,'Aucun destinataire pour ce groupe.');
        foreach ($recipients as $recipient) { $id=DB::table('promethee_messages')->insertGetId(['sender_id'=>$r->user()->id,'recipient_id'=>$recipient->id,'recipient_email'=>$recipient->email,'audience'=>$data['audience'],'subject'=>$data['subject'],'body'=>$data['body'],'shared_staff'=>$r->boolean('shared_staff') || $data['audience']==='staff','direction'=>'outbound','status'=>'queued','created_at'=>now(),'updated_at'=>now()]); try { Mail::raw($data['body'],fn($mail)=>$mail->to($recipient->email,$recipient->name)->subject($data['subject'])); DB::table('promethee_messages')->where('id',$id)->update(['status'=>'sent','sent_at'=>now(),'updated_at'=>now()]); } catch (\Throwable) { DB::table('promethee_messages')->where('id',$id)->update(['status'=>'failed','updated_at'=>now()]); } }
        return back()->with('success',$recipients->count().' message(s) préparé(s) pour envoi. Consultez le statut dans la boîte partagée.');
    }
    public function network(\Modules\Promethee\Services\PresenceService $presence) {
        $monthStart=now()->startOfMonth();
        $monthCounts=DB::table('pireps')->select('flight_id',DB::raw('COUNT(*) as total'))->where('state',PirepState::ACCEPTED)->where('submitted_at','>=',$monthStart)->groupBy('flight_id');
        $totalCounts=DB::table('pireps')->select('flight_id',DB::raw('COUNT(*) as total'))->where('state',PirepState::ACCEPTED)->groupBy('flight_id');
        // The query builder prefixes subquery aliases, while raw SQL
        // fragments are left untouched. Keep both references aligned.
        $prefix=DB::getTablePrefix();
        $monthAlias=$prefix.'month_counts';
        $totalAlias=$prefix.'total_counts';
        $flights=Flight::where('flights.active',true)->where('flights.visible',true)->with('airline')
            ->leftJoinSub($monthCounts,'month_counts',fn ($join) => $join->on('flights.id','=','month_counts.flight_id'))
            ->leftJoinSub($totalCounts,'total_counts',fn ($join) => $join->on('flights.id','=','total_counts.flight_id'))
            ->select('flights.*',DB::raw("COALESCE(`{$monthAlias}`.`total`,0) as month_pireps"),DB::raw("COALESCE(`{$totalAlias}`.`total`,0) as total_pireps"))
            ->orderByDesc('month_pireps')->limit(100)->get();
        return $this->page('network',['flights'=>$flights,'season'=>DB::table('promethee_seasons')->where('active',true)->first(),'subfleets'=>Subfleet::with('airline')->orderBy('type')->get(),'imports'=>DB::table('promethee_audit_logs')->where('action','schedule.imported')->latest()->limit(10)->get(),'presence'=>$presence->network()]);
    }
    public function health() {
        $latest=DB::table('promethee_telemetry')->latest('recorded_at')->first();
        return $this->page('health',['latestTelemetry'=>$latest,'audit'=>DB::table('promethee_audit_logs')->latest()->limit(30)->get(),'tables'=>['Télémétrie'=>DB::table('promethee_telemetry')->count(),'Briefings'=>DB::table('promethee_briefings')->count(),'Événements'=>DB::table('promethee_events')->count(),'Saisons'=>DB::table('promethee_seasons')->count()]]);
    }
    
    
    
    
    
    

    
    
    
    
    public function safety(Request $r,BulletinService $service) {
        $month=$this->month($r);
        $saved=DB::table('promethee_bulletins')->where('month',$month)->first();
        $report=$saved ? json_decode($saved->report,true) : $service->build($month);
        if (($report['version'] ?? 0) < SafetyAnalyzer::VERSION) $report=$service->build($month);
        return $this->page('safety',['report'=>$report,'month'=>$month,'saved'=>$saved]);
    }
    public function generate(Request $r,BulletinService $service) {
        $d=$r->validate(['month'=>'required|date_format:Y-m']);
        $service->save($d['month']);
        return redirect()->route('promethee.safety',$d)->with('success','Bulletin enregistré. Prêt à exporter, aucun envoi effectué.');
    }
    public function export(Request $r,BulletinService $service) {
        $month=$this->month($r);
        $saved=DB::table('promethee_bulletins')->where('month',$month)->first();
        $report=$saved ? json_decode($saved->report,true) : $service->build($month);
        return response()->streamDownload(function () use ($report) {
            $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,['Mois','Indicateur','Valeur'],';');
            foreach ($report as $k=>$v) {
                if (is_array($v)) foreach ($v as $key=>$value) fputcsv($out,[$report['month'],$k.'.'.$key,$value],';');
                else fputcsv($out,[$report['month'],$k,$v],';');
            }
            fclose($out);
        },'AIR-INTER-BULLETIN-'.$month.'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }
    public function acars(Request $r) {
        return $this->page('acars',[
            'bids'=>DB::table('bids')->where('user_id',$r->user()->id)->count(),
            'recent'=>Pirep::where('user_id',$r->user()->id)->orderByDesc('created_at')->limit(8)->get(),
        ]);
    }
    public function adminDashboard(Request $r) {
        $monthStart = now()->startOfMonth();
        $activePilots = User::where('state', UserState::ACTIVE)->count();
        $newPilots = User::where('created_at', '>=', $monthStart)->count();
        $pendingPireps = Pirep::where('state', PirepState::PENDING)->count();
        $acceptedPireps = Pirep::where('state', PirepState::ACCEPTED)->where('submitted_at','>=',$monthStart)->count();
        $rejectedPireps = Pirep::where('state', PirepState::REJECTED)->where('submitted_at','>=',$monthStart)->count();
        $simbriefApiConfigured = app(\Modules\Promethee\Services\SimBriefCompanyKeyService::class)->configured();

        $attentionItems = [];
        if ($pendingPireps > 0) {
            $attentionItems[] = [
                'severity' => 'critical',
                'title' => 'PIREPs en attente',
                'description' => 'Des rapports attendent une décision staff.',
                'count' => $pendingPireps,
                'href' => url('/admin/pireps'),
                'action' => 'Traiter les PIREPs',
            ];
        }
        if ($rejectedPireps > 0) {
            $attentionItems[] = [
                'severity' => 'warning',
                'title' => 'PIREPs rejetés ce mois',
                'description' => 'Contrôlez les rejets récents et les éventuelles reprises pilote.',
                'count' => $rejectedPireps,
                'href' => url('/admin/pireps'),
                'action' => 'Voir les rejets',
            ];
        }
        if (!$simbriefApiConfigured) {
            $attentionItems[] = [
                'severity' => 'warning',
                'title' => 'SimBrief compagnie non configuré',
                'description' => 'La génération OFP peut être dégradée tant que la clé compagnie n’est pas disponible.',
                'count' => null,
                'href' => route('admin.promethee.simbrief'),
                'action' => 'Configurer SimBrief',
            ];
        }

        return $this->page('admin.dashboard', [
            'activePilots' => $activePilots,
            'newPilots' => $newPilots,
            'pendingPireps' => $pendingPireps,
            'acceptedPireps' => $acceptedPireps,
            'rejectedPireps' => $rejectedPireps,
            'attentionItems' => $attentionItems,
            'flightMinutes' => (int) Pirep::where('state', PirepState::ACCEPTED)->sum('flight_time'),
            'activeEvents' => DB::table('promethee_events')->where('ends_at','>=',now())->count(),
            'recentProgression' => DB::table('promethee_progression_history')->latest()->limit(8)->get(),
            'activeMissions' => DB::table('promethee_missions')->where('active',true)->count(),
            'activeCircuits' => DB::table('promethee_circuits')->where('active',true)->count(),
            'monthlyAssignments' => DB::table('promethee_assignments')->where('month',now('Europe/Paris')->format('Y-m'))->count(),
            'shopItems' => DB::table('promethee_shop_items')->where('active',true)->count(),
            'regionalBases' => DB::table('promethee_operational_bases')->where('active',true)->count(),
            'jumpseatCount' => DB::table('promethee_transfer_requests')->where('type','jumpseat')->count(),
            'simbriefApiConfigured' => $simbriefApiConfigured,
        ]);
    }
    // Automation/progression administration moved to AutomationController.
    public function adminSimbrief()
    {
        $companyKey = app(\Modules\Promethee\Services\SimBriefCompanyKeyService::class);

        return $this->page('admin.simbrief', [
            'apiConfigured' => $companyKey->configured(),
            'aircraftCount' => \App\Models\SimBriefAircraft::count(),
            'airframeCount' => \App\Models\SimBriefAirframe::count(),
            'layoutCount' => \App\Models\SimBriefLayout::count(),
            'lastSync' => DB::table('promethee_settings')->where('key', 'simbrief.support_synced_at')->value('value'),
        ]);
    }

    public function saveSimbriefSettings(Request $r)
    {
        $data = $r->validate([
            'api_key' => ['nullable', 'string', 'max:255'],
            'clear_api_key' => ['nullable', 'boolean'],
        ]);

        $clear = $r->boolean('clear_api_key');
        $incoming = trim((string) ($data['api_key'] ?? ''));
        $current = (string) (DB::table('settings')
            ->where('key', 'simbrief.api_key')
            ->value('value') ?? '');

        if (!$clear && $incoming === '') {
            if ($current === '') {
                return back()->withErrors([
                    'simbrief' => 'Saisissez la clé API compagnie SimBrief avant d’enregistrer.',
                ]);
            }

            return back()->with('success', 'Clé API SimBrief inchangée.');
        }

        $value = $clear ? '' : $incoming;
        $now = now();
        $existing = DB::table('settings')->where('key', 'simbrief.api_key')->first();

        if ($existing) {
            DB::table('settings')->where('key', 'simbrief.api_key')->update([
                'name' => 'SimBrief Company API Key',
                'group' => 'simbrief',
                'type' => 'secret',
                'description' => 'Company SimBrief API key used server-side by Prométhée. It is never sent to Hermès.',
                'value' => $value,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('settings')->insert([
                'id' => 'simbrief_api_key',
                'offset' => 0,
                'order' => 99,
                'key' => 'simbrief.api_key',
                'name' => 'SimBrief Company API Key',
                'value' => $value,
                'default' => null,
                'group' => 'simbrief',
                'type' => 'secret',
                'options' => '',
                'description' => 'Company SimBrief API key used server-side by Prométhée. It is never sent to Hermès.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $cache = config('cache.keys.SETTINGS');
        if (is_array($cache) && isset($cache['key'])) {
            Cache::forget($cache['key'].'simbrief.api_key');
        }

        return back()->with(
            'success',
            $clear
                ? 'Clé API SimBrief supprimée. Le mode API compagnie est désormais désactivé.'
                : 'Clé API SimBrief enregistrée. Le mode API compagnie est disponible pour Hermès.'
        );
    }

    public function syncSimbrief(\App\Services\SimBriefService $simbrief)
    {
        $airframesOk = $simbrief->getAircraftAndAirframes();
        $layoutsOk = $simbrief->GetBriefingLayouts();

        if (!$airframesOk || !$layoutsOk) {
            return back()->withErrors(['simbrief' => 'La synchronisation SimBrief est incomplète. Consultez les logs serveur avant de réessayer.']);
        }

        DB::table('promethee_settings')->updateOrInsert(
            ['key' => 'simbrief.support_synced_at'],
            ['value' => now('UTC')->toIso8601String(), 'created_at' => now(), 'updated_at' => now()]
        );

        return back()->with('success', 'Avions, airframes et layouts SimBrief synchronisés.');
    }

    public function branding(BrandingService $branding) {
        return $this->page('admin.branding', ['logos' => $branding->logos(), 'branding' => $branding->active()]);
    }
    public function saveBranding(Request $r, BrandingService $branding) {
        $data = $r->validate(['logo' => 'required|string|in:'.implode(',', array_keys($branding->logos()))]);
        $branding->save($data['logo']);
        return redirect()->route('admin.promethee.branding')->with('success', 'Le logo Air Inter a été mis à jour.');
    }
    public function importBranding(Request $r, BrandingService $branding) {
        $data = $r->validate(['logo_file' => 'required|file|mimes:png,jpg,jpeg,webp|max:5120']);
        $directory = public_path('promethee-assets/logos/custom');
        Filesystem::ensureDirectoryExists($directory);
        $file = $data['logo_file'];
        $filename = 'logo-'.bin2hex(random_bytes(12)).'.'.$file->extension();
        $file->move($directory, $filename);
        $branding->saveCustom($filename);
        return redirect()->route('admin.promethee.branding')->with('success', 'Le logo importé est maintenant actif.');
    }
    public function emergencyPireps(Request $r)
    {
        $filters = $r->validate([
            'q' => 'nullable|string|max:120',
            'state' => 'nullable|in:all,pending,accepted,rejected,in_progress,cancelled',
        ]);

        $term = trim((string) ($filters['q'] ?? ''));
        $state = $filters['state'] ?? 'all';
        $stateMap = [
            'pending' => PirepState::PENDING,
            'accepted' => PirepState::ACCEPTED,
            'rejected' => PirepState::REJECTED,
            'in_progress' => PirepState::IN_PROGRESS,
            'cancelled' => PirepState::CANCELLED,
        ];

        $pireps = Pirep::query()
            ->with(['user:id,name,pilot_id', 'flight:id,route_code,flight_number,dpt_airport_id,arr_airport_id', 'aircraft:id,registration'])
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($nested) use ($term) {
                    $nested->where('id', 'like', '%'.$term.'%')
                        ->orWhere('flight_number', 'like', '%'.strtoupper($term).'%')
                        ->orWhere('route_code', 'like', '%'.strtoupper($term).'%')
                        ->orWhere('dpt_airport_id', 'like', '%'.strtoupper($term).'%')
                        ->orWhere('arr_airport_id', 'like', '%'.strtoupper($term).'%')
                        ->orWhereHas('user', fn ($users) => $users
                            ->where('name', 'like', '%'.$term.'%')
                            ->orWhere('pilot_id', 'like', '%'.$term.'%'));
                });
            })
            ->when($state !== 'all' && isset($stateMap[$state]), fn ($query) => $query->where('state', $stateMap[$state]))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return $this->page('admin-pireps-emergency', compact('pireps', 'filters'));
    }

    public function emergencyDeletePirep(string $id, Request $r)
    {
        $data = $r->validate([
            'confirmation' => 'required|string',
        ]);
        abort_unless($data['confirmation'] === 'SUPPRIMER', 422, 'Saisissez SUPPRIMER pour confirmer la remise à zéro.');

        $pirep = Pirep::with(['user', 'aircraft', 'flight'])->findOrFail($id);
        $snapshot = [
            'id' => $pirep->id,
            'user_id' => $pirep->user_id,
            'pilot_id' => $pirep->user?->pilot_id,
            'flight_id' => $pirep->flight_id,
            'ident' => $pirep->ident,
            'aircraft_id' => $pirep->aircraft_id,
            'aircraft_registration' => $pirep->aircraft?->registration,
            'state' => (int) $pirep->state,
            'status' => (string) $pirep->status,
            'source_name' => (string) $pirep->source_name,
            'submitted_at' => optional($pirep->submitted_at)->toIso8601String(),
        ];

        DB::transaction(function () use ($pirep, $r, $snapshot) {
            $wasAccepted = (int) $pirep->state === PirepState::ACCEPTED;
            $aircraft = $pirep->aircraft;
            $user = $pirep->user;
            $departure = $pirep->dpt_airport_id;

            // Reverse phpVMS flight-time/count accounting first when an already
            // accepted report must be removed in an emergency.
            $service = app(\App\Services\PirepService::class);
            if ($wasAccepted) {
                $pirep = $service->reject($pirep);
            }

            // Prométhée-owned operational data is not known by core phpVMS.
            DB::table('promethee_telemetry')->where('pirep_id', $pirep->id)->delete();
            if (preg_match('/Hermes ACARS \\[(op_[^\\]]+)\\]/', (string) $pirep->source_name, $match)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('promethee_datalink_stores')) {
                    DB::table('promethee_datalink_stores')->where('operation_id', $match[1])->delete();
                }
            }

            $service->delete($pirep);

            // Only move an aircraft/pilot backwards when this PIREP is still
            // their latest operational movement. Never overwrite a newer flight.
            if ($aircraft) {
                $hasNewerAircraftPirep = Pirep::where('aircraft_id', $aircraft->id)
                    ->where('created_at', '>', $pirep->created_at)
                    ->exists();
                if (!$hasNewerAircraftPirep && $departure) {
                    $aircraft->airport_id = $departure;
                    $aircraft->save();
                }
            }

            if ($user) {
                $lastAccepted = Pirep::where('user_id', $user->id)
                    ->where('state', PirepState::ACCEPTED)
                    ->latest('submitted_at')
                    ->first();
                $user->last_pirep_id = $lastAccepted?->id;
                if (!$lastAccepted || (string) $user->curr_airport_id === (string) ($snapshot['flight_id'] ? $pirep->arr_airport_id : $user->curr_airport_id)) {
                    $user->curr_airport_id = $lastAccepted?->arr_airport_id ?: $user->home_airport_id;
                }
                $user->save();
            }

            DB::table('promethee_audit_logs')->insert([
                'actor_id' => $r->user()->id,
                'action' => 'pirep.emergency_delete',
                'subject_type' => 'pirep',
                'subject_id' => $snapshot['id'],
                'context' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('admin.promethee.pireps-emergency')
            ->with('success', 'PIREP '.$snapshot['id'].' supprimé en urgence. Le pilote peut recréer son opération.');
    }

    
    
    
    
    
    public function adminAssignments(Request $r) {
        $month=$r->query('month',now('Europe/Paris')->format('Y-m'));
        abort_unless((bool) preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $month), 422, 'Mois invalide.');
        $filters=$r->validate(['q'=>'nullable|string|max:80','pilot'=>'nullable|integer|exists:users,id','route'=>'nullable|string|max:16']);
        $assignments=DB::table('promethee_assignments as assignment')->join('users','users.id','=','assignment.user_id')->join('flights','flights.id','=','assignment.flight_id')->where('assignment.month',$month)
            ->when($filters['pilot'] ?? null, fn($query,$pilot) => $query->where('assignment.user_id',$pilot))
            ->when($filters['route'] ?? null, fn($query,$route) => $query->where('flights.route_code',$route))
            ->when($filters['q'] ?? null, fn($query,$q) => $query->where(fn($nested) => $nested->where('users.name','like','%'.$q.'%')->orWhere('users.pilot_id','like','%'.$q.'%')->orWhere('flights.flight_number','like','%'.$q.'%')->orWhere('flights.route_code','like','%'.$q.'%')->orWhere('flights.dpt_airport_id','like','%'.$q.'%')->orWhere('flights.arr_airport_id','like','%'.$q.'%')))
            ->select('assignment.*','users.name as user_name','users.pilot_id','flights.route_code','flights.flight_number','flights.dpt_airport_id','flights.arr_airport_id')->orderBy('users.pilot_id')->get();
        return $this->page('admin-assignments',['month'=>$month,'assignments'=>$assignments,'pilots'=>User::where('state',UserState::ACTIVE)->orderBy('pilot_id')->get(['id','pilot_id','name']),'flights'=>Flight::where('active',true)->where('visible',true)->orderBy('dpt_airport_id')->get(['id','route_code','flight_number','dpt_airport_id','arr_airport_id'])]);
    }
    /** Prométhée-native read surface over phpVMS ranks. Mutations stay on core routes. */
    public function adminRanks(Request $r) {
        $term = trim((string) $r->query('q'));
        $ranks = Rank::query()->withCount(['users','subfleets'])
            ->when($term !== '', fn ($query) => $query->where('name','like','%'.$term.'%'))
            ->orderBy('hours')->get();
        return $this->page('admin-ranks', compact('ranks'));
    }

    /** Prométhée-native read surface over phpVMS users. Mutations stay on core routes. */
    public function adminUsers(Request $r) {
        $filters = $r->validate(['q'=>'nullable|string|max:100','rank'=>'nullable|integer|exists:ranks,id','airline'=>'nullable|integer|exists:airlines,id']);
        $users = User::query()->with(['rank','airline','home_airport'])
            ->when($filters['q'] ?? null, fn ($query,$q) => $query->where(fn ($nested) => $nested->where('name','like','%'.$q.'%')->orWhere('pilot_id','like','%'.$q.'%')->orWhere('email','like','%'.$q.'%')))
            ->when($filters['rank'] ?? null, fn ($query,$id) => $query->where('rank_id',$id))
            ->when($filters['airline'] ?? null, fn ($query,$id) => $query->where('airline_id',$id))
            ->orderBy('pilot_id')->paginate(40)->withQueryString();
        return $this->page('admin-users', [
            'users'=>$users,
            'ranks'=>Rank::orderBy('hours')->get(['id','name']),
            'airlines'=>Airline::orderBy('name')->get(['id','name','icao']),
        ]);
    }

    public function createAdminRank() {
        return $this->page('admin-rank-create', [
            'subfleets' => Subfleet::with('airline')->orderBy('name')->get(),
        ]);
    }

    public function storeAdminRank(Request $r) {
        $data=$r->validate([
            'name'=>'required|string|max:191',
            'hours'=>'required|integer|min:0',
            'acars_base_pay_rate'=>'nullable|numeric|min:0',
            'manual_base_pay_rate'=>'nullable|numeric|min:0',
            'auto_promote'=>'nullable|boolean',
            'auto_approve_acars'=>'nullable|boolean',
            'auto_approve_manual'=>'nullable|boolean',
            'subfleet_ids'=>'nullable|array',
            'subfleet_ids.*'=>'string|exists:subfleets,id',
        ]);
        $rank = Rank::create(collect($data)->except('subfleet_ids')->merge([
            'auto_promote'=>$r->boolean('auto_promote'),
            'auto_approve_acars'=>$r->boolean('auto_approve_acars'),
            'auto_approve_manual'=>$r->boolean('auto_approve_manual'),
        ])->all());
        $rank->subfleets()->sync($data['subfleet_ids'] ?? []);
        Cache::forget(config('cache.keys.RANKS_PILOT_LIST.key'));
        return redirect()->route('admin.promethee.ranks.edit',$rank)->with('success','Grade créé dans Prométhée.');
    }

    public function editAdminRank(Rank $rank) {
        $rank->load('subfleets');
        return $this->page('admin-rank-edit', ['rank'=>$rank,'subfleets'=>Subfleet::with('airline')->orderBy('name')->get()]);
    }
    public function updateAdminRank(Rank $rank, Request $r) {
        $data=$r->validate(['name'=>'required|string|max:191','hours'=>'required|integer|min:0','acars_base_pay_rate'=>'nullable|numeric|min:0','manual_base_pay_rate'=>'nullable|numeric|min:0','auto_promote'=>'nullable|boolean','auto_approve_acars'=>'nullable|boolean','auto_approve_manual'=>'nullable|boolean','subfleet_ids'=>'nullable|array','subfleet_ids.*'=>'string|exists:subfleets,id']);
        $rank->update(collect($data)->except('subfleet_ids')->merge(['auto_promote'=>$r->boolean('auto_promote'),'auto_approve_acars'=>$r->boolean('auto_approve_acars'),'auto_approve_manual'=>$r->boolean('auto_approve_manual')])->all());
        $rank->subfleets()->sync($data['subfleet_ids'] ?? []);
        Cache::forget(config('cache.keys.RANKS_PILOT_LIST.key'));
        return redirect()->route('admin.promethee.ranks')->with('success','Grade mis à jour dans phpVMS.');
    }
    public function editAdminUser(User $user) {
        $user->load(['rank','airline','home_airport']);
        return $this->page('admin-user-edit',['pilot'=>$user,'ranks'=>Rank::orderBy('hours')->get(),'airlines'=>Airline::orderBy('name')->get(),'airports'=>Airport::orderBy('name')->get(['id','name','icao','iata'])]);
    }
    public function updateAdminUser(User $user, Request $r) {
        $data=$r->validate(['name'=>'required|string|max:191','email'=>'required|email|max:191|unique:users,email,'.$user->id,'pilot_id'=>'required|integer|unique:users,pilot_id,'.$user->id,'callsign'=>'nullable|string|max:4','airline_id'=>'required|integer|exists:airlines,id','rank_id'=>'nullable|integer|exists:ranks,id','home_airport_id'=>'nullable|string|exists:airports,id']);
        $oldRank=$user->rank_id; $user->update($data);
        if ((string)$oldRank !== (string)$user->rank_id) event(new \App\Events\UserStatsChanged($user,'rank',$user->rank_id));
        return redirect()->route('admin.promethee.users')->with('success','Pilote mis à jour dans phpVMS.');
    }

    /** Prométhée-native airline catalogue; legacy phpVMS URLs remain valid. */
    public function adminAirlines(Request $r) {
        $filters=$r->validate(['q'=>'nullable|string|max:80','active'=>'nullable|in:all,active,inactive']);
        $airlines=Airline::query()->when($filters['q'] ?? null, fn($query,$q)=>$query->where(fn($nested)=>$nested->where('name','like','%'.$q.'%')->orWhere('icao','like','%'.$q.'%')->orWhere('iata','like','%'.$q.'%')->orWhere('callsign','like','%'.$q.'%')))->when(($filters['active'] ?? 'all') !== 'all', fn($query)=>$query->where('active',($filters['active'] ?? '')==='active'))->orderBy('name')->get();
        $accessRules = DB::table('promethee_airline_access_rules')->pluck('min_flight_hours', 'airline_id');
        $airlines->each(fn (Airline $airline) => $airline->setAttribute('min_flight_hours', (int) ($accessRules[$airline->id] ?? 0)));
        return $this->page('admin-airlines', ['airlines'=>$airlines,'countries'=>Countries::getSelectList()]);
    }
    public function editAdminAirline(Airline $airline) {
        $rule = DB::table('promethee_airline_access_rules')->where('airline_id', $airline->id)->first();
        $airline->setAttribute('min_flight_hours', (int) ($rule->min_flight_hours ?? 0));
        return $this->page('admin-airline-edit', ['airline'=>$airline,'countries'=>Countries::getSelectList()]);
    }
    public function updateAdminAirline(Airline $airline, Request $r) {
        $r->merge(['id'=>$airline->id]);
        return $this->saveAdminAirline($r);
    }
    public function deleteAdminAirlineLogo(Airline $airline) {
        $airline->update(['logo'=>null]);
        return redirect()->route('admin.promethee.airlines.edit',$airline)->with('success','Logo de la compagnie supprimé.');
    }
    public function saveAdminAirline(Request $r) {
        $data=$r->validate(['id'=>'nullable|integer|exists:airlines,id','icao'=>'required|string|max:5','iata'=>'nullable|string|max:5','name'=>'required|string|max:191','callsign'=>'nullable|string|max:191','logo'=>'nullable|string|max:2000','logo_upload'=>'nullable|image|mimes:png,jpg,jpeg,webp|max:2048','country'=>'nullable|string|size:2','active'=>'nullable|boolean','min_flight_hours'=>'nullable|integer|min:0|max:100000']);
        $attributes=collect($data)->except(['id','min_flight_hours','logo_upload'])->all();
        if ($r->hasFile('logo_upload')) {
            $filename = strtolower(preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $data['icao'])).'-'.now()->format('YmdHis').'.'.$r->file('logo_upload')->extension();
            $attributes['logo'] = $r->file('logo_upload')->storeAs('airline-logos', $filename, config('filesystems.public_files'));
        }
        $attributes['active']=$r->boolean('active');
        if (!empty($data['id'])) {
            $airline = Airline::findOrFail($data['id']); $airline->update($attributes);
        } else {
            $airline = Airline::create($attributes);
        }
        DB::table('promethee_airline_access_rules')->updateOrInsert(
            ['airline_id'=>$airline->id],
            ['min_flight_hours'=>(int)($data['min_flight_hours'] ?? 0),'created_at'=>now(),'updated_at'=>now()]
        );
        return back()->with('success','Compagnie et seuil d’accès enregistrés.');
    }

    

    

    

    

    

    

    
    

    

    
    public function adminPassport() { return $this->page('admin-passport',['enabled'=>DB::table('promethee_settings')->where('key','passport.enabled')->value('value') !== '0','showMap'=>DB::table('promethee_settings')->where('key','passport.map_enabled')->value('value') !== '0']); }
    public function savePassportSettings(Request $r) { $r->validate(['enabled'=>'nullable|boolean','map_enabled'=>'nullable|boolean']); foreach(['passport.enabled'=>$r->boolean('enabled'),'passport.map_enabled'=>$r->boolean('map_enabled')] as $key=>$value) DB::table('promethee_settings')->updateOrInsert(['key'=>$key],['value'=>$value?'1':'0','created_at'=>now(),'updated_at'=>now()]); return back()->with('success','Paramètres du passeport enregistrés.'); }
    public function saveAssignment(Request $r) { $data=$r->validate(['user_id'=>'required|integer|exists:users,id','flight_id'=>'required|string|exists:flights,id','month'=>'required|date_format:Y-m','notes'=>'nullable|string|max:2000']); DB::table('promethee_assignments')->updateOrInsert(['user_id'=>$data['user_id'],'flight_id'=>$data['flight_id'],'month'=>$data['month']],['notes'=>$data['notes']??null,'assigned_by'=>$r->user()->id,'updated_at'=>now(),'created_at'=>now()]); return back()->with('success','Affectation enregistrée.'); }
    public function deleteAssignment(int $id) { DB::table('promethee_assignments')->where('id',$id)->delete(); return back()->with('success','Affectation supprimée.'); }
    public function deleteAssignments(Request $r) { $data=$r->validate(['ids'=>'required|array|min:1|max:500','ids.*'=>'integer|exists:promethee_assignments,id']); DB::table('promethee_assignments')->whereIn('id',$data['ids'])->delete(); return back()->with('success',count($data['ids']).' affectation(s) supprimée(s).'); }
    public function adminShop() { return $this->page('admin-shop',['items'=>DB::table('promethee_shop_items')->latest()->get(),'pilots'=>User::where('state',UserState::ACTIVE)->orderBy('pilot_id')->get(['id','pilot_id','name']),'wallets'=>DB::table('promethee_wallets as wallet')->join('users','users.id','=','wallet.user_id')->select('wallet.*','users.pilot_id','users.name')->orderByDesc('wallet.balance')->get()]); }
    public function saveShopItem(Request $r) { $data=$r->validate(['name'=>'required|string|max:160','description'=>'nullable|string|max:5000','price'=>'required|numeric|min:0|max:1000000','active'=>'nullable|boolean']); DB::table('promethee_shop_items')->insert(['name'=>$data['name'],'description'=>$data['description'] ?? null,'price'=>Money::convertToSubunit($data['price']),'active'=>$r->boolean('active'),'created_at'=>now(),'updated_at'=>now()]); return back()->with('success','Article ajouté au catalogue.'); }
    public function creditWallet(Request $r, FinanceService $finance) { $data=$r->validate(['user_id'=>'required|integer|exists:users,id','amount'=>'required|numeric|between:-100000,100000']); $user=User::with('journal')->findOrFail($data['user_id']); $amount=new Money(Money::convertToSubunit(abs($data['amount']))); if($data['amount']>=0)$finance->creditToJournal($user->journal,$amount,$user,'Crédit boutique Prométhée','shop','shop'); else { if((int)$user->journal->getBalance()->getAmount()<(int)$amount->getAmount()) return back()->withErrors(['amount'=>'Solde phpVMS insuffisant pour ce débit.']); $finance->debitFromJournal($user->journal,$amount,$user,'Débit boutique Prométhée','shop','shop'); } return back()->with('success','Journal phpVMS du pilote mis à jour.'); }
    public function adminTransfers() { return $this->page('admin-transfers',['requests'=>DB::table('promethee_transfer_requests as request')->join('users','users.id','=','request.user_id')->leftJoin('airlines','airlines.id','=','request.target_airline_id')->leftJoin('airports','airports.id','=','request.target_airport_id')->select('request.*','users.name as user_name','users.pilot_id','airlines.name as airline_name','airports.name as airport_name')->latest('request.created_at')->get()]); }
    public function adminJumpseats() { return $this->page('admin-jumpseats',['requests'=>DB::table('promethee_transfer_requests as request')->join('users','users.id','=','request.user_id')->leftJoin('airports','airports.id','=','request.target_airport_id')->where('request.type','jumpseat')->select('request.*','users.name as user_name','users.pilot_id','airports.icao','airports.name as airport_name')->latest('request.created_at')->get(),'basePrice'=>(float) (DB::table('promethee_settings')->where('key','jumpseat.base_price')->value('value') ?: 0.13)]); }
    public function saveJumpseatSettings(Request $r) { $data=$r->validate(['base_price'=>'required|numeric|min:0|max:10000']); DB::table('promethee_settings')->updateOrInsert(['key'=>'jumpseat.base_price'],['value'=>$data['base_price'],'updated_at'=>now(),'created_at'=>now()]); return back()->with('success','Tarif de base du jumpseat enregistré.'); }
    public function decideTransfer(int $id, Request $r) { $data=$r->validate(['status'=>'required|in:approved,rejected','decision_note'=>'nullable|string|max:2000']); $request=DB::table('promethee_transfer_requests')->where('id',$id)->where('status','pending')->first(); abort_unless($request,404); DB::transaction(function() use($request,$data,$r){ if($data['status']==='approved'){ if($request->type==='airline') User::where('id',$request->user_id)->update(['airline_id'=>$request->target_airline_id]); elseif($request->type==='hub') User::where('id',$request->user_id)->update(['home_airport_id'=>$request->target_airport_id]); } DB::table('promethee_transfer_requests')->where('id',$request->id)->update(['status'=>$data['status'],'decision_note'=>$data['decision_note']??null,'decided_by'=>$r->user()->id,'decided_at'=>now(),'updated_at'=>now()]); }); return back()->with('success',$request->type==='jumpseat' ? 'Décision de jumpseat enregistrée.' : 'Décision de transfert enregistrée.'); }
}
