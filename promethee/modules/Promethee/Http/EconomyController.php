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


class EconomyController extends PrometheeWebController
{
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
}
