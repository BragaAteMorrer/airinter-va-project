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


abstract class PrometheeWebController extends \App\Contracts\Controller
{
protected function page(string $name, array $data=[]) {
        return view('promethee::'.$name, $data + ['branding' => app(BrandingService::class)->active()]);
    }

protected function month(Request $r): string {
        $r->validate(['month'=>'nullable|date_format:Y-m']);
        return $r->query('month',now('Europe/Paris')->format('Y-m'));
    }

protected function bookingOperation(Bid $booking): Bid
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

protected function releaseTerminalReservationsForFlight(Flight $flight, User $user): ?Bid
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

protected function isLegacyHermesGhostForPortal(Pirep $pirep, bool $hasTelemetry): bool
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

protected function nextDeparture(Flight $flight): ?CarbonImmutable {
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

protected function scheduledDateTime(string $raw, CarbonImmutable $date): ?CarbonImmutable
    {
        if (!preg_match('/^(\d{1,2}):(\d{2})(Z)?$/i', trim($raw), $parts)) return null;

        return $date->setTimezone(!empty($parts[3]) ? 'UTC' : 'Europe/Paris')
            ->startOfDay()->setTime((int) $parts[1], (int) $parts[2]);
    }
}
