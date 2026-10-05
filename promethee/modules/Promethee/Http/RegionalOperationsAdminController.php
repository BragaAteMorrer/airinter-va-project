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


class RegionalOperationsAdminController extends PrometheeWebController
{
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
}
