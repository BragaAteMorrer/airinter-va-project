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


class CompanyController extends PrometheeWebController
{
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
                // Only "due" and "warning" are selected above, so a normal
                // qualified order keeps "due" first without bypassing the
                // connection table-prefix handling (DB_PREFIX=phpvms7_ in prod).
                ->orderBy('engine.status')
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
}
