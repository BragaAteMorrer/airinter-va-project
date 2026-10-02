<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Schema;
use Modules\Promethee\Models\AircraftConfigurationAssignment;
use Modules\Promethee\Models\AircraftHistoricalVariant;
use Modules\Promethee\Models\AircraftModification;
use Modules\Promethee\Models\AircraftTypeProfile;
use Modules\Promethee\Models\AirframeConfiguration;
use Modules\Promethee\Models\AirframeSimulatorProfile;

class AircraftConfigurationResolver
{
    public function resolveByRegistration(string $registration, CarbonInterface|string|null $date = null): array
    {
        $aircraft = Aircraft::query()
            ->with('subfleet')
            ->whereRaw('UPPER(registration) = ?', [strtoupper(trim($registration))])
            ->firstOrFail();

        return $this->resolveAircraft($aircraft, $date);
    }

    public function resolveAircraft(Aircraft $aircraft, CarbonInterface|string|null $date = null): array
    {
        $at = $this->date($date);
        $aircraft->loadMissing('subfleet');

        $assignment = $this->assignment($aircraft, $at);
        $variant = $assignment?->variant;
        $configuration = $assignment?->configuration;

        // A configuration always belongs to one historical variant. Prefer that
        // relation if old/imported assignment data points at a different ID.
        if ($configuration && (!$variant || (int) $configuration->variant_id !== (int) $variant->id)) {
            $variant = $configuration->variant;
        }

        // Once a registration has an explicit historical variant, its type_key
        // is the most reliable link to the type defaults (important for legacy
        // ICAO codes such as A30B vs the administrative A300 family key).
        $typeProfile = $this->typeProfile($aircraft, $variant?->type_key);

        $base = $this->coreData($aircraft);
        $effective = $this->merge(
            $base,
            $typeProfile?->data ?? [],
            $variant?->data ?? [],
            $configuration?->data ?? [],
            $assignment?->overrides ?? []
        );

        $simbrief = $this->resolveSimBrief(
            $aircraft,
            $typeProfile,
            $variant,
            $configuration,
            $assignment?->overrides ?? [],
            $effective
        );

        return [
            'registration' => strtoupper((string) $aircraft->registration),
            'resolved_at' => $at->toDateString(),
            'aircraft' => [
                'id' => $aircraft->id,
                'registration' => strtoupper((string) $aircraft->registration),
                'icao' => strtoupper((string) $aircraft->icao),
                'name' => $aircraft->name,
                'subfleet_id' => $aircraft->subfleet_id,
                'subfleet' => $aircraft->subfleet?->name,
                'type_key' => $typeProfile?->type_key ?? $this->typeKey($aircraft),
                'type_name' => $typeProfile?->name ?? ($aircraft->subfleet?->name ?: $aircraft->name),
            ],
            'type_profile' => $this->profileDto($typeProfile),
            'variant' => $this->variantDto($variant),
            'configuration' => $this->configurationDto($configuration, $effective),
            'period' => $assignment ? [
                'valid_from' => optional($assignment->valid_from)?->toDateString(),
                'valid_until' => optional($assignment->valid_until)?->toDateString(),
                'active' => (bool) $assignment->active,
            ] : null,
            'effective' => $effective,
            'simbrief' => $simbrief,
            'simulator_profiles' => $this->simulatorProfiles($variant, $configuration),
            'modifications' => $this->modifications($aircraft, $variant, $configuration, $at),
            'provenance' => array_values(array_filter([
                $typeProfile ? [
                    'layer' => 'aircraft_type',
                    'source' => $typeProfile->source,
                    'source_url' => $typeProfile->source_url,
                    'confidence' => $typeProfile->historical_confidence,
                ] : null,
                $variant ? [
                    'layer' => 'historical_variant',
                    'source' => $variant->source,
                    'source_url' => $variant->source_url,
                    'confidence' => $variant->historical_confidence,
                ] : null,
                $configuration ? [
                    'layer' => 'airframe_configuration',
                    'source' => $configuration->source,
                    'source_url' => $configuration->source_url,
                    'confidence' => $configuration->historical_confidence,
                ] : null,
                $assignment ? [
                    'layer' => 'registration',
                    'source' => $assignment->source,
                    'source_url' => $assignment->source_url,
                    'confidence' => $assignment->historical_confidence,
                ] : null,
            ])),
        ];
    }

    private function assignment(Aircraft $aircraft, CarbonInterface $at): ?AircraftConfigurationAssignment
    {
        if (!Schema::hasTable('promethee_aircraft_configuration_assignments')) {
            return null;
        }

        return AircraftConfigurationAssignment::query()
            ->with(['variant', 'configuration.variant'])
            ->where('aircraft_id', $aircraft->id)
            ->where('active', true)
            ->where(function ($query) use ($at) {
                $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', $at->toDateString());
            })
            ->where(function ($query) use ($at) {
                $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', $at->toDateString());
            })
            ->orderByRaw('CASE WHEN valid_from IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('valid_from')
            ->orderByDesc('id')
            ->first();
    }

    private function typeProfile(Aircraft $aircraft, ?string $preferredTypeKey = null): ?AircraftTypeProfile
    {
        if (!Schema::hasTable('promethee_aircraft_type_profiles')) {
            return null;
        }

        $candidates = collect([
            $this->normaliseKey($preferredTypeKey),
            $this->normaliseKey($aircraft->icao),
            $this->normaliseKey($aircraft->subfleet?->type),
            $this->normaliseKey($aircraft->subfleet?->simbrief_type),
            $this->normaliseKey($aircraft->name),
            $this->normaliseKey($aircraft->subfleet?->name),
        ])->filter()->unique()->values()->all();

        if ($candidates === []) return null;

        $profiles = AircraftTypeProfile::query()
            ->whereIn('type_key', $candidates)
            ->get()
            ->keyBy('type_key');

        foreach ($candidates as $candidate) {
            if ($profiles->has($candidate)) return $profiles->get($candidate);
        }

        return null;
    }

    private function coreData(Aircraft $aircraft): array
    {
        return array_filter([
            'icao_type' => $this->normaliseKey($aircraft->icao ?: $aircraft->subfleet?->type),
            'oew' => $this->number($aircraft->dow),
            'mzfw' => $this->number($aircraft->zfw),
            'mtow' => $this->number($aircraft->mtow),
            'mlw' => $this->number($aircraft->mlw),
            'max_fuel' => $this->number($aircraft->subfleet?->fuel_capacity),
            'max_cargo' => $this->number($aircraft->subfleet?->cargo_capacity),
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function resolveSimBrief(
        Aircraft $aircraft,
        ?AircraftTypeProfile $typeProfile,
        ?AircraftHistoricalVariant $variant,
        ?AirframeConfiguration $configuration,
        array $registrationOverrides,
        array $effective
    ): array {
        $state = [
            'strategy' => 'native',
            'type' => $this->firstFilled([
                $aircraft->simbrief_type,
                $aircraft->subfleet?->simbrief_type,
                $aircraft->icao,
                $aircraft->subfleet?->type,
            ]),
            'internal_id' => null,
            'proxy_type' => null,
            'source' => 'phpvms',
        ];

        foreach ([
            ['aircraft_type', $typeProfile],
            ['historical_variant', $variant],
            ['airframe_configuration', $configuration],
        ] as [$source, $model]) {
            if (!$model) continue;

            foreach ([
                'strategy' => 'simbrief_strategy',
                'type' => 'simbrief_type',
                'internal_id' => 'simbrief_internal_id',
                'proxy_type' => 'simbrief_proxy_type',
            ] as $target => $column) {
                if (filled($model->{$column})) {
                    $state[$target] = trim((string) $model->{$column});
                    $state['source'] = $source;
                }
            }
        }

        $override = is_array($registrationOverrides['simbrief'] ?? null)
            ? $registrationOverrides['simbrief']
            : [];
        foreach (['strategy', 'type', 'internal_id', 'proxy_type'] as $key) {
            if (filled($override[$key] ?? null)) {
                $state[$key] = trim((string) $override[$key]);
                $state['source'] = 'registration_override';
            }
        }

        $strategy = strtolower((string) ($state['strategy'] ?: 'native'));
        if (!in_array($strategy, ['native', 'internal_id', 'proxy'], true)) {
            $strategy = 'native';
        }

        $value = match ($strategy) {
            'proxy' => $state['proxy_type'] ?: $state['type'],
            'internal_id' => $state['internal_id'] ?: $state['type'],
            default => $state['internal_id'] ?: $state['type'],
        };

        $acdata = $this->simBriefAircraftData($aircraft, $variant, $effective, $strategy);

        return [
            'strategy' => $strategy,
            'type' => $this->upper($state['type']),
            'internal_id' => $state['internal_id'] ?: null,
            'proxy_type' => $this->upper($state['proxy_type']),
            'base_type' => $strategy === 'proxy'
                ? $this->upper($state['proxy_type'] ?: $state['type'])
                : $this->upper($state['type']),
            'value' => $value ? strtoupper((string) $value) : null,
            'source' => $state['source'],
            'actual_aircraft' => $this->normaliseKey($aircraft->icao ?: $aircraft->subfleet?->type),
            'actual_variant' => $variant?->code,
            'acdata' => $acdata,
        ];
    }

    /**
     * Build SimBrief's documented acdata payload. SimBrief requires all weight
     * values in thousands of pounds regardless of the OFP display unit, so we
     * only transmit weights when sb-airframe explicitly records their unit.
     */
    private function simBriefAircraftData(
        Aircraft $aircraft,
        ?AircraftHistoricalVariant $variant,
        array $effective,
        string $strategy
    ): array {
        $data = [];
        $weightUnit = strtolower((string) ($effective['weight_unit'] ?? ''));

        if (isset($effective['max_pax']) && (int) $effective['max_pax'] >= 0) {
            $data['maxpax'] = (string) (int) $effective['max_pax'];
        }

        foreach ([
            'oew' => 'oew',
            'mzfw' => 'mzfw',
            'mtow' => 'mtow',
            'mlw' => 'mlw',
            'max_fuel' => 'maxfuel',
        ] as $source => $target) {
            $converted = $this->toThousandsOfPounds($effective[$source] ?? null, $weightUnit);
            if ($converted !== null) $data[$target] = $converted;
        }

        $category = strtoupper(trim((string) ($effective['weight_category'] ?? '')));
        $equipment = trim((string) ($effective['equipment'] ?? ''));
        $transponder = trim((string) ($effective['transponder'] ?? ''));
        if ($category !== '' && $equipment !== '' && $transponder !== '') {
            $data['cat'] = $category;
            $data['equip'] = strtoupper($equipment);
            $data['transponder'] = strtoupper($transponder);
        }

        if (filled($effective['pbn'] ?? null)) {
            $pbn = strtoupper(trim((string) $effective['pbn']));
            $data['pbn'] = str_starts_with($pbn, 'PBN/') ? $pbn : 'PBN/'.$pbn;
        }

        if (filled($aircraft->hex_code)) {
            $data['hexcode'] = strtoupper((string) $aircraft->hex_code);
        }

        // For unsupported types, SimBrief explicitly supports spoofing the real
        // identity on top of a similar proxy performance model.
        if ($strategy === 'proxy') {
            $actualIcao = $this->normaliseKey($aircraft->icao ?: $aircraft->subfleet?->type);
            if ($actualIcao) $data['icao'] = substr($actualIcao, 0, 4);

            $name = $variant?->short_name ?: $variant?->name ?: $aircraft->name;
            if (filled($name)) $data['name'] = substr(trim((string) $name), 0, 12);

            $engine = is_array($effective['engine'] ?? null) ? $effective['engine'] : [];
            $engineLabel = $engine['simbrief_label'] ?? null;
            if (!filled($engineLabel)) {
                $engineLabel = trim(implode(' ', array_filter([
                    $engine['model'] ?? null,
                    $engine['variant'] ?? null,
                ])));
            }
            if (filled($engineLabel)) $data['engines'] = substr((string) $engineLabel, 0, 12);
        }

        return $data;
    }

    private function toThousandsOfPounds(mixed $value, string $unit): ?float
    {
        if ($value === null || $value === '' || !is_numeric($value)) return null;

        $weight = (float) $value;
        $pounds = match ($unit) {
            'kg', 'kgs' => $weight * 2.2046226218,
            'lb', 'lbs' => $weight,
            'klb' => $weight * 1000,
            default => null,
        };

        return $pounds === null ? null : round($pounds / 1000, 3);
    }

    private function simulatorProfiles(
        ?AircraftHistoricalVariant $variant,
        ?AirframeConfiguration $configuration
    ): array {
        if (!$variant || !Schema::hasTable('promethee_airframe_simulator_profiles')) {
            return [];
        }

        return AirframeSimulatorProfile::query()
            ->with('simbriefAirframe')
            ->where('variant_id', $variant->id)
            ->where('active', true)
            ->where(function ($query) use ($configuration) {
                $query->whereNull('configuration_id');
                if ($configuration) {
                    $query->orWhere('configuration_id', $configuration->id);
                }
            })
            ->orderByRaw('configuration_id IS NULL')
            ->orderBy('simulator')
            ->orderBy('addon_name')
            ->get()
            ->unique(fn ($profile) => strtolower($profile->simulator.'|'.$profile->addon_name))
            ->map(fn ($profile) => [
                'id' => $profile->id,
                'simulator' => $profile->simulator,
                'addon_name' => $profile->addon_name,
                'addon_version' => $profile->addon_version,
                'aircraft_identifier' => $profile->aircraft_identifier,
                'telemetry_profile' => $profile->telemetry_profile,
                'simbrief_airframe_id' => $profile->simbrief_airframe_id,
                'simbrief_type' => $profile->simbriefAirframe?->airframe_id
                    ?: $profile->simbriefAirframe?->icao,
            ])
            ->values()
            ->all();
    }

    private function modifications(
        Aircraft $aircraft,
        ?AircraftHistoricalVariant $variant,
        ?AirframeConfiguration $configuration,
        CarbonInterface $at
    ): array {
        if (!Schema::hasTable('promethee_aircraft_modifications')) return [];

        return AircraftModification::query()
            ->where(function ($query) use ($aircraft, $variant, $configuration) {
                $query->where('aircraft_id', $aircraft->id);
                if ($variant) $query->orWhere('variant_id', $variant->id);
                if ($configuration) $query->orWhere('configuration_id', $configuration->id);
            })
            ->where(function ($query) use ($at) {
                $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $at->toDateString());
            })
            ->where(function ($query) use ($at) {
                $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', $at->toDateString());
            })
            ->orderBy('effective_from')
            ->get()
            ->map(fn ($modification) => [
                'id' => $modification->id,
                'name' => $modification->name,
                'category' => $modification->category,
                'description' => $modification->description,
                'effective_from' => optional($modification->effective_from)?->toDateString(),
                'effective_until' => optional($modification->effective_until)?->toDateString(),
                'previous_value' => $modification->previous_value,
                'new_value' => $modification->new_value,
                'source' => $modification->source,
                'source_url' => $modification->source_url,
            ])
            ->all();
    }

    private function profileDto(?AircraftTypeProfile $profile): ?array
    {
        if (!$profile) return null;

        return [
            'id' => $profile->id,
            'type_key' => $profile->type_key,
            'name' => $profile->name,
            'historical_confidence' => $profile->historical_confidence,
        ];
    }

    private function variantDto(?AircraftHistoricalVariant $variant): ?array
    {
        if (!$variant) return null;

        return [
            'id' => $variant->id,
            'code' => $variant->code,
            'name' => $variant->name,
            'short_name' => $variant->short_name,
            'icao_type' => $variant->icao_type,
            'manufacturer_variant' => $variant->manufacturer_variant,
            'operator_variant' => $variant->operator_variant,
            'valid_from' => optional($variant->valid_from)?->toDateString(),
            'valid_until' => optional($variant->valid_until)?->toDateString(),
            'historical_confidence' => $variant->historical_confidence,
        ];
    }

    private function configurationDto(?AirframeConfiguration $configuration, array $effective): ?array
    {
        if (!$configuration) return null;

        return [
            'id' => $configuration->id,
            'code' => $configuration->code,
            'name' => $configuration->name,
            'kind' => $configuration->configuration_kind,
            'phase' => $configuration->phase,
            'valid_from' => optional($configuration->valid_from)?->toDateString(),
            'valid_until' => optional($configuration->valid_until)?->toDateString(),
            'historical_confidence' => $configuration->historical_confidence,
            'max_pax' => $effective['max_pax'] ?? null,
            'seat_configuration' => $effective['seat_configuration'] ?? null,
            'oew' => $effective['oew'] ?? null,
            'mzfw' => $effective['mzfw'] ?? null,
            'mtow' => $effective['mtow'] ?? null,
            'mlw' => $effective['mlw'] ?? null,
            'max_fuel' => $effective['max_fuel'] ?? null,
            'max_cargo' => $effective['max_cargo'] ?? null,
            'engine' => $effective['engine'] ?? null,
        ];
    }

    private function merge(array ...$layers): array
    {
        $result = [];

        foreach ($layers as $layer) {
            foreach ($layer as $key => $value) {
                if ($value === null || $value === '') continue;

                if (is_array($value) && is_array($result[$key] ?? null)) {
                    $result[$key] = $this->merge($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    private function date(CarbonInterface|string|null $date): CarbonInterface
    {
        if ($date instanceof CarbonInterface) return $date;
        if (filled($date)) return Carbon::parse((string) $date)->startOfDay();

        return now()->startOfDay();
    }

    private function typeKey(Aircraft $aircraft): string
    {
        return $this->normaliseKey(
            $aircraft->icao
                ?: $aircraft->subfleet?->type
                ?: $aircraft->subfleet?->name
                ?: $aircraft->name
        ) ?: 'AIRCRAFT';
    }

    private function normaliseKey(mixed $value): ?string
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '') return null;

        return preg_replace('/[^A-Z0-9]+/', '', $value) ?: null;
    }

    private function upper(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : strtoupper($value);
    }

    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            if (filled($value)) return trim((string) $value);
        }
        return null;
    }

    private function number(mixed $value): int|float|null
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) return $value + 0;
        if (is_object($value) && method_exists($value, '__toString') && is_numeric((string) $value)) {
            return ((string) $value) + 0;
        }

        return null;
    }
}
