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


class CalendarController extends PrometheeWebController
{
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
}
