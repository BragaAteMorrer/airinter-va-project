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


class MissionsController extends PrometheeWebController
{
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
}
