<?php
namespace Modules\Promethee\Http;
use App\Contracts\Controller;
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

class PortalController extends Controller
{
    private const MAX_ITINERARY_STOPS = 3;
    private const MAX_ITINERARY_LEGS = 4;
    private const MAX_ITINERARY_RESULTS = 3;
    private const MAX_ITINERARY_EXPANSIONS = 12000;
    private function page(string $name, array $data=[]) {
        return view('promethee::'.$name, $data + ['branding' => app(BrandingService::class)->active()]);
    }
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
    public function publicPireps(Request $r) {
        return $this->pirepsIndex($r);
    }

    public function myPireps(Request $r) {
        return $this->pirepsIndex($r, true);
    }

    private function pirepsIndex(Request $r, bool $mine = false) {
        $filters = $r->validate([
            'pilot' => 'nullable|string|max:80',
            'flight' => 'nullable|string|max:32',
            'departure' => 'nullable|string|max:8',
            'arrival' => 'nullable|string|max:8',
            'aircraft' => 'nullable|string|max:32',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $pireps = Pirep::with(['user:id,name,pilot_id','aircraft','airline'])
            ->where('state', PirepState::ACCEPTED)
            ->when($mine, fn ($q) => $q->where('user_id', $r->user()->id))
            ->when($r->filled('pilot'), fn ($q) => $q->whereHas('user', fn ($users) => $users->where('name', 'like', '%'.$filters['pilot'].'%')->orWhere('pilot_id', 'like', '%'.$filters['pilot'].'%')))
            ->when($r->filled('flight'), fn ($q) => $q->where(fn ($reports) => $reports->where('flight_number', 'like', '%'.strtoupper($filters['flight']).'%')->orWhere('route_code', 'like', '%'.strtoupper($filters['flight']).'%')))
            ->when($r->filled('departure'), fn ($q) => $q->where('dpt_airport_id', strtoupper($filters['departure'])))
            ->when($r->filled('arrival'), fn ($q) => $q->where('arr_airport_id', strtoupper($filters['arrival'])))
            ->when($r->filled('aircraft'), fn ($q) => $q->whereHas('aircraft', fn ($aircraft) => $aircraft->where('registration', 'like', '%'.strtoupper($filters['aircraft']).'%')->orWhere('icao', 'like', '%'.strtoupper($filters['aircraft']).'%')))
            ->when($r->filled('from'), fn ($q) => $q->whereDate('submitted_at', '>=', $filters['from']))
            ->when($r->filled('to'), fn ($q) => $q->whereDate('submitted_at', '<=', $filters['to']))
            ->latest('submitted_at')
            ->paginate(30)
            ->withQueryString();

        return $this->page('public-pireps', compact('pireps', 'mine'));
    }
    /** The branded, public replacement for /legacy/pireps/{id}. */
    public function pirep(string $id, Request $r) {
        $pirep = Pirep::with([
            'acars', 'acars_logs', 'acars_route', 'aircraft.airline', 'airline.journal',
            'arr_airport', 'dpt_airport', 'alt_airport', 'fares', 'field_values',
            'flight', 'simbrief', 'user.rank', 'user.journal', 'comments.user',
        ])->findOrFail($id);

        $companyScore=['available'=>false,'score'=>$pirep->score,'starting_score'=>100,'penalty_total'=>$pirep->score===null?0:max(0,100-(int)$pirep->score),'items'=>[],'unavailable_rules'=>[]];
        if (str_starts_with((string)$pirep->source_name,'Hermes ACARS [op_')) {
            try { $companyScore=app(LegacyPirepScoringService::class)->forPirep($pirep); }
            catch (\Throwable $e) { logger()->warning('promethee_pirep_score_display_failed',['pirep_id'=>$pirep->id,'error'=>$e->getMessage()]); }
        }
        $farePassengers=(int)$pirep->fares->filter(fn($fare)=>(int)$fare->type===FareType::PASSENGER)->sum('count');
        $simbriefPassengers=null;
        if ($pirep->simbrief?->xml) foreach ([(string)($pirep->simbrief->xml->weights->pax_count??''),(string)($pirep->simbrief->xml->general->passengers??'')] as $candidate) {
            if ($candidate!==''&&is_numeric($candidate)) { $simbriefPassengers=max(0,(int)round((float)$candidate)); break; }
        }

        $finance = $this->pirepFinance($pirep);

        return $this->page('pirep', [
            'pirep' => $pirep,
            'flightJournal' => $this->pirepJournal($pirep, $companyScore),
            'companyScore' => $companyScore,
            'finance' => $finance,
            'passengerCount' => $farePassengers > 0 ? $farePassengers : $simbriefPassengers,
            'passengerSource' => $farePassengers > 0 ? 'PIREP' : ($simbriefPassengers !== null ? 'OFP SimBrief' : null),
            'canDeletePirep' => app(PilotPirepDeletionService::class)->canDelete($pirep, $r->user()),
        ]);
    }

    /**
     * Keep the historical controller seam while delegating the increasingly
     * rich journal reconstruction to its dedicated service.
     */
    private function pirepJournal(Pirep $pirep, array $companyScore = [])
    {
        return app(PirepJournalService::class)->build($pirep, $companyScore);
    }

    private function pirepFinance(Pirep $pirep): array
    {
        $companyTransactions = $pirep->airline?->journal
            ? $pirep->airline->journal
                ->transactionsReferencingObjectQuery($pirep)
                ->orderBy('post_date')
                ->get()
            : collect();

        $pilotTransactions = $pirep->user?->journal
            ? $pirep->user->journal
                ->transactionsReferencingObjectQuery($pirep)
                ->orderBy('post_date')
                ->get()
            : collect();

        $credits = (int) $companyTransactions->sum('credit');
        $debits = (int) $companyTransactions->sum('debit');
        $pilotNet = (int) $pilotTransactions->sum('credit') - (int) $pilotTransactions->sum('debit');

        return [
            'company_transactions' => $companyTransactions,
            'pilot_transactions' => $pilotTransactions,
            'credits' => new Money($credits),
            'debits' => new Money($debits),
            'net' => new Money($credits - $debits),
            'pilot_net' => new Money($pilotNet),
        ];
    }

    /**
     * Start a fresh operation from a genuinely finished PIREP.
     *
     * Repeating a flight must never reuse the old Bid/operation id: Hermès
     * deliberately scopes duplicate detection and PIREP correlation to that
     * immutable id. Reusing it would make the new attempt inherit the terminal
     * state of the previous flight.
     */
    public function repeatPirep(string $id, Request $r, \App\Services\BidService $bids) {
        $pirep = Pirep::with('flight')
            ->where('user_id', $r->user()->id)
            ->findOrFail($id);

        if (!$pirep->flight) {
            return back()->withErrors(['reservation' => 'Ce rapport n’est plus rattaché à un vol réservable.']);
        }

        $isFinal = $pirep->submitted_at !== null
            || in_array((int) $pirep->state, [PirepState::PENDING, PirepState::ACCEPTED, PirepState::REJECTED], true);
        if (!$isFinal) {
            return back()->withErrors(['reservation' => 'Finalisez d’abord le Flight Review et le dépôt du PIREP avant de refaire ce vol.']);
        }

        $flight = $pirep->flight;
        abort_unless($flight->active && $flight->visible, 409, 'Ce vol n’est plus réservable dans le programme actuel.');
        abort_unless(app(CompanyAccessService::class)->canAccessAirline($r->user(), (int) $flight->airline_id), 403, 'Cette compagnie n’est pas encore accessible avec votre nombre d’heures de vol.');

        $existing = $this->releaseTerminalReservationsForFlight($flight, $r->user());
        if ($existing) {
            return redirect()->route('promethee.flights.show', $flight->id)
                ->with('success', 'Une opération active existe déjà pour ce vol ('.$existing->operation_id.').');
        }

        try {
            $newBid = $bids->addBid($flight, $r->user());
            $operationId = app(\Modules\Promethee\Services\OperationIdentityService::class)->id($newBid);

            return redirect()->route('promethee.flights.briefing', $flight->id)
                ->with('success', 'Nouvelle opération '.$operationId.' créée pour '.$flight->ident.'. Le précédent PIREP reste archivé.');
        } catch (\Throwable $e) {
            return back()->withErrors(['reservation' => $e->getMessage() ?: 'Impossible de créer une nouvelle opération pour ce vol.']);
        }
    }
    public function publicLive() { return view('promethee::public-live'); }

    /** Native company pages replacing the disabled Disposable module. */
    public function airlines(Request $r) {
        $airlines = Airline::query()->withCount(['aircraft', 'subfleets', 'users', 'flights'])->where('active', 1)
            ->when($r->filled('q'), function ($query) use ($r) {
                $term = trim((string) $r->query('q'));
                $query->where(fn ($airlines) => $airlines->where('name', 'like', "%{$term}%")->orWhere('icao', 'like', "%{$term}%")->orWhere('iata', 'like', "%{$term}%"));
            })->orderBy('name')->get();
        $accessCounts = [];
        $userService = app(UserService::class);
        User::where('state', UserState::ACTIVE)->get()->each(function (User $pilot) use (&$accessCounts, $userService) {
            try {
                $airlineIds = $userService->getAllowableSubfleets($pilot)->pluck('airline_id')->filter()->unique();
            } catch (\Throwable) {
                $airlineIds = collect([$pilot->airline_id])->filter();
            }
            foreach ($airlineIds as $airlineId) $accessCounts[(int) $airlineId] = ($accessCounts[(int) $airlineId] ?? 0) + 1;
        });
        $accessRules = DB::table('promethee_airline_access_rules')->pluck('min_flight_hours', 'airline_id');
        $companyAccess = app(CompanyAccessService::class);
        $pilotHours = $r->user() ? $companyAccess->flightHours($r->user()) : null;
        $airlines->each(function (Airline $airline) use ($accessCounts, $accessRules, $r, $companyAccess) {
            $airline->setAttribute('promethee_logo', $this->airlineLogoUrl($airline));
            $airline->setAttribute('eligible_users_count', $accessCounts[(int) $airline->id] ?? $airline->users_count);
            $airline->setAttribute('min_flight_hours', (int) ($accessRules[$airline->id] ?? 0));
            $airline->setAttribute('pilot_has_access', $r->user() ? $companyAccess->canAccessAirline($r->user(), (int) $airline->id) : null);
            $airline->setAttribute('remaining_hours', $r->user() ? $companyAccess->remainingHours($r->user(), (int) $airline->id) : null);
        });
        return $this->page('airlines', compact('airlines', 'pilotHours'));
    }

    public function finances(Request $r) {
        $filters = $r->validate([
            'period' => 'nullable|in:month,6months,year',
            'airline' => 'nullable|integer',
        ]);
        $period = $filters['period'] ?? 'year';
        $selectedAirlineId = isset($filters['airline']) ? (int) $filters['airline'] : null;
        $parisNow = now('Europe/Paris');

        $periodDefinitions = [
            'month' => [
                'label' => 'Mois',
                'start' => $parisNow->copy()->startOfMonth(),
            ],
            '6months' => [
                'label' => '6 mois',
                'start' => $parisNow->copy()->subMonths(5)->startOfMonth(),
            ],
            'year' => [
                'label' => 'Année',
                'start' => $parisNow->copy()->startOfYear(),
            ],
        ];

        $chartStart = $parisNow->copy()->subMonths(11)->startOfMonth();
        $queryStart = collect($periodDefinitions)->pluck('start')->push($chartStart)->sort()->first()->copy()->utc();

        $airlines = Airline::where('active', 1)
            ->with('journal')
            ->when($selectedAirlineId, fn ($query) => $query->where('id', $selectedAirlineId))
            ->orderBy('name')
            ->get();

        $financeRows = $airlines->map(function (Airline $airline) use ($queryStart, $periodDefinitions, $chartStart, $parisNow, $period) {
            $journal = $airline->journal;
            $transactions = $journal
                ? $journal->transactions()->where('post_date', '>=', $queryStart)->orderBy('post_date')->get()
                : collect();

            $periods = [];
            foreach ($periodDefinitions as $key => $definition) {
                $startUtc = $definition['start']->copy()->utc();
                $slice = $transactions->filter(fn ($transaction) => \Carbon\Carbon::parse($transaction->post_date)->gte($startUtc));
                $credits = (int) $slice->sum('credit');
                $debits = (int) $slice->sum('debit');
                $net = $credits - $debits;
                $margin = $credits > 0 ? round(($net / $credits) * 100, 1) : 0.0;

                $durationSeconds = max(1, $parisNow->copy()->utc()->diffInSeconds($startUtc));
                $previousStart = $startUtc->copy()->subSeconds($durationSeconds);
                $previous = $journal
                    ? $journal->transactions()
                        ->where('post_date', '>=', $previousStart)
                        ->where('post_date', '<', $startUtc)
                        ->get()
                    : collect();
                $previousCredits = (int) $previous->sum('credit');
                $previousNet = (int) $previous->sum('credit') - (int) $previous->sum('debit');

                $periods[$key] = [
                    'label' => $definition['label'],
                    'credits_raw' => $credits,
                    'debits_raw' => $debits,
                    'net_raw' => $net,
                    'credits' => new Money($credits),
                    'debits' => new Money($debits),
                    'net' => new Money($net),
                    'margin' => $margin,
                    'transactions' => $slice->count(),
                    'credit_change' => $previousCredits > 0 ? round((($credits - $previousCredits) / $previousCredits) * 100, 1) : null,
                    'net_change' => $previousNet != 0 ? round((($net - $previousNet) / abs($previousNet)) * 100, 1) : null,
                ];
            }

            $monthly = [];
            $cursor = $chartStart->copy();
            $runningBalance = $journal ? (int) $journal->getBalance()->getAmount() : 0;
            while ($cursor->lte($parisNow)) {
                $key = $cursor->format('Y-m');
                $monthly[$key] = [
                    'key' => $key,
                    'label' => $cursor->translatedFormat('M'),
                    'full_label' => $cursor->translatedFormat('M Y'),
                    'credits' => 0,
                    'debits' => 0,
                    'net' => 0,
                    'margin' => 0,
                    'balance' => 0,
                    'transactions' => 0,
                ];
                $cursor->addMonth();
            }

            foreach ($transactions as $transaction) {
                $key = \Carbon\Carbon::parse($transaction->post_date)->timezone('Europe/Paris')->format('Y-m');
                if (!isset($monthly[$key])) continue;
                $credit = (int) $transaction->credit;
                $debit = (int) $transaction->debit;
                $monthly[$key]['credits'] += $credit;
                $monthly[$key]['debits'] += $debit;
                $monthly[$key]['net'] += $credit - $debit;
                $monthly[$key]['transactions']++;
            }

            foreach ($monthly as &$point) {
                $point['margin'] = $point['credits'] > 0 ? round(($point['net'] / $point['credits']) * 100, 1) : 0;
                $point['credits_money'] = (string) new Money($point['credits']);
                $point['debits_money'] = (string) new Money($point['debits']);
                $point['net_money'] = (string) new Money($point['net']);
            }
            unset($point);

            $months = array_values($monthly);
            $selectedStats = $periods[$period];
            $risk = 'faible';
            if ($selectedStats['net_raw'] < 0 || $selectedStats['margin'] < 0) {
                $risk = 'élevé';
            } elseif ($selectedStats['margin'] < 10 || ($selectedStats['credit_change'] !== null && $selectedStats['credit_change'] < -5)) {
                $risk = 'modéré';
            }

            $observations = [];
            if ($selectedStats['credit_change'] !== null) {
                $observations[] = ($selectedStats['credit_change'] >= 0 ? 'Progression' : 'Repli').' des recettes de '.abs($selectedStats['credit_change']).' % par rapport à la période précédente.';
            }
            $observations[] = $selectedStats['margin'] >= 15
                ? 'Marge comptable robuste sur la période analysée.'
                : ($selectedStats['margin'] >= 0 ? 'Marge positive mais à surveiller.' : 'Résultat déficitaire sur la période.');
            $observations[] = $selectedStats['transactions'].' écritures comptables intégrées à l’analyse.';
            if ($selectedStats['debits_raw'] > $selectedStats['credits_raw'] * 0.85 && $selectedStats['credits_raw'] > 0) {
                $observations[] = 'Le niveau de charges absorbe plus de 85 % des recettes.';
            }

            return [
                'airline' => $airline,
                'balance' => $journal ? $journal->getBalance() : new Money(0),
                'balance_raw' => $journal ? (int) $journal->getBalance()->getAmount() : 0,
                'periods' => $periods,
                'selected' => $selectedStats,
                'monthly' => $months,
                'risk' => $risk,
                'observations' => $observations,
            ];
        });

        $consolidated = [
            'credits' => new Money((int) $financeRows->sum(fn ($row) => $row['selected']['credits_raw'])),
            'debits' => new Money((int) $financeRows->sum(fn ($row) => $row['selected']['debits_raw'])),
            'net' => new Money((int) $financeRows->sum(fn ($row) => $row['selected']['net_raw'])),
            'balance' => new Money((int) $financeRows->sum('balance_raw')),
        ];
        $consolidatedCreditsRaw = (int) $financeRows->sum(fn ($row) => $row['selected']['credits_raw']);
        $consolidatedNetRaw = (int) $financeRows->sum(fn ($row) => $row['selected']['net_raw']);
        $consolidated['margin'] = $consolidatedCreditsRaw > 0 ? round(($consolidatedNetRaw / $consolidatedCreditsRaw) * 100, 1) : 0;

        $allAirlines = Airline::where('active', 1)->orderBy('name')->get(['id', 'name', 'icao']);

        return $this->page('finances', compact(
            'financeRows',
            'period',
            'selectedAirlineId',
            'consolidated',
            'allAirlines',
            'parisNow'
        ));
    }

    public function fleet(Request $r) {
        $filters = $r->validate([
            'q' => 'nullable|string|max:32',
            'airline' => 'nullable|integer',
            'sort' => 'nullable|in:registration,icao,subfleet,hub,airport,flight_time,fuel_onboard,landing_time,state,status',
            'direction' => 'nullable|in:asc,desc',
        ]);
        $sort = $filters['sort'] ?? 'registration';
        $direction = $filters['direction'] ?? 'asc';
        $sortColumns = [
            'registration' => 'registration', 'icao' => 'icao', 'hub' => 'hub_id',
            'airport' => 'airport_id', 'flight_time' => 'flight_time',
            'fuel_onboard' => 'fuel_onboard', 'landing_time' => 'landing_time',
            'state' => 'state', 'status' => 'status',
        ];
        $airlines = Airline::where('active', 1)->orderBy('name')->get(['id', 'name', 'icao']);
        // Aircraft belongs to an airline through its subfleet. Loading that
        // chain avoids an ambiguous `id` select produced by belongsToThrough.
        $aircraft = Aircraft::with(['subfleet.airline', 'airport:id,icao'])
            // Keep this directory consistent with flight booking: a signed-in
            // pilot only sees subfleets authorised by their rank (and, if
            // enabled, their type rating). Guests retain the public catalogue.
            ->when($r->user() && !$r->user()->ability('admin', 'admin-access') && (setting('pireps.restrict_aircraft_to_rank', false) || setting('pireps.restrict_aircraft_to_typerating', false)), function ($query) use ($r) {
                $allowedSubfleetIds = app(UserService::class)->getAllowableSubfleets($r->user())->pluck('id');
                $query->whereIn('subfleet_id', $allowedSubfleetIds);
            })
            ->when(!empty($filters['airline']), fn ($query) => $query->whereHas('subfleet', fn ($subfleet) => $subfleet->where('airline_id', $filters['airline'])))
            ->when(!empty($filters['q']), function ($query) use ($filters) {
                $term = $filters['q'];
                $query->where(fn ($fleet) => $fleet->where('registration', 'like', "%{$term}%")->orWhere('icao', 'like', "%{$term}%")->orWhereHas('subfleet', fn ($subfleet) => $subfleet->where('name', 'like', "%{$term}%")));
            })
            ->when($sort === 'subfleet', function ($query) use ($direction) {
                $aircraftTable = (new Aircraft)->getTable();
                $query->leftJoin('subfleets as fleet_sort_subfleets', 'fleet_sort_subfleets.id', '=', $aircraftTable.'.subfleet_id')
                    ->select($aircraftTable.'.*')->orderBy('fleet_sort_subfleets.name', $direction);
            }, fn ($query) => $query->orderBy($sortColumns[$sort], $direction))
            ->paginate(40)->withQueryString();
        $maintenanceByAircraft = DB::table('disposable_maintenance')
            ->whereIn('aircraft_id', $aircraft->getCollection()->pluck('id'))
            ->get()->keyBy('aircraft_id');
        $aircraft->getCollection()->each(function (Aircraft $plane) use ($maintenanceByAircraft) {
            $maintenance = $maintenanceByAircraft->get($plane->id);
            $hours = collect([$maintenance?->rem_ta, $maintenance?->rem_tb, $maintenance?->rem_tc])->filter(fn ($value) => is_numeric($value));
            $cycles = collect([$maintenance?->rem_ca, $maintenance?->rem_cb, $maintenance?->rem_cc])->filter(fn ($value) => is_numeric($value));
            $plane->setAttribute('maintenance_hours_remaining', $hours->isNotEmpty() ? $hours->min() : null);
            $plane->setAttribute('maintenance_cycles_remaining', $cycles->isNotEmpty() ? $cycles->min() : null);
            $plane->setAttribute('state_label', AircraftState::$labels[$plane->state] ?? 'Inconnu');
            $plane->setAttribute('status_label', __(AircraftStatus::$labels[$plane->status] ?? 'aircraft.status.active'));
            $plane->subfleet?->airline?->setAttribute('promethee_logo', $this->airlineLogoUrl($plane->subfleet->airline));
        });
        $baseAssignments = DB::table('promethee_aircraft_bases as assignment')
            ->leftJoin('promethee_operational_bases as base', 'base.airport_id', '=', 'assignment.base_airport_id')
            ->whereIn('assignment.aircraft_id', $aircraft->getCollection()->pluck('id'))
            ->select('assignment.*', 'base.kind as base_kind', 'base.small_maintenance', 'base.heavy_maintenance')
            ->get()->keyBy('aircraft_id');
        $aircraft->getCollection()->each(function (Aircraft $plane) use ($baseAssignments) {
            $assignment = $baseAssignments->get($plane->id);
            $plane->setAttribute('operational_base_id', $assignment?->base_airport_id ?: $plane->hub_id ?: 'LFPO');
            $plane->setAttribute('operational_base_kind', $assignment?->base_kind ?: (($plane->hub_id ?: 'LFPO') === 'LFPO' ? 'hub' : 'regional'));
            $plane->setAttribute('away_since', $assignment?->away_since);
        });
        return $this->page('fleet', compact('aircraft', 'airlines'));
    }

    public function maintenance(Request $r) {
        // Read the existing maintenance data directly: no disabled module or
        // legacy event listener is required for this dashboard.
        $maintenance = DB::table('disposable_maintenance as maintenance')->join('aircraft as aircraft', 'aircraft.id', '=', 'maintenance.aircraft_id')->leftJoin('subfleets as subfleets', 'subfleets.id', '=', 'aircraft.subfleet_id')->leftJoin('airlines as airlines', 'airlines.id', '=', 'subfleets.airline_id')->leftJoin('promethee_operational_bases as ops_base', 'ops_base.airport_id', '=', 'aircraft.airport_id')
            ->select(['maintenance.*', 'aircraft.registration', 'aircraft.icao', 'aircraft.airport_id', 'airlines.name as airline_name', 'airlines.icao as airline_icao', 'ops_base.kind as maintenance_base_kind', 'ops_base.small_maintenance', 'ops_base.heavy_maintenance'])
            ->where(function ($query) {
                $query->whereNotNull('maintenance.act_note')->orWhere('maintenance.curr_state', '<', 80)->orWhere('maintenance.rem_ta', '<', 600)->orWhere('maintenance.rem_tb', '<', 600)->orWhere('maintenance.rem_tc', '<', 600)->orWhere('maintenance.rem_ca', '<', 3)->orWhere('maintenance.rem_cb', '<', 3)->orWhere('maintenance.rem_cc', '<', 3);
            })
            ->when($r->user() && !$r->user()->ability('admin', 'admin-access') && (setting('pireps.restrict_aircraft_to_rank', false) || setting('pireps.restrict_aircraft_to_typerating', false)), function ($query) use ($r) {
                $allowedSubfleetIds = app(UserService::class)->getAllowableSubfleets($r->user())->pluck('id');
                $query->whereIn('aircraft.subfleet_id', $allowedSubfleetIds);
            })->orderBy('maintenance.curr_state')->paginate(40);

        $warningHours = (int) config('maintenance-warning.hours', 100);
        $warningCycles = (int) config('maintenance-warning.cycles', 2);
        $upcomingMaintenance = DB::table('disposable_maintenance as maintenance')
            ->join('aircraft as aircraft', 'aircraft.id', '=', 'maintenance.aircraft_id')
            ->leftJoin('subfleets as subfleets', 'subfleets.id', '=', 'aircraft.subfleet_id')
            ->leftJoin('airlines as airlines', 'airlines.id', '=', 'subfleets.airline_id')
            ->leftJoin('promethee_operational_bases as ops_base', 'ops_base.airport_id', '=', 'aircraft.airport_id')
            ->select(['maintenance.*', 'aircraft.registration', 'aircraft.icao', 'aircraft.airport_id', 'airlines.name as airline_name', 'airlines.icao as airline_icao', 'ops_base.kind as maintenance_base_kind', 'ops_base.small_maintenance', 'ops_base.heavy_maintenance'])
            ->whereNull('maintenance.act_note')
            ->where(function ($query) use ($warningHours, $warningCycles) {
                $query->whereBetween('maintenance.rem_ta', [0, $warningHours])
                    ->orWhereBetween('maintenance.rem_tb', [0, $warningHours])
                    ->orWhereBetween('maintenance.rem_tc', [0, $warningHours])
                    ->orWhereBetween('maintenance.rem_ca', [0, $warningCycles])
                    ->orWhereBetween('maintenance.rem_cb', [0, $warningCycles])
                    ->orWhereBetween('maintenance.rem_cc', [0, $warningCycles]);
            })
            ->when($r->user() && !$r->user()->ability('admin', 'admin-access') && (setting('pireps.restrict_aircraft_to_rank', false) || setting('pireps.restrict_aircraft_to_typerating', false)), function ($query) use ($r) {
                $allowedSubfleetIds = app(UserService::class)->getAllowableSubfleets($r->user())->pluck('id');
                $query->whereIn('aircraft.subfleet_id', $allowedSubfleetIds);
            })
            ->limit(12)->get()
            ->sortBy(fn ($item) => min(array_filter([
                is_numeric($item->rem_ta) ? (float) $item->rem_ta : INF,
                is_numeric($item->rem_tb) ? (float) $item->rem_tb : INF,
                is_numeric($item->rem_tc) ? (float) $item->rem_tc : INF,
                is_numeric($item->rem_ca) ? (float) $item->rem_ca * 25 : INF,
                is_numeric($item->rem_cb) ? (float) $item->rem_cb * 25 : INF,
                is_numeric($item->rem_cc) ? (float) $item->rem_cc * 25 : INF,
            ], fn ($value) => is_finite($value)) ?: [INF]))->values();

        $engineWarnings = Schema::hasTable('promethee_aircraft_engines')
            ? DB::table('promethee_aircraft_engines as installation')
                ->join('promethee_engines as engine', 'engine.id', '=', 'installation.engine_id')
                ->join('aircraft as aircraft', 'aircraft.id', '=', 'installation.aircraft_id')
                ->leftJoin('subfleets as subfleets', 'subfleets.id', '=', 'aircraft.subfleet_id')
                ->leftJoin('airlines as airlines', 'airlines.id', '=', 'subfleets.airline_id')
                ->whereIn('engine.status', ['warning', 'due'])
                ->select([
                    'engine.*',
                    'installation.position',
                    'aircraft.id as aircraft_id',
                    'aircraft.registration',
                    'aircraft.icao',
                    'aircraft.airport_id',
                    'airlines.icao as airline_icao',
                ])
                ->orderByRaw("CASE WHEN engine.status = 'due' THEN 0 ELSE 1 END")
                ->orderBy('aircraft.registration')
                ->get()
                ->map(function ($engine) {
                    $engine->remaining_hours = $engine->tbo_hours !== null ? round((float) $engine->tbo_hours - (float) $engine->hours_since_overhaul, 2) : null;
                    $engine->remaining_cycles = $engine->tbo_cycles !== null ? (int) $engine->tbo_cycles - (int) $engine->cycles_since_overhaul : null;
                    return $engine;
                })
            : collect();

        return $this->page('maintenance', compact('maintenance', 'upcomingMaintenance', 'warningHours', 'warningCycles', 'engineWarnings'));
    }

    /** Operational record for one aircraft, including type-specific downloads. */
    public function aircraftDetail(Request $r, string $registration) {
        $aircraftQuery = Aircraft::with(['airport', 'files', 'subfleet.airline', 'subfleet.fares', 'subfleet.files']);
        if ($r->user() && !$r->user()->ability('admin', 'admin-access') && (setting('pireps.restrict_aircraft_to_rank', false) || setting('pireps.restrict_aircraft_to_typerating', false))) {
            $allowedSubfleetIds = app(UserService::class)->getAllowableSubfleets($r->user())->pluck('id');
            $aircraftQuery->whereIn('subfleet_id', $allowedSubfleetIds);
        }
        $aircraft = $aircraftQuery
            ->where('registration', $registration)->firstOrFail();
        $aircraft->subfleet?->airline?->setAttribute('promethee_logo', $this->airlineLogoUrl($aircraft->subfleet->airline));
        $aircraft->setAttribute('state_label', AircraftState::$labels[$aircraft->state] ?? 'Inconnu');
        $aircraft->setAttribute('status_label', __(AircraftStatus::$labels[$aircraft->status] ?? 'aircraft.status.active'));

        $pireps = Pirep::with(['dpt_airport', 'arr_airport'])
            ->where('aircraft_id', $aircraft->id)->where('state', PirepState::ACCEPTED)
            ->latest('submitted_at')->take(10)->get();
        $maintenance = DB::table('disposable_maintenance')->where('aircraft_id', $aircraft->id)->first();
        $engineUnits = Schema::hasTable('promethee_aircraft_engines') ? app(EngineMaintenanceService::class)->installedForAircraft((int) $aircraft->id) : collect();
        $stats = Pirep::where('aircraft_id', $aircraft->id)->where('state', PirepState::ACCEPTED)
            ->selectRaw('COUNT(*) as pireps, COALESCE(SUM(flight_time), 0) as flight_minutes, COALESCE(SUM(fuel_used), 0) as fuel_used, COALESCE(SUM(distance), 0) as distance, AVG(landing_rate) as landing_rate')
            ->first();
        $downloads = $aircraft->files->concat($aircraft->subfleet?->files ?? collect())->unique('id')->values();

        return $this->page('aircraft', compact('aircraft', 'pireps', 'maintenance', 'engineUnits', 'stats', 'downloads'));
    }

    /**
     * Return the next departure occurrence in Paris time.
     *
     * phpVMS schedules are recurring rather than dated.  Sorting their raw
     * HH:MM values made the dispatch board call flights from this morning
     * "upcoming" for the rest of the day.  A trailing Z explicitly denotes a
     * UTC time; otherwise the historic Air Inter timetable is Paris local time.
     */
    private function nextDeparture(Flight $flight): ?CarbonImmutable {
        $raw = trim((string) $flight->dpt_time);
        if (!preg_match('/^(\d{1,2}):(\d{2})(Z)?$/i', $raw, $parts)) return null;

        $timezone = !empty($parts[3]) ? 'UTC' : 'Europe/Paris';
        $now = CarbonImmutable::now('Europe/Paris');
        $base = $now->setTimezone($timezone)->startOfDay();
        $hour = (int) $parts[1];
        $minute = (int) $parts[2];
        $days = (int) ($flight->days ?? 0);

        for ($offset = 0; $offset <= 7; $offset++) {
            $departure = $base->addDays($offset)->setTime($hour, $minute);
            $parisDeparture = $departure->setTimezone('Europe/Paris');
            $dayBit = 1 << ($departure->isoWeekday() - 1);
            $operatesThatDay = $days === 0 || ($days & $dayBit);

            if ($operatesThatDay && $parisDeparture->greaterThan($now)) return $parisDeparture;
        }

        return null;
    }

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
            $flight->setAttribute('next_departure_time',$departure?->format('H:i') ?: trim((string)$flight->dpt_time));
            $flight->setAttribute('next_arrival_time',$arrival?->format('H:i') ?: trim((string)$flight->arr_time));
            $flight->setAttribute('next_departure_iso',$departure?->toIso8601String());
            $flight->setAttribute('next_arrival_iso',$arrival?->toIso8601String());
            $flight->setAttribute('next_departure_relative',$relative);
            $flight->setAttribute('next_departure_soon',$minutes!==null && $minutes<=90);
            $flight->setAttribute('next_departure_sort',$departure?->getTimestamp());
        });
    }

    /**
     * The phpVMS timetable stores recurring HH:MM values, not dated flights.
     * A Z suffix means UTC; legacy Air Inter timetable values are Paris local
     * time. This is deliberately independent of the server's UTC timezone.
     */
    private function scheduledDateTime(string $raw, CarbonImmutable $date): ?CarbonImmutable
    {
        if (!preg_match('/^(\d{1,2}):(\d{2})(Z)?$/i', trim($raw), $parts)) return null;

        return $date->setTimezone(!empty($parts[3]) ? 'UTC' : 'Europe/Paris')
            ->startOfDay()->setTime((int) $parts[1], (int) $parts[2]);
    }

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

    private function airlineLogoUrl(?Airline $airline): ?string
    {
        $logo = trim((string) $airline?->logo);
        if ($logo === '') {
            // Several historic timetable carriers have no logo URL in phpVMS.
            // Keep the board's airline column visual instead of falling back
            // to vertically stacked split-flap letters.
            $code = strtoupper((string) ($airline?->code ?: $airline?->icao ?: $airline?->callsign));
            $bundledLogos = [
                'ITF' => 'SPTheme/images/LogoITF002.png',
                'ACF' => 'SPTheme/images/AirCharterLogo.png',
                'ICS' => 'SPTheme/images/ICSLogo.png',
            ];

            return isset($bundledLogos[$code]) ? asset($bundledLogos[$code]) : null;
        }

        return filter_var($logo, FILTER_VALIDATE_URL) ? $logo : asset(ltrim($logo, '/'));
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

    private function month(Request $r): string {
        $r->validate(['month'=>'nullable|date_format:Y-m']);
        return $r->query('month',now('Europe/Paris')->format('Y-m'));
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
        $pilot = $r->user()->load(['airline','home_airport']);
        return $this->page('profile-edit', [
            'pilot' => $pilot,
            'airlines' => Airline::where('active', true)->orderBy('name')->get(['id','name','icao']),
            'airports' => Airport::orderBy('icao')->get(['id','icao','name','location']),
            'countries' => Countries::getSelectList(),
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }
    public function updateProfile(Request $r) {
        $pilot = $r->user();
        $data = $r->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,'.$pilot->id,
            'airline_id' => 'required|integer|exists:airlines,id',
            'home_airport_id' => 'nullable|string|max:10|exists:airports,id',
            'country' => 'nullable|string|size:2',
            'timezone' => 'required|timezone',
            'vatsim_id' => 'nullable|string|max:32',
            'ivao_id' => 'nullable|string|max:32',
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);
        $emailChanged = $pilot->email !== $data['email'];
        if (blank($data['password'] ?? null)) unset($data['password']);
        else $data['password'] = Hash::make($data['password']);
        unset($data['avatar']);
        if ($r->hasFile('avatar')) {
            $file = $r->file('avatar');
            $data['avatar'] = $file->storeAs('avatars', $pilot->ident.'.'.$file->extension(), config('filesystems.public_files'));
        }
        if ($emailChanged) $data['email_verified_at'] = null;
        $pilot->fill($data)->save();
        if ($emailChanged) $pilot->sendEmailVerificationNotification();
        return redirect()->route('promethee.profile')->with('success', 'Profil mis à jour.');
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

   public function deleteOwnPirep(string $id, Request $r)
   {
       $pirep = Pirep::with(['user', 'aircraft', 'flight'])->findOrFail($id);
       $ident = $pirep->ident;
       app(PilotPirepDeletionService::class)->deleteOwn($pirep, $r->user(), 'promethee-pirep');

       return redirect()->route('promethee.bookings')
           ->with('success', 'PIREP '.$ident.' supprimé. Vous pouvez préparer une nouvelle tentative.');
   }

   private function bookingOperation(Bid $booking): Bid
   {
       $operationId = 'op_'.$booking->id;
       // Hermès correlates by immutable operation id. The aircraft can change
       // during preparation, so filtering the PIREP by the current aircraft
       // can hide an older ghost and make the same reservation look completed
       // again as soon as that aircraft is re-selected.
       $pirep = Pirep::where('user_id', $booking->user_id)
           ->where('flight_id', $booking->flight_id)
           ->where('source_name', 'Hermes ACARS ['.$operationId.']')
           ->latest('created_at')->first();

       $ofp = null;
       if ($booking->aircraft_id) {
           if ($pirep) {
               $ofp = SimBrief::where('user_id', $booking->user_id)->where('flight_id', $booking->flight_id)
                   ->where('aircraft_id', $booking->aircraft_id)->where('pirep_id', $pirep->id)
                   ->latest('updated_at')->first();
           }
           $ofp ??= SimBrief::where('user_id', $booking->user_id)->where('flight_id', $booking->flight_id)
               ->where('aircraft_id', $booking->aircraft_id)->whereNull('pirep_id')
               ->when($booking->created_at, fn ($query) => $query->where('updated_at', '>=', $booking->created_at))
               ->latest('updated_at')->first();
       }

       $hasTelemetry = $pirep && DB::table('promethee_telemetry')->where('pirep_id', $pirep->id)->exists();
       $legacyGhost = $pirep && $this->isLegacyHermesGhostForPortal($pirep, $hasTelemetry);
       $statusPirep = $legacyGhost ? null : $pirep;
       $cancelled = $statusPirep && ((int) $statusPirep->state === PirepState::CANCELLED || $statusPirep->status === PirepStatus::CANCELLED);
       $completed = $statusPirep && !$cancelled && ($statusPirep->submitted_at !== null
           || in_array((int) $statusPirep->state, [PirepState::PENDING, PirepState::ACCEPTED, PirepState::REJECTED], true));
       // ARRIVED is deliberately non-terminal: the simulator flight is over,
       // but the pilot still has to review and file the final report.
       $awaitingFiling = $statusPirep && !$cancelled && !$completed && $statusPirep->status === PirepStatus::ARRIVED;

       $status = $cancelled ? 'CANCELLED'
           : ($completed ? 'COMPLETED'
           : ($awaitingFiling ? 'AWAITING_FILING'
           : ($hasTelemetry ? 'IN_PROGRESS'
           : ($statusPirep ? 'READY'
           : ($ofp && $booking->aircraft_id ? 'PIREP_REQUIRED'
           : ($booking->aircraft_id ? 'OFP_REQUIRED' : 'AIRCRAFT_REQUIRED'))))));

       $progress = match ($status) {
           'AIRCRAFT_REQUIRED' => 10,
           'OFP_REQUIRED' => 30,
           'PIREP_REQUIRED' => 55,
           'READY' => 70,
           'IN_PROGRESS' => 85,
           'AWAITING_FILING' => 95,
           'COMPLETED', 'CANCELLED' => 100,
           default => 0,
       };

       $booking->setAttribute('operation_id', $operationId);
       $booking->setAttribute('operation_ofp', $ofp);
       $booking->setAttribute('operation_pirep', $pirep);
       $booking->setAttribute('operation_status', $status);
       $booking->setAttribute('operation_progress', $progress);
       $booking->setAttribute('operation_can_delete', $pirep === null);
       $booking->setAttribute(
           'operation_can_delete_pirep',
           $pirep !== null && app(PilotPirepDeletionService::class)->isDeletableState($pirep)
       );
       $booking->setAttribute('operation_legacy_ghost', $legacyGhost);
       $booking->setAttribute('operation_next_action', $legacyGhost
           ? 'Réparer l’ancien PIREP dans Hermès'
           : match ($status) {
           'AIRCRAFT_REQUIRED' => 'Sélectionner un appareil',
           'OFP_REQUIRED' => 'Préparer l’OFP',
           'PIREP_REQUIRED' => 'Finaliser la préparation',
           'READY' => 'Démarrer dans Hermès',
           'IN_PROGRESS' => 'Vol en cours',
           'AWAITING_FILING' => 'Ouvrir le Flight Review',
           'COMPLETED' => 'Consulter le vol',
           'CANCELLED' => 'Opération annulée',
           default => null,
       });

       return $booking;
   }

   /**
    * Remove only stale terminal reservations for this exact pilot/flight.
    * Any non-terminal operation wins and is returned untouched.
    */
   private function releaseTerminalReservationsForFlight(Flight $flight, User $user): ?Bid
   {
       $active = null;
       $reservations = Bid::with(['flight', 'aircraft'])
           ->where(['flight_id' => $flight->id, 'user_id' => $user->id])
           ->latest()
           ->get();

       foreach ($reservations as $reservation) {
           $operation = $this->bookingOperation($reservation);
           if (in_array($operation->operation_status, ['COMPLETED', 'CANCELLED'], true)) {
               $reservation->delete();
               continue;
           }

           $active ??= $operation;
       }

       return $active;
   }

   private function isLegacyHermesGhostForPortal(Pirep $pirep, bool $hasTelemetry): bool
   {
       if (!str_starts_with((string) $pirep->source_name, 'Hermes ACARS [op_')) return false;

       $terminal = $pirep->submitted_at !== null
           || in_array((int) $pirep->state, [PirepState::PENDING, PirepState::ACCEPTED, PirepState::REJECTED], true)
           || $pirep->status === PirepStatus::ARRIVED;
       if (!$terminal) return false;

       // Legacy broken Hermès clients could write synthetic summary values
       // while filing an unflown PIREP, so flight_time/block timestamps are
       // not sufficient proof that the simulator actually ran.
       if ($hasTelemetry) return false;

       // SimBrief stores planned ROUTE points in the core ACARS table as soon
       // as a PIREP is prefiled. Ignore those rows; only real flight/log rows
       // count as evidence for older Hermès builds.
       if (DB::table('acars')
           ->where('pirep_id', $pirep->id)
           ->where('type', '!=', \App\Models\Enums\AcarsType::ROUTE)
           ->exists()) return false;

       return true;
   }

   private function downloadCategory(File $file): string {
       $reference = strtolower((string) $file->ref_model);
       $search = strtolower(implode(' ', [$file->name, $file->description, $file->path, $reference]));
       if (str_contains($reference, 'promethee\\download\\')) {
           return strtolower((string) preg_replace('/^.*\\\\/', '', $file->ref_model)) ?: 'documents';
       }
       if (str_contains($search, 'acars')) return 'acars';
       if (str_contains($reference, 'aircraft') || str_contains($reference, 'subfleet')) return 'fleet';
       if (str_contains($reference, 'airport')) return 'airports';
       return 'documents';
   }
   private function downloadSubcategory(File $file): string {
       $category = $this->downloadCategory($file);
       $subcategory = trim((string) $file->ref_model_id);
       // Downloads created before subcategories used the category as their ID.
       return $subcategory === '' || strtolower($subcategory) === $category ? 'Général' : $subcategory;
   }
   /**
    * Only expose files owned by Promethee or attached to operational catalogue
    * models. The global files table also contains unrelated application assets
    * which must never fall through into the Documents category.
    */
   private function downloadGroups() {
       $files = File::query()->orderBy('name')->get();

       // The files table is shared with phpVMS. Keep every resource that is
       // explicitly owned by Prométhée, linked to the operational catalogue,
       // or looks like a legacy standalone download. Unknown legacy entries
       // are deliberately kept in an "uncategorized" bucket so admins can
       // recover/reclassify them instead of making them invisible.
       $knownModels = [Aircraft::class, Subfleet::class, Airport::class];
       $files = $files->filter(function (File $file) use ($knownModels) {
           $reference = trim((string) $file->ref_model);
           if (str_starts_with($reference, 'Modules\\Promethee\\Download\\')) return true;
           if (in_array($reference, $knownModels, true)) return true;
           if ($reference === '') return true;

           return false;
       });

       return $files->groupBy(function (File $file) {
           $category = $this->downloadCategory($file);
           return in_array($category, ['acars', 'fleet', 'airports', 'documents'], true)
               ? $category
               : 'uncategorized';
       });
   }
   public function downloads(Request $r) {
       return $this->page('downloads', ['groups' => $this->downloadGroups()]);
   }
   public function documents() {
       return $this->downloadCategoryPage('documents');
   }

   public function myDocuments(Request $r, UserService $users) {
       $pilot = $r->user();
       $allowedSubfleets = $users->getAllowableSubfleets($pilot);
       $aircraftTypes = $allowedSubfleets->pluck('type')->filter()->map(fn ($type) => strtoupper(trim((string) $type)))->unique()->sort()->values();

       $documents = $this->downloadGroups()->get('documents', collect())->map(function (File $file) {
           $subcategory = $this->downloadSubcategory($file);
           $parts = array_map('trim', explode('·', $subcategory, 2));
           $file->setAttribute('promethee_document_section', $parts[0] ?: 'Général');
           $file->setAttribute('promethee_aircraft_type', strtoupper($parts[1] ?? ''));
           $file->setAttribute('promethee_extension', strtoupper(pathinfo(parse_url((string) $file->path, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION)));
           return $file;
       });

       $personalDocuments = $documents->filter(function (File $file) use ($aircraftTypes) {
           $type = (string) $file->promethee_aircraft_type;
           return $type === '' || $aircraftTypes->contains($type);
       })->values();

       $sections = $personalDocuments->groupBy(fn (File $file) => $file->promethee_document_section);

       return $this->page('my-documents', [
           'pilot' => $pilot,
           'aircraftTypes' => $aircraftTypes,
           'documents' => $personalDocuments,
           'sections' => $sections,
           'allDocumentsCount' => $documents->count(),
       ]);
   }
   public function document(string $file) {
       $asset = File::findOrFail($file);
       abort_unless($this->downloadCategory($asset) === 'documents' && $this->isManagedDownload($asset), 404);

       $urlPath = parse_url((string) $asset->path, PHP_URL_PATH) ?: '';
       $extension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
       $officeExtensions = ['ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx'];
       $previewType = match (true) {
           $extension === 'pdf' => 'pdf',
           in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true) => 'image',
           in_array($extension, $officeExtensions, true) => 'office',
           in_array($extension, ['txt', 'md', 'csv'], true) => 'text',
           default => 'none',
       };

       return $this->page('document-viewer', compact('asset', 'extension', 'previewType'));
   }
   public function documentContent(string $file) {
       $asset = File::findOrFail($file);
       abort_unless($this->downloadCategory($asset) === 'documents' && $this->isManagedDownload($asset), 404);

       if ($asset->isExternalFile) {
           return redirect()->away($asset->url);
       }

       $disk = $asset->disk ?? config('filesystems.public_files');
       abort_unless(\Illuminate\Support\Facades\Storage::disk($disk)->exists($asset->path), 404);

       $path = \Illuminate\Support\Facades\Storage::disk($disk)->path($asset->path);
       $mime = mime_content_type($path) ?: 'application/octet-stream';

       return response()->file($path, [
           'Content-Type' => $mime,
           'Content-Disposition' => 'inline; filename="'.addslashes($asset->filename).'"',
           'X-Content-Type-Options' => 'nosniff',
           'Cache-Control' => 'private, max-age=300',
       ]);
   }
   public function downloadCategoryPage(string $category) {
       $sections = ['acars' => ['ACARS', 'Clients et documentation de connexion'], 'fleet' => ['Avions et flotte', 'Livrées, appareils et documents associés'], 'airports' => ['Aéroports et HUBs', 'Scènes, cartes et ressources réseau'], 'documents' => ['Documentation interne', 'Procédures, carrière, formation et documentation par type d’avion']];
       abort_unless(array_key_exists($category, $sections), 404);
       $files = $this->downloadGroups()->get($category, collect())->groupBy(fn (File $file) => $this->downloadSubcategory($file));
       return $this->page('download-category', compact('category', 'sections', 'files'));
   }
   public function download(string $file) {
       $allowed = $this->downloadGroups()->flatten(1)->contains(fn (File $asset) => (string) $asset->id === $file);
       abort_unless($allowed, 404);

       return app(\App\Http\Controllers\Frontend\DownloadController::class)->show($file);
   }
   public function adminDownloads() {
       return $this->page('admin.downloads', [
           'groups' => $this->downloadGroups(),
           'aircraftTypes' => Subfleet::query()->pluck('type')->filter()->unique()->sort()->values(),
       ]);
   }
   public function storeDownload(Request $r, FileService $files) {
       $data = $r->validate([
           'name' => 'required|string|max:120', 'description' => 'nullable|string|max:1000',
           'category' => 'required|in:acars,fleet,airports,documents', 'subcategory' => 'nullable|string|max:80',
           'document_section' => 'nullable|in:general,operations,career,training,aircraft,regulations,forms',
           'aircraft_type' => 'nullable|string|max:30', 'file' => 'nullable|file|max:102400',
           'url' => 'nullable|url|max:2000', 'public' => 'nullable|boolean',
       ]);
       if (!$r->hasFile('file') && empty($data['url'])) return back()->withErrors(['url' => 'Ajoutez un fichier ou une URL.'])->withInput();
       $documentSections = [
           'general' => 'Général', 'operations' => 'Opérations', 'career' => 'Carrière',
           'training' => 'Formation', 'aircraft' => 'Documentation avion',
           'regulations' => 'Réglementation', 'forms' => 'Formulaires',
       ];
       $subcategory = trim($data['subcategory'] ?? '');
       if ($data['category'] === 'documents') {
           $section = $documentSections[$data['document_section'] ?? 'general'] ?? 'Général';
           $aircraftType = strtoupper(trim($data['aircraft_type'] ?? ''));
           $subcategory = $section.($aircraftType !== '' ? ' · '.$aircraftType : '');
       }
       $attributes = [
           'name' => $data['name'], 'description' => $data['description'] ?? '', 'public' => $r->boolean('public'),
           'ref_model' => 'Modules\\Promethee\\Download\\'.ucfirst($data['category']),
           'ref_model_id' => $subcategory !== '' ? $subcategory : $data['category'],
       ];
       if ($r->hasFile('file')) $files->saveFile($r->file('file'), 'promethee-downloads', $attributes);
       else { $asset = new File($attributes); $asset->id = File::createNewHashId(); $asset->path = $data['url']; $asset->save(); }
       return back()->with('success', 'Téléchargement enregistré.');
   }
   private function isManagedDownload(File $asset): bool {
       // Everything surfaced by the Prométhée download centre is manageable
       // from this screen. On first edit, legacy phpVMS catalogue attachments
       // are adopted by Prométhée and become ordinary download resources.
       return $this->downloadGroups()->flatten(1)
           ->contains(fn (File $download) => (string) $download->id === (string) $asset->id);
   }
   public function editDownload(string $file) {
       $asset = File::findOrFail($file);
       abort_unless($this->isManagedDownload($asset), 403);
       $aircraftTypes = Subfleet::query()->pluck('type')->filter()->unique()->sort()->values();
       return $this->page('admin.edit-download', compact('asset', 'aircraftTypes'));
   }
   public function updateDownload(Request $r, string $file, FileService $files) {
       $asset = File::findOrFail($file);
       abort_unless($this->isManagedDownload($asset), 403);

       $data = $r->validate([
           'name' => 'required|string|max:120', 'description' => 'nullable|string|max:1000',
           'category' => 'required|in:acars,fleet,airports,documents', 'subcategory' => 'nullable|string|max:80',
           'document_section' => 'nullable|in:general,operations,career,training,aircraft,regulations,forms',
           'aircraft_type' => 'nullable|string|max:30',
           'file' => 'nullable|file|max:102400', 'url' => 'nullable|url|max:2000', 'public' => 'nullable|boolean',
       ]);
       $documentSections = [
           'general' => 'Général', 'operations' => 'Opérations', 'career' => 'Carrière',
           'training' => 'Formation', 'aircraft' => 'Documentation avion',
           'regulations' => 'Réglementation', 'forms' => 'Formulaires',
       ];
       $subcategory = trim($data['subcategory'] ?? '');
       if ($data['category'] === 'documents') {
           $section = $documentSections[$data['document_section'] ?? 'general'] ?? 'Général';
           $aircraftType = strtoupper(trim($data['aircraft_type'] ?? ''));
           $subcategory = $section.($aircraftType !== '' ? ' · '.$aircraftType : '');
       }
       $attributes = [
           'name' => $data['name'], 'description' => $data['description'] ?? '', 'public' => $r->boolean('public'),
           'ref_model' => 'Modules\\Promethee\\Download\\'.ucfirst($data['category']),
           'ref_model_id' => $subcategory !== '' ? $subcategory : $data['category'],
       ];

       if ($r->hasFile('file')) {
           // Replace the physical payload while keeping the same database ID.
           // This preserves download counters and existing Prométhée links.
           $oldPath = (string) $asset->path;
           $oldDisk = $asset->disk ?? config('filesystems.public_files');
           $replacement = $files->saveFile($r->file('file'), 'promethee-downloads', $attributes);
           $asset->fill($attributes);
           $asset->path = $replacement->path;
           $asset->disk = $replacement->disk;
           $asset->save();
           $replacement->delete();
           if ($oldPath !== '' && !str_starts_with($oldPath, 'http') && $oldPath !== $asset->path) {
               \Illuminate\Support\Facades\Storage::disk($oldDisk)->delete($oldPath);
           }
       } else {
           $asset->fill($attributes);
           if (!empty($data['url'])) {
               $oldPath = (string) $asset->path;
               $oldDisk = $asset->disk ?? config('filesystems.public_files');
               $asset->path = $data['url'];
               $asset->disk = null;
               if ($oldPath !== '' && !str_starts_with($oldPath, 'http')) {
                   \Illuminate\Support\Facades\Storage::disk($oldDisk)->delete($oldPath);
               }
           }
           $asset->save();
       }

       return redirect()->route('admin.promethee.downloads')->with('success', 'Téléchargement modifié.');
   }
   public function deleteDownload(string $file, FileService $files) {
       $asset = File::findOrFail($file);
       abort_unless($this->isManagedDownload($asset), 403);
       $files->removeFile($asset);
       return back()->with('success', 'Téléchargement supprimé.');
   }
    /** Current missions and circuits. Completion is derived from accepted PIREPs. */
    public function missions(Request $r, RegionalOperationsService $regionalOperations) {
        $regionalOperations->sync();
        $today = today('Europe/Paris')->toDateString(); $userId = $r->user()->id;
        $reports = Pirep::where('user_id',$userId)->where('state',PirepState::ACCEPTED)
            ->get(['id','flight_id','dpt_airport_id','arr_airport_id','submitted_at']);
        $matches = function ($item) use ($reports) {
            return $reports->first(fn ($report) =>
                (!$item->flight_id || (string)$report->flight_id === (string)$item->flight_id) &&
                (!$item->dpt_airport_id || $report->dpt_airport_id === $item->dpt_airport_id) &&
                (!$item->arr_airport_id || $report->arr_airport_id === $item->arr_airport_id)
            );
        };
        $missions = $regionalOperations->decorateMissionsForPilot(
            DB::table('promethee_missions')->where('active',true)
                ->where(fn($q)=>$q->whereNull('starts_on')->orWhere('starts_on','<=',$today))
                ->where(fn($q)=>$q->whereNull('ends_on')->orWhere('ends_on','>=',$today))->orderBy('ends_on')->get(),
            $r->user()
        )->map(function ($mission) use ($matches, $userId) {
            $mission->completion = $matches($mission);
            $mission->booking = DB::table('promethee_mission_bookings')
                ->where('mission_id', $mission->id)
                ->where('user_id', $userId)
                ->latest('created_at')->first();
            $mission->reserved_by_other = DB::table('promethee_mission_bookings')
                ->where('mission_id', $mission->id)
                ->where('user_id', '!=', $userId)
                ->where('status', 'reserved')->exists();
            return $mission;
        });
        $circuits = DB::table('promethee_circuits')->where('active',true)
            ->where(fn($q)=>$q->whereNull('starts_on')->orWhere('starts_on','<=',$today))
            ->where(fn($q)=>$q->whereNull('ends_on')->orWhere('ends_on','>=',$today))->orderBy('ends_on')->get()
            ->map(function ($circuit) use ($matches) { $circuit->legs=DB::table('promethee_circuit_legs')->where('circuit_id',$circuit->id)->orderBy('position')->get()->map(function($leg) use($matches){ $leg->completion=$matches($leg); return $leg; }); $circuit->completed=$circuit->legs->isNotEmpty() && $circuit->legs->every(fn($leg)=>$leg->completion); return $circuit; });
        return $this->page('missions', compact('missions','circuits'));
    }

    public function reserveMission(int $id, Request $r, FinanceService $finance, RegionalOperationsService $regionalOperations) {
        $mission = DB::table('promethee_missions')->where('id', $id)->where('active', true)->first();
        abort_unless($mission, 404);
        abort_unless($mission->mission_type === 'repatriation', 422, 'Cette mission ne nécessite pas de réservation.');
        $regionalOperations->reserveRepatriationMission($id, $r->user(), $finance);
        return back()->with('success', 'Mission de rapatriement réservée. Votre position pilote a été ajustée si un jumpseat était nécessaire.');
    }

    public function cancelMissionReservation(int $id, Request $r) {
        $booking = DB::table('promethee_mission_bookings')
            ->where('mission_id', $id)
            ->where('user_id', $r->user()->id)
            ->where('status', 'reserved')
            ->first();

        abort_unless($booking, 404, 'Aucune réservation active pour cette mission.');

        DB::table('promethee_mission_bookings')
            ->where('id', $booking->id)
            ->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);

        return back()->with(
            'success',
            'Mission abandonnée et de nouveau disponible. Un éventuel jumpseat déjà effectué n’est pas annulé.'
        );
    }

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
        $personalizedDefault = collect($explicitFilterKeys)->every(fn ($key) => !$r->filled($key))
            && (!$r->filled('sort') || ($filters['sort'] ?? 'departure') === 'departure');
        $programmeBaseId = $r->user()?->home_airport_id ?: $r->user()?->curr_airport_id;
        $programmeBase = $programmeBaseId ? Airport::find($programmeBaseId) : null;

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
        $allowedSubfleetIds = app(UserService::class)->getAllowableSubfleets($r->user())->pluck('id');
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
    public function calendar(Request $r) {
        $month=$this->month($r);
        $start=CarbonImmutable::createFromFormat('!Y-m',$month,'Europe/Paris');
        $events=DB::table('promethee_events')->where('starts_at','<',$start->addMonth()->utc())
            ->where('ends_at','>=',$start->utc())->orderBy('starts_at')->get();
        $rsvps=DB::table('promethee_event_rsvps')->whereIn('event_id',$events->pluck('id'))->select('event_id',DB::raw('COUNT(*) as total'))->groupBy('event_id')->pluck('total','event_id');
        $mine=DB::table('promethee_event_rsvps')->where('user_id',$r->user()->id)->whereIn('event_id',$events->pluck('id'))->pluck('status','event_id');
        $events=$events->map(function ($event) use ($rsvps,$mine) { $event->rsvp_count=$rsvps[$event->id]??0; $event->my_rsvp=$mine[$event->id]??null; return $event; });

        $editingEvent = null;
        if (($r->user()?->ability('admin','admin-access') ?? false) && $r->filled('edit_event')) {
            $editingEvent = DB::table('promethee_events')->find((int) $r->query('edit_event'));
        }

        return $this->page('calendar',[
            'month'=>$month,'start'=>$start,
            'events'=>$events,
            'editingEvent'=>$editingEvent,
        ]);
    }
    public function rsvpEvent(int $id, Request $r) {
        DB::table('promethee_events')->find($id) ?? abort(404);
        $data=$r->validate(['status'=>'required|in:going,maybe']);
        DB::table('promethee_event_rsvps')->updateOrInsert(['event_id'=>$id,'user_id'=>$r->user()->id],$data+['updated_at'=>now(),'created_at'=>now()]);
        return back()->with('success','Participation enregistrée.');
    }
    public function saveEvent(Request $r) {
        $d=$r->validate(['event_id'=>'nullable|integer|exists:promethee_events,id','title'=>'required|string|max:191','description'=>'nullable|string|max:4000',
            'starts_at'=>'required|date','ends_at'=>'required|date|after:starts_at',
            'departure'=>'nullable|exists:airports,id','arrival'=>'nullable|exists:airports,id']);
        foreach (['starts_at','ends_at'] as $key) $d[$key]=CarbonImmutable::parse($d[$key],'Europe/Paris')->utc();

        $id=$d['event_id'] ?? null;
        unset($d['event_id']);

        if ($id) DB::table('promethee_events')->where('id',$id)->update($d+['updated_at'=>now()]);
        else DB::table('promethee_events')->insert($d+['created_by'=>$r->user()->id,'created_at'=>now(),'updated_at'=>now()]);

        return redirect()->route('promethee.calendar',['month'=>$d['starts_at']->setTimezone('Europe/Paris')->format('Y-m')])
            ->with('success',$id ? 'Événement modifié.' : 'Événement ajouté au calendrier.');
    }
    public function deleteEvent(int $id) {
        DB::transaction(function () use ($id) {
            DB::table('promethee_event_rsvps')->where('event_id',$id)->delete();
            DB::table('promethee_events')->where('id',$id)->delete();
        });
        return back()->with('success','Événement supprimé.');
    }
    public function adminEvents() {
        return $this->page('admin.events', ['events' => DB::table('promethee_events')->orderByDesc('starts_at')->get()]);
    }
    public function saveAdminEvent(Request $r) {
        $d=$r->validate(['event_id'=>'nullable|integer|exists:promethee_events,id','title'=>'required|string|max:191','description'=>'nullable|string|max:4000',
            'starts_at'=>'required|date','ends_at'=>'required|date|after:starts_at','departure'=>'nullable|exists:airports,id','arrival'=>'nullable|exists:airports,id']);
        foreach (['starts_at','ends_at'] as $key) $d[$key]=CarbonImmutable::parse($d[$key],'Europe/Paris')->utc();
        $id=$d['event_id'] ?? null; unset($d['event_id']);
        if ($id) DB::table('promethee_events')->where('id',$id)->update($d+['updated_at'=>now()]);
        else DB::table('promethee_events')->insert($d+['created_by'=>$r->user()->id,'created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('admin.promethee.events')->with('success',$id ? 'Événement mis à jour.' : 'Événement ajouté au calendrier.');
    }
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
            'communityDocuments'=>$this->downloadGroups()->get('documents',collect())->take(3),
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
    public function bbrSettings(DemandProfileService $demand)
    {
        return $this->page('admin.bbr', ['bbr' => $demand->settings()]);
    }

    public function saveBbrSettings(Request $r)
    {
        $data = $r->validate([
            'enabled' => 'nullable|boolean',
            'blue' => 'required|numeric|between:1,100',
            'white' => 'required|numeric|between:1,100',
            'blue_min' => 'required|numeric|between:1,100',
            'blue_max' => 'required|numeric|between:1,100',
            'white_min' => 'required|numeric|between:1,100',
            'white_max' => 'required|numeric|between:1,100',
            'red_min' => 'required|numeric|between:1,100',
            'red_max' => 'required|numeric|between:1,100',
        ]);

        if ((float) $data['blue'] > (float) $data['white']) {
            return back()->withErrors(['blue' => 'Le tarif Bleu doit rester inférieur ou égal au tarif Blanc.'])->withInput();
        }

        foreach (['blue', 'white', 'red'] as $band) {
            if ((float) $data[$band.'_min'] > (float) $data[$band.'_max']) {
                return back()->withErrors([$band.'_min' => 'Le minimum de remplissage doit être inférieur ou égal au maximum.'])->withInput();
            }
        }

        $values = [
            'pricing.bands.enabled' => $r->boolean('enabled') ? '1' : '0',
            'pricing.bands.blue' => (string) $data['blue'],
            'pricing.bands.white' => (string) $data['white'],
            'pricing.demand.blue_min' => (string) $data['blue_min'],
            'pricing.demand.blue_max' => (string) $data['blue_max'],
            'pricing.demand.white_min' => (string) $data['white_min'],
            'pricing.demand.white_max' => (string) $data['white_max'],
            'pricing.demand.red_min' => (string) $data['red_min'],
            'pricing.demand.red_max' => (string) $data['red_max'],
        ];

        foreach ($values as $key => $value) {
            DB::table('promethee_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        return redirect()->route('admin.promethee.bbr')->with('success', 'Tarification et remplissage Bleu-Blanc-Rouge enregistrés.');
    }

    public function pricingCriteria(Request $r)
    {
        $itf = Airline::where('icao', 'ITF')->first();
        $profiles = DB::table('promethee_route_pricing_profiles')->pluck('network_class', 'flight_id');

        $flights = $itf
            ? Flight::where('airline_id', $itf->id)
                ->where('active', true)
                ->with(['dpt_airport:id,icao,name,location', 'arr_airport:id,icao,name,location'])
                ->orderBy('dpt_airport_id')->orderBy('arr_airport_id')->orderBy('flight_number')
                ->get()
            : collect();

        $flights->each(function ($flight) use ($profiles) {
            $flight->setAttribute('pricing_network_class', $profiles[(string) $flight->id] ?? 'unclassified');
        });

        return $this->page('admin-pricing-criteria', [
            'itf' => $itf,
            'flights' => $flights,
            'seasons' => DB::table('promethee_seasons')->orderBy('starts_on')->get(),
            'counts' => [
                'principal' => $flights->where('pricing_network_class', 'principal')->count(),
                'diagonal' => $flights->where('pricing_network_class', 'diagonal')->count(),
                'unclassified' => $flights->where('pricing_network_class', 'unclassified')->count(),
            ],
        ]);
    }

    public function savePricingCriteria(Request $r)
    {
        $data = $r->validate([
            'flight_ids' => 'required|array|min:1',
            'flight_ids.*' => 'exists:flights,id',
            'network_class' => 'required|in:principal,diagonal,unclassified',
        ]);

        $itfId = Airline::where('icao', 'ITF')->value('id');
        abort_unless($itfId, 422, 'Compagnie ITF introuvable.');

        $validIds = Flight::where('airline_id', $itfId)
            ->whereIn('id', $data['flight_ids'])
            ->pluck('id')->map(fn ($id) => (string) $id);

        abort_if($validIds->count() !== count($data['flight_ids']), 422, 'La classification régionale est réservée aux lignes ITF.');

        DB::transaction(function () use ($validIds, $data) {
            foreach ($validIds as $flightId) {
                if ($data['network_class'] === 'unclassified') {
                    DB::table('promethee_route_pricing_profiles')->where('flight_id', $flightId)->delete();
                    continue;
                }

                DB::table('promethee_route_pricing_profiles')->updateOrInsert(
                    ['flight_id' => $flightId],
                    ['network_class' => $data['network_class'], 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        return back()->with('success', $validIds->count().' ligne(s) ITF classée(s).');
    }

    public function economy(Request $r, EconomyFareResolver $fareResolver) {
        foreach (['flight_dpt_airport', 'flight_arr_airport'] as $airportFilter) {
            if ($r->filled($airportFilter)) {
                $r->merge([$airportFilter => strtoupper(trim((string) $r->input($airportFilter)))]);
            }
        }

        $flightFilters=$r->validate(['flight_airline'=>'nullable|string|max:10','flight_origin'=>'nullable|string|size:2','flight_arrival'=>'nullable|string|size:2','flight_dpt_airport'=>'nullable|string|max:10','flight_arr_airport'=>'nullable|string|max:10','flight_search'=>'nullable|string|max:80','flight_network_class'=>'nullable|in:principal,diagonal,unclassified','flight_select_all'=>'nullable|boolean']);
        $fuelFilters=$r->validate(['fuel_country'=>'nullable|string|size:2','fuel_region'=>'nullable|string|max:191','fuel_search'=>'nullable|string|max:80','fuel_select_all'=>'nullable|boolean']);
        $airportOptions=Airport::select('id','icao','name','country','region','location')->orderBy('country')->orderBy('region')->orderBy('location')->get();
        $flightPricing=Flight::where('active',true)
            ->when($flightFilters['flight_airline'] ?? null,fn($query,$icao)=>$query->whereHas('airline',fn($airline)=>$airline->where('icao',$icao)))
            ->when($flightFilters['flight_origin'] ?? null,fn($query,$country)=>$query->whereHas('dpt_airport',fn($airport)=>$airport->where('country',$country)))
            ->when($flightFilters['flight_arrival'] ?? null,fn($query,$country)=>$query->whereHas('arr_airport',fn($airport)=>$airport->where('country',$country)))
            ->when($flightFilters['flight_dpt_airport'] ?? null,fn($query,$airport)=>$query->where('dpt_airport_id',$airport))
            ->when($flightFilters['flight_arr_airport'] ?? null,fn($query,$airport)=>$query->where('arr_airport_id',$airport))
            ->when($flightFilters['flight_search'] ?? null,fn($query,$search)=>$query->where(fn($where)=>$where->where('flight_number','like','%'.$search.'%')->orWhere('route_code','like','%'.$search.'%')->orWhere('dpt_airport_id','like','%'.$search.'%')->orWhere('arr_airport_id','like','%'.$search.'%')))
            ->when($flightFilters['flight_network_class'] ?? null, function ($query, $class) {
                if ($class === 'unclassified') {
                    $query->whereNotIn('flights.id', DB::table('promethee_route_pricing_profiles')->select('flight_id'));
                    return;
                }
                $query->whereIn('flights.id', DB::table('promethee_route_pricing_profiles')->where('network_class', $class)->select('flight_id'));
            })
            ->with(['airline','fares','subfleets.fares','dpt_airport','arr_airport'])
            ->orderBy('airline_id')->orderBy('dpt_airport_id')->orderBy('arr_airport_id')->get();
        $networkProfiles = DB::table('promethee_route_pricing_profiles')->pluck('network_class', 'flight_id');
        $flightPricing->each(fn ($flight) => $flight->setAttribute(
            'pricing_network_class',
            $networkProfiles[(string) $flight->id] ?? 'unclassified'
        ));
        $fuelPricing=Airport::select('id','icao','name','country','region','fuel_jeta_cost','fuel_100ll_cost','fuel_mogas_cost')
            ->when($fuelFilters['fuel_country'] ?? null,fn($query,$country)=>$query->where('country',$country))
            ->when($fuelFilters['fuel_region'] ?? null,fn($query,$region)=>$query->where('region',$region))
            ->when($fuelFilters['fuel_search'] ?? null,fn($query,$search)=>$query->where(fn($where)=>$where->where('country','like','%'.$search.'%')->orWhere('region','like','%'.$search.'%')))
            ->orderBy('country')->orderBy('region')->orderBy('icao')->get();
        $scopeFromAirports=function($airports, string $country, ?string $region) {
            $display=function(string $field) use ($airports) {
                $prices=$airports->pluck($field)->filter(fn($price)=>$price !== null && $price !== '')->unique()->values();
                return $prices->count() === 1 ? $prices->first() : ($prices->isEmpty() ? 'Défaut' : 'Variés');
            };
            return (object)['country'=>$country,'region'=>$region,'count'=>$airports->count(),'jeta'=>$display('fuel_jeta_cost'),'100ll'=>$display('fuel_100ll_cost'),'mogas'=>$display('fuel_mogas_cost')];
        };
        $fuelScopes=collect();
        foreach ($fuelPricing->filter(fn($airport)=>preg_match('/^[A-Z]{2}$/',strtoupper((string)$airport->country)))->groupBy('country') as $country=>$countryAirports) {
            $fuelScopes->push($scopeFromAirports($countryAirports,$country,null));
            foreach ($countryAirports->filter(fn($airport)=>filled($airport->region))->groupBy('region') as $region=>$regionAirports) $fuelScopes->push($scopeFromAirports($regionAirports,$country,$region));
        }
        $flightFareDisplay=$flightPricing->mapWithKeys(fn($flight)=>[
            (string)$flight->id=>$fareResolver->rows($flight)->map(fn($row)=>[
                'fare_id'=>$row['fare_id'],
                'code'=>$row['fare']->code,
                'name'=>$row['fare']->name,
                'type'=>$row['fare']->type,
                'price'=>$row['current_price'],
                'band'=>$row['band'],
                'ambiguous'=>$row['ambiguous'],
            ])->all(),
        ]);

        return $this->page('economy',['fares'=>Fare::where('active',true)->get(),'airlines'=>Airline::all(),
            'history'=>DB::table('promethee_changes')->orderByDesc('id')->limit(15)->get(),
            'priceHistory'=>DB::table('promethee_price_history')->latest()->limit(120)->get(),
            'pricingRules'=>DB::table('promethee_pricing_rules')->orderBy('priority')->orderByDesc('id')->get(),
            'diagnostics'=>$this->pricingDiagnostics(),'seasons'=>DB::table('promethee_seasons')->orderByDesc('starts_on')->get(['id','name','starts_on','ends_on','active']),
            'subfleets'=>Subfleet::with('airline')->orderBy('name')->get(['id','name','airline_id','type']),
            'preview'=>$r->session()->get('promethee.preview'),'bandSettings'=>$this->bandSettings(),'airportOptions'=>$airportOptions,
            'flightPricing'=>$flightPricing,'flightFareDisplay'=>$flightFareDisplay,'fuelPricing'=>$fuelPricing,'fuelScopes'=>$fuelScopes,'flightFilters'=>$flightFilters,'fuelFilters'=>$fuelFilters,'flightSelectAll'=>$r->boolean('flight_select_all'),'fuelSelectAll'=>$r->boolean('fuel_select_all'),
            'baseFares'=>Fare::whereIn('code',['Y','T','CGO'])->get()->keyBy('code'),'baseFareDefaults'=>$this->fareDefaults(),
            'pricingBands'=>DB::table('promethee_pricing')->get()->mapWithKeys(fn($row)=>[$row->flight_id.'|'.$row->fare_id=>$row->band]),
            'loadFactor'=>(float)(DB::table('promethee_settings')->where('key','pricing.simulation.load_factor')->value('value') ?: 70),
            'countries'=>$airportOptions->pluck('country')->filter()->unique()->sort()->values(),
            'regions'=>$airportOptions->filter(fn($airport)=>filled($airport->region))->map(fn($airport)=>['country'=>$airport->country,'region'=>$airport->region])->unique()->values()]);
    }
    public function flightPriceEditor(Request $r, EconomyFareResolver $fareResolver) {
        $airportOptions=Airport::select('id','icao','name','country','region')->orderBy('country')->orderBy('icao')->get();
        $flightPricing=Flight::where('active',true)->with(['airline','fares','subfleets.fares','dpt_airport','arr_airport'])
            ->orderBy('airline_id')->orderBy('dpt_airport_id')->orderBy('arr_airport_id')->get();
        $networkProfiles = DB::table('promethee_route_pricing_profiles')->pluck('network_class', 'flight_id');
        $flightPricing->each(fn ($flight) => $flight->setAttribute(
            'pricing_network_class',
            $networkProfiles[(string) $flight->id] ?? 'unclassified'
        ));
        $flightFareDisplay=$flightPricing->mapWithKeys(fn($flight)=>[
            (string)$flight->id=>$fareResolver->rows($flight)->map(fn($row)=>[
                'fare_id'=>$row['fare_id'],
                'code'=>$row['fare']->code,
                'name'=>$row['fare']->name,
                'type'=>$row['fare']->type,
                'price'=>$row['current_price'],
                'band'=>$row['band'],
                'ambiguous'=>$row['ambiguous'],
            ])->all(),
        ]);

        return $this->page('economy-flight-prices',[
            'airlines'=>Airline::orderBy('name')->get(),
            'airportOptions'=>$airportOptions,
            'countries'=>$airportOptions->pluck('country')->filter()->unique()->sort()->values(),
            'flightPricing'=>$flightPricing,
            'flightFareDisplay'=>$flightFareDisplay,
            'baseFares'=>Fare::whereIn('code',['Y','T','CGO'])->get()->keyBy('code'),'baseFareDefaults'=>$this->fareDefaults(),
            'pricingBands'=>DB::table('promethee_pricing')->get()->mapWithKeys(fn($row)=>[$row->flight_id.'|'.$row->fare_id=>$row->band]),
            'selectedFlights'=>collect($r->query('flights',[]))->push($r->query('flight'))->filter()->map(fn($id)=>(string)$id)->unique()->values()->all(),
        ]);
    }
    public function flightPriceEdit(string $flight, EconomyFareResolver $fareResolver) {
        $flight=Flight::with(['airline','fares','subfleets.fares','dpt_airport','arr_airport'])->findOrFail($flight);
        $row=$fareResolver->rows($flight)->first();
        $fare=$row['fare'] ?? null;
        $fareCode=$fare?->code ?: ($flight->airline?->icao === 'ICS' ? 'CGO' : ($flight->airline?->icao === 'ACF' ? 'T' : 'Y'));
        $price=$row['current_price'] ?? null;

        return $this->page('economy-flight-price-edit',[
            'flight'=>$flight,
            'fare'=>$fare,
            'fareCode'=>$fareCode,
            'price'=>$price,
            'band'=>$row['band'] ?? 'rouge',
            'redPrice'=>$row['red_price'] ?? $price,
            'fareAmbiguous'=>$row['ambiguous'] ?? false,
        ]);
    }
    public function fuelPriceEdit(Request $r, string $country) {
        $region=$r->query('region');
        $airports=Airport::where('country',strtoupper($country))->when($region,fn($query)=>$query->where('region',$region))->orderBy('icao')->get();
        abort_if($airports->isEmpty(),404);
        $price=function(string $field) use ($airports) {
            $values=$airports->pluck($field)->filter(fn($value)=>$value !== null && $value !== '')->unique()->values();
            return $values->count() === 1 ? (float)$values->first() : null;
        };
        return $this->page('economy-fuel-price-edit',['country'=>strtoupper($country),'region'=>$region,'airportCount'=>$airports->count(),'prices'=>['fuel_jeta_cost'=>$price('fuel_jeta_cost'),'fuel_100ll_cost'=>$price('fuel_100ll_cost'),'fuel_mogas_cost'=>$price('fuel_mogas_cost')]]);
    }
    public function fuelPriceEditor(Request $r) {
        $airports=Airport::select('id','country','region')->orderBy('country')->orderBy('region')->get();
        $scopes=$airports->filter(fn($airport)=>preg_match('/^[A-Z]{2}$/',strtoupper((string)$airport->country)))->groupBy(fn($airport)=>$airport->country.'|'.$airport->region)->map(function($items) {
            $first=$items->first();
            return (object)['key'=>$first->country.'|'.$first->region,'country'=>$first->country,'region'=>$first->region,'count'=>$items->count()];
        })->values();
        return $this->page('economy-fuel-price-editor',['scopes'=>$scopes,'countries'=>$airports->pluck('country')->filter()->unique()->sort()->values(),'regions'=>$airports->map(fn($airport)=>['country'=>$airport->country,'region'=>$airport->region])->filter(fn($row)=>filled($row['region']))->unique(fn($row)=>$row['country'].'|'.$row['region'])->values(),'selectedScopes'=>collect($r->query('scopes',[]))->map(fn($key)=>(string)$key)->all()]);
    }
    private function pricingDiagnostics(): array {
        $alerts=[];
        $defaultFuel=Airport::where(fn($q)=>$q->whereNull('fuel_jeta_cost')->orWhere('fuel_jeta_cost','<=',0))->count();
        if ($defaultFuel) $alerts[]=['level'=>'info','title'=>'Tarif Jet A par défaut','count'=>$defaultFuel,'detail'=>'aéroport(s) utilisent le prix carburant global de phpVMS.'];
        $coordinates=Airport::whereNull('lat')->orWhereNull('lon')->count();
        if ($coordinates) $alerts[]=['level'=>'warning','title'=>'Coordonnées manquantes','count'=>$coordinates,'detail'=>'aéroport(s) ne pourront pas être placés correctement sur les cartes.'];
        $invalidFlights=Flight::where(fn($q)=>$q->whereDoesntHave('dpt_airport')->orWhereDoesntHave('arr_airport'))->count();
        if ($invalidFlights) $alerts[]=['level'=>'danger','title'=>'Lignes à corriger','count'=>$invalidFlights,'detail'=>'ligne(s) référencent un aéroport absent.'];
        $noCabin=Flight::where('active',true)->whereDoesntHave('subfleets.fares')->count();
        if ($noCabin) $alerts[]=['level'=>'warning','title'=>'Cabine manquante','count'=>$noCabin,'detail'=>'ligne(s) actives ne proposent aucune cabine tarifaire.'];
        return $alerts;
    }
    private function bandSettings(): array {
        return ['enabled'=>DB::table('promethee_settings')->where('key','pricing.bands.enabled')->value('value') !== '0','blue'=>(float)(DB::table('promethee_settings')->where('key','pricing.bands.blue')->value('value') ?: 50),'white'=>(float)(DB::table('promethee_settings')->where('key','pricing.bands.white')->value('value') ?: 75)];
    }
    private function fareDefaults(): array {
        return ['Y'=>['name'=>'Air Inter Economy','price'=>218.0], 'T'=>['name'=>'Air Charter International','price'=>352.0], 'CGO'=>['name'=>'Inter Cargo Service','price'=>1.5]];
    }
    public function saveBandSettings(Request $r) {
        $data=$r->validate(['enabled'=>'nullable|boolean','blue'=>'required|numeric|between:1,100','white'=>'required|numeric|between:1,100']);
        foreach (['enabled'=>$r->boolean('enabled') ? '1' : '0','blue'=>(string)$data['blue'],'white'=>(string)$data['white']] as $key=>$value) DB::table('promethee_settings')->updateOrInsert(['key'=>'pricing.bands.'.$key],['value'=>$value,'updated_at'=>now(),'created_at'=>now()]);
        return back()->with('success','Profil Bleu-Blanc-Rouge enregistré.');
    }
    public function saveSimulationSettings(Request $r) {
        $data=$r->validate(['load_factor'=>'required|numeric|between:1,100']);
        DB::table('promethee_settings')->updateOrInsert(['key'=>'pricing.simulation.load_factor'],['value'=>(string)$data['load_factor'],'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Taux de remplissage de simulation enregistré.');
    }
    public function savePricingRule(Request $r) {
        $data=$r->validate(['name'=>'required|string|max:120','target'=>'required|in:ticket,fuel','country'=>'nullable|string|size:2','region'=>'nullable|string|max:191','airline_id'=>'nullable|exists:airlines,id','subfleet_id'=>'nullable|exists:subfleets,id','arrival_country'=>'nullable|string|size:2','distance_min'=>'nullable|numeric|min:0','distance_max'=>'nullable|numeric|gte:distance_min','season_id'=>'nullable|exists:promethee_seasons,id','fare_id'=>'nullable|exists:fares,id','fuel_type'=>'nullable|in:fuel_jeta_cost,fuel_100ll_cost,fuel_mogas_cost','mode'=>'required|in:percent,add,set','value'=>'required|numeric|between:-999999,999999','band'=>'nullable|in:keep,bleu,blanc,rouge','blue_percent'=>'nullable|numeric|between:1,100','white_percent'=>'nullable|numeric|between:1,100','priority'=>'required|integer|between:1,255','active'=>'nullable|boolean']);
        if ($data['target']==='ticket' && empty($data['fare_id'])) return back()->withErrors(['fare_id'=>'Une cabine est requise pour une règle billet.'])->withInput();
        if ($data['target']==='fuel' && empty($data['fuel_type'])) return back()->withErrors(['fuel_type'=>'Un type de carburant est requis.'])->withInput();
        $data['airport_id']=$r->validate(['airport_id'=>'nullable|exists:airports,id'])['airport_id'] ?? null;
        $definition=array_filter($data,fn($value,$key)=>!in_array($key,['name','priority','active'],true) && $value!==null && $value!=='',ARRAY_FILTER_USE_BOTH);
        $definition += ['band'=>'keep','blue_percent'=>$this->bandSettings()['blue'],'white_percent'=>$this->bandSettings()['white'],'fuel_type'=>'fuel_jeta_cost'];
        DB::table('promethee_pricing_rules')->insert(['user_id'=>$r->user()->id,'name'=>$data['name'],'target'=>$data['target'],'definition'=>json_encode($definition),'active'=>$r->boolean('active'),'priority'=>$data['priority'],'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Règle tarifaire enregistrée. Elle ne modifie rien tant que sa simulation n’est pas confirmée.');
    }
    public function updatePricingRule(int $id, Request $r) {
        $data=$r->validate(['name'=>'required|string|max:120','target'=>'required|in:ticket,fuel','country'=>'nullable|string|size:2','region'=>'nullable|string|max:191','airport_id'=>'nullable|exists:airports,id','airline_id'=>'nullable|exists:airlines,id','subfleet_id'=>'nullable|exists:subfleets,id','arrival_country'=>'nullable|string|size:2','distance_min'=>'nullable|numeric|min:0','distance_max'=>'nullable|numeric|gte:distance_min','season_id'=>'nullable|exists:promethee_seasons,id','fare_id'=>'nullable|exists:fares,id','fuel_type'=>'nullable|in:fuel_jeta_cost,fuel_100ll_cost,fuel_mogas_cost','mode'=>'required|in:percent,add,set','value'=>'required|numeric|between:-999999,999999','band'=>'nullable|in:keep,bleu,blanc,rouge','blue_percent'=>'nullable|numeric|between:1,100','white_percent'=>'nullable|numeric|between:1,100','priority'=>'required|integer|between:1,255','active'=>'nullable|boolean']);
        if ($data['target']==='ticket' && empty($data['fare_id'])) return back()->withErrors(['fare_id'=>'Une cabine est requise pour une règle billet.'])->withInput();
        if ($data['target']==='fuel' && empty($data['fuel_type'])) return back()->withErrors(['fuel_type'=>'Un type de carburant est requis.'])->withInput();
        $definition=array_filter($data,fn($value,$key)=>!in_array($key,['name','priority','active'],true) && $value!==null && $value!=='',ARRAY_FILTER_USE_BOTH);
        $definition += ['band'=>'keep','blue_percent'=>$this->bandSettings()['blue'],'white_percent'=>$this->bandSettings()['white'],'fuel_type'=>'fuel_jeta_cost'];
        DB::table('promethee_pricing_rules')->where('id',$id)->update(['name'=>$data['name'],'target'=>$data['target'],'definition'=>json_encode($definition),'active'=>$r->boolean('active'),'priority'=>$data['priority'],'updated_at'=>now()]);
        return back()->with('success','Règle tarifaire mise à jour. Simulez-la avant de publier les changements.');
    }
    public function previewPricingRule(int $id, Request $r, EconomyService $service) {
        $rule=DB::table('promethee_pricing_rules')->where('id',$id)->where('active',true)->first(); abort_unless($rule,404);
        $definition=(array)json_decode($rule->definition,true);
        if (!empty($definition['season_id'])) { $season=DB::table('promethee_seasons')->find($definition['season_id']); abort_unless($season,422,'Saison introuvable.'); abort_if(today()->lt($season->starts_on)||today()->gt($season->ends_on),422,'Cette règle est hors de sa saison active.'); }
        $r->session()->put('promethee.preview',$service->preview($definition)+['expires'=>time()+900,'rule_id'=>$rule->id]);
        DB::table('promethee_pricing_rules')->where('id',$id)->update(['last_previewed_at'=>now(),'updated_at'=>now()]);
        return redirect()->to(route('admin.promethee.economy').'#preview');
    }
    public function deletePricingRule(int $id) { DB::table('promethee_pricing_rules')->where('id',$id)->delete(); return back()->with('success','Règle supprimée.'); }
    public function importPricing(Request $r, EconomyService $service) {
        $data=$r->validate(['pricing_file'=>'required|file|mimes:csv,txt|max:2048']); $handle=fopen($data['pricing_file']->getRealPath(),'r');
        $first=fgets($handle); rewind($handle); $delimiter=substr_count((string)$first,';')>=substr_count((string)$first,',') ? ';' : ','; $header=fgetcsv($handle,0,$delimiter) ?: []; $header=array_map(fn($v)=>strtolower(trim(preg_replace('/^\xEF\xBB\xBF/','',$v))),$header);
        $required=['target','id','field','price']; abort_unless(!array_diff($required,$header),422,'Colonnes requises : target;id;field;price;band (facultatif).'); $items=[]; $line=1;
        while (($row=fgetcsv($handle,0,$delimiter))!==false) { $line++; if (!array_filter($row,fn($v)=>trim((string)$v)!=='')) continue; $raw=array_combine($header,array_pad($row,count($header),null)); $target=strtolower(trim($raw['target'])); $field=trim($raw['field']);
            if (!in_array($target,['fuel','ticket'],true)) abort(422,"Ligne {$line} : target doit être fuel ou ticket.");
            if ($target==='fuel' && !in_array($field,['fuel_jeta_cost','fuel_100ll_cost','fuel_mogas_cost'],true)) abort(422,"Ligne {$line} : champ carburant invalide.");
            if ($target==='ticket' && (!ctype_digit($field) || !in_array(strtolower($raw['band'] ?? 'rouge'),['bleu','blanc','rouge'],true))) abort(422,"Ligne {$line} : field doit être l’ID cabine et band bleu/blanc/rouge.");
            if (!is_numeric($raw['price'])) abort(422,"Ligne {$line} : prix invalide."); $items[$line]=['target'=>$target,'id'=>trim($raw['id']),'field'=>$field,'fare_id'=>$target==='ticket' ? (int)$field : null,'price'=>(float)$raw['price'],'band'=>$target==='ticket' ? strtolower($raw['band'] ?? 'rouge') : null];
        } fclose($handle); $r->session()->put('promethee.preview',$service->importPreview($items)+['expires'=>time()+900]); return redirect()->to(route('admin.promethee.economy').'#preview');
    }
    public function cancelPreview(Request $r) { $r->session()->forget('promethee.preview'); return redirect()->route('admin.promethee.economy')->with('success','Aperçu annulé : aucune modification n’a été appliquée.'); }
    public function mailbox(Request $r) {
        $messages=DB::table('promethee_messages')->where(function ($query) use ($r) {$query->where('recipient_id',$r->user()->id)->orWhere('sender_id',$r->user()->id)->orWhere('shared_staff',true);})->latest()->paginate(30);
        return $this->page('mailbox',['messages'=>$messages,'pilots'=>User::whereIn('state',[UserState::ACTIVE,UserState::ON_LEAVE])->orderBy('pilot_id')->get(['id','name','email','pilot_id']),'activeCount'=>User::where('state',UserState::ACTIVE)->count(),'staffCount'=>User::whereRoleIs('admin')->count()]);
    }
    public function sendMessage(Request $r) {
        $data=$r->validate(['audience'=>'required|in:user,active,staff','user_id'=>'required_if:audience,user|nullable|exists:users,id','subject'=>'required|string|max:191','body'=>'required|string|max:10000','shared_staff'=>'nullable|boolean']);
        $recipients=match($data['audience']) { 'user'=>User::whereKey($data['user_id'])->get(), 'active'=>User::where('state',UserState::ACTIVE)->get(), 'staff'=>User::whereRoleIs('admin')->get() };
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
    public function seasons(Request $r) {
        $seasons = DB::table('promethee_seasons')->orderByDesc('starts_on')->get();
        $seasonAdjustments = DB::table('promethee_season_pricing_adjustments as adjustment')
            ->join('promethee_seasons as season', 'season.id', '=', 'adjustment.season_id')
            ->leftJoin('flights', 'flights.id', '=', 'adjustment.flight_id')
            ->leftJoin('airlines', 'airlines.id', '=', 'flights.airline_id')
            ->select('adjustment.*', 'season.name as season_name', 'season.starts_on', 'season.ends_on',
                'airlines.icao as airline_icao', 'flights.flight_number', 'flights.dpt_airport_id', 'flights.arr_airport_id')
            ->orderByDesc('season.starts_on')->orderBy('adjustment.scope')->orderBy('adjustment.id')->get();
        $seasonFlights = Flight::with('airline')->where('active', true)
            ->orderBy('airline_id')->orderBy('flight_number')->get();

        return $this->page('seasons', compact('seasons', 'seasonAdjustments', 'seasonFlights'));
    }
    public function saveSeason(Request $r) {
        $data=$r->validate(['name'=>'required|string|max:80','starts_on'=>'required|date','ends_on'=>'required|date|after:starts_on','notes'=>'nullable|string|max:2000','active'=>'nullable|boolean']);
        if (!empty($data['active'])) DB::table('promethee_seasons')->update(['active'=>false,'updated_at'=>now()]);
        DB::table('promethee_seasons')->insert($data+['active'=>$r->boolean('active'),'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Saison enregistrée.');
    }
    public function saveSeasonPricingAdjustment(Request $r) {
        $data=$r->validate([
            'season_id'=>'required|exists:promethee_seasons,id',
            'scope'=>'required|in:global,flight',
            'flight_id'=>'nullable|required_if:scope,flight|exists:flights,id',
            'direction'=>'required|in:increase,decrease',
            'mode'=>'required|in:percent,amount',
            'value'=>'required|numeric|min:0.01|max:999999',
            'notes'=>'nullable|string|max:1000',
            'active'=>'nullable|boolean',
        ]);

        DB::table('promethee_season_pricing_adjustments')->insert([
            'season_id'=>(int)$data['season_id'],
            'scope'=>$data['scope'],
            'flight_id'=>$data['scope']==='flight' ? $data['flight_id'] : null,
            'direction'=>$data['direction'],
            'mode'=>$data['mode'],
            'value'=>(float)$data['value'],
            'notes'=>$data['notes'] ?? null,
            'active'=>$r->boolean('active'),
            'created_by'=>$r->user()->id,
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);

        return back()->with('success','Ajustement tarifaire saisonnier enregistré.');
    }
    public function deleteSeasonPricingAdjustment(int $id) {
        DB::table('promethee_season_pricing_adjustments')->where('id',$id)->delete();
        return back()->with('success','Ajustement tarifaire saisonnier supprimé.');
    }
    public function importSchedule(Request $r) {
        $data=$r->validate(['schedule'=>'required|file|mimes:csv,txt|max:5120']);
        $handle=fopen($data['schedule']->getRealPath(),'r'); $header=fgetcsv($handle,0,';') ?: fgetcsv($handle); $header=array_map(fn ($v)=>strtolower(trim($v)),$header ?: []);
        $required=['airline_id','flight_number','departure','arrival']; abort_unless(!array_diff($required,$header),422,'Colonnes CSV requises : airline_id;flight_number;departure;arrival');
        $created=0; $errors=[];
        while (($row=fgetcsv($handle,0,';')) !== false) { $line=array_combine($header,array_pad($row,count($header),null)); try {
            $flight=Flight::firstOrNew(['airline_id'=>(int)$line['airline_id'],'flight_number'=>(int)$line['flight_number'],'dpt_airport_id'=>strtoupper($line['departure']),'arr_airport_id'=>strtoupper($line['arrival'])]);
            $flight->fill(['dpt_time'=>$line['dpt_time']??null,'arr_time'=>$line['arr_time']??null,'route'=>$line['route']??null,'active'=>true,'visible'=>true,'flight_type'=>$line['flight_type']??'J']); $flight->id ??= (string) \Illuminate\Support\Str::uuid(); $flight->save(); $created++;
        } catch (\Throwable $e) { $errors[]='ligne '.($created+count($errors)+2); } }
        fclose($handle); DB::table('promethee_audit_logs')->insert(['actor_id'=>$r->user()->id,'action'=>'schedule.imported','subject_type'=>'schedule','subject_id'=>null,'context'=>json_encode(['created'=>$created,'errors'=>$errors]),'created_at'=>now(),'updated_at'=>now()]); return back()->with('success',"Import : {$created} ligne(s) traitée(s).".(count($errors) ? ' Erreurs : '.implode(', ',$errors) : ''));
    }
    public function changeFlightPrices(Request $r, EconomyFareResolver $fareResolver) {
        $data=$r->validate([
            'flight_ids'=>'required|array|min:1',
            'flight_ids.*'=>'exists:flights,id',
            'mode'=>'required|in:set,add,percent,band',
            'value'=>'nullable|numeric',
            'band'=>'required|in:keep,bleu,blanc,rouge',
        ]);
        if ($data['mode'] !== 'band'
            && (!array_key_exists('value', $data) || $data['value'] === null || $data['value'] === '')) {
            abort(422, 'Une valeur est requise pour modifier le prix Rouge.');
        }

        $bandSettings=$this->bandSettings();
        $multipliers=[
            'bleu'=>$bandSettings['blue']/100,
            'blanc'=>$bandSettings['white']/100,
            'rouge'=>1.0,
        ];

        DB::transaction(function () use ($data,$r,$fareResolver,$multipliers) {
            $flights=Flight::with(['fares','airline','subfleets.fares'])
                ->whereIn('id',$data['flight_ids'])
                ->lockForUpdate()
                ->get();

            foreach ($flights as $flight) {
                foreach ($fareResolver->rows($flight) as $row) {
                    $fare=$row['fare'];
                    $pricing=$row['pricing'];
                    $current=$row['current_price'];

                    if ($current === null && $data['mode'] !== 'set') {
                        abort(422,'La ligne '.$flight->ident.' a plusieurs prix selon la sous-flotte. Fixez d’abord explicitement le prix Rouge pour unifier cette ligne.');
                    }

                    // No Prométhée BBR record means the inherited phpVMS fare is
                    // the RED reference. A colour-only change must never replace
                    // that reference with the global fare default.
                    $red=$pricing ? (float)$pricing->red_price : (float)$current;
                    $newRed=match($data['mode']) {
                        'band' => $red,
                        'set' => (float)$data['value'],
                        'add' => $red+(float)$data['value'],
                        'percent' => $red*(1+(float)$data['value']/100),
                    };

                    $oldBand=$pricing?->band ?? 'rouge';
                    $band=$data['band']==='keep' ? $oldBand : $data['band'];
                    $next=round($newRed*$multipliers[$band],2);

                    if ($newRed < 0 || $next < 0) abort(422,'Un prix ne peut pas être négatif.');

                    DB::table('flight_fare')->updateOrInsert(
                        ['flight_id'=>$flight->id,'fare_id'=>$fare->id],
                        ['price'=>(string)$next,'updated_at'=>now()]
                    );
                    DB::table('promethee_pricing')->updateOrInsert(
                        ['flight_id'=>$flight->id,'fare_id'=>$fare->id],
                        [
                            'red_price'=>round($newRed,2),
                            'band'=>$band,
                            'multiplier'=>$multipliers[$band],
                            'updated_at'=>now(),
                            'created_at'=>$pricing?->created_at ?? now(),
                        ]
                    );
                    DB::table('promethee_price_history')->insert([
                        'target'=>'ticket',
                        'subject_id'=>$flight->id,
                        'field'=>'fare:'.$fare->id,
                        'before_price'=>$current,
                        'after_price'=>$next,
                        'context'=>json_encode([
                            'label'=>$flight->ident,
                            'operation'=>$data['mode'],
                            'value'=>$data['value'] ?? null,
                            'band'=>$band,
                            'red_price'=>$newRed,
                            'source'=>$pricing ? 'promethee_red_reference' : 'phpvms_effective_fare',
                        ]),
                        'created_at'=>now(),
                        'updated_at'=>now(),
                    ]);
                }
            }
        });

        return back()->with('success','Prix des lignes sélectionnées mis à jour.');
    }

    public function changeFuelPrices(Request $r) {
        $data=$r->validate(['country'=>'required_without:scope_keys|nullable|string|size:2','region'=>'nullable|string|max:191','scope_keys'=>'nullable|array|min:1','scope_keys.*'=>'string|max:200','fuel_type'=>'required|in:fuel_jeta_cost,fuel_100ll_cost,fuel_mogas_cost','mode'=>'required|in:set,add,percent','value'=>'required|numeric']);
        $scopes=collect($data['scope_keys'] ?? [strtoupper((string)$data['country']).'|'.($data['region'] ?? '')])->filter(function($key) {
            [$country,$region]=array_pad(explode('|',$key,2),2,'');
            return preg_match('/^[A-Z]{2}$/',strtoupper($country));
        })->map(function($key) {
            [$country,$region]=array_pad(explode('|',$key,2),2,'');
            return ['country'=>strtoupper($country),'region'=>$region];
        })->unique(fn($scope)=>$scope['country'].'|'.$scope['region'])->values();
        abort_if($scopes->isEmpty(),422,'Aucun périmètre carburant valide n’a été sélectionné.');
        $count=DB::transaction(function() use ($data,$scopes) {
            $airports=Airport::where(function($query) use ($scopes) { foreach ($scopes as $scope) $query->orWhere(function($where) use ($scope) { $where->where('country',$scope['country']); if ($scope['region'] !== '') $where->where('region',$scope['region']); }); })->lockForUpdate()->get();
            abort_if($airports->isEmpty(),404);
            $defaults=['fuel_jeta_cost'=>'airports.default_jet_a_fuel_cost','fuel_100ll_cost'=>'airports.default_100ll_fuel_cost','fuel_mogas_cost'=>'airports.default_mogas_fuel_cost'];
            $updated=0;
            foreach ($airports as $airport) {
                $before=$airport->getRawOriginal($data['fuel_type']);
                $current=(float)($before ?: setting($defaults[$data['fuel_type']],0));
                $next=$data['mode']==='set' ? (float)$data['value'] : ($data['mode']==='add' ? $current+(float)$data['value'] : $current*(1+(float)$data['value']/100));
                if ($next<=0) {
                    if ($current<=0 && $data['mode']!=='set') continue;
                    abort(422,'Le prix du carburant doit être supérieur à zéro.');
                }
                DB::table('airports')->where('id',$airport->id)->update([$data['fuel_type']=>round($next,4)]);
                DB::table('promethee_price_history')->insert(['target'=>'fuel','subject_id'=>$airport->id,'field'=>$data['fuel_type'],'before_price'=>$current,'after_price'=>$next,'context'=>json_encode(['label'=>$airport->icao.' — '.$airport->name,'country'=>$airport->country,'region'=>$airport->region,'operation'=>$data['mode'],'value'=>$data['value']]),'created_at'=>now(),'updated_at'=>now()]);
                $updated++;
            }
            return $updated;
        });
        return redirect()->route('admin.promethee.economy')->with('success','Prix carburant mis à jour pour '.$count.' aéroport(s).');
    }
    public function preview(Request $r,EconomyService $service) {
        $d=$r->validate([
            'target'=>'required|in:ticket,fuel','mode'=>'required|in:percent,add,set','value'=>'required|numeric|between:-999999,999999',
            'location'=>'nullable|string|max:191','region'=>'nullable|string|max:191','country'=>'nullable|string|size:2','airport_id'=>'nullable|exists:airports,id',
            'airline_id'=>'nullable|exists:airlines,id','fare_id'=>'required_if:target,ticket|nullable|exists:fares,id',
            'arrival_country'=>'nullable|string|size:2','subfleet_id'=>'nullable|exists:subfleets,id','distance_min'=>'nullable|numeric|min:0','distance_max'=>'nullable|numeric|gte:distance_min','include_inactive'=>'nullable|boolean',
            'fuel_type'=>'required|in:fuel_jeta_cost,fuel_100ll_cost,fuel_mogas_cost',
            'band'=>'required|in:keep,bleu,blanc,rouge','blue_percent'=>'required|numeric|between:1,100','white_percent'=>'required|numeric|between:1,100',
        ]);
        $r->session()->put('promethee.preview',$service->preview($d)+['expires'=>time()+900]);
        return redirect()->to(route('admin.promethee.economy').'#preview');
    }
    public function apply(Request $r,EconomyService $service) {
        $preview=$r->session()->get('promethee.preview');
        abort_unless($preview && $preview['expires']>=time(),419,'Aperçu expiré. Refaire la simulation.');
        $id=$service->apply($preview,$r->user()->id);
        if (!empty($preview['rule_id'])) DB::table('promethee_pricing_rules')->where('id',$preview['rule_id'])->update(['last_applied_at'=>now(),'updated_at'=>now()]);
        $r->session()->forget('promethee.preview');
        return redirect()->route('admin.promethee.economy')->with('success','Modification nº '.$id.' appliquée. Les anciens PIREP n’ont pas été recalculés.');
    }
    public function revert(int $id,EconomyService $service) {
        $service->revert($id);
        return back()->with('success','Tarifs précédents restaurés.');
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

    public function adminMissions() {
        return $this->page('admin-missions', [
            'missions'=>DB::table('promethee_missions')->orderByDesc('created_at')->get(),
            'circuits'=>DB::table('promethee_circuits')->orderByDesc('created_at')->get()->map(function($circuit){ $circuit->legs=DB::table('promethee_circuit_legs')->where('circuit_id',$circuit->id)->orderBy('position')->get(); return $circuit; }),
            'flights'=>Flight::where('active',true)->where('visible',true)->orderBy('route_code')->orderBy('flight_number')->limit(200)->get(['id','route_code','flight_number','dpt_airport_id','arr_airport_id']),
        ]);
    }
    public function saveMission(Request $r) {
        $data=$r->validate(['title'=>'required|string|max:160','description'=>'nullable|string|max:5000','dpt_airport_id'=>'nullable|string|max:8','arr_airport_id'=>'nullable|string|max:8','flight_id'=>'nullable|string|max:36','starts_on'=>'nullable|date','ends_on'=>'nullable|date|after_or_equal:starts_on','active'=>'nullable|boolean']);
        DB::table('promethee_missions')->insert(['created_by'=>$r->user()->id,'title'=>$data['title'],'description'=>$data['description'] ?? null,'dpt_airport_id'=>strtoupper($data['dpt_airport_id'] ?? '') ?: null,'arr_airport_id'=>strtoupper($data['arr_airport_id'] ?? '') ?: null,'flight_id'=>$data['flight_id'] ?? null,'starts_on'=>$data['starts_on'] ?? null,'ends_on'=>$data['ends_on'] ?? null,'active'=>$r->boolean('active'),'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Mission publiée.');
    }
    public function deleteMission(int $id) { DB::table('promethee_missions')->where('id',$id)->delete(); return back()->with('success','Mission supprimée.'); }
    public function saveCircuit(Request $r) {
        $data=$r->validate(['title'=>'required|string|max:160','description'=>'nullable|string|max:5000','starts_on'=>'nullable|date','ends_on'=>'nullable|date|after_or_equal:starts_on','active'=>'nullable|boolean','legs'=>'required|array|min:1|max:30','legs.*.departure'=>'required|string|max:8','legs.*.arrival'=>'required|string|max:8','legs.*.flight_id'=>'nullable|string|max:36']);
        $id=DB::table('promethee_circuits')->insertGetId(['created_by'=>$r->user()->id,'title'=>$data['title'],'description'=>$data['description'] ?? null,'starts_on'=>$data['starts_on'] ?? null,'ends_on'=>$data['ends_on'] ?? null,'active'=>$r->boolean('active'),'created_at'=>now(),'updated_at'=>now()]);
        foreach($data['legs'] as $position=>$leg) DB::table('promethee_circuit_legs')->insert(['circuit_id'=>$id,'position'=>$position+1,'dpt_airport_id'=>strtoupper($leg['departure']),'arr_airport_id'=>strtoupper($leg['arrival']),'flight_id'=>$leg['flight_id'] ?? null,'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Circuit publié.');
    }
    public function deleteCircuit(int $id) { DB::table('promethee_circuit_legs')->where('circuit_id',$id)->delete(); DB::table('promethee_circuits')->where('id',$id)->delete(); return back()->with('success','Circuit supprimé.'); }
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
    /** Prométhée-native airline catalogue; legacy phpVMS URLs remain valid. */
    public function adminAirlines(Request $r) {
        $filters=$r->validate(['q'=>'nullable|string|max:80','active'=>'nullable|in:all,active,inactive']);
        $airlines=Airline::query()->when($filters['q'] ?? null, fn($query,$q)=>$query->where(fn($nested)=>$nested->where('name','like','%'.$q.'%')->orWhere('icao','like','%'.$q.'%')->orWhere('iata','like','%'.$q.'%')->orWhere('callsign','like','%'.$q.'%')))->when(($filters['active'] ?? 'all') !== 'all', fn($query)=>$query->where('active',($filters['active'] ?? '')==='active'))->orderBy('name')->get();
        $accessRules = DB::table('promethee_airline_access_rules')->pluck('min_flight_hours', 'airline_id');
        $airlines->each(fn (Airline $airline) => $airline->setAttribute('min_flight_hours', (int) ($accessRules[$airline->id] ?? 0)));
        return $this->page('admin-airlines', ['airlines'=>$airlines,'countries'=>Countries::getSelectList()]);
    }
    public function saveAdminAirline(Request $r) {
        $data=$r->validate(['id'=>'nullable|integer|exists:airlines,id','icao'=>'required|string|max:5','iata'=>'nullable|string|max:5','name'=>'required|string|max:191','callsign'=>'nullable|string|max:191','logo'=>'nullable|url|max:2000','country'=>'nullable|string|size:2','active'=>'nullable|boolean','min_flight_hours'=>'nullable|integer|min:0|max:100000']);
        $attributes=collect($data)->except(['id','min_flight_hours'])->all(); $attributes['active']=$r->boolean('active');
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

    public function adminMaintenance(AirframeMaintenanceService $airframeService) {
        abort_unless(Schema::hasTable('promethee_engine_profiles'), 503, 'Migration maintenance moteur non appliquée.');
        abort_unless(Schema::hasTable('promethee_airframe_maintenance'), 503, 'Migration maintenance cellule non appliquée.');

        $profiles = DB::table('promethee_engine_profiles as profile')
            ->join('subfleets as subfleet', 'subfleet.id', '=', 'profile.subfleet_id')
            ->leftJoin('airlines as airline', 'airline.id', '=', 'subfleet.airline_id')
            ->select('profile.*', 'subfleet.name as subfleet_name', 'subfleet.type as subfleet_type', 'airline.icao as airline_icao')
            ->orderBy('airline.icao')->orderBy('subfleet.name')->get();

        $subfleets = Subfleet::with('airline')->orderBy('name')->get();
        $aircraft = Aircraft::with('subfleet.airline')->orderBy('registration')->get();

        $airframeSettings = $airframeService->settings();
        $airframeStates = $airframeService->fleetStatus();
        $airframeSummary = [
            'total' => $airframeStates->count(),
            'serviceable' => $airframeStates->where('maintenance_state', 'serviceable')->count(),
            'warning' => $airframeStates->where('maintenance_state', 'warning')->count(),
            'due' => $airframeStates->where('maintenance_state', 'due')->count(),
            'maintenance' => $airframeStates->where('maintenance_state', 'maintenance')->count(),
        ];
        $airframeEvents = DB::table('promethee_airframe_maintenance_events as event')
            ->join('aircraft', 'aircraft.id', '=', 'event.aircraft_id')
            ->select('event.*', 'aircraft.registration')
            ->latest('event.occurred_at')->limit(30)->get();

        $engineUnits = DB::table('promethee_engines as engine')
            ->join('promethee_engine_profiles as profile', 'profile.id', '=', 'engine.engine_profile_id')
            ->join('subfleets as subfleet', 'subfleet.id', '=', 'profile.subfleet_id')
            ->leftJoin('airlines as airline', 'airline.id', '=', 'subfleet.airline_id')
            ->leftJoin('promethee_aircraft_engines as installation', 'installation.engine_id', '=', 'engine.id')
            ->leftJoin('aircraft as aircraft', 'aircraft.id', '=', 'installation.aircraft_id')
            ->select([
                'engine.*',
                'profile.subfleet_id',
                'profile.warning_hours',
                'profile.warning_cycles',
                'subfleet.name as subfleet_name',
                'airline.icao as airline_icao',
                'installation.aircraft_id',
                'installation.position',
                'aircraft.registration',
                'aircraft.airport_id',
            ])
            ->orderBy('airline.icao')->orderBy('subfleet.name')->orderBy('aircraft.registration')->orderBy('installation.position')->orderBy('engine.serial_number')->get()
            ->map(function ($engine) {
                $engine->remaining_hours = $engine->tbo_hours !== null
                    ? round((float) $engine->tbo_hours - (float) $engine->hours_since_overhaul, 2)
                    : null;
                $engine->remaining_cycles = $engine->tbo_cycles !== null
                    ? (int) $engine->tbo_cycles - (int) $engine->cycles_since_overhaul
                    : null;

                $hoursPotential = $engine->tbo_hours !== null && (float) $engine->tbo_hours > 0
                    ? max(0, min(100, round(($engine->remaining_hours / (float) $engine->tbo_hours) * 100, 1)))
                    : null;
                $cyclesPotential = $engine->tbo_cycles !== null && (int) $engine->tbo_cycles > 0
                    ? max(0, min(100, round(($engine->remaining_cycles / (int) $engine->tbo_cycles) * 100, 1)))
                    : null;

                $engine->potential_hours_percent = $hoursPotential;
                $engine->potential_cycles_percent = $cyclesPotential;
                $availablePotentials = array_values(array_filter([$hoursPotential, $cyclesPotential], fn ($value) => $value !== null));
                $engine->potential_percent = $availablePotentials ? min($availablePotentials) : null;
                $engine->potential_basis = $hoursPotential !== null && $cyclesPotential !== null
                    ? ($hoursPotential <= $cyclesPotential ? 'heures' : 'cycles')
                    : ($hoursPotential !== null ? 'heures' : ($cyclesPotential !== null ? 'cycles' : null));

                return $engine;
            });

        $engineSummary = [
            'total' => $engineUnits->count(),
            'installed' => $engineUnits->whereNotNull('aircraft_id')->count(),
            'stock' => $engineUnits->whereNull('aircraft_id')->count(),
            'serviceable' => $engineUnits->where('status', 'serviceable')->count(),
            'warning' => $engineUnits->where('status', 'warning')->count(),
            'due' => $engineUnits->where('status', 'due')->count(),
        ];

        $engineSites = DB::table('promethee_operational_bases')
            ->where('active', true)->where('engine_overhaul', true)->orderBy('airport_id')->get();

        $engineEvents = DB::table('promethee_engine_maintenance_events as event')
            ->join('promethee_engines as engine', 'engine.id', '=', 'event.engine_id')
            ->leftJoin('aircraft as aircraft', 'aircraft.id', '=', 'event.aircraft_id')
            ->select('event.*', 'engine.serial_number', 'aircraft.registration')
            ->latest('event.occurred_at')->limit(30)->get();

        return $this->page('admin-maintenance', compact(
            'profiles','subfleets','aircraft','engineUnits','engineSites','engineEvents','engineSummary',
            'airframeSettings','airframeStates','airframeSummary','airframeEvents'
        ));
    }

    public function saveAirframeMaintenanceSettings(Request $r, AirframeMaintenanceService $airframeService) {
        $data = $r->validate([
            'a_time_limit_hours'=>'required|numeric|min:0.1|max:100000',
            'a_cycle_limit'=>'required|integer|min:1|max:100000',
            'a_duration_hours'=>'required|numeric|min:0|max:10000',
            'b_time_limit_hours'=>'required|numeric|min:0.1|max:100000',
            'b_cycle_limit'=>'required|integer|min:1|max:100000',
            'b_duration_hours'=>'required|numeric|min:0|max:10000',
            'c_time_limit_hours'=>'required|numeric|min:0.1|max:100000',
            'c_cycle_limit'=>'required|integer|min:1|max:100000',
            'c_duration_hours'=>'required|numeric|min:0|max:10000',
            'warning_percent'=>'required|numeric|min:0|max:100',
        ]);

        $airframeService->saveSettings($data);

        return back()->with('success','Limites heures/cycles et durées des checks A/B/C enregistrées.');
    }

    public function startAirframeCheck(int $aircraft, Request $r, AirframeMaintenanceService $airframeService) {
        $data = $r->validate(['check'=>'required|in:a,b,c']);
        try {
            $airframeService->startCheck($aircraft, $data['check'], (int) $r->user()->id);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['airframe'=>$exception->getMessage()]);
        }

        return back()->with('success',strtoupper($data['check']).' Check démarré ; l’appareil est immobilisé pendant la durée configurée.');
    }

    public function syncEngineFleet(EngineMaintenanceService $engineService) {
        $references = (array) config('promethee.engine-profiles', []);
        $createdProfiles = 0;

        Subfleet::with('airline')->orderBy('id')->get()->each(function (Subfleet $subfleet) use ($references, &$createdProfiles) {
            $airlineIcao = strtoupper((string) ($subfleet->airline?->icao ?: ''));
            $referenceKey = $airlineIcao.'|'.(string) $subfleet->type;
            $reference = $references[$referenceKey] ?? null;

            if (!$reference || DB::table('promethee_engine_profiles')->where('subfleet_id', $subfleet->id)->exists()) {
                return;
            }

            DB::table('promethee_engine_profiles')->insert([
                'subfleet_id' => $subfleet->id,
                'engine_type' => (string) $reference['engine_type'],
                'engine_count' => (int) $reference['engine_count'],
                'tbo_hours' => $reference['tbo_hours'] ?? null,
                'tbo_cycles' => $reference['tbo_cycles'] ?? null,
                'warning_hours' => (float) ($reference['warning_hours'] ?? 100),
                'warning_cycles' => $reference['warning_cycles'] ?? null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $createdProfiles++;
        });

        $profileIds = DB::table('promethee_engine_profiles')->where('active', true)->pluck('subfleet_id');
        $synced = 0;
        foreach ($profileIds as $subfleetId) {
            $synced += $engineService->syncSubfleet((int) $subfleetId);
        }

        return back()->with(
            'success',
            $createdProfiles.' profil(s) moteur créé(s) depuis le référentiel · '
            .$synced.' position(s) moteur vérifiée(s) / synchronisée(s).'
        );
    }

    public function saveEngineProfile(Request $r, EngineMaintenanceService $engineService) {
        $data = $r->validate([
            'subfleet_id'=>'required|integer|exists:subfleets,id',
            'engine_type'=>'required|string|max:80',
            'engine_count'=>'required|integer|min:1|max:4',
            'tbo_hours'=>'nullable|numeric|min:1|max:100000',
            'tbo_cycles'=>'nullable|integer|min:1|max:100000',
            'warning_hours'=>'required|numeric|min:0|max:10000',
            'warning_cycles'=>'nullable|integer|min:0|max:10000',
            'active'=>'nullable|boolean',
        ]);

        if (!$r->filled('tbo_hours') && !$r->filled('tbo_cycles')) {
            return back()->withErrors(['tbo_hours'=>'Renseignez au moins une limite TBO en heures ou en cycles.'])->withInput();
        }

        DB::table('promethee_engine_profiles')->updateOrInsert(
            ['subfleet_id'=>$data['subfleet_id']],
            [
                'engine_type'=>trim($data['engine_type']),
                'engine_count'=>$data['engine_count'],
                'tbo_hours'=>$data['tbo_hours'] ?? null,
                'tbo_cycles'=>$data['tbo_cycles'] ?? null,
                'warning_hours'=>$data['warning_hours'],
                'warning_cycles'=>$data['warning_cycles'] ?? null,
                'active'=>$r->boolean('active'),
                'created_at'=>now(),
                'updated_at'=>now(),
            ]
        );

        $engineService->syncSubfleet((int) $data['subfleet_id']);

        return back()->with('success','Profil moteur enregistré et flotte correspondante synchronisée.');
    }

    public function createEngineUnit(Request $r, EngineMaintenanceService $engineService) {
        $data = $r->validate([
            'engine_profile_id'=>'required|integer|exists:promethee_engine_profiles,id',
            'serial_number'=>'required|string|max:96|unique:promethee_engines,serial_number',
            'hours_since_overhaul'=>'nullable|numeric|min:0|max:100000',
            'cycles_since_overhaul'=>'nullable|integer|min:0|max:100000',
        ]);

        $profile = DB::table('promethee_engine_profiles')->where('id',$data['engine_profile_id'])->first();
        abort_unless($profile,404);

        $engineId=DB::table('promethee_engines')->insertGetId([
            'engine_profile_id'=>$profile->id,
            'serial_number'=>strtoupper(trim($data['serial_number'])),
            'engine_type'=>$profile->engine_type,
            'tbo_hours'=>$profile->tbo_hours,
            'tbo_cycles'=>$profile->tbo_cycles,
            'hours_since_overhaul'=>$data['hours_since_overhaul'] ?? 0,
            'cycles_since_overhaul'=>$data['cycles_since_overhaul'] ?? 0,
            'status'=>'serviceable',
            'last_overhaul_at'=>null,
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);
        $engineService->refreshStatus((int)$engineId);

        return back()->with('success','Moteur ajouté au stock.');
    }

    public function overhaulEngine(int $engine, Request $r, EngineMaintenanceService $engineService) {
        $data=$r->validate(['notes'=>'nullable|string|max:2000']);
        try {
            $engineService->overhaul($engine, (int) $r->user()->id, $data['notes'] ?? null);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['engine'=>$exception->getMessage()]);
        }
        return back()->with('success','Révision moteur enregistrée ; TBO et cycles remis à zéro.');
    }

    public function installEngine(int $engine, Request $r, EngineMaintenanceService $engineService) {
        $data=$r->validate([
            'aircraft_id'=>'required|integer|exists:aircraft,id',
            'position'=>'required|integer|min:1|max:4',
        ]);
        try {
            $engineService->install($engine, (int) $data['aircraft_id'], (int) $data['position'], (int) $r->user()->id);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['engine'=>$exception->getMessage()]);
        }
        return back()->with('success','Moteur installé ; l’ancien moteur de la position est revenu au stock.');
    }

    public function regionalOperations(Request $r, RegionalOperationsService $operations, FleetRotationService $rotation) {
        $operations->sync();
        $bases = DB::table('promethee_operational_bases as base')
            ->leftJoin('airports', 'airports.id', '=', 'base.airport_id')
            ->select('base.*', 'airports.name as airport_name')
            // Keep this prefix-aware: phpVMS prefixes table aliases as well
            // (e.g. "base" becomes "phpvms7_base"), while raw SQL does not.
            // The only supported kinds are "hub" and "regional", so the
            // query builder can safely sort the wrapped alias directly.
            ->orderBy('base.kind')
            ->orderBy('base.airport_id')->get();
        $aircraft = Aircraft::with('subfleet.airline')->orderBy('registration')->get();
        $assignments = DB::table('promethee_aircraft_bases')->get()->keyBy('aircraft_id');
        $settings = $operations->settings();
        $rotationSettings = $rotation->settings();
        $rotationLog = Schema::hasTable('promethee_fleet_rotation_log')
            ? DB::table('promethee_fleet_rotation_log as rotation')
                ->join('aircraft as first_aircraft', 'first_aircraft.id', '=', 'rotation.first_aircraft_id')
                ->join('aircraft as second_aircraft', 'second_aircraft.id', '=', 'rotation.second_aircraft_id')
                ->select('rotation.*', 'first_aircraft.registration as first_registration', 'second_aircraft.registration as second_registration')
                ->latest('rotation.rotated_at')->limit(20)->get()
            : collect();
        return $this->page('admin-regional-operations', compact('bases','aircraft','assignments','settings','rotationSettings','rotationLog'));
    }

    public function saveRegionalOperations(Request $r) {
        $data = $r->validate([
            'mission_after_days'=>'required|integer|min:1|max:365',
            'auto_return_after_days'=>'required|integer|min:2|max:730|gt:mission_after_days',
            'reward_multiplier'=>'required|numeric|min:1|max:10',
        ]);
        foreach ([
            'regional.repatriation_mission_after_days'=>$data['mission_after_days'],
            'regional.auto_return_after_days'=>$data['auto_return_after_days'],
            'regional.repatriation_reward_multiplier'=>$data['reward_multiplier'],
        ] as $key=>$value) {
            DB::table('promethee_settings')->updateOrInsert(['key'=>$key],['value'=>(string)$value,'created_at'=>now(),'updated_at'=>now()]);
        }
        return back()->with('success','Règles de rapatriement enregistrées.');
    }

    public function saveRegionalBase(Request $r) {
        $data=$r->validate([
            'airport_id'=>'required|string|max:8|exists:airports,id',
            'is_hub'=>'nullable|boolean',
            'is_regional_platform'=>'nullable|boolean',
            'is_technical_stop'=>'nullable|boolean',
            'active'=>'nullable|boolean',
            'engine_overhaul'=>'nullable|boolean',
        ]);

        $airportId=strtoupper($data['airport_id']);
        $isHub=$r->boolean('is_hub');
        $isRegional=$r->boolean('is_regional_platform');
        $isTechnical=$r->boolean('is_technical_stop');

        if (!$isHub && !$isRegional && !$isTechnical) {
            return back()->withErrors(['airport_id'=>'Sélectionnez au moins un rôle : hub, plateforme régionale ou escale technique.'])->withInput();
        }
        if ($isHub && $airportId !== 'LFPO') {
            return back()->withErrors(['airport_id'=>'Orly (LFPO) reste le seul hub principal Air Inter VA. CDG dispose des checks A/B/C sans être déclaré hub.'])->withInput();
        }

        // Maintenance policy requested by Flight Operations:
        // - every operating/technical station may perform an A CHECK;
        // - Orly (hub) and Paris-CDG may perform A/B/C;
        // - a technical stop never gains B/C merely because of that role.
        $fullChecks=in_array($airportId,['LFPO','LFPG'],true);
        $checkA=true;
        $checkB=$fullChecks;
        $checkC=$fullChecks;

        if ($airportId === 'LFPO') {
            $isHub=true;
            DB::table('promethee_operational_bases')
                ->where('airport_id','!=','LFPO')
                ->update(['is_hub'=>false,'updated_at'=>now()]);
        }

        DB::table('promethee_operational_bases')->updateOrInsert(
            ['airport_id'=>$airportId],
            [
                // Keep the legacy column for older code while multi-role flags
                // are the new source of truth.
                'kind'=>$isHub ? 'hub' : 'regional',
                'is_hub'=>$isHub,
                'is_regional_platform'=>$isRegional,
                'is_technical_stop'=>$isTechnical,
                'check_a'=>$checkA,
                'check_b'=>$checkB,
                'check_c'=>$checkC,
                'engine_overhaul'=>$r->boolean('engine_overhaul'),
                'small_maintenance'=>$checkA,
                'heavy_maintenance'=>$checkB || $checkC,
                'active'=>$r->boolean('active'),
                'created_at'=>now(),'updated_at'=>now(),
            ]
        );
        return back()->with('success','Rôles et capacités de maintenance enregistrés.');
    }

    public function assignAircraftBase(Request $r) {
        $data=$r->validate(['aircraft_id'=>'required|integer|exists:aircraft,id','base_airport_id'=>'required|string|max:8|exists:promethee_operational_bases,airport_id','rotation_locked'=>'nullable|boolean']);
        $aircraft=Aircraft::findOrFail($data['aircraft_id']);
        $assignment=DB::table('promethee_aircraft_bases')->where('aircraft_id',$aircraft->id)->first();
        $newBase=strtoupper($data['base_airport_id']);
        $baseChanged=!$assignment || strtoupper((string)$assignment->base_airport_id) !== $newBase;

        $values=[
            'base_airport_id'=>$newBase,
            'rotation_locked'=>$r->boolean('rotation_locked'),
            'updated_at'=>now(),
        ];

        if (!$assignment) {
            $values += ['assigned_at'=>now(),'away_since'=>null,'repatriation_mission_id'=>null,'created_at'=>now()];
        } elseif ($baseChanged) {
            if ($assignment->repatriation_mission_id) {
                DB::table('promethee_missions')->where('id',$assignment->repatriation_mission_id)->update(['active'=>false,'updated_at'=>now()]);
            }
            $current=strtoupper((string)$aircraft->airport_id);
            $values += [
                'assigned_at'=>now(),
                'away_since'=>$current !== '' && $current !== $newBase ? ($aircraft->landing_time ?: now()) : null,
                'repatriation_mission_id'=>null,
            ];
        }

        DB::table('promethee_aircraft_bases')->updateOrInsert(['aircraft_id'=>$aircraft->id],$values);
        if ($baseChanged) {
            $aircraft->update(['hub_id'=>$newBase]);
        }
        return back()->with('success',$baseChanged ? 'Base de l’appareil mise à jour.' : 'Préférence de rotation mise à jour.');
    }
    public function syncRegionalRepatriations(RegionalOperationsService $operations) {
        $result = $operations->sync(true);
        return back()->with(
            'success',
            $result['created'].' mission(s) de rapatriement créée(s), '
            .$result['cleared'].' situation(s) régularisée(s), '
            .$result['returned'].' retour(s) automatique(s).'
        );
    }

    public function saveFleetRotationSettings(Request $r) {
        $data = $r->validate([
            'enabled'=>'nullable|boolean',
            'frequency'=>'required|in:daily,weekly',
            'percent'=>'required|integer|min:0|max:100',
            'min_idle_hours'=>'required|integer|min:0|max:720',
            'cooldown_days'=>'required|integer|min:0|max:365',
            'maintenance_bias_hours'=>'required|integer|min:0|max:5000',
            'airframe_bias_percent'=>'required|numeric|min:0|max:100',
        ]);

        foreach ([
            'regional.rotation_enabled'=>$r->boolean('enabled') ? '1' : '0',
            'regional.rotation_frequency'=>$data['frequency'],
            'regional.rotation_percent'=>$data['percent'],
            'regional.rotation_min_idle_hours'=>$data['min_idle_hours'],
            'regional.rotation_cooldown_days'=>$data['cooldown_days'],
            'regional.rotation_maintenance_bias_hours'=>$data['maintenance_bias_hours'],
            'regional.rotation_airframe_bias_percent'=>$data['airframe_bias_percent'],
        ] as $key=>$value) {
            DB::table('promethee_settings')->updateOrInsert(
                ['key'=>$key],
                ['value'=>(string)$value,'created_at'=>now(),'updated_at'=>now()]
            );
        }

        return back()->with('success','Règles de rotation automatique enregistrées.');
    }

    public function runFleetRotation(FleetRotationService $rotation) {
        $result = $rotation->rotate(true);
        return back()->with(
            'success',
            $result['pairs'].' permutation(s) effectuée(s), '
            .$result['aircraft'].' appareil(s) déplacé(s), '
            .$result['maintenance_priority'].' priorité(s) moteur, '
            .$result['airframe_maintenance_priority'].' priorité(s) cellule. ['.$result['reason'].']'
        );
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
