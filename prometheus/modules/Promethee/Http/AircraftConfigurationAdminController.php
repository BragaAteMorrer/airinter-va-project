<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\Aircraft;
use App\Models\SimBriefAirframe;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Promethee\Models\AircraftConfigurationAssignment;
use Modules\Promethee\Models\AircraftHistoricalVariant;
use Modules\Promethee\Models\AircraftModification;
use Modules\Promethee\Models\AircraftTypeProfile;
use Modules\Promethee\Models\AirframeConfiguration;
use Modules\Promethee\Models\AirframeSimulatorProfile;
use Modules\Promethee\Services\AircraftConfigurationResolver;
use Modules\Promethee\Services\BrandingService;

class AircraftConfigurationAdminController extends Controller
{
    private const CONFIDENCE = ['confirmed', 'probable', 'VA_configuration'];
    private const STRATEGIES = ['native', 'internal_id', 'proxy'];
    private const MOD_CATEGORIES = [
        'Cabin', 'Engine', 'Avionics', 'Performance', 'Weight', 'Fuel',
        'Navigation', 'Seats', 'Doors', 'Cargo', 'Electrical', 'Other',
    ];

    public function __construct(
        private readonly AircraftConfigurationResolver $resolver,
        private readonly BrandingService $branding
    ) {}

    public function index()
    {
        $types = AircraftTypeProfile::query()->orderBy('type_key')->get();
        $variants = AircraftHistoricalVariant::query()
            ->with('configurations')
            ->orderBy('type_key')->orderBy('name')->get();
        $configurations = AirframeConfiguration::query()
            ->with('variant')
            ->orderBy('variant_id')->orderBy('name')->get();
        $aircraft = Aircraft::query()
            ->with('subfleet')
            ->orderBy('registration')
            ->get()
            ->map(function (Aircraft $aircraft) {
                $resolved = $this->resolver->resolveAircraft($aircraft);

                return [
                    'model' => $aircraft,
                    'resolved' => $resolved,
                ];
            });
        $assignments = AircraftConfigurationAssignment::query()
            ->with(['aircraft', 'variant', 'configuration'])
            ->orderByDesc('valid_from')->orderByDesc('id')->limit(100)->get();
        $simulatorProfiles = AirframeSimulatorProfile::query()
            ->with(['variant', 'configuration', 'simbriefAirframe'])
            ->orderBy('simulator')->orderBy('addon_name')->get();
        $modifications = AircraftModification::query()
            ->orderByDesc('effective_from')->orderByDesc('id')->limit(100)->get();

        return view('promethee::admin.airframes', [
            'types' => $types,
            'variants' => $variants,
            'configurations' => $configurations,
            'aircraft' => $aircraft,
            'assignments' => $assignments,
            'simulatorProfiles' => $simulatorProfiles,
            'modifications' => $modifications,
            'simbriefAirframes' => SimBriefAirframe::query()->orderBy('icao')->orderBy('name')->get(),
            'confidenceOptions' => self::CONFIDENCE,
            'strategyOptions' => self::STRATEGIES,
            'modificationCategories' => self::MOD_CATEGORIES,
            'branding' => $this->branding->active(),
        ]);
    }

    public function storeType(Request $request)
    {
        $data = $request->validate([
            'type_key' => 'required|string|max:32',
            'name' => 'required|string|max:120',
            'simbrief_strategy' => 'nullable|in:'.implode(',', self::STRATEGIES),
            'simbrief_type' => 'nullable|string|max:80',
            'simbrief_internal_id' => 'nullable|string|max:120',
            'simbrief_proxy_type' => 'nullable|string|max:80',
            'source' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:500',
            'notes' => 'nullable|string|max:4000',
            'historical_confidence' => 'required|in:'.implode(',', self::CONFIDENCE),
            ...$this->technicalRules(),
        ]);
        $data['type_key'] = $this->key($data['type_key']);
        $data['data'] = $this->technicalData($data);
        $this->assertTechnicalConsistency($data['data']);
        $data = $this->stripTechnical($data);

        AircraftTypeProfile::query()->updateOrCreate(['type_key' => $data['type_key']], $data);

        return back()->with('success', 'Profil de type enregistré.');
    }

    public function storeVariant(Request $request)
    {
        $data = $request->validate([
            'type_key' => 'required|string|max:32',
            'code' => 'required|string|max:64',
            'name' => 'required|string|max:120',
            'short_name' => 'nullable|string|max:80',
            'icao_type' => 'nullable|string|max:16',
            'manufacturer_variant' => 'nullable|string|max:120',
            'operator_variant' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:4000',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'simbrief_strategy' => 'nullable|in:'.implode(',', self::STRATEGIES),
            'simbrief_type' => 'nullable|string|max:80',
            'simbrief_internal_id' => 'nullable|string|max:120',
            'simbrief_proxy_type' => 'nullable|string|max:80',
            'source' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:500',
            'notes' => 'nullable|string|max:4000',
            'historical_confidence' => 'required|in:'.implode(',', self::CONFIDENCE),
            'active' => 'nullable|boolean',
            ...$this->technicalRules(),
        ]);
        $data['type_key'] = $this->key($data['type_key']);
        $data['code'] = strtoupper(trim($data['code']));
        $data['active'] = $request->boolean('active', true);
        $data['data'] = $this->technicalData($data);
        $typeDefaults = AircraftTypeProfile::query()
            ->where('type_key', $data['type_key'])
            ->value('data') ?? [];
        if (is_string($typeDefaults)) $typeDefaults = json_decode($typeDefaults, true) ?: [];
        $this->assertTechnicalConsistency($this->mergeTechnical($typeDefaults, $data['data']));
        $data = $this->stripTechnical($data);

        AircraftHistoricalVariant::query()->updateOrCreate(
            ['type_key' => $data['type_key'], 'code' => $data['code']],
            $data
        );

        return back()->with('success', 'Variante historique enregistrée.');
    }

    public function duplicateVariant(AircraftHistoricalVariant $variant, Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:64',
            'name' => 'required|string|max:120',
        ]);

        DB::transaction(function () use ($variant, $data) {
            $copy = $variant->replicate();
            $copy->code = strtoupper(trim($data['code']));
            $copy->name = trim($data['name']);
            $copy->historical_confidence = 'VA_configuration';
            $copy->source = null;
            $copy->source_url = null;
            $copy->notes = trim(($copy->notes ? $copy->notes."\n" : '').'Dupliqué depuis '.$variant->code.'.');
            $copy->save();

            foreach ($variant->configurations as $configuration) {
                $newConfiguration = $configuration->replicate();
                $newConfiguration->variant_id = $copy->id;
                $newConfiguration->historical_confidence = 'VA_configuration';
                $newConfiguration->source = null;
                $newConfiguration->source_url = null;
                $newConfiguration->save();

                AirframeSimulatorProfile::query()
                    ->where('variant_id', $variant->id)
                    ->where('configuration_id', $configuration->id)
                    ->get()
                    ->each(function (AirframeSimulatorProfile $profile) use ($copy, $newConfiguration) {
                        $clone = $profile->replicate();
                        $clone->variant_id = $copy->id;
                        $clone->configuration_id = $newConfiguration->id;
                        $clone->save();
                    });
            }

            AirframeSimulatorProfile::query()
                ->where('variant_id', $variant->id)
                ->whereNull('configuration_id')
                ->get()
                ->each(function (AirframeSimulatorProfile $profile) use ($copy) {
                    $clone = $profile->replicate();
                    $clone->variant_id = $copy->id;
                    $clone->configuration_id = null;
                    $clone->save();
                });
        });

        return back()->with('success', 'Variante et configurations dupliquées.');
    }

    public function storeConfiguration(Request $request)
    {
        $data = $request->validate([
            'variant_id' => 'required|integer|exists:promethee_aircraft_historical_variants,id',
            'code' => 'required|string|max:64',
            'name' => 'required|string|max:120',
            'configuration_kind' => 'required|in:historical,VA_operational',
            'phase' => 'nullable|string|max:80',
            'description' => 'nullable|string|max:4000',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'simbrief_strategy' => 'nullable|in:'.implode(',', self::STRATEGIES),
            'simbrief_type' => 'nullable|string|max:80',
            'simbrief_internal_id' => 'nullable|string|max:120',
            'simbrief_proxy_type' => 'nullable|string|max:80',
            'source' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:500',
            'notes' => 'nullable|string|max:4000',
            'historical_confidence' => 'required|in:'.implode(',', self::CONFIDENCE),
            'active' => 'nullable|boolean',
            ...$this->technicalRules(),
        ]);
        $data['code'] = strtoupper(trim($data['code']));
        $data['active'] = $request->boolean('active', true);
        $data['data'] = $this->technicalData($data);
        $variant = AircraftHistoricalVariant::query()->findOrFail($data['variant_id']);
        $typeDefaults = AircraftTypeProfile::query()
            ->where('type_key', $variant->type_key)
            ->value('data') ?? [];
        if (is_string($typeDefaults)) $typeDefaults = json_decode($typeDefaults, true) ?: [];
        $this->assertTechnicalConsistency($this->mergeTechnical(
            $typeDefaults,
            $variant->data ?? [],
            $data['data']
        ));
        $data = $this->stripTechnical($data);

        AirframeConfiguration::query()->updateOrCreate(
            ['variant_id' => $data['variant_id'], 'code' => $data['code']],
            $data
        );

        return back()->with('success', 'Configuration enregistrée.');
    }

    public function assign(Request $request)
    {
        $data = $request->validate([
            'aircraft_ids' => 'required|array|min:1',
            'aircraft_ids.*' => 'integer|exists:aircraft,id',
            'variant_id' => 'required|integer|exists:promethee_aircraft_historical_variants,id',
            'configuration_id' => 'nullable|integer|exists:promethee_airframe_configurations,id',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'source' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:500',
            'notes' => 'nullable|string|max:4000',
            'historical_confidence' => 'required|in:'.implode(',', self::CONFIDENCE),
            ...$this->technicalRules(),
        ]);

        if (!empty($data['configuration_id'])) {
            $configuration = AirframeConfiguration::query()->findOrFail($data['configuration_id']);
            if ((int) $configuration->variant_id !== (int) $data['variant_id']) {
                throw ValidationException::withMessages([
                    'configuration_id' => 'Cette configuration n’appartient pas à la variante sélectionnée.',
                ]);
            }
        }

        $overrides = $this->technicalData($data);
        $variant = AircraftHistoricalVariant::query()->findOrFail($data['variant_id']);
        $configuration = !empty($data['configuration_id'])
            ? AirframeConfiguration::query()->findOrFail($data['configuration_id'])
            : null;
        $typeDefaults = AircraftTypeProfile::query()
            ->where('type_key', $variant->type_key)
            ->value('data') ?? [];
        if (is_string($typeDefaults)) $typeDefaults = json_decode($typeDefaults, true) ?: [];
        $this->assertTechnicalConsistency($this->mergeTechnical(
            $typeDefaults,
            $variant->data ?? [],
            $configuration?->data ?? [],
            $overrides
        ));

        $validFrom = Carbon::parse($data['valid_from'] ?? now()->toDateString())->startOfDay();
        $validUntil = filled($data['valid_until'] ?? null)
            ? Carbon::parse($data['valid_until'])->startOfDay()
            : null;

        DB::transaction(function () use ($data, $overrides, $validFrom, $validUntil) {
            foreach (array_unique($data['aircraft_ids']) as $aircraftId) {
                // Close only an open period which started before the new one.
                // The former row remains active and therefore resolvable historically.
                AircraftConfigurationAssignment::query()
                    ->where('aircraft_id', $aircraftId)
                    ->where('active', true)
                    ->whereNull('valid_until')
                    ->where(function ($query) use ($validFrom) {
                        $query->whereNull('valid_from')->orWhereDate('valid_from', '<', $validFrom->toDateString());
                    })
                    ->update(['valid_until' => $validFrom->copy()->subDay()->toDateString()]);

                AircraftConfigurationAssignment::query()->create([
                    'aircraft_id' => $aircraftId,
                    'variant_id' => $data['variant_id'],
                    'configuration_id' => $data['configuration_id'] ?? null,
                    'valid_from' => $validFrom->toDateString(),
                    'valid_until' => $validUntil?->toDateString(),
                    'active' => true,
                    'overrides' => $overrides,
                    'source' => $data['source'] ?? null,
                    'source_url' => $data['source_url'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'historical_confidence' => $data['historical_confidence'],
                ]);
            }
        });

        return back()->with('success', count($data['aircraft_ids']).' immatriculation(s) affectée(s).');
    }

    public function storeSimulatorProfile(Request $request)
    {
        $data = $request->validate([
            'variant_id' => 'required|integer|exists:promethee_aircraft_historical_variants,id',
            'configuration_id' => 'nullable|integer|exists:promethee_airframe_configurations,id',
            'simulator' => 'required|in:fs2004,fsx,p3d,msfs2020,msfs2024,xplane',
            'addon_name' => 'required|string|max:120',
            'addon_version' => 'nullable|string|max:80',
            'aircraft_identifier' => 'nullable|string|max:160',
            'simbrief_airframe_id' => 'nullable|integer|exists:simbrief_airframes,id',
            'telemetry_profile' => 'nullable|string|max:120',
            'active' => 'nullable|boolean',
        ]);

        if (!empty($data['configuration_id'])) {
            $configuration = AirframeConfiguration::query()->findOrFail($data['configuration_id']);
            if ((int) $configuration->variant_id !== (int) $data['variant_id']) {
                throw ValidationException::withMessages([
                    'configuration_id' => 'Cette configuration n’appartient pas à la variante sélectionnée.',
                ]);
            }
        }

        $data['active'] = $request->boolean('active', true);
        AirframeSimulatorProfile::query()->create($data);

        return back()->with('success', 'Profil simulateur ajouté.');
    }

    public function storeModification(Request $request)
    {
        $data = $request->validate([
            'aircraft_id' => 'nullable|integer|exists:aircraft,id',
            'variant_id' => 'nullable|integer|exists:promethee_aircraft_historical_variants,id',
            'configuration_id' => 'nullable|integer|exists:promethee_airframe_configurations,id',
            'name' => 'required|string|max:160',
            'description' => 'nullable|string|max:4000',
            'category' => 'required|in:'.implode(',', self::MOD_CATEGORIES),
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after_or_equal:effective_from',
            'previous_value' => 'nullable|string|max:2000',
            'new_value' => 'nullable|string|max:2000',
            'source' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:500',
        ]);

        if (empty($data['aircraft_id']) && empty($data['variant_id']) && empty($data['configuration_id'])) {
            throw ValidationException::withMessages([
                'aircraft_id' => 'Ciblez une immatriculation, une variante ou une configuration.',
            ]);
        }

        AircraftModification::query()->create($data);

        return back()->with('success', 'Modification documentée.');
    }

    private function technicalRules(): array
    {
        return [
            'max_pax' => 'nullable|integer|min:0',
            'seat_configuration' => 'nullable|string|max:120',
            'weight_unit' => 'nullable|in:kg,lb,klb',
            'weight_category' => 'nullable|in:L,M,H,J',
            'oew' => 'nullable|numeric|min:0',
            'mzfw' => 'nullable|numeric|min:0',
            'mtow' => 'nullable|numeric|min:0',
            'mlw' => 'nullable|numeric|min:0',
            'max_fuel' => 'nullable|numeric|min:0',
            'max_cargo' => 'nullable|numeric|min:0',
            'engine_manufacturer' => 'nullable|string|max:120',
            'engine_model' => 'nullable|string|max:120',
            'engine_variant' => 'nullable|string|max:120',
            'engine_count' => 'nullable|integer|min:0|max:16',
            'engine_simbrief_label' => 'nullable|string|max:120',
            'cruise_speed' => 'nullable|numeric|min:0',
            'cruise_mach' => 'nullable|numeric|min:0|max:2',
            'ceiling' => 'nullable|numeric|min:0',
            'range' => 'nullable|numeric|min:0',
            'equipment' => 'nullable|string|max:255',
            'transponder' => 'nullable|string|max:120',
            'pbn' => 'nullable|string|max:255',
            'fuel_factor' => 'nullable|numeric|min:-50|max:100',
            'climb_profile' => 'nullable|string|max:120',
            'cruise_profile' => 'nullable|string|max:120',
            'descent_profile' => 'nullable|string|max:120',
        ];
    }

    private function technicalData(array $data): array
    {
        $technical = [];
        foreach (array_keys($this->technicalRules()) as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                $technical[$key] = $data[$key];
            }
        }

        $engine = array_filter([
            'manufacturer' => $technical['engine_manufacturer'] ?? null,
            'model' => $technical['engine_model'] ?? null,
            'variant' => $technical['engine_variant'] ?? null,
            'count' => $technical['engine_count'] ?? null,
            'simbrief_label' => $technical['engine_simbrief_label'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        unset(
            $technical['engine_manufacturer'],
            $technical['engine_model'],
            $technical['engine_variant'],
            $technical['engine_count'],
            $technical['engine_simbrief_label']
        );

        if ($engine !== []) $technical['engine'] = $engine;

        return $technical;
    }

    private function stripTechnical(array $data): array
    {
        foreach (array_keys($this->technicalRules()) as $key) unset($data[$key]);
        return $data;
    }

    private function mergeTechnical(array ...$layers): array
    {
        $result = [];
        foreach ($layers as $layer) {
            foreach ($layer as $key => $value) {
                if ($value === null || $value === '') continue;
                if (is_array($value) && is_array($result[$key] ?? null)) {
                    $result[$key] = $this->mergeTechnical($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

    private function assertTechnicalConsistency(array $technical): void
    {
        $mtow = isset($technical['mtow']) ? (float) $technical['mtow'] : null;
        if ($mtow !== null) {
            foreach (['oew', 'mzfw', 'mlw'] as $key) {
                if (isset($technical[$key]) && (float) $technical[$key] > $mtow) {
                    throw ValidationException::withMessages([
                        $key => strtoupper($key).' ne peut pas être supérieur au MTOW effectif (héritage inclus).',
                    ]);
                }
            }
        }

        if (isset($technical['oew'], $technical['mzfw'])
            && (float) $technical['oew'] > (float) $technical['mzfw']) {
            throw ValidationException::withMessages([
                'oew' => 'OEW ne peut pas être supérieur au MZFW effectif.',
            ]);
        }
    }

    private function key(string $value): string
    {
        return preg_replace('/[^A-Z0-9]+/', '', strtoupper(trim($value))) ?: 'AIRCRAFT';
    }
}
