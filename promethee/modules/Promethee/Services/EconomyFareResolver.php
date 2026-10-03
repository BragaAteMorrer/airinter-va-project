<?php

namespace Modules\Promethee\Services;

use App\Models\Fare;
use App\Models\Flight;
use App\Services\FareService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EconomyFareResolver
{
    public function __construct(private readonly FareService $fares)
    {
    }

    /**
     * Resolve the actual fare inherited by a flight.
     *
     * phpVMS pricing inheritance is:
     * base fare -> subfleet override -> flight override.
     * Prométhée must use that effective value as the initial RED reference;
     * falling back directly to the base fare (e.g. Y = 218 EUR) corrupts
     * routes whose fleet fare is different (e.g. 177 EUR).
     */
    public function rows(Flight $flight): Collection
    {
        $flight->loadMissing(['airline', 'fares', 'subfleets.fares']);

        $resolved = [];

        foreach ($flight->subfleets as $subfleet) {
            foreach ($this->fares->getFareWithOverrides($subfleet->fares, $flight->fares) as $fare) {
                if (!$fare->active) {
                    continue;
                }

                $id = (int) $fare->id;
                $resolved[$id] ??= [
                    'fare' => $fare,
                    'prices' => [],
                ];
                $resolved[$id]['prices'][] = round((float) $fare->price, 4);
            }
        }

        // Some schedules can carry a flight-level fare without a subfleet fare.
        if ($flight->subfleets->isEmpty() || empty($resolved)) {
            foreach ($this->fares->getFareWithOverrides(collect(), $flight->fares) as $fare) {
                if (!$fare->active) {
                    continue;
                }

                $id = (int) $fare->id;
                $resolved[$id] ??= [
                    'fare' => $fare,
                    'prices' => [],
                ];
                $resolved[$id]['prices'][] = round((float) $fare->price, 4);
            }
        }

        // Preserve the historic Air Inter defaults only as a last-resort
        // fallback when neither the flight nor its subfleets expose a fare.
        if (empty($resolved)) {
            $code = $flight->airline?->icao === 'ICS'
                ? 'CGO'
                : ($flight->airline?->icao === 'ACF' ? 'T' : 'Y');

            $fare = Fare::withTrashed()->where('code', $code)->first();
            if ($fare) {
                $resolved[(int) $fare->id] = [
                    'fare' => $fare,
                    'prices' => [round((float) $fare->price, 4)],
                ];
            }
        }

        return collect($resolved)->map(function (array $entry, int $fareId) use ($flight) {
            $prices = collect($entry['prices'])
                ->filter(fn ($price) => $price !== null)
                ->map(fn ($price) => round((float) $price, 4))
                ->unique()
                ->values();

            $pricing = DB::table('promethee_pricing')
                ->where('flight_id', $flight->id)
                ->where('fare_id', $fareId)
                ->first();

            // Once Prométhée has a BBR record, flight_fare contains the currently
            // discounted value. The immutable red_price is the reference price.
            $current = $prices->count() === 1
                ? (float) $prices->first()
                : ($pricing
                    ? round((float) $pricing->red_price * (float) $pricing->multiplier, 4)
                    : null);

            return [
                'fare' => $entry['fare'],
                'fare_id' => $fareId,
                'current_price' => $current,
                'effective_prices' => $prices->all(),
                'ambiguous' => $prices->count() > 1 && !$pricing,
                'pricing' => $pricing,
                'band' => $pricing?->band ?? 'rouge',
                'red_price' => $pricing ? (float) $pricing->red_price : $current,
                'multiplier' => $pricing ? (float) $pricing->multiplier : 1.0,
            ];
        })->values();
    }
}
