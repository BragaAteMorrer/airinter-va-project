<?php

namespace Modules\Promethee\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BbrReferenceRepairService
{
    public function repairBlankBulkReferences(): int
    {
        if (!Schema::hasTable('promethee_pricing')
            || !Schema::hasTable('promethee_price_history')
            || !Schema::hasTable('flight_subfleet')
            || !Schema::hasTable('subfleet_fare')) {
            return 0;
        }

        $repaired = 0;

        DB::transaction(function () use (&$repaired) {
            foreach (DB::table('promethee_pricing')
                ->where('red_price', '<=', 0)
                ->orderBy('id')
                ->lockForUpdate()
                ->get() as $pricing) {
                if (!$this->lastRedMutationWasBlank(
                    (string) $pricing->flight_id,
                    (int) $pricing->fare_id
                )) {
                    continue;
                }

                $inherited = $this->inheritedSubfleetPrice(
                    (string) $pricing->flight_id,
                    (int) $pricing->fare_id
                );
                if ($inherited === null || $inherited <= 0) {
                    continue;
                }

                $key = [
                    'flight_id' => $pricing->flight_id,
                    'fare_id' => $pricing->fare_id,
                ];
                $currentFare = DB::table('flight_fare')->where($key)->first();
                $before = $currentFare?->price !== null ? (float) $currentFare->price : null;
                $multiplier = (float) ($pricing->multiplier ?: 1);
                $after = round($inherited * $multiplier, 2);

                DB::table('promethee_pricing')->where('id', $pricing->id)->update([
                    'red_price' => round($inherited, 2),
                    'updated_at' => now(),
                ]);

                DB::table('flight_fare')->updateOrInsert(
                    $key,
                    ['price' => (string) $after, 'updated_at' => now()]
                );

                DB::table('promethee_price_history')->insert([
                    'target' => 'ticket',
                    'subject_id' => (string) $pricing->flight_id,
                    'field' => 'fare:'.$pricing->fare_id,
                    'before_price' => $before,
                    'after_price' => $after,
                    'context' => json_encode([
                        'operation' => 'repair_blank_bulk_bbr_reference',
                        'band' => $pricing->band,
                        'old_red_price' => (float) $pricing->red_price,
                        'red_price' => round($inherited, 2),
                        'reason' => 'blank_bulk_red_mutation_was_cast_to_zero',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $repaired++;
            }
        });

        return $repaired;
    }

    public function repairLegacyBandOnlyReferences(): int
    {
        if (!Schema::hasTable('promethee_pricing')
            || !Schema::hasTable('promethee_price_history')
            || !Schema::hasTable('flight_subfleet')
            || !Schema::hasTable('subfleet_fare')) {
            return 0;
        }

        $repaired = 0;

        DB::transaction(function () use (&$repaired) {
            foreach (DB::table('promethee_pricing')->orderBy('id')->lockForUpdate()->get() as $pricing) {
                if ($this->hasExplicitRedPriceChange((string) $pricing->flight_id, (int) $pricing->fare_id)) {
                    continue;
                }

                $inherited = $this->inheritedSubfleetPrice((string) $pricing->flight_id, (int) $pricing->fare_id);
                if ($inherited === null || abs((float) $pricing->red_price - $inherited) < 0.005) {
                    continue;
                }

                $key = [
                    'flight_id' => $pricing->flight_id,
                    'fare_id' => $pricing->fare_id,
                ];
                $currentFare = DB::table('flight_fare')->where($key)->first();
                $before = $currentFare?->price !== null ? (float) $currentFare->price : null;
                $multiplier = (float) ($pricing->multiplier ?: 1);
                $after = round($inherited * $multiplier, 2);

                DB::table('promethee_pricing')->where('id', $pricing->id)->update([
                    'red_price' => round($inherited, 2),
                    'updated_at' => now(),
                ]);

                DB::table('flight_fare')->updateOrInsert(
                    $key,
                    ['price' => (string) $after, 'updated_at' => now()]
                );

                DB::table('promethee_price_history')->insert([
                    'target' => 'ticket',
                    'subject_id' => (string) $pricing->flight_id,
                    'field' => 'fare:'.$pricing->fare_id,
                    'before_price' => $before,
                    'after_price' => $after,
                    'context' => json_encode([
                        'operation' => 'repair_legacy_bbr_reference',
                        'band' => $pricing->band,
                        'old_red_price' => (float) $pricing->red_price,
                        'red_price' => round($inherited, 2),
                        'reason' => 'band_only_history_must_preserve_inherited_red_fare',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $repaired++;
            }
        });

        return $repaired;
    }

    public function inheritedSubfleetPrice(string $flightId, int $fareId): ?float
    {
        $rows = DB::table('flight_subfleet as flight_sf')
            ->join('subfleet_fare as sf_fare', 'sf_fare.subfleet_id', '=', 'flight_sf.subfleet_id')
            ->join('fares', 'fares.id', '=', 'sf_fare.fare_id')
            ->where('flight_sf.flight_id', $flightId)
            ->where('sf_fare.fare_id', $fareId)
            ->get([
                'fares.price as base_price',
                'sf_fare.price as subfleet_price',
            ]);

        $prices = $rows->map(function ($row) {
            $base = (float) $row->base_price;
            $override = $row->subfleet_price;

            if ($override === null || trim((string) $override) === '') {
                return round($base, 4);
            }

            $override = trim((string) $override);
            if (str_ends_with($override, '%')) {
                return round($base * ((float) rtrim($override, '%')) / 100, 4);
            }

            return round((float) $override, 4);
        })->unique()->values();

        return $prices->count() === 1 ? (float) $prices->first() : null;
    }

    private function lastRedMutationWasBlank(string $flightId, int $fareId): bool
    {
        $history = DB::table('promethee_price_history')
            ->where('target', 'ticket')
            ->where('subject_id', $flightId)
            ->where('field', 'fare:'.$fareId)
            ->orderBy('id')
            ->get(['context']);

        $lastMutationWasBlank = false;
        $sawMutation = false;

        foreach ($history as $entry) {
            $context = json_decode((string) $entry->context, true) ?: [];
            $operation = $context['operation'] ?? null;

            if (!in_array($operation, ['set', 'add', 'percent'], true)) {
                continue;
            }

            $sawMutation = true;
            $value = $context['value'] ?? null;
            $lastMutationWasBlank = $value === null || $value === '';
        }

        return $sawMutation && $lastMutationWasBlank;
    }

    private function hasExplicitRedPriceChange(string $flightId, int $fareId): bool
    {
        $history = DB::table('promethee_price_history')
            ->where('target', 'ticket')
            ->where('subject_id', $flightId)
            ->where('field', 'fare:'.$fareId)
            ->get(['change_id', 'context']);

        foreach ($history as $entry) {
            $context = json_decode((string) $entry->context, true) ?: [];
            $operation = $context['operation'] ?? null;

            if (in_array($operation, ['set', 'add', 'percent'], true)) {
                return true;
            }

            if (!$entry->change_id || !Schema::hasTable('promethee_changes')) {
                continue;
            }

            $change = DB::table('promethee_changes')->where('id', $entry->change_id)->first(['target', 'filters']);
            if (!$change) {
                continue;
            }

            if ($change->target === 'import') {
                return true;
            }

            $filters = json_decode((string) $change->filters, true) ?: [];
            if (in_array($filters['mode'] ?? null, ['set', 'add', 'percent'], true)) {
                return true;
            }
        }

        return false;
    }
}
