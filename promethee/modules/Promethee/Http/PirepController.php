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


class PirepController extends PrometheeWebController
{
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

        $passengerCount = $farePassengers > 0 ? $farePassengers : $simbriefPassengers;
        $finance = $this->pirepFinance($pirep, $passengerCount);

        return $this->page('pirep', [
            'pirep' => $pirep,
            'flightJournal' => $this->pirepJournal($pirep, $companyScore),
            'companyScore' => $companyScore,
            'finance' => $finance,
            'passengerCount' => $passengerCount,
            'passengerSource' => $farePassengers > 0 ? 'PIREP' : ($simbriefPassengers !== null ? 'OFP SimBrief' : null),
            'canDeletePirep' => app(PilotPirepDeletionService::class)->canDelete($pirep, $r->user()),
        ]);
    }

private function pirepJournal(Pirep $pirep, array $companyScore = [])
    {
        return app(PirepJournalService::class)->build($pirep, $companyScore);
    }

private function pirepFinance(Pirep $pirep, ?int $passengerCount = null): array
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
        $net = $credits - $debits;
        $pilotNet = (int) $pilotTransactions->sum('credit') - (int) $pilotTransactions->sum('debit');

        $rows = $companyTransactions->map(function ($transaction) {
            $credit = (int) ($transaction->credit ?? 0);
            $debit = (int) ($transaction->debit ?? 0);
            $amount = $credit - $debit;
            $tags = collect((array) ($transaction->tags ?? []))
                ->map(fn ($tag) => strtolower((string) $tag));
            $group = strtolower((string) ($transaction->transaction_group ?? ''));
            $memo = strtolower((string) ($transaction->memo ?? ''));
            $search = trim($group.' '.$memo.' '.$tags->implode(' '));

            $category = 'Autres charges';
            $label = $transaction->transaction_group ?: ($transaction->memo ?: 'Écriture phpVMS');

            if ($amount >= 0 || str_contains($search, 'fare')) {
                $category = 'Revenus commerciaux';
                $label = str_contains($search, 'fare') ? 'Billetterie passagers' : $label;
            } elseif (str_contains($search, 'fuel')) {
                $category = 'Carburant';
                $label = 'Carburant';
            } elseif (str_contains($search, 'pilot_pay') || str_contains($search, 'pilot pay')) {
                $category = 'Personnel navigant';
                $label = 'Rémunération pilote';
            } elseif (
                str_contains($search, 'ground_handling')
                || str_contains($search, 'ground handling')
                || str_contains($search, 'handling')
                || str_contains($search, 'de-icing')
                || str_contains($search, 'deicing')
            ) {
                $category = 'Assistance au sol';
                if (str_contains($search, 'departure')) {
                    $label = 'Handling départ';
                } elseif (str_contains($search, 'arrival')) {
                    $label = 'Handling arrivée';
                } elseif (str_contains($search, 'de-icing') || str_contains($search, 'deicing')) {
                    $label = 'Assistance sol / Dégivrage';
                } elseif (str_contains($search, 'ground staff')) {
                    $label = 'Personnel sol / Handling';
                }
            } elseif (str_contains($search, 'maintenance')) {
                $category = 'Maintenance';
                $label = 'Maintenance';
            } elseif (str_contains($search, 'catering')) {
                $category = 'Service passagers';
                $label = 'Catering';
            } elseif (
                str_contains($search, 'eurocontrol')
                || str_contains($search, 'navigation')
                || str_contains($search, 'landing')
                || str_contains($search, 'airport fee')
                || str_contains($search, 'airport:')
            ) {
                $category = 'Taxes & redevances';
                if (str_contains($search, 'eurocontrol') || str_contains($search, 'navigation')) {
                    $label = 'Eurocontrol / Navigation';
                } elseif (str_contains($search, 'landing') || str_contains($search, 'airport fee')) {
                    $label = 'Redevances aéroportuaires';
                }
            } elseif (str_contains($search, 'subfleet') || str_contains($search, 'aircraft')) {
                $category = 'Exploitation appareil';
                $label = str_contains($search, 'block time')
                    ? 'Coût temps de vol appareil'
                    : 'Coût opérationnel appareil';
            }

            return [
                'transaction' => $transaction,
                'label' => $label,
                'category' => $category,
                'credit_raw' => $credit,
                'debit_raw' => $debit,
                'amount_raw' => $amount,
                'credit' => $credit > 0 ? new Money($credit) : null,
                'debit' => $debit > 0 ? new Money($debit) : null,
                'amount' => new Money(abs($amount)),
                'is_credit' => $amount >= 0,
            ];
        })->values();

        $categoryOrder = [
            'Exploitation appareil',
            'Carburant',
            'Assistance au sol',
            'Maintenance',
            'Service passagers',
            'Taxes & redevances',
            'Personnel navigant',
            'Autres charges',
        ];

        $categorySummary = $rows
            ->filter(fn ($row) => $row['amount_raw'] < 0)
            ->groupBy('category')
            ->map(function ($categoryRows, $category) use ($debits, $categoryOrder) {
                $amount = abs((int) $categoryRows->sum('amount_raw'));

                return [
                    'category' => $category,
                    'amount_raw' => $amount,
                    'amount' => new Money($amount),
                    'share' => $debits > 0 ? ($amount / $debits) * 100 : 0.0,
                    'sort' => (($index = array_search($category, $categoryOrder, true)) === false) ? 999 : $index,
                ];
            })
            ->sortBy('sort')
            ->values();

        $safePassengerCount = $passengerCount !== null && $passengerCount > 0 ? $passengerCount : null;

        return [
            'company_transactions' => $companyTransactions,
            'pilot_transactions' => $pilotTransactions,
            'rows' => $rows,
            'category_summary' => $categorySummary,
            'credits' => new Money($credits),
            'debits' => new Money($debits),
            'net' => new Money($net),
            'pilot_net' => new Money($pilotNet),
            'margin' => $credits > 0 ? ($net / $credits) * 100 : null,
            'revenue_per_passenger' => $safePassengerCount ? new Money((int) round($credits / $safePassengerCount)) : null,
            'cost_per_passenger' => $safePassengerCount ? new Money((int) round($debits / $safePassengerCount)) : null,
            'net_per_passenger' => $safePassengerCount ? new Money((int) round($net / $safePassengerCount)) : null,
        ];
    }

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

public function deleteOwnPirep(string $id, Request $r)
   {
       $pirep = Pirep::with(['user', 'aircraft', 'flight'])->findOrFail($id);
       $ident = $pirep->ident;
       app(PilotPirepDeletionService::class)->deleteOwn($pirep, $r->user(), 'promethee-pirep');

       return redirect()->route('promethee.bookings')
           ->with('success', 'PIREP '.$ident.' supprimé. Vous pouvez préparer une nouvelle tentative.');
   }
}
