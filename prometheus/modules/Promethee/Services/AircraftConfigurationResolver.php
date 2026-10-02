<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AircraftConfigurationResolver
{
    private const OVERRIDABLE = [
        'max_pax','seat_configuration','oew','mzfw','mtow','mlw','max_fuel','max_cargo',
        'engine_manufacturer','engine_model','engine_variant','engine_count','engine_simbrief_label',
        'cruise_speed','cruise_mach','ceiling','range_nm','equipment','transponder','pbn',
        'simbrief_strategy','simbrief_type','simbrief_internal_id','simbrief_proxy_type',
        'fuel_factor','climb_profile','cruise_profile','descent_profile',
    ];

    public function resolveByRegistration(string $registration, mixed $date = null, bool $preferVaActive = true): array
    {
        $aircraft = Aircraft::query()
            ->with('subfleet')
            ->whereRaw('UPPER(registration) = ?', [strtoupper(trim($registration))])
            ->firstOrFail();

        return $this->resolve($aircraft, $date, $preferVaActive);
    }

    public function resolve(Aircraft $aircraft, mixed $date = null, bool $preferVaActive = true): array
    {
        $on = $date ? CarbonImmutable::parse($date)->startOfDay() : CarbonImmutable::today();

        $assignment = $this->assignmentFor($aircraft, $on, $preferVaActive);
        $variant = $assignment?->variant_id
            ? DB::table('promethee_aircraft_variants')->where('id', $assignment->variant_id)->first()
            : null;
        $configuration = $assignment?->configuration_id
            ? DB::table('promethee_airframe_configurations')->where('id', $assignment->configuration_id)->first()
            : null;

        if (!$variant && $configuration?->variant_id) {
            $variant = DB::table('promethee_aircraft_variants')->where('id', $configuration->variant_id)->first();
        }

        $base = $this->aircraftTypeDefaults($aircraft);
        $resolved = $base;
        $provenance = array_fill_keys(self::OVERRIDABLE, 'aircraft_type');

        $this->applyRow($resolved, $provenance, $variant, 'aircraft_variant');
        $this->applyRow($resolved, $provenance, $configuration, 'historical_configuration');
        if ($assignment) {
            $this->applyJsonOverrides($resolved, $provenance, $assignment->overrides ?? null, 'registration_override');
        }

        $this->validateResolved($resolved);

        $profiles = $this->simulatorProfiles($variant?->id, $configuration?->id);
        $simbrief = $this->simbriefProfile($resolved);

        return [
            'registration' => (string) $aircraft->registration,
            'aircraft_id' => $aircraft->id,
            'aircraft' => [
                'icao' => $base['icao_type'],
                'name' => (string) ($aircraft->name ?: $aircraft->subfleet?->name ?: $base['icao_type']),
                'type_key' => $base['aircraft_type_key'],
            ],
            'variant' => $variant ? [
                'id' => $variant->id,
                'code' => $variant->short_name ?: $variant->manufacturer_variant ?: $variant->name,
                'name' => $variant->name,
                'manufacturer_variant' => $variant->manufacturer_variant,
                'operator_variant' => $variant->operator_variant,
                'phase' => $variant->phase,
                'historical_confidence' => $variant->historical_confidence,
            ] : null,
            'configuration' => $configuration ? [
                'id' => $configuration->id,
                'code' => $configuration->code,
                'name' => $configuration->name,
                'kind' => $configuration->kind,
                'phase' => $configuration->phase,
                'valid_from' => $configuration->valid_from,
                'valid_until' => $configuration->valid_until,
                'historical_confidence' => $configuration->historical_confidence,
            ] : null,
            'assignment' => $assignment ? [
                'id' => $assignment->id,
                'valid_from' => $assignment->valid_from,
                'valid_until' => $assignment->valid_until,
                'active_for_va' => (bool) $assignment->active_for_va,
                'historical_confidence' => $assignment->historical_confidence,
            ] : null,
            'resolved' => Arr::only($resolved, array_merge(self::OVERRIDABLE, [
                'aircraft_type_key','icao_type',
            ])),
            'provenance' => $provenance,
            'simbrief' => $simbrief,
            'simulator_profiles' => $profiles,
            'resolved_for' => $on->toDateString(),
        ];
    }

    private function assignmentFor(Aircraft $aircraft, CarbonImmutable $on, bool $preferVaActive): ?object
    {
        $query = DB::table('promethee_aircraft_configuration_assignments')
            ->where('aircraft_id', $aircraft->id)
            ->where(function ($q) use ($on) {
                $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', $on->toDateString());
            })
            ->where(function ($q) use ($on) {
                $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', $on->toDateString());
            });

        if ($preferVaActive) {
            $active = (clone $query)->where('active_for_va', true)->orderByDesc('valid_from')->first();
            if ($active) return $active;
        }

        return $query->orderByDesc('valid_from')->orderByDesc('id')->first();
    }

    private function aircraftTypeDefaults(Aircraft $aircraft): array
    {
        $typeKey = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) (
            $aircraft->icao ?: $aircraft->subfleet?->type ?: $aircraft->subfleet?->simbrief_type ?: $aircraft->subfleet?->name
        )));
        $icao = strtoupper((string) ($aircraft->icao ?: $aircraft->subfleet?->type ?: $aircraft->subfleet?->simbrief_type ?: $typeKey));

        return [
            'aircraft_type_key' => $typeKey,
            'icao_type' => $icao,
            'max_pax' => $this->nullableInt($aircraft->max_pax ?? $aircraft->subfleet?->max_pax ?? null),
            'seat_configuration' => null,
            'oew' => $this->nullableInt($aircraft->oew ?? null),
            'mzfw' => $this->nullableInt($aircraft->mzfw ?? null),
            'mtow' => $this->nullableInt($aircraft->mtow ?? null),
            'mlw' => $this->nullableInt($aircraft->mlw ?? null),
            'max_fuel' => $this->nullableInt($aircraft->max_fuel ?? null),
            'max_cargo' => $this->nullableInt($aircraft->max_cargo ?? null),
            'engine_manufacturer' => null,
            'engine_model' => null,
            'engine_variant' => null,
            'engine_count' => null,
            'engine_simbrief_label' => null,
            'cruise_speed' => null,
            'cruise_mach' => null,
            'ceiling' => null,
            'range_nm' => null,
            'equipment' => null,
            'transponder' => null,
            'pbn' => null,
            'simbrief_strategy' => filled($aircraft->simbrief_type ?? null) ? 'type' : null,
            'simbrief_type' => $aircraft->simbrief_type ?? $aircraft->subfleet?->simbrief_type ?? $icao,
            'simbrief_internal_id' => null,
            'simbrief_proxy_type' => null,
            'fuel_factor' => null,
            'climb_profile' => null,
            'cruise_profile' => null,
            'descent_profile' => null,
        ];
    }

    private function applyRow(array &$resolved, array &$provenance, ?object $row, string $source): void
    {
        if (!$row) return;
        foreach (self::OVERRIDABLE as $field) {
            if (property_exists($row, $field) && $row->{$field} !== null && $row->{$field} !== '') {
                $resolved[$field] = $row->{$field};
                $provenance[$field] = $source;
            }
        }
        $this->applyJsonOverrides($resolved, $provenance, $row->overrides ?? null, $source);
    }

    private function applyJsonOverrides(array &$resolved, array &$provenance, mixed $json, string $source): void
    {
        if (!$json) return;
        $values = is_array($json) ? $json : json_decode((string) $json, true);
        if (!is_array($values)) return;

        foreach ($values as $field => $value) {
            if (!in_array($field, self::OVERRIDABLE, true) || $value === null || $value === '') continue;
            $resolved[$field] = $value;
            $provenance[$field] = $source;
        }
    }

    private function simulatorProfiles(?int $variantId, ?int $configurationId): array
    {
        return DB::table('promethee_aircraft_simulator_profiles')
            ->where('active', true)
            ->where(function ($q) use ($variantId, $configurationId) {
                if ($configurationId) $q->orWhere('configuration_id', $configurationId);
                if ($variantId) $q->orWhere('variant_id', $variantId);
            })
            ->orderBy('simulator')
            ->orderBy('addon_name')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'simulator' => $row->simulator,
                'addon_name' => $row->addon_name,
                'addon_version' => $row->addon_version,
                'aircraft_identifier' => $row->aircraft_identifier,
                'simbrief_airframe' => $row->simbrief_airframe,
                'telemetry_profile' => $row->telemetry_profile,
                'scope' => $row->configuration_id ? 'configuration' : 'variant',
            ])
            ->values()
            ->all();
    }

    private function simbriefProfile(array $resolved): array
    {
        $strategy = $resolved['simbrief_strategy'] ?: (
            filled($resolved['simbrief_internal_id']) ? 'internal_id' : (
                filled($resolved['simbrief_proxy_type']) ? 'proxy' : 'type'
            )
        );

        return [
            'strategy' => $strategy,
            'type' => $resolved['simbrief_type'] ?: $resolved['icao_type'],
            'internal_id' => $resolved['simbrief_internal_id'],
            'proxy_type' => $resolved['simbrief_proxy_type'],
            'effective_type' => match ($strategy) {
                'internal_id' => $resolved['simbrief_internal_id'] ?: $resolved['simbrief_type'] ?: $resolved['icao_type'],
                'proxy' => $resolved['simbrief_proxy_type'] ?: $resolved['simbrief_type'] ?: $resolved['icao_type'],
                default => $resolved['simbrief_type'] ?: $resolved['icao_type'],
            },
            'fuel_factor' => $resolved['fuel_factor'],
            'climb_profile' => $resolved['climb_profile'],
            'cruise_profile' => $resolved['cruise_profile'],
            'descent_profile' => $resolved['descent_profile'],
        ];
    }

    private function validateResolved(array $resolved): void
    {
        foreach (['max_pax','oew','mzfw','mtow','mlw','max_fuel','max_cargo'] as $field) {
            if (isset($resolved[$field]) && $resolved[$field] !== null && (float) $resolved[$field] < 0) {
                throw new \DomainException($field.' ne peut pas être négatif.');
            }
        }
        $mtow = $resolved['mtow'] ?? null;
        if ($mtow) {
            foreach (['oew','mzfw','mlw'] as $field) {
                if (($resolved[$field] ?? null) && (float) $resolved[$field] > (float) $mtow) {
                    throw new \DomainException(strtoupper($field).' ne peut pas être supérieur au MTOW.');
                }
            }
        }
    }

    private function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
