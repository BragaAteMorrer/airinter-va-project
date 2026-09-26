<?php
namespace Modules\Promethee\Http;
use App\Contracts\Controller;
use App\Models\{Aircraft,Airline,Airport,Award,Bid,File,Flight,Pirep,SimBrief,User,Fare,Subfleet,Rank};
use App\Models\Enums\{AircraftState,AircraftStatus,FlightType,PirepState,PirepStatus,UserState};
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File as Filesystem;
use Illuminate\Support\Facades\Hash;
use App\Services\AirportService;
use App\Services\FinanceService;
use App\Services\FileService;
use App\Services\UserService;
use App\Support\Money;
use App\Support\Countries;
use Modules\Promethee\Services\{BrandingService,BulletinService,CompanyAccessService,DemandProfileService,EconomyFareResolver,EconomyService,FlightOpsService,RegionalOperationsService,SafetyAnalyzer};

class PortalController extends Controller
{
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
    public function pirep(string $id) {
        $pirep = Pirep::with([
            'acars', 'acars_logs', 'acars_route', 'aircraft.airline', 'airline',
            'arr_airport', 'dpt_airport', 'alt_airport', 'fares', 'field_values',
            'flight', 'simbrief', 'user.rank', 'comments.user',
        ])->findOrFail($id);

        return $this->page('pirep', ['pirep' => $pirep]);
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

        return $this->page('maintenance', compact('maintenance', 'upcomingMaintenance', 'warningHours', 'warningCycles'));
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
        $stats = Pirep::where('aircraft_id', $aircraft->id)->where('state', PirepState::ACCEPTED)
            ->selectRaw('COUNT(*) as pireps, COALESCE(SUM(flight_time), 0) as flight_minutes, COALESCE(SUM(fuel_used), 0) as fuel_used, COALESCE(SUM(distance), 0) as distance, AVG(landing_rate) as landing_rate')
            ->first();
        $downloads = $aircraft->files->concat($aircraft->subfleet?->files ?? collect())->unique('id')->values();

        return $this->page('aircraft', compact('aircraft', 'pireps', 'maintenance', 'stats', 'downloads'));
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
            $operatesThatDay = $days === 0 || ($days & (1 << $parisDeparture->dayOfWeek));

            if ($operatesThatDay && $parisDeparture->greaterThan($now)) return $parisDeparture;
        }

        return null;
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

    private function boardStatus(?Pirep $pirep): string
    {
        if (!$pirep) return 'scheduled';
        if ($pirep->state === PirepState::CANCELLED || $pirep->status === PirepStatus::CANCELLED) return 'cancelled';

        return match ($pirep->status) {
            PirepStatus::BOARDING => 'boarding',
            PirepStatus::DEPARTED, PirepStatus::PUSHBACK_TOW, PirepStatus::TAXI => 'departed',
            PirepStatus::TAKEOFF, PirepStatus::INIT_CLIM, PirepStatus::AIRBORNE,
            PirepStatus::ENROUTE, PirepStatus::DIVERTED, PirepStatus::APPROACH,
            PirepStatus::APPROACH_ICAO, PirepStatus::ON_FINAL => 'en_route',
            PirepStatus::LANDING, PirepStatus::LANDED, PirepStatus::ON_BLOCK,
            PirepStatus::ARRIVED => 'landed',
            default => 'scheduled',
        };
    }

    private function airportCode(?Airport $airport, string $fallback): string
    {
        return strtoupper((string) ($airport?->iata ?: $airport?->icao ?: $fallback));
    }

    private function airportDestination(?Airport $airport, string $fallback): string
    {
        return strtoupper((string) ($airport?->location ?: $airport?->name ?: $airport?->iata ?: $airport?->icao ?: $fallback));
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
    private function departureBoardFlights(?string $homeAirportId): \Illuminate\Support\Collection
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
            ->when($homeAirportId, fn ($flights) => $flights->where('dpt_airport_id', $homeAirportId))
            ->with(['airline', 'dpt_airport', 'arr_airport'])->get()
            ->flatMap(function (Flight $flight) use ($now, $activePireps) {
                $rows = collect();
                // Include the next week so the empty-window fallback can use
                // the same dated, operation-day-aware source.
                for ($offset = -1; $offset <= 7; $offset++) {
                    $date = $now->addDays($offset);
                    $departure = $this->scheduledDateTime((string) $flight->dpt_time, $date);
                    if (!$departure || (($flight->days ?? 0) !== 0 && !($flight->days & (1 << $departure->dayOfWeek)))) continue;

                    $arrival = $this->scheduledDateTime((string) $flight->arr_time, $date);
                    if ($arrival && $arrival->lte($departure)) $arrival = $arrival->addDay();
                    $pirep = $activePireps->get($flight->id);
                    $row = clone $flight;
                    $row->setAttribute('board_departure_at', $departure->setTimezone('Europe/Paris'));
                    $row->setAttribute('board_departure_time', $departure->setTimezone('Europe/Paris')->format('H:i'));
                    $row->setAttribute('board_arrival_time', $arrival?->setTimezone('Europe/Paris')->format('H:i') ?? '----');
                    $row->setAttribute('board_departure_airport', $this->airportDestination($flight->dpt_airport, $flight->dpt_airport_id));
                    $row->setAttribute('board_destination', $this->airportDestination($flight->arr_airport, $flight->arr_airport_id));
                    $row->setAttribute('board_status', $this->boardStatus($pirep));
                    $row->setAttribute('board_logo_url', $this->airlineLogoUrl($flight->airline));
                    $row->setAttribute('board_airline_code', strtoupper((string) ($flight->airline?->code ?: $flight->airline?->callsign ?: '---')));
                    $rows->push($row);
                }
                return $rows;
            })->sortBy('board_departure_at')->values();

        $inWindow = $occurrences->filter(fn (Flight $flight) => $flight->board_departure_at->betweenIncluded($from, $until));
        if ($inWindow->isNotEmpty() || !config('departure-board.future_fallback')) {
            return $inWindow->take(config('departure-board.max_rows'))->values();
        }

        return $occurrences->filter(fn (Flight $flight) => $flight->board_departure_at->gt($until))
            ->take(config('departure-board.max_rows'))->values();
    }

    private function departureBoardPayload(?string $homeAirportId): array
    {
        return $this->departureBoardFlights($homeAirportId)->map(fn (Flight $flight) => [
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

        $base = fn () => DB::table('pireps as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->where('p.state', PirepState::ACCEPTED)
            ->where('p.submitted_at', '>=', $monthStart)
            ->where('p.submitted_at', '<', $monthEnd)
            ->whereNotNull('p.user_id');

        $groupPilot = function ($query) {
            return $query
                ->groupBy('p.user_id', 'u.name', 'u.pilot_id');
        };

        $flights = $groupPilot(
            $base()->select([
                'p.user_id as user_id',
                'u.name as name',
                'u.pilot_id as pilot_id',
            ])->selectRaw('COUNT(*) as value')
        )->orderByDesc('value')->limit(5)->get();

        $block = $groupPilot(
            $base()->select([
                'p.user_id as user_id',
                'u.name as name',
                'u.pilot_id as pilot_id',
            ])->selectRaw('SUM(COALESCE(NULLIF(p.block_time, 0), p.flight_time, 0)) as value')
        )->orderByDesc('value')->limit(5)->get();

        $softest = $groupPilot(
            $base()
                ->whereNotNull('p.landing_rate')
                ->where('p.landing_rate', '<', 0)
                ->select([
                    'p.user_id as user_id',
                    'u.name as name',
                    'u.pilot_id as pilot_id',
                ])
                ->selectRaw('MAX(p.landing_rate) as value')
        )->orderByDesc('value')->limit(5)->get();

        $distance = $groupPilot(
            $base()
                ->whereNotNull('p.distance')
                ->select([
                    'p.user_id as user_id',
                    'u.name as name',
                    'u.pilot_id as pilot_id',
                ])
                ->selectRaw('SUM(p.distance) as raw_value')
        )->orderByDesc('raw_value')->limit(5)->get()
            ->map(function ($row) {
                $row->value = \App\Support\Units\Distance::make(
                    (float) $row->raw_value,
                    config('phpvms.internal_units.distance')
                )->toUnit('nmi', 0);

                return $row;
            });

        $score = $groupPilot(
            $base()
                ->whereNotNull('p.score')
                ->select([
                    'p.user_id as user_id',
                    'u.name as name',
                    'u.pilot_id as pilot_id',
                ])
                ->selectRaw('ROUND(AVG(p.score)) as value')
        )->orderByDesc('value')->limit(5)->get();

        $hardest = $groupPilot(
            $base()
                ->whereNotNull('p.landing_rate')
                ->where('p.landing_rate', '<', 0)
                ->select([
                    'p.user_id as user_id',
                    'u.name as name',
                    'u.pilot_id as pilot_id',
                ])
                ->selectRaw('MIN(p.landing_rate) as value')
        )->orderBy('value')->limit(5)->get();

        return [
            'monthLabel' => now('Europe/Paris')->locale('fr')->isoFormat('MMMM'),
            'topPilotsByFlights' => $flights,
            'topPilotsByBlockTime' => $block,
            'topPilotsBySoftLanding' => $softest,
            'topPilotsByDistance' => $distance,
            'topPilotsByScore' => $score,
            'topPilotsByHardLanding' => $hardest,
        ];
    }

    private function operationsData(Request $r): array {
        $dayStart = now('Europe/Paris')->startOfDay()->utc();
        $dayEnd = now('Europe/Paris')->endOfDay()->utc();
        $monthStart = now('Europe/Paris')->startOfMonth()->utc();
        $homeAirportId = $r->user()->home_airport_id;
        return [
            'activeFlights'=>Pirep::whereIn('state',[PirepState::IN_PROGRESS,PirepState::PAUSED])->count(),
            'pendingPireps'=>Pirep::where('state',PirepState::PENDING)->count(),
            'acceptedToday'=>Pirep::where('state',PirepState::ACCEPTED)->whereBetween('submitted_at',[$dayStart,$dayEnd])->count(),
            'acceptedMonth'=>Pirep::where('state',PirepState::ACCEPTED)->where('submitted_at','>=',$monthStart)->count(),
            'telemetrySamples'=>DB::table('promethee_telemetry')->where('created_at','>=',$dayStart)->count(),
            'pricingRules'=>DB::table('promethee_pricing')->count(),
            'lastBulletin'=>DB::table('promethee_bulletins')->orderByDesc('month')->first(),
            // Keep the pilot's departure-base scope, while allowing every
            // airline actually scheduled at that airport to appear.
            'departureBoard'=>$this->departureBoardPayload($homeAirportId),
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
        return response()->json([
            'updated_at' => now()->toIso8601String(),
            'flights' => $this->departureBoardPayload($r->user()->home_airport_id),
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

        // Load the recent Hermès archive once for the whole OCC instead of
        // issuing one telemetry query per active flight. The archive remains
        // attached to the phpVMS PIREP; operation_id is the stable public key.
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

        abort_unless(DB::table('promethee_settings')->where('key','passport.enabled')->value('value') !== '0', 404);
        return $this->page('passport', compact('pilot', 'viewer', 'countries', 'ranking'));
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

   private function bookingOperation(Bid $booking): Bid
   {
       $operationId = 'op_'.$booking->id;
       $pirep = Pirep::where('user_id', $booking->user_id)
           ->where('flight_id', $booking->flight_id)
           ->where('aircraft_id', $booking->aircraft_id)
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
       $completed = $pirep && ($pirep->submitted_at !== null
           || in_array((int) $pirep->state, [PirepState::PENDING, PirepState::ACCEPTED, PirepState::REJECTED], true)
           || $pirep->status === PirepStatus::ARRIVED);
       $cancelled = $pirep && ((int) $pirep->state === PirepState::CANCELLED || $pirep->status === PirepStatus::CANCELLED);

       $status = $cancelled ? 'CANCELLED'
           : ($completed ? 'COMPLETED'
           : ($hasTelemetry ? 'IN_PROGRESS'
           : ($pirep ? 'READY'
           : ($ofp && $booking->aircraft_id ? 'PIREP_REQUIRED'
           : ($booking->aircraft_id ? 'OFP_REQUIRED' : 'AIRCRAFT_REQUIRED')))));

       $progress = match ($status) {
           'AIRCRAFT_REQUIRED' => 10,
           'OFP_REQUIRED' => 30,
           'PIREP_REQUIRED' => 55,
           'READY' => 70,
           'IN_PROGRESS' => 85,
           'COMPLETED', 'CANCELLED' => 100,
           default => 0,
       };

       $booking->setAttribute('operation_id', $operationId);
       $booking->setAttribute('operation_ofp', $ofp);
       $booking->setAttribute('operation_pirep', $pirep);
       $booking->setAttribute('operation_status', $status);
       $booking->setAttribute('operation_progress', $progress);
       $booking->setAttribute('operation_can_delete', $pirep === null);
       $booking->setAttribute('operation_next_action', match ($status) {
           'AIRCRAFT_REQUIRED' => 'Sélectionner un appareil',
           'OFP_REQUIRED' => 'Préparer l’OFP',
           'PIREP_REQUIRED' => 'Préparer le PIREP',
           'READY' => 'Démarrer dans Hermès',
           'IN_PROGRESS' => 'Vol en cours',
           'COMPLETED' => 'Consulter le vol',
           'CANCELLED' => 'Opération annulée',
           default => null,
       });

       return $booking;
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
   public function downloadCategoryPage(string $category) {
       $sections = ['acars' => ['ACARS', 'Clients et documentation de connexion'], 'fleet' => ['Avions et flotte', 'Livrées, appareils et documents associés'], 'airports' => ['Aéroports et HUBs', 'Scènes, cartes et ressources réseau'], 'documents' => ['Documents', 'Manuels et documents opérationnels']];
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
       return $this->page('admin.downloads', ['groups' => $this->downloadGroups()]);
   }
   public function storeDownload(Request $r, FileService $files) {
       $data = $r->validate([
           'name' => 'required|string|max:120', 'description' => 'nullable|string|max:1000',
           'category' => 'required|in:acars,fleet,airports,documents', 'subcategory' => 'nullable|string|max:80', 'file' => 'nullable|file|max:102400',
           'url' => 'nullable|url|max:2000', 'public' => 'nullable|boolean',
       ]);
       if (!$r->hasFile('file') && empty($data['url'])) return back()->withErrors(['url' => 'Ajoutez un fichier ou une URL.'])->withInput();
       $attributes = [
           'name' => $data['name'], 'description' => $data['description'] ?? '', 'public' => $r->boolean('public'),
           'ref_model' => 'Modules\\Promethee\\Download\\'.ucfirst($data['category']),
           'ref_model_id' => trim($data['subcategory'] ?? '') ?: $data['category'],
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
       return $this->page('admin.edit-download', compact('asset'));
   }
   public function updateDownload(Request $r, string $file, FileService $files) {
       $asset = File::findOrFail($file);
       abort_unless($this->isManagedDownload($asset), 403);

       $data = $r->validate([
           'name' => 'required|string|max:120', 'description' => 'nullable|string|max:1000',
           'category' => 'required|in:acars,fleet,airports,documents', 'subcategory' => 'nullable|string|max:80',
           'file' => 'nullable|file|max:102400', 'url' => 'nullable|url|max:2000', 'public' => 'nullable|boolean',
       ]);
       $attributes = [
           'name' => $data['name'], 'description' => $data['description'] ?? '', 'public' => $r->boolean('public'),
           'ref_model' => 'Modules\\Promethee\\Download\\'.ucfirst($data['category']),
           'ref_model_id' => trim($data['subcategory'] ?? '') ?: $data['category'],
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
        $missions = DB::table('promethee_missions')->where('active',true)
            ->where(fn($q)=>$q->whereNull('starts_on')->orWhere('starts_on','<=',$today))
            ->where(fn($q)=>$q->whereNull('ends_on')->orWhere('ends_on','>=',$today))->orderBy('ends_on')->get()
            ->map(function ($mission) use ($matches, $userId) {
                $mission->completion = $matches($mission);
                $mission->booking = DB::table('promethee_mission_bookings')
                    ->where('mission_id', $mission->id)
                    ->where('user_id', $userId)
                    ->latest('created_at')->first();
                $mission->reserved_by_other = DB::table('promethee_mission_bookings')
                    ->where('mission_id', $mission->id)
                    ->where('user_id', '!=', $userId)
                    ->where('status', 'reserved')->exists();
                if ($mission->aircraft_id) {
                    $mission->aircraft_registration = Aircraft::where('id', $mission->aircraft_id)->value('registration');
                }
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
            'q'           => 'nullable|string|max:32',
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

        $q = Flight::where('active', true)->where('visible', true)->with(['airline', 'fares', 'dpt_airport', 'arr_airport', 'subfleets']);
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
            $term = '%'.strtoupper($filters['q']).'%';
            $q->where(function ($flights) use ($term) {
                $flights->where('flight_number', 'like', $term)
                    ->orWhere('callsign', 'like', $term)
                    ->orWhere('route_code', 'like', $term)
                    ->orWhere('dpt_airport_id', 'like', $term)
                    ->orWhere('arr_airport_id', 'like', $term);
            });
        }

        $sort = $filters['sort'] ?? 'departure';
        if ($sort === 'ident') $q->orderBy('route_code')->orderBy('flight_number');
        elseif ($sort === 'distance') $q->orderBy('distance');
        else $q->orderBy('dpt_time')->orderBy('route_code')->orderBy('flight_number');

        $airportIds = Flight::where('active', true)->where('visible', true)
            ->select('dpt_airport_id as id')->union(
                Flight::where('active', true)->where('visible', true)->select('arr_airport_id as id')
            );
        $mapAirports = Airport::whereIn('id', $airportIds)->orderBy('icao')->get(['id', 'icao', 'name', 'location', 'lat', 'lon']);
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
            'name' => $airport->name,
            'location' => $airport->location,
            'lat' => (float) $airport->lat,
            'lon' => (float) $airport->lon,
            'x' => round(35 + (($airport->lon - $bounds['west']) / $lonSpan) * 930, 1),
            'y' => round(35 + (($bounds['north'] - $airport->lat) / $latSpan) * 470, 1),
        ]);
        $mapByCode = $mapAirports->keyBy('code');
        $mapRoutes = collect();
        if ($selectedDeparture || $selectedArrival) {
            $mapRoutes = (clone $q)->reorder()->select('dpt_airport_id', 'arr_airport_id', 'airline_id')->distinct()->limit(160)->get()
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
                ->filter(fn ($route) => $route['from'] && $route['to'])->values();
        }

        return $this->page('flights', [
            'flights' => $q->paginate(24)->withQueryString(),
            'airlines' => Airline::where('active', true)->orderBy('name')->get(['id', 'name', 'icao']),
            'subfleets' => Subfleet::with('airline')->orderBy('name')->get(['id', 'name', 'type', 'airline_id']),
            'flightTypes' => Flight::where('active', true)->where('visible', true)->distinct()->orderBy('flight_type')->pluck('flight_type')
                ->mapWithKeys(fn ($type) => [$type => FlightType::label($type)]),
            'mapAirports' => $mapAirports,
            'mapRoutes' => $mapRoutes,
            'selectedDeparture' => $selectedDeparture,
            'selectedArrival' => $selectedArrival,
        ]);
    }
    public function flight(string $id) {
        $flight = Flight::with(['airline','fares','subfleets','dpt_airport','arr_airport','alt_airport'])->findOrFail($id);
        $stats = [
            'pireps'=>Pirep::where('flight_id',$flight->id)->where('state',PirepState::ACCEPTED)->count(),
            'last'=>Pirep::where('flight_id',$flight->id)->where('state',PirepState::ACCEPTED)->orderByDesc('submitted_at')->first(),
        ];
        $history=Pirep::where('flight_id',$flight->id)->where('state',PirepState::ACCEPTED);
        $routeHistory=['flights'=>(clone $history)->count(),'average_time'=>(int) (clone $history)->avg('flight_time'),'best_time'=>(int) (clone $history)->min('flight_time')];
        $weather=[];
        try {
            $service=app(\App\Services\AirportService::class);
            foreach (array_filter([$flight->dpt_airport_id,$flight->arr_airport_id,$flight->alt_airport_id]) as $icao) {
                $weather[$icao]=['metar'=>$service->getMetar($icao)?->raw,'taf'=>$service->getTaf($icao)?->raw];
            }
        } catch (\Throwable) { }
        return $this->page('flight',['flight'=>$flight,'stats'=>$stats,'routeHistory'=>$routeHistory,'weather'=>$weather, 'recentPireps'=>Pirep::with(['user','aircraft'])->where('flight_id',$flight->id)->where('state',PirepState::ACCEPTED)->latest('submitted_at')->limit(12)->get(), 'reservation'=>DB::table('bids')->where(['flight_id'=>$flight->id,'user_id'=>auth()->id()])->first()]);
    }
    public function reserveFlight(string $id, Request $r, \App\Services\BidService $bids) {
        $flight=Flight::where(['id'=>$id,'active'=>true,'visible'=>true])->firstOrFail();
        abort_unless(app(CompanyAccessService::class)->canAccessAirline($r->user(), (int) $flight->airline_id), 403, 'Cette compagnie n’est pas encore accessible avec votre nombre d’heures de vol.');
        try { $bids->addBid($flight,$r->user()); return back()->with('success','Vol '.$flight->ident.' réservé.'); }
        catch (\Throwable $e) { return back()->withErrors(['reservation'=>$e->getMessage() ?: 'Cette réservation ne peut pas être créée.']); }
    }
    public function briefing(string $id, Request $r, DemandProfileService $demand) {
        $flight=Flight::with(['airline','dpt_airport','arr_airport','alt_airport','subfleets'])->findOrFail($id);
        $weather=[];
        try {
            $service=app(\App\Services\AirportService::class);
            foreach (array_filter([$flight->dpt_airport_id,$flight->arr_airport_id,$flight->alt_airport_id]) as $icao) {
                $weather[$icao]=['metar'=>$service->getMetar($icao)?->raw,'taf'=>$service->getTaf($icao)?->raw];
            }
        } catch (\Throwable) { }
        $distance=(float) $flight->distance->toUnit('nmi');
        $fuelUnit=setting('units.fuel', 'kg');
        // The dispatch rule is calculated in kilograms, then converted to the
        // unit selected for this installation before it is shown to the pilot.
        $suggestedFuel=(int) round(\App\Support\Units\Fuel::make(ceil(max(250,$distance*3.2)), 'kg')->toUnit($fuelUnit));
        $briefing=DB::table('promethee_briefings')->where(['user_id'=>$r->user()->id,'flight_id'=>$flight->id])->first();
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
        $simbrief=SimBrief::with(['flight.airline','aircraft.subfleet','pirep'])->where('user_id',$r->user()->id)->findOrFail($id);
        abort_unless($simbrief->xml, 404, 'OFP SimBrief indisponible.');
        $fares=collect();
        if (!empty($simbrief->fare_data)) {
            $decoded=json_decode($simbrief->fare_data, true);
            if (is_array($decoded)) $fares=collect($decoded);
        }
        return $this->page('simbrief', ['simbrief'=>$simbrief,'ofp'=>$simbrief->xml,'flight'=>$simbrief->flight,'aircraft'=>$simbrief->aircraft,'fares'=>$fares]);
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
        return $this->page('calendar',[
            'month'=>$month,'start'=>$start,
            'events'=>$events,
        ]);
    }
    public function rsvpEvent(int $id, Request $r) {
        DB::table('promethee_events')->find($id) ?? abort(404);
        $data=$r->validate(['status'=>'required|in:going,maybe']);
        DB::table('promethee_event_rsvps')->updateOrInsert(['event_id'=>$id,'user_id'=>$r->user()->id],$data+['updated_at'=>now(),'created_at'=>now()]);
        return back()->with('success','Participation enregistrée.');
    }
    public function saveEvent(Request $r) {
        $d=$r->validate(['title'=>'required|string|max:191','description'=>'nullable|string|max:4000',
            'starts_at'=>'required|date','ends_at'=>'required|date|after:starts_at',
            'departure'=>'nullable|exists:airports,id','arrival'=>'nullable|exists:airports,id']);
        foreach (['starts_at','ends_at'] as $key) $d[$key]=CarbonImmutable::parse($d[$key],'Europe/Paris')->utc();
        DB::table('promethee_events')->insert($d+['created_by'=>$r->user()->id,'created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('promethee.calendar',['month'=>$d['starts_at']->setTimezone('Europe/Paris')->format('Y-m')])->with('success','Événement ajouté au calendrier.');
    }
    public function deleteEvent(int $id) {
        DB::table('promethee_events')->where('id',$id)->delete();
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
            $portrait=null;
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

    public function economy(Request $r, EconomyFareResolver $fareResolver) {
        foreach (['flight_dpt_airport', 'flight_arr_airport'] as $airportFilter) {
            if ($r->filled($airportFilter)) {
                $r->merge([$airportFilter => strtoupper(trim((string) $r->input($airportFilter)))]);
            }
        }

        $flightFilters=$r->validate(['flight_airline'=>'nullable|string|max:10','flight_origin'=>'nullable|string|size:2','flight_arrival'=>'nullable|string|size:2','flight_dpt_airport'=>'nullable|string|max:10','flight_arr_airport'=>'nullable|string|max:10','flight_search'=>'nullable|string|max:80','flight_select_all'=>'nullable|boolean']);
        $fuelFilters=$r->validate(['fuel_country'=>'nullable|string|size:2','fuel_region'=>'nullable|string|max:191','fuel_search'=>'nullable|string|max:80','fuel_select_all'=>'nullable|boolean']);
        $airportOptions=Airport::select('id','icao','name','country','region','location')->orderBy('country')->orderBy('region')->orderBy('location')->get();
        $flightPricing=Flight::where('active',true)
            ->when($flightFilters['flight_airline'] ?? null,fn($query,$icao)=>$query->whereHas('airline',fn($airline)=>$airline->where('icao',$icao)))
            ->when($flightFilters['flight_origin'] ?? null,fn($query,$country)=>$query->whereHas('dpt_airport',fn($airport)=>$airport->where('country',$country)))
            ->when($flightFilters['flight_arrival'] ?? null,fn($query,$country)=>$query->whereHas('arr_airport',fn($airport)=>$airport->where('country',$country)))
            ->when($flightFilters['flight_dpt_airport'] ?? null,fn($query,$airport)=>$query->where('dpt_airport_id',$airport))
            ->when($flightFilters['flight_arr_airport'] ?? null,fn($query,$airport)=>$query->where('arr_airport_id',$airport))
            ->when($flightFilters['flight_search'] ?? null,fn($query,$search)=>$query->where(fn($where)=>$where->where('flight_number','like','%'.$search.'%')->orWhere('route_code','like','%'.$search.'%')->orWhere('dpt_airport_id','like','%'.$search.'%')->orWhere('arr_airport_id','like','%'.$search.'%')))
            ->with(['airline','fares','subfleets.fares','dpt_airport','arr_airport'])
            ->orderBy('airline_id')->orderBy('dpt_airport_id')->orderBy('arr_airport_id')->get();
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
        return $this->page('seasons',['seasons'=>DB::table('promethee_seasons')->orderByDesc('starts_on')->get()]);
    }
    public function saveSeason(Request $r) {
        $data=$r->validate(['name'=>'required|string|max:80','starts_on'=>'required|date','ends_on'=>'required|date|after:starts_on','notes'=>'nullable|string|max:2000','active'=>'nullable|boolean']);
        if (!empty($data['active'])) DB::table('promethee_seasons')->update(['active'=>false,'updated_at'=>now()]);
        DB::table('promethee_seasons')->insert($data+['active'=>$r->boolean('active'),'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Saison enregistrée.');
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
        return $this->page('admin.dashboard', [
            'activePilots' => User::where('state', UserState::ACTIVE)->count(),
            'newPilots' => User::where('created_at', '>=', $monthStart)->count(),
            'pendingPireps' => Pirep::where('state', PirepState::PENDING)->count(),
            'acceptedPireps' => Pirep::where('state', PirepState::ACCEPTED)->where('submitted_at','>=',$monthStart)->count(),
            'rejectedPireps' => Pirep::where('state', PirepState::REJECTED)->where('submitted_at','>=',$monthStart)->count(),
            'flightMinutes' => (int) Pirep::where('state', PirepState::ACCEPTED)->sum('flight_time'),
            'activeEvents' => DB::table('promethee_events')->where('ends_at','>=',now())->count(),
            'recentProgression' => DB::table('promethee_progression_history')->latest()->limit(8)->get(),
            'activeMissions' => DB::table('promethee_missions')->where('active',true)->count(),
            'activeCircuits' => DB::table('promethee_circuits')->where('active',true)->count(),
            'monthlyAssignments' => DB::table('promethee_assignments')->where('month',now('Europe/Paris')->format('Y-m'))->count(),
            'shopItems' => DB::table('promethee_shop_items')->where('active',true)->count(),
            'regionalBases' => DB::table('promethee_operational_bases')->where('active',true)->count(),
            'jumpseatCount' => DB::table('promethee_transfer_requests')->where('type','jumpseat')->count(),
            'simbriefApiConfigured' => app(\Modules\Promethee\Services\SimBriefCompanyKeyService::class)->configured(),
        ]);
    }
    public function automation() {
        $badgeRules = DB::table('promethee_badge_rules')->orderByDesc('updated_at')->get();
        $rankRules = DB::table('promethee_rank_rules')->orderByDesc('updated_at')->get();
        return $this->page('admin.automation', [
            'badgeRules' => $badgeRules,
            'rankRules' => $rankRules,
            'badgeRuleData' => $badgeRules->groupBy('award_id')->map(fn ($rules) => $this->rulePayload($rules->first())),
            'rankRuleData' => $rankRules->groupBy('rank_id')->map(fn ($rules) => $this->rulePayload($rules->first())),
            'awards' => Award::orderBy('name')->get(), 'ranks' => Rank::orderBy('hours')->get(),
            'awardData' => Award::orderBy('name')->get()->mapWithKeys(fn ($award) => [$award->id => ['name'=>$award->name, 'description'=>$award->description, 'image_url'=>$award->image_url]]),
            'rankData' => Rank::orderBy('hours')->get()->mapWithKeys(fn ($rank) => [$rank->id => ['name'=>$rank->name, 'hours'=>$rank->hours, 'image_url'=>$rank->image_url]]),
            'airlines' => Airline::where('active', true)->orderBy('name')->get(['id','name','icao']),
            'aircraftTypes' => \App\Models\Aircraft::whereNotNull('icao')->where('icao', '!=', '')->distinct()->orderBy('icao')->pluck('icao'),
            'events' => DB::table('promethee_events')->orderByDesc('starts_at')->limit(100)->get(['id','title','starts_at']),
            'airlines' => Airline::orderBy('name')->get(['id','name','icao']),
            'events' => DB::table('promethee_events')->orderByDesc('starts_at')->get(['id','title','starts_at']),
            'history' => DB::table('promethee_progression_history')->latest()->limit(30)->get(),
        ]);
    }
    private function rulePayload(object $rule): array {
        return ['id'=>$rule->id, 'operator'=>$rule->operator, 'criteria'=>json_decode($rule->criteria, true) ?: [], 'active'=>(bool)$rule->active, 'allow_demotion'=>(bool)($rule->allow_demotion ?? false)];
    }
    public function automationRule(string $kind, int $id) {
        $table = $kind === 'badge' ? 'promethee_badge_rules' : 'promethee_rank_rules';
        $column = $kind === 'badge' ? 'award_id' : 'rank_id';
        $rule = DB::table($table)->where($column, $id)->orderByDesc('updated_at')->first();
        return response()->json($rule ? $this->rulePayload($rule) : null);
    }
    public function recalculateAutomation(Request $r, \Modules\Promethee\Services\ProgressionService $progression) {
        $result = $progression->recalculate(null, 'manual');
        return back()->with('success', $result['awards'].' badge(s) et '.$result['promotions'].' promotion(s) attribué(e)(s).');
    }
    private function automationCriteria(Request $r): array {
        $data = $r->validate(['operator'=>'required|in:and,or','criteria'=>'required|array|min:1|max:12','criteria.*.metric'=>'required|string','criteria.*.value'=>'nullable','criteria.*.text'=>'nullable|string|max:30']);
        $criteria = $data['criteria'];
        $metrics = ['validated_flights','flight_minutes','total_distance','visited_airports','visited_countries','seniority_days','required_badge','route','airline','aircraft_icao','event_completed','night_flights'];
        foreach ($criteria as &$criterion) {
            abort_unless(is_array($criterion) && in_array($criterion['metric'] ?? null, $metrics, true), 422, 'Critère non valide.');
            $criterion['value'] = isset($criterion['value']) ? (float) $criterion['value'] : 0;
            $criterion['route'] = $criterion['metric'] === 'route' ? strtoupper(substr((string)($criterion['text'] ?? ''), 0, 30)) : null;
            $criterion['text'] = $criterion['metric'] === 'aircraft_icao' ? strtoupper(substr((string)($criterion['text'] ?? ''), 0, 30)) : null;
        }
        return [$data['operator'], $criteria];
    }
    private function distinctionImage(Request $r): ?string {
        if ($r->hasFile('image')) {
            // Public assets are replaced whenever the local image is rebuilt.
            // Keep uploads on Laravel's persistent storage volume and expose
            // that directory through the stable public symlink created at boot.
            $file=$r->file('image'); $directory=storage_path('app/promethee-distinctions');
            Filesystem::ensureDirectoryExists($directory); 
            $name=uniqid('distinction_', true).'.'.$file->extension(); $file->move($directory,$name);
            return '/promethee-assets/distinctions/'.$name;
        }
        return $r->filled('image_url') ? $r->string('image_url')->toString() : null;
    }
    public function createAutomationAward(Request $r) {
        $d=$r->validate(['name'=>'required|string|max:191','description'=>'nullable|string|max:1000','image_url'=>'nullable|url|max:2000','image'=>'nullable|image|max:4096']);
        $d['image_url']=$this->distinctionImage($r); unset($d['image']);
        Award::create($d+['active'=>true]);
        return back()->with('success','Badge ajouté au catalogue.');
    }
    public function createAutomationRank(Request $r) {
        $d=$r->validate(['name'=>'required|string|max:50|unique:ranks,name','hours'=>'required|integer|min:0','image_url'=>'nullable|url|max:2000','image'=>'nullable|image|max:4096']);
        $d['image_url']=$this->distinctionImage($r); unset($d['image']);
        Rank::create($d);
        return back()->with('success','Grade ajouté au catalogue.');
    }
    public function updateAutomationAward(Request $r, Award $award) {
        $data=$r->validate(['name'=>'required|string|max:191','description'=>'nullable|string|max:1000','image_url'=>'nullable|url|max:2000','image'=>'nullable|image|max:4096']);
        $image=$r->hasFile('image') || $r->filled('image_url') ? $this->distinctionImage($r) : $award->image_url;
        $award->update(['name'=>$data['name'],'description'=>$data['description'] ?? null,'image_url'=>$image]);
        return back()->with('success', 'Badge mis à jour.');
    }
    public function updateAutomationRank(Request $r, Rank $rank) {
        $data=$r->validate(['name'=>'required|string|max:50|unique:ranks,name,'.$rank->id,'hours'=>'required|integer|min:0','image_url'=>'nullable|url|max:2000','image'=>'nullable|image|max:4096']);
        $image=$r->hasFile('image') || $r->filled('image_url') ? $this->distinctionImage($r) : $rank->image_url;
        $rank->update(['name'=>$data['name'],'hours'=>$data['hours'],'image_url'=>$image]);
        return back()->with('success', 'Grade mis à jour.');
    }
    public function previewAutomation(Request $r, \Modules\Promethee\Services\ProgressionService $progression) {
        [$operator, $criteria] = $this->automationCriteria($r);
        $rule = ['operator' => $operator, 'criteria' => $criteria];
        $pilots = User::where('state', UserState::ACTIVE)->get()->filter(fn ($user) => $progression->eligible($user, $rule))->take(100)->values();
        return back()->with('automation_preview', ['count'=>$pilots->count(), 'pilots'=>$pilots->map(fn($pilot)=>$pilot->pilot_id.' · '.$pilot->name)->all()]);
    }
    public function saveBadgeRule(Request $r, \Modules\Promethee\Services\ProgressionService $progression) {
        $data = $r->validate(['award_id'=>'required|integer|exists:awards,id','rule_id'=>'nullable|integer|exists:promethee_badge_rules,id','active'=>'nullable|boolean']);
        [$operator,$criteria] = $this->automationCriteria($r);
        $payload=['award_id'=>$data['award_id'],'operator'=>$operator,'criteria'=>json_encode($criteria),'active'=>$r->boolean('active'),'updated_at'=>now()];
        if (!empty($data['rule_id'])) {
            DB::table('promethee_badge_rules')->where('id',$data['rule_id'])->update($payload);
        } else {
            DB::table('promethee_badge_rules')->insert($payload+['created_at'=>now()]);
        }

        $result = $progression->recalculate(null, 'rule:badge_saved');

        return back()->with('success','Règle de badge enregistrée · '.$result['awards'].' badge(s) attribué(s) automatiquement.');
    }
    public function saveRankRule(Request $r, \Modules\Promethee\Services\ProgressionService $progression) {
        $data = $r->validate(['rank_id'=>'required|integer|exists:ranks,id','rule_id'=>'nullable|integer|exists:promethee_rank_rules,id','active'=>'nullable|boolean','allow_demotion'=>'nullable|boolean']);
        [$operator,$criteria] = $this->automationCriteria($r);
        $payload=['rank_id'=>$data['rank_id'],'operator'=>$operator,'criteria'=>json_encode($criteria),'active'=>$r->boolean('active'),'allow_demotion'=>$r->boolean('allow_demotion'),'updated_at'=>now()];
        if (!empty($data['rule_id'])) {
            DB::table('promethee_rank_rules')->where('id',$data['rule_id'])->update($payload);
        } else {
            DB::table('promethee_rank_rules')->insert($payload+['created_at'=>now()]);
        }

        $result = $progression->recalculate(null, 'rule:rank_saved');

        return back()->with('success','Règle de grade enregistrée · '.$result['promotions'].' promotion(s) appliquée(s) automatiquement.');
    }
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

    public function regionalOperations(Request $r, RegionalOperationsService $operations) {
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
        return $this->page('admin-regional-operations', compact('bases','aircraft','assignments','settings'));
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
            'kind'=>'required|in:hub,regional',
            'small_maintenance'=>'nullable|boolean',
            'heavy_maintenance'=>'nullable|boolean',
            'active'=>'nullable|boolean',
        ]);
        $airportId=strtoupper($data['airport_id']);
        if ($data['kind'] === 'hub' && $airportId !== 'LFPO') {
            return back()->withErrors(['airport_id'=>'Orly (LFPO) est le seul hub Air Inter VA. Les autres bases doivent être des plateformes régionales.'])->withInput();
        }
        $kind = $airportId === 'LFPO' ? 'hub' : 'regional';
        if ($airportId === 'LFPO') {
            DB::table('promethee_operational_bases')->where('kind','hub')->where('airport_id','!=','LFPO')->update(['kind'=>'regional','heavy_maintenance'=>false,'updated_at'=>now()]);
        }
        DB::table('promethee_operational_bases')->updateOrInsert(
            ['airport_id'=>$airportId],
            [
                'kind'=>$kind,
                'small_maintenance'=>$r->boolean('small_maintenance'),
                'heavy_maintenance'=>$airportId === 'LFPO' && $r->boolean('heavy_maintenance'),
                'active'=>$r->boolean('active'),
                'created_at'=>now(),'updated_at'=>now(),
            ]
        );
        return back()->with('success','Base opérationnelle enregistrée.');
    }

    public function assignAircraftBase(Request $r) {
        $data=$r->validate(['aircraft_id'=>'required|integer|exists:aircraft,id','base_airport_id'=>'required|string|max:8|exists:promethee_operational_bases,airport_id']);
        DB::table('promethee_aircraft_bases')->updateOrInsert(
            ['aircraft_id'=>$data['aircraft_id']],
            ['base_airport_id'=>strtoupper($data['base_airport_id']),'assigned_at'=>now(),'away_since'=>null,'repatriation_mission_id'=>null,'created_at'=>now(),'updated_at'=>now()]
        );
        Aircraft::where('id',$data['aircraft_id'])->update(['hub_id'=>strtoupper($data['base_airport_id'])]);
        return back()->with('success','Base de l’appareil mise à jour.');
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
