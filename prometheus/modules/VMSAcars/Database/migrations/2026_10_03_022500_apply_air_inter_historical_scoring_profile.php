<?php

use App\Contracts\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Restores the point/threshold profile Air Inter used in vmsACARS.
 *
 * The rules seed deliberately preserves local administrator values on update,
 * so an explicit migration is required for existing Prométhée installations.
 * PAUSE_ACTIVATED is a Hermès-only extension and is intentionally left intact.
 */
return new class() extends Migration
{
    public function up(): void
    {
        $this->apply([
            'BEACON_LIGHTS_ON_ENGINE_RUNNING' => [null, 10, 10, true, 60],
            'STROBES_ON_IN_FLIGHT' => [null, 0, 10, true, 60],
            'STROBES_ON_TAXI' => [null, 0, 30, true, 60],
            'LAND_LIGHTS_OVER_10K' => [18000, 2, 10, true, 60],
            'LAND_LIGHTS_UNDER_10K' => [5000, 2, 10, true, 60],
            'LAND_LIGHTS_OFF_TAXI' => [null, 0, 30, true, 60],
            'EXCESS_TAXI_SPEED' => [25, 5, 30, true, 60],
            'EXCESS_GFORCE' => [2, 5, 10, true, 60],
            'FUEL_REFILLED' => [null, 50, 10, true, 60],
            'OVERSPEED_WARNING' => [null, 5, 5, true, 60],
            'EXCESS_BANK' => [45, 15, 5, true, 60],
            'EXCESS_PITCH' => [20, 5, 5, true, 60],
            'RUNWAY_OVERRUN' => [null, 75, 0, false, 0],
            'SIMRATE_INCREASED' => [1, 15, 10, true, 60],
            'SLEW_ACTIVATED' => [null, 10, 15, true, 60],
            'SPEED_UNDER_10K' => [null, 5, 20, true, 60],
            'STABILIZED_APPROACH' => [1000, 10, 0, false, 0],
            'STALL_WARNING' => [null, 25, 5, true, 60],
            'THRUST_REVERSERS_INFLIGHT' => [null, 25, 5, true, 60],
            'THRUST_REVERSERS_SPEED' => [60, 2, 10, false, 60],
            'HARD_LANDING' => [450, 20, 0, false, 0],
        ]);
    }

    public function down(): void
    {
        $this->apply([
            'BEACON_LIGHTS_ON_ENGINE_RUNNING' => [null, 0, 10, true, 60],
            'STROBES_ON_IN_FLIGHT' => [null, 0, 10, true, 60],
            'STROBES_ON_TAXI' => [null, 0, 30, true, 60],
            'LAND_LIGHTS_OVER_10K' => [10000, 0, 10, true, 60],
            'LAND_LIGHTS_UNDER_10K' => [10000, 0, 10, true, 60],
            'LAND_LIGHTS_OFF_TAXI' => [null, 0, 30, true, 60],
            'EXCESS_TAXI_SPEED' => [30, 5, 30, true, 60],
            'EXCESS_GFORCE' => [1.5, 5, 10, true, 60],
            'FUEL_REFILLED' => [null, 10, 10, true, 60],
            'OVERSPEED_WARNING' => [null, 2, 10, true, 60],
            'EXCESS_BANK' => [60, 2, 10, true, 60],
            'EXCESS_PITCH' => [30, 2, 10, true, 60],
            'RUNWAY_OVERRUN' => [null, 10, 0, false, 0],
            'SIMRATE_INCREASED' => [1, 2, 10, true, 60],
            'SLEW_ACTIVATED' => [null, 2, 10, true, 60],
            'SPEED_UNDER_10K' => [null, 2, 10, true, 60],
            'STABILIZED_APPROACH' => [1500, 5, 0, false, 0],
            'STALL_WARNING' => [null, 5, 10, true, 60],
            'THRUST_REVERSERS_INFLIGHT' => [null, 2, 10, true, 60],
            'THRUST_REVERSERS_SPEED' => [60, 10, 10, false, 60],
            'HARD_LANDING' => [500, 20, 0, false, 0],
        ]);
    }

    private function apply(array $profile): void
    {
        if (!Schema::hasTable('vmsacars_rules')) return;

        foreach ($profile as $id => [$parameter, $points, $delay, $repeatable, $cooldown]) {
            DB::table('vmsacars_rules')->where('id', $id)->update([
                'parameter' => $parameter,
                'points' => $points,
                'delay' => $delay,
                'repeatable' => $repeatable,
                'cooldown' => $cooldown,
                'enabled' => true,
                'updated_at' => now(),
            ]);
        }
    }
};
