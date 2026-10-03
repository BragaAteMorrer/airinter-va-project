<?php

namespace Modules\DisposableAirports\Http\Controllers;

use App\Contracts\Controller;
use App\Models\Airport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Modules\DisposableAirports\Services\DA_AirportServices;
use Nwidart\Modules\Facades\Module;

class DA_AirportController extends Controller
{
    // Airports
    public function index()
    {
        $deleted_airports = Airport::onlyTrashed()->orderBy('icao')->get();
        $module_settings = DB::table('disposable_settings')->where('key', 'LIKE', 'dairports.%')->get();

        return view('DAirports::index', [
            'deleted_airports' => $deleted_airports,
            'details'          => $this->module_details('DisposableAirports'),
            'settings'         => $module_settings,
        ]);
    }

    // Restore Deleted Airport
    public function restore($icao = null)
    {
        $ap = Airport::onlyTrashed()->where('id', $icao)->first();

        if ($ap) {
            $ap->restore();
            flash()->success('Airport ' . $icao . ' restored successfully.');
            Log::debug('Disposable Airports | ' . $icao . ' restored by ' . Auth::user()->name_private);
        } else {
            flash()->error('Airport not found or not deleted.');
        }

        return back();
    }

    public function destroy($icao = null)
    {
        $ap = Airport::withTrashed()->where('id', $icao)->first();

        if ($ap) {
            $ap->forceDelete();
            flash()->success('Airport ' . $icao . ' permanently deleted.');
            Log::notice('Disposable Airports | ' . $icao . ' permanently deleted by ' . Auth::user()->name_private);
        } else {
            flash()->error('Airport not found.');
        }

        return back();
    }

    // Update Airports from Online Source
    public function update_all()
    {
        $DA_AirportSVC = app(DA_AirportServices::class);
        $result = $DA_AirportSVC->UpdateAirports();

        flash()->success('Airport data processing completed... Processed:' . $result['processed'] . ', Updated:' . $result['updated'] . ', Created:' . $result['created'] . ', Skipped:' . $result['skipped']);
        return back();
    }

    // Module Settings
    public function update_settings()
    {
        $formdata = Request::post();
        $section = null;

        foreach ($formdata as $id => $value) {
            if ($id === 'group') {
                $section = $value;
            }

            $setting = DB::table('disposable_settings')->where('id', $id)->first();

            if (!$setting) {
                continue;
            }

            Log::debug('Disposable Airports | ' . $setting->group . ' setting for ' . $setting->name . ' changed to ' . $value);
            DB::table('disposable_settings')->where(['id' => $setting->id])->update(['value' => $value]);
        }

        flash()->success($section . ' settings saved.');

        return back();
    }

    // Read module.json file
    public function module_details($module_name = null)
    {
        $details = collect();
        $file = isset($module_name) ? base_path() . '/modules/' . $module_name . '/module.json' : null;

        if (!is_file($file)) {
            return $details;
        }

        $contents = json_decode(file_get_contents($file));

        $details->name = isset($contents->name) ? $contents->name : $module_name;
        $details->description = isset($contents->description) ? $contents->description : null;
        $details->version = isset($contents->version) ? $contents->version : null;
        $details->readme_url = isset($contents->readme_url) ? $contents->readme_url : null;
        $details->license_url = isset($contents->license_url) ? $contents->license_url : null;
        $details->attribution = isset($contents->attribution) ? $contents->attribution : null;
        $details->active = Module::isEnabled($contents->name);

        return $details;
    }

    // Fix Uzbekistan ICAO codes
    public function fix_uzbekistan_airports()
    {
        // Old to New ICAO Codes for Uzbekistan
        $uzbekistan = [
            "UTSB" => "UZSB", // Bukhara
            "UTSS" => "UZSS", // Samarkand
            "UTTT" => "UZTT", // Tashkent
            "UTST" => "UZST", // Termez
            "UTNU" => "UZNU", // Urgench
            "UTSA" => "UZSA", // Navoi
            "UTNN" => "UZNN", // Nukus
            "UTFA" => "UZFA", // Andijan
            "UTFF" => "UZFF", // Fergana
            "UTKF" => "UZFF", // Fergana, fix for duplicate code used in some sources
            "UTSK" => "UZSK", // Karshi
            "UTFN" => "UZFN", // Namangan
            "UTTP" => "UZTP", // Tashkent - Vostochny
            "UTTC" => "UZTC", // Chirchik Air Base
            "UTSL" => "UZSL", // Karshi Air Base - NOT IN AIP
            "UTNM" => "UZNM", // Muynak - NOT IN AIP
            "UTSH" => "UZSH", // Shakhrisabz - NOT IN AIP
            "UTSN" => "UZSN", // Navoi - Zarafshan - NOT IN AIP
            "UTSR" => "UZSR", // Sary Asiya - NOT IN AIP
            "UTSU" => "UZSU", // Uchkuduk - NOT IN AIP
            "UTKK" => "UZKK", // Kokand - NOT IN AIP
            "UTNT" => "UZNT", // Turtkul - NOT IN AIP
            "UTSM" => "UZSM", // Tandy Bulak - NOT IN AIP
            "UTTZ" => "UZTZ", // Zomin - NOT IN AIP
            "UT1M" => "UZ1M", // Kakady - NOT IN AIP
            "UT1N" => "UZ1N", // Karshi South - NOT IN AIP
            "UT1O" => "UZ1O", // Beleuli North - NOT IN AIP
            "UT1P" => "UZ1P", // Kagan South - NOT IN AIP
            "UT1Q" => "UZ1Q", // Pakhtakor - NOT IN AIP
            "UT73" => "UZ73", // Maymanak - NOT IN AIP
            "UT77" => "UZ77", // Kungrad - NOT IN AIP
        ];

        $DA_AirportSVC = app(DA_AirportServices::class);
        $result = $DA_AirportSVC->FixAirportCodes($uzbekistan);

        flash()->success('Checked and Fixed Uzbekistan Airports (Airport, Flight, Pirep)... Codes Checked:' . $result['found'] . ', Updated:' . $result['updated'] . ', Skipped:' . $result['skipped'] . ' records.');
        return back();
    }

    // Remove Not Used Airports
    public function cleanup_airports()
    {
        $DA_AirportSVC = app(DA_AirportServices::class);
        $result = $DA_AirportSVC->RemoveUnusedAirports();

        flash()->success('Airport Cleanup Completed... Before:' . $result['total'] . ', Removed:' . $result['deleted'] . ', Remaining:' . $result['remaining'] . ' airport records.');
        return back();
    }
}
