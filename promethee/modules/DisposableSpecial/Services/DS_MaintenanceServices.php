<?php

namespace Modules\DisposableSpecial\Services;

use App\Models\Enums\AircraftStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\DisposableSpecial\Models\DS_Maintenance;

class DS_MaintenanceServices
{
    // Process Maintenance Records and Release Aircraft Back To Service
    public function ProcessMaintenance()
    {
        $current_time = Carbon::now();
        $active_maint_ops = DS_Maintenance::with('aircraft')->whereNotNull('act_end')->where('act_end', '<', $current_time)->get();
        $hasPrometheeAirframe = Schema::hasTable('promethee_airframe_maintenance');
        foreach ($active_maint_ops as $active) {
            $prometheeState = $hasPrometheeAirframe
                ? DB::table('promethee_airframe_maintenance')->where('aircraft_id', $active->aircraft_id)->first()
                : null;

            // Prométhée owns the lifecycle and check counter resets for these visits.
            // The legacy cron must not clear them or release the aircraft prematurely.
            if ($prometheeState && $prometheeState->active_check) {
                continue;
            }

            $active->last_note = $active->act_note;
            $active->last_time = $active->act_end;
            $active->act_note = null;
            $active->act_start = null;
            $active->act_end = null;

            if ($active->aircraft && $active->aircraft->status === AircraftStatus::MAINTENANCE
                && !($prometheeState->safety_hold_at ?? null)
                && !($prometheeState->ferry_status ?? null)) {
                $active->aircraft->status = AircraftStatus::ACTIVE;
                $active->aircraft->save();
            }

            $active->save();
            Log::info('Disposable Special | '.$active->aircraft->registration.' released back to service after '.$active->last_note);
        }
    }
}
