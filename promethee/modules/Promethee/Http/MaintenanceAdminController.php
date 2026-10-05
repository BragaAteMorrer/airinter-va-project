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


class MaintenanceAdminController extends PrometheeWebController
{
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
        $itvaEngineCategories = (array) data_get(config('promethee.engine-profiles', []), '__meta.itva_categories', []);

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
            'airframeSettings','airframeStates','airframeSummary','airframeEvents','itvaEngineCategories'
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


public function releaseAirframeSafetyHold(int $aircraft, Request $r, AirframeMaintenanceService $airframeService) {
        $data = $r->validate(['notes'=>'nullable|string|max:1000']);

        try {
            $released = $airframeService->releaseSafetyHold(
                $aircraft,
                (int) $r->user()->id,
                $data['notes'] ?? null
            );
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['airframe'=>$exception->getMessage()]);
        }

        if (!$released) {
            return back()->withErrors(['airframe'=>'Aucune immobilisation FDM active sur cet appareil.']);
        }

        return back()->with('success','Immobilisation FDM levée après inspection technique.');
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
}
