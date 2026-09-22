<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Enums\AircraftState;
use App\Models\Pirep;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Projects Hermès observations onto the existing phpVMS aircraft record.
 *
 * No parallel fleet ledger is created: phpVMS aircraft remains authoritative
 * for location, accumulated time, fuel and current operational state.
 */
class AircraftOperationalStateService
{
    public function snapshot(Pirep $pirep): array
    {
        $aircraft = $pirep->aircraft;
        if (!$aircraft) return ['available' => false];

        $samples = DB::table('promethee_telemetry')
            ->where('pirep_id', $pirep->id)->orderBy('recorded_at')->get();

        $first = $samples->first();
        $last = $samples->last();
        $firstPayload = $first ? (json_decode($first->payload, true) ?: []) : [];
        $lastPayload = $last ? (json_decode($last->payload, true) ?: []) : [];
        $phase = strtoupper((string) ($lastPayload['phase'] ?? ''));
        $onGround = $lastPayload['on_ground'] ?? null;

        return [
            'available' => true,
            'aircraft_id' => $aircraft->id,
            'registration' => $aircraft->registration,
            'airport' => $aircraft->airport_id,
            'state' => $aircraft->state,
            'flight_time_minutes' => (float) $aircraft->flight_time,
            'fuel_onboard' => $this->scalar($aircraft->fuel_onboard),
            'observed' => [
                'phase' => $phase ?: null,
                'on_ground' => $onGround,
                'fuel' => isset($lastPayload['fuel']) ? (float) $lastPayload['fuel'] : null,
                'first_at' => $first?->recorded_at,
                'last_at' => $last?->recorded_at,
                'elapsed_minutes' => ($first && $last)
                    ? max(0, CarbonImmutable::parse($first->recorded_at)->diffInMinutes(CarbonImmutable::parse($last->recorded_at)))
                    : 0,
                'samples' => $samples->count(),
            ],
        ];
    }

    /**
     * Apply only facts supported by a completed PIREP/telemetry archive.
     * This method is deliberately idempotent: the location is a set, not an
     * increment, and accumulated flight time is left to phpVMS acceptance.
     */
    public function reconcile(Pirep $pirep): array
    {
        $pirep->loadMissing('aircraft');
        $aircraft = $pirep->aircraft;
        if (!$aircraft) return ['updated' => false, 'reason' => 'NO_AIRCRAFT'];

        $last = DB::table('promethee_telemetry')->where('pirep_id', $pirep->id)->latest('recorded_at')->first();
        if (!$last) return ['updated' => false, 'reason' => 'NO_TELEMETRY'];

        $payload = json_decode($last->payload, true) ?: [];
        $phase = strtoupper((string) ($payload['phase'] ?? ''));
        $arrived = in_array($phase, ['LANDING', 'TAXI_IN', 'IN'], true) && ($payload['on_ground'] ?? true);

        if (!$arrived) return ['updated' => false, 'reason' => 'NOT_ARRIVED'];

        $before = $this->aircraftDto($aircraft);
        $aircraft->airport_id = $pirep->arr_airport_id;
        $aircraft->state = AircraftState::PARKED;
        if (isset($payload['fuel']) && is_numeric($payload['fuel']) && (float) $payload['fuel'] >= 0) {
            $aircraft->fuel_onboard = (float) $payload['fuel'];
        }
        $aircraft->landing_time = $last->recorded_at;
        $aircraft->save();
        $aircraft->refresh();

        return [
            'updated' => true,
            'reason' => 'ARRIVAL_RECONCILED',
            'before' => $before,
            'after' => $this->aircraftDto($aircraft),
            'flight_time_source' => 'phpvms_pirep_acceptance',
        ];
    }

    private function aircraftDto(Aircraft $aircraft): array
    {
        return [
            'id' => $aircraft->id,
            'registration' => $aircraft->registration,
            'airport' => $aircraft->airport_id,
            'state' => $aircraft->state,
            'flight_time_minutes' => (float) $aircraft->flight_time,
            'fuel_onboard' => $this->scalar($aircraft->fuel_onboard),
            'landing_time' => optional($aircraft->landing_time)?->toIso8601String(),
        ];
    }

    private function scalar(mixed $value): mixed
    {
        if (is_scalar($value) || $value === null) return $value;
        if (is_object($value)) {
            foreach (['value', 'local', 'raw'] as $property) {
                if (isset($value->{$property}) && is_scalar($value->{$property})) return $value->{$property};
            }
            if (method_exists($value, '__toString')) return (string) $value;
        }
        return null;
    }
}
