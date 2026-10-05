<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Enums\FareType;
use App\Models\Subfleet;
use App\Support\Units\Mass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Promethee\Models\AircraftConfigurationAssignment;
use Modules\Promethee\Models\AircraftHistoricalVariant;
use Modules\Promethee\Models\AircraftTypeProfile;
use RuntimeException;

final class PhpVmsAirframeImportService
{
    public const SOURCE = 'phpVMS legacy subfleet/fares';
    public const NOTE_MARKER = '[AUTO:PHPVMS_AIRFRAME_IMPORT]';

    /**
     * Summarise what can be recovered from the native phpVMS fleet tables.
     *
     * The important detail for Air Inter is that passenger/cargo capacities are
     * not aircraft columns: they live on the subfleet_fare pivot.
     */
    public function preview(): array
    {
        if (!Schema::hasTable('subfleets') || !Schema::hasTable('aircraft')) {
            return [
                'subfleets' => 0,
                'aircraft' => 0,
                'with_passenger_capacity' => 0,
                'with_cargo_capacity' => 0,
            ];
        }

        $subfleets = Subfleet::query()->with('fares')->get();

        return [
            'subfleets' => $subfleets->count(),
            'aircraft' => Aircraft::query()->count(),
            'with_passenger_capacity' => $subfleets
                ->filter(fn (Subfleet $subfleet) => $this->fareTechnicalData($subfleet)['max_pax'] ?? null)
                ->count(),
            'with_cargo_capacity' => $subfleets
                ->filter(fn (Subfleet $subfleet) => $this->fareTechnicalData($subfleet)['max_cargo'] ?? null)
                ->count(),
        ];
    }

    /**
     * Materialise the existing phpVMS fleet into Promethee's layered Airframes
     * catalogue without deleting or replacing manually curated assignments.
     *
     * Layer mapping:
     * - subfleet + fares => type defaults (PAX/cargo/cabin + legacy snapshot)
     * - normal/common aircraft weights => historical variant defaults
     * - each registration => assignment overrides for tail-specific differences
     */
    public function import(): array
    {
        foreach ([
            'subfleets',
            'aircraft',
            'promethee_aircraft_type_profiles',
            'promethee_aircraft_historical_variants',
            'promethee_aircraft_configuration_assignments',
        ] as $table) {
            if (!Schema::hasTable($table)) {
                throw new RuntimeException('Table requise absente pour l’import Airframes : '.$table);
            }
        }

        $result = [
            'subfleets' => 0,
            'types_created' => 0,
            'types_updated' => 0,
            'variants_created' => 0,
            'variants_updated' => 0,
            'aircraft' => 0,
            'assignments_created' => 0,
            'assignments_updated' => 0,
            'manual_assignments_preserved' => 0,
            'tail_overrides' => 0,
            'passenger_capacities' => 0,
            'cargo_capacities' => 0,
        ];

        DB::transaction(function () use (&$result) {
            Subfleet::query()
                ->with(['airline', 'fares', 'ranks', 'aircraft'])
                ->orderBy('id')
                ->chunkById(25, function ($subfleets) use (&$result) {
                    foreach ($subfleets as $subfleet) {
                        $aircraft = $subfleet->aircraft
                            ->sortBy('registration', SORT_NATURAL | SORT_FLAG_CASE)
                            ->values();

                        $result['subfleets']++;
                        $result['aircraft'] += $aircraft->count();

                        $fareData = $this->fareTechnicalData($subfleet);
                        if (isset($fareData['max_pax'])) $result['passenger_capacities']++;
                        if (isset($fareData['max_cargo'])) $result['cargo_capacities']++;

                        $typeKey = $this->typeKey($subfleet);
                        $variantCode = $this->variantCode($subfleet);
                        $commonWeights = $this->commonAircraftTechnicalData($aircraft);
                        $icaoType = $this->mode(
                            $aircraft->map(fn (Aircraft $plane) => strtoupper(trim((string) $plane->icao)))->filter()
                        );
                        $simbriefType = $this->firstFilled([
                            $subfleet->simbrief_type,
                            $this->mode($aircraft->pluck('simbrief_type')->filter()),
                            $icaoType,
                            $subfleet->type,
                        ]);

                        $typeImportedData = $this->deepMerge(
                            $fareData,
                            [
                                'legacy_phpvms' => $this->subfleetSnapshot($subfleet),
                            ]
                        );

                        $type = AircraftTypeProfile::query()->firstOrNew(['type_key' => $typeKey]);
                        $typeWasNew = !$type->exists;
                        $type->name = (string) ($subfleet->name ?: $subfleet->type ?: $typeKey);
                        $type->data = $this->mergeForOwnedRecord($type->data ?? [], $typeImportedData, $type->source);
                        $type->simbrief_strategy = filled($simbriefType) ? 'native' : ($type->simbrief_strategy ?: null);
                        $type->simbrief_type = $simbriefType ?: $type->simbrief_type;
                        $type->source = $typeWasNew || $type->source === self::SOURCE ? self::SOURCE : $type->source;
                        $type->notes = $this->appendMarker($type->notes, 'Import des paramètres du subfleet #'.$subfleet->id.', capacités lues dans fares.');
                        $type->historical_confidence = $type->historical_confidence ?: 'VA_configuration';
                        $type->save();
                        $result[$typeWasNew ? 'types_created' : 'types_updated']++;

                        $variant = AircraftHistoricalVariant::query()
                            ->firstOrNew(['type_key' => $typeKey, 'code' => $variantCode]);
                        $variantWasNew = !$variant->exists;
                        $variantImportedData = $this->deepMerge(
                            $commonWeights,
                            [
                                'legacy_phpvms' => [
                                    'subfleet_id' => $subfleet->id,
                                    'aircraft_count' => $aircraft->count(),
                                    'icao_types' => $aircraft
                                        ->pluck('icao')
                                        ->filter()
                                        ->map(fn ($value) => strtoupper(trim((string) $value)))
                                        ->unique()
                                        ->values()
                                        ->all(),
                                ],
                            ]
                        );

                        $variant->name = (string) ($subfleet->name ?: $subfleet->type ?: $variantCode);
                        $variant->short_name = $variant->short_name ?: substr((string) ($subfleet->name ?: $subfleet->type), 0, 80);
                        $variant->icao_type = $icaoType ?: $variant->icao_type;
                        $variant->data = $this->mergeForOwnedRecord($variant->data ?? [], $variantImportedData, $variant->source);
                        $variant->simbrief_strategy = filled($simbriefType) ? 'native' : ($variant->simbrief_strategy ?: null);
                        $variant->simbrief_type = $simbriefType ?: $variant->simbrief_type;
                        $variant->active = true;
                        $variant->source = $variantWasNew || $variant->source === self::SOURCE ? self::SOURCE : $variant->source;
                        $variant->notes = $this->appendMarker($variant->notes, 'Variante issue du subfleet phpVMS #'.$subfleet->id.'.');
                        $variant->historical_confidence = $variant->historical_confidence ?: 'VA_configuration';
                        $variant->save();
                        $result[$variantWasNew ? 'variants_created' : 'variants_updated']++;

                        foreach ($aircraft as $plane) {
                            $tailData = $this->aircraftTechnicalData($plane);
                            $tailOverrides = $this->technicalDifferences($tailData, $commonWeights);

                            if ($tailOverrides !== []) {
                                $result['tail_overrides']++;
                            }

                            $tailOverrides = $this->deepMerge(
                                $tailOverrides,
                                [
                                    'legacy_phpvms' => $this->aircraftSnapshot($plane),
                                ]
                            );

                            $current = AircraftConfigurationAssignment::query()
                                ->where('aircraft_id', $plane->id)
                                ->where('active', true)
                                ->where(function ($query) {
                                    $query->whereNull('valid_until')
                                        ->orWhereDate('valid_until', '>=', now()->toDateString());
                                })
                                ->orderByRaw('CASE WHEN valid_from IS NULL THEN 1 ELSE 0 END')
                                ->orderByDesc('valid_from')
                                ->orderByDesc('id')
                                ->first();

                            if ($current && $current->source !== self::SOURCE) {
                                // Never replace a manually curated historical assignment. We
                                // only fill missing registration-level source/technical data.
                                $current->overrides = $this->deepMerge($tailOverrides, $current->overrides ?? []);
                                $current->save();
                                $result['assignments_updated']++;
                                $result['manual_assignments_preserved']++;
                                continue;
                            }

                            if (!$current) {
                                $current = new AircraftConfigurationAssignment();
                                $current->aircraft_id = $plane->id;
                                $current->valid_from = null;
                                $current->valid_until = null;
                                $current->active = true;
                                $current->source = self::SOURCE;
                                $current->historical_confidence = 'VA_configuration';
                                $result['assignments_created']++;
                            } else {
                                $result['assignments_updated']++;
                            }

                            $current->variant_id = $variant->id;
                            $current->configuration_id = null;
                            $current->overrides = $this->mergeForOwnedRecord(
                                $current->overrides ?? [],
                                $tailOverrides,
                                $current->source
                            );
                            $current->source = self::SOURCE;
                            $current->notes = $this->appendMarker(
                                $current->notes,
                                'Paramètres de l’immatriculation importés depuis aircraft.'
                            );
                            $current->historical_confidence = 'VA_configuration';
                            $current->save();
                        }
                    }
                });
        });

        return $result;
    }

    private function fareTechnicalData(Subfleet $subfleet): array
    {
        $passenger = [];
        $cargo = [];

        foreach ($subfleet->fares as $fare) {
            $capacity = $fare->pivot?->capacity;
            if (!is_numeric($capacity)) {
                $capacity = $fare->capacity;
            }
            if (!is_numeric($capacity) || (float) $capacity <= 0) {
                continue;
            }

            $item = [
                'code' => strtoupper(trim((string) $fare->code)),
                'name' => (string) $fare->name,
                'type' => (int) $fare->type,
                'capacity' => (float) $capacity,
                'price' => is_numeric($fare->pivot?->price) ? (float) $fare->pivot->price : $fare->price,
                'cost' => is_numeric($fare->pivot?->cost) ? (float) $fare->pivot->cost : $fare->cost,
            ];

            $isCargo = (int) $fare->type === FareType::CARGO
                || in_array($item['code'], ['CGO', 'CARGO'], true);

            if ($isCargo) {
                $cargo[] = $item;
            } else {
                $passenger[] = $item;
            }
        }

        $data = [];
        if ($passenger !== []) {
            $data['max_pax'] = (int) round(array_sum(array_column($passenger, 'capacity')));
            $data['seat_configuration'] = implode(' / ', array_map(
                fn (array $fare) => ($fare['code'] ?: 'PAX').(int) round($fare['capacity']),
                $passenger
            ));
        }
        if ($cargo !== []) {
            // The Air Inter legacy fare capacities are maintained in kilograms.
            $data['max_cargo'] = round(array_sum(array_column($cargo, 'capacity')), 2);
            $data['weight_unit'] = 'kg';
        }

        return $data;
    }

    private function commonAircraftTechnicalData(Collection $aircraft): array
    {
        $rows = $aircraft->map(fn (Aircraft $plane) => $this->aircraftTechnicalData($plane));
        $data = [];

        foreach (['oew', 'mzfw', 'mtow', 'mlw'] as $field) {
            $value = $this->mode($rows->pluck($field)->filter(fn ($value) => $value !== null));
            if ($value !== null) {
                $data[$field] = (float) $value;
            }
        }

        if ($data !== []) {
            $data['weight_unit'] = 'kg';
        }

        return $data;
    }

    private function aircraftTechnicalData(Aircraft $aircraft): array
    {
        $data = array_filter([
            'oew' => $this->massKg($aircraft->dow),
            'mzfw' => $this->massKg($aircraft->zfw),
            'mtow' => $this->massKg($aircraft->mtow),
            'mlw' => $this->massKg($aircraft->mlw),
        ], fn ($value) => $value !== null);

        if ($data !== []) {
            $data['weight_unit'] = 'kg';
        }

        return $data;
    }

    private function technicalDifferences(array $tail, array $common): array
    {
        $differences = [];

        foreach (['oew', 'mzfw', 'mtow', 'mlw'] as $field) {
            if (!array_key_exists($field, $tail)) continue;

            if (!array_key_exists($field, $common)
                || abs((float) $tail[$field] - (float) $common[$field]) > 0.01) {
                $differences[$field] = $tail[$field];
            }
        }

        if ($differences !== []) {
            $differences['weight_unit'] = 'kg';
        }

        return $differences;
    }

    private function subfleetSnapshot(Subfleet $subfleet): array
    {
        return [
            'subfleet_id' => $subfleet->id,
            'airline' => $subfleet->airline?->icao,
            'hub_id' => $subfleet->hub_id,
            'type' => $subfleet->type,
            'simbrief_type' => $subfleet->simbrief_type,
            'name' => $subfleet->name,
            'fuel_type' => $subfleet->fuel_type,
            'cost_block_hour' => $subfleet->cost_block_hour,
            'cost_delay_minute' => $subfleet->cost_delay_minute,
            'ground_handling_multiplier' => $subfleet->ground_handling_multiplier,
            'fares' => $subfleet->fares->map(function ($fare) {
                $capacity = is_numeric($fare->pivot?->capacity) ? (float) $fare->pivot->capacity : $fare->capacity;

                return [
                    'code' => $fare->code,
                    'name' => $fare->name,
                    'type' => (int) $fare->type,
                    'capacity' => $capacity,
                    'price' => is_numeric($fare->pivot?->price) ? (float) $fare->pivot->price : $fare->price,
                    'cost' => is_numeric($fare->pivot?->cost) ? (float) $fare->pivot->cost : $fare->cost,
                ];
            })->values()->all(),
            'ranks' => $subfleet->ranks->map(fn ($rank) => [
                'id' => $rank->id,
                'name' => $rank->name,
                'acars_pay' => $rank->pivot?->acars_pay,
                'manual_pay' => $rank->pivot?->manual_pay,
            ])->values()->all(),
        ];
    }

    private function aircraftSnapshot(Aircraft $aircraft): array
    {
        return [
            'aircraft_id' => $aircraft->id,
            'subfleet_id' => $aircraft->subfleet_id,
            'iata' => $aircraft->iata,
            'icao' => $aircraft->icao,
            'hub_id' => $aircraft->hub_id,
            'airport_id' => $aircraft->airport_id,
            'name' => $aircraft->name,
            'registration' => $aircraft->registration,
            'fin' => $aircraft->fin,
            'hex_code' => $aircraft->hex_code,
            'selcal' => $aircraft->selcal,
            'status' => $aircraft->status,
            'simbrief_type' => $aircraft->simbrief_type,
            'weights_kg' => $this->aircraftTechnicalData($aircraft),
        ];
    }

    private function massKg(mixed $value): ?float
    {
        if ($value === null || $value === '') return null;

        if (is_object($value) && method_exists($value, 'toUnit')) {
            $converted = $value->toUnit('kg');

            return is_numeric($converted) && (float) $converted > 0
                ? round((float) $converted, 2)
                : null;
        }

        if (!is_numeric($value) || (float) $value <= 0) return null;

        try {
            $converted = Mass::make((float) $value, config('phpvms.internal_units.mass'))->toUnit('kg');

            return is_numeric($converted) ? round((float) $converted, 2) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function typeKey(Subfleet $subfleet): string
    {
        $operator = $this->key($subfleet->airline?->icao ?: 'VA');
        $type = $this->key($subfleet->type ?: $subfleet->name ?: 'AIRCRAFT');
        $value = $operator.$type;

        if (strlen($value) <= 32) return $value;

        return substr($value, 0, 25).'SF'.$subfleet->id;
    }

    private function variantCode(Subfleet $subfleet): string
    {
        $code = strtoupper(trim((string) ($subfleet->type ?: 'SF-'.$subfleet->id)));

        return substr($code, 0, 64);
    }

    private function key(mixed $value): string
    {
        return preg_replace('/[^A-Z0-9]+/', '', strtoupper(trim((string) $value))) ?: 'AIRCRAFT';
    }

    private function mode(Collection $values): mixed
    {
        if ($values->isEmpty()) return null;

        $groups = [];
        $firstIndex = [];
        foreach ($values->values() as $index => $value) {
            $key = is_numeric($value)
                ? number_format((float) $value, 2, '.', '')
                : strtoupper(trim((string) $value));

            if ($key === '') continue;
            $groups[$key] = ($groups[$key] ?? 0) + 1;
            $firstIndex[$key] ??= $index;
        }

        if ($groups === []) return null;

        uksort($groups, function ($a, $b) use ($groups, $firstIndex) {
            $count = $groups[$b] <=> $groups[$a];

            return $count !== 0 ? $count : ($firstIndex[$a] <=> $firstIndex[$b]);
        });

        $winner = array_key_first($groups);

        foreach ($values as $value) {
            $key = is_numeric($value)
                ? number_format((float) $value, 2, '.', '')
                : strtoupper(trim((string) $value));
            if ($key === $winner) return $value;
        }

        return null;
    }

    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            if (filled($value)) return trim((string) $value);
        }

        return null;
    }

    private function appendMarker(?string $notes, string $message): string
    {
        $notes = trim((string) $notes);
        $line = self::NOTE_MARKER.' '.$message;

        if (str_contains($notes, self::NOTE_MARKER)) {
            return $notes;
        }

        return trim($notes.($notes === '' ? '' : "\n").$line);
    }

    /**
     * Imported records are refreshed from phpVMS. Manually-owned records keep
     * their explicit values while still receiving missing legacy fields.
     */
    private function mergeForOwnedRecord(array $existing, array $imported, ?string $source): array
    {
        if ($source !== null && $source !== '' && $source !== self::SOURCE) {
            return $this->deepMerge($imported, $existing);
        }

        return $this->deepMerge($existing, $imported);
    }

    private function deepMerge(array ...$layers): array
    {
        $result = [];

        foreach ($layers as $layer) {
            foreach ($layer as $key => $value) {
                if ($value === null || $value === '') continue;

                if (is_array($value) && is_array($result[$key] ?? null)
                    && !array_is_list($value) && !array_is_list($result[$key])) {
                    $result[$key] = $this->deepMerge($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}
