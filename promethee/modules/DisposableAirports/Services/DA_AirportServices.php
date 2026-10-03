<?php

namespace Modules\DisposableAirports\Services;

use App\Models\Airport;
use App\Models\Flight;
use App\Models\Pirep;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DA_AirportServices
{

    public function UpdateAirports($file_name = null)
    {
        $file_name = filled($file_name) ? $file_name : 'mwgg_airports.json';

        $check_file = Storage::disk('public')->exists($file_name);
        if ($check_file) {
            $file_modified = Storage::disk('public')->lastModified($file_name);

            if (Carbon::createFromTimestamp($file_modified)->greaterThan(Carbon::now()->subDays(3))) {
                $file_contents = Storage::disk('public')->get($file_name);
                $airports = json_decode($file_contents, true);
                Log::notice('Disposable Airports | Airport data file found and up to date. Last modified: ' . Carbon::createFromTimestamp($file_modified)->format('d.m.Y H:i') . ', Airports fetched: ' . count($airports));
            } else {
                $file_contents = $this->DownloadAirports();
                $airports = json_decode($file_contents, true);
                Log::notice('Disposable Airports | Airport data file downloaded. Total airports fetched: ' . count($airports));
            }
        } else {
            $file_contents = $this->DownloadAirports();
            $airports = json_decode($file_contents, true);
            Log::notice('Disposable Airports | Airport data file downloaded. Total airports fetched: ' . count($airports));
        }

        return $this->ProcessAirports($airports);
    }

    public function ProcessAirports($airports = [])
    {
        set_time_limit(210); // Increase time limit for possible long process

        $records_count = count($airports);
        $updated_count = 0;
        $created_count = 0;
        $skipped_count = 0;

        // Process each airport
        foreach ($airports as $icao => $data) {

            if (isset($data['icao']) && isset($data['lat']) && isset($data['lon']) && isset($data['country'])) {
                // Valid data
                $ap = Airport::withTrashed()->where('id', $icao)->first();

                if ($ap) {
                    // Update existing
                    $ap->id         = $data['icao'];
                    $ap->iata       = $data['iata'] ?? null;
                    $ap->icao       = $data['icao'];
                    $ap->name       = $data['name'] ?? $data['icao'];
                    $ap->location   = $data['city'] ?? null;
                    $ap->region     = $data['state'] ?? null;
                    $ap->country    = $data['country'];
                    $ap->timezone   = $data['tz'] ?? null;
                    $ap->lat        = $data['lat'];
                    $ap->lon        = $data['lon'];
                    $ap->elevation  = $data['elevation'];

                    $ap->save();
                    $updated_count++;
                } else {
                    // Check module setting for adding new airports
                    if (DA_Setting('dairports.update_only', true) === false) {
                        // Create new
                        Airport::create([
                            'id'        => $data['icao'],
                            'iata'      => $data['iata'] ?? null,
                            'icao'      => $data['icao'],
                            'name'      => $data['name'] ?? $data['icao'],
                            'location'  => $data['city'] ?? null,
                            'region'    => $data['state'] ?? null,
                            'country'   => $data['country'],
                            'timezone'  => $data['tz'] ?? null,
                            'lat'       => $data['lat'],
                            'lon'       => $data['lon'],
                            'elevation' => $data['elevation'] ?? null,
                        ]);
                        $created_count++;
                    }
                }
            } else {
                Log::warning('Disposable Airports | Skipped record. Incomplete data for airport: ' . $icao, [$data]);
                $skipped_count++;
                continue;
            }
        }

        Log::notice('Disposable Airports | Airport data update completed. Updated: ' . $updated_count . ', Created: ' . $created_count . ', Skipped: ' . $skipped_count);

        return ['updated' => $updated_count, 'created' => $created_count, 'skipped' => $skipped_count, 'processed' => ($records_count)];
    }

    public function DownloadAirports($source_url = null, $source_file = null, $target_file = null)
    {
        $source_url = filled($source_url) ? $source_url : 'https://raw.githubusercontent.com/mwgg/Airports/refs/heads/master';
        $source_file = filled($source_file) ? $source_file : 'airports.json';
        $target_file = filled($target_file) ? $target_file : 'mwgg_airports.json';

        $response = retry(3, function () use ($source_url, $source_file) {
            return $resp = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->withOptions([
                'timeout'         => 120,
                'connect_timeout' => 30,
                'verify'          => false,
            ])->acceptJson()->get($source_url . '/' . $source_file);
        }, 1000);

        // Log result and store file if successful
        if ($response->successful()) {
            Storage::disk('public')->put($target_file, $response->body());
            $airports = $response->body();
            Log::notice('Disposable Airports | Connection initiated and airport data obtained from source.');
        } else {
            $airports = null;
            Log::error('Disposable Airports | Connection failed !!! Status: ' . $response->status(), [$response->body()]);
        }

        return $airports;
    }

    public function FixAirportCodes($airports_array = [])
    {
        $update_count = 0;
        $skip_count = 0;

        foreach ($airports_array as $old_code => $new_code) {
            // World Airports
            $check = Airport::withTrashed()->where('id', $old_code)->count();
            $check_new = Airport::withTrashed()->where('id', $new_code)->count();
            if ($check > 0 && $check_new == 0) {
                $update = Airport::withTrashed()->where('id', $old_code)->update(['id' => $new_code, 'icao' => $new_code]);
                $update_count = $update_count + $check;
                Log::notice('AIRPORTS | ICAO code change | Updated ' . $update . ' records from ' . $old_code . ' to ' . $new_code);
            } elseif ($check_new > 0) {
                $skip_count = $skip_count + $check_new;
                Log::warning('AIRPORTS | ICAO code change skipped for ' . $old_code . ' to ' . $new_code . ' as new code already exists.');
            }

            // Flights - Departures
            $check = Flight::withTrashed()->where('dpt_airport_id', $old_code)->count();
            if ($check > 0) {
                $update = Flight::where('dpt_airport_id', $old_code)->update(['dpt_airport_id' => $new_code]);
                $update_count = $update_count + $check;
                Log::notice('FLIGHT - DEPARTURES | ICAO code change | Updated ' . $update . ' records from ' . $old_code . ' to ' . $new_code);
            }

            // Flights - Arrivals
            $check = Flight::withTrashed()->where('arr_airport_id', $old_code)->count();
            if ($check > 0) {
                $update = Flight::where('arr_airport_id', $old_code)->update(['arr_airport_id' => $new_code]);
                $update_count = $update_count + $check;
                Log::notice('FLIGHTS - ARRIVALS | ICAO code change | Updated ' . $update . ' records from ' . $old_code . ' to ' . $new_code);
            }

            // Flight - Alternates
            $check = Flight::withTrashed()->where('alt_airport_id', $old_code)->count();
            if ($check > 0) {
                $update = Flight::where('alt_airport_id', $old_code)->update(['alt_airport_id' => $new_code]);
                $update_count = $update_count + $check;
                Log::notice('FLIGHT - ALTERNATES | ICAO code changes | Updated ' . $update . ' records from ' . $old_code . ' to ' . $new_code);
            }

            // Pireps - Departures
            $check = Pirep::withTrashed()->where('dpt_airport_id', $old_code)->count();
            if ($check > 0) {
                $update = Pirep::where('dpt_airport_id', $old_code)->update(['dpt_airport_id' => $new_code]);
                $update_count = $update_count + $check;
                Log::notice('PIREP - DEPARTURES | ICAO code changes | Updated ' . $update . ' records from ' . $old_code . ' to ' . $new_code);
            }

            // Pireps - Arrivals
            $check = Pirep::withTrashed()->where('arr_airport_id', $old_code)->count();
            if ($check > 0) {
                $update = Pirep::where('arr_airport_id', $old_code)->update(['arr_airport_id' => $new_code]);
                $update_count = $update_count + $check;
                Log::notice('PIREP - ARRIVALS | ICAO code changes | Updated ' . $update . ' records from ' . $old_code . ' to ' . $new_code);
            }

            // Pireps - Alternates
            $check = Pirep::withTrashed()->where('alt_airport_id', $old_code)->count();
            if ($check > 0) {
                $update = Pirep::where('alt_airport_id', $old_code)->update(['alt_airport_id' => $new_code]);
                $update_count = $update_count + $check;
                Log::notice('PIREP - ALTERNATES | ICAO code changes | Updated ' . $update . ' records from ' . $old_code . ' to ' . $new_code);
            }
        }

        return ['found' => count($airports_array), 'updated' => $update_count, 'skipped' => $skip_count];
    }

    public function RemoveUnusedAirports()
    {
        // Count total airports before cleanup
        $airports_count = Airport::withTrashed()->count();

        // Gather all pilot locations
        $pilot_curr = User::whereNotNull('curr_airport_id')->orderBy('curr_airport_id')->groupBy('curr_airport_id')->pluck('curr_airport_id')->toArray();
        $pilot_home = User::whereNotNull('home_airport_id')->orderBy('home_airport_id')->groupBy('home_airport_id')->pluck('home_airport_id')->toArray();
        // Combine all pilot airports
        $pilot_airports = array_filter(array_unique(array_merge($pilot_curr, $pilot_home)));

        // Gather all used airports from Flights
        $origins = Flight::withTrashed()->orderBy('dpt_airport_id')->groupBy('dpt_airport_id')->pluck('dpt_airport_id')->toArray();
        $destinations = Flight::withTrashed()->orderBy('arr_airport_id')->groupBy('arr_airport_id')->pluck('arr_airport_id')->toArray();
        $alternates = Flight::withTrashed()->whereNotNull('alt_airport_id')->orderBy('alt_airport_id')->groupBy('alt_airport_id')->pluck('alt_airport_id')->toArray();
        // Combine all scheduled airports
        $scheduled_airports = array_filter(array_unique(array_merge($origins, $destinations, $alternates)));

        // Gather all used airports from PIREPs
        $pirep_origins = Pirep::withTrashed()->orderBy('dpt_airport_id')->groupBy('dpt_airport_id')->pluck('dpt_airport_id')->toArray();
        $pirep_destinations = Pirep::withTrashed()->orderBy('arr_airport_id')->groupBy('arr_airport_id')->pluck('arr_airport_id')->toArray();
        $pirep_alternates = Pirep::withTrashed()->whereNotNull('alt_airport_id')->orderBy('alt_airport_id')->groupBy('alt_airport_id')->pluck('alt_airport_id')->toArray();
        // Combine all flown airports
        $flown_airports = array_filter(array_unique(array_merge($pirep_origins, $pirep_destinations, $pirep_alternates)));

        // Combine scheduled and flown airports
        $combined_airports = array_unique(array_merge($pilot_airports, $scheduled_airports, $flown_airports));

        // Delete all airports not in the combined list
        $airports_deleted = Airport::withTrashed()->whereNotIn('id', $combined_airports)->forceDelete();
        Log::notice('Disposable Airports | Unused airport cleanup completed. Total deleted: ' . $airports_deleted);

        return ['total' => $airports_count, 'deleted' => $airports_deleted, 'remaining' => ($airports_count - $airports_deleted)];
    }
}
