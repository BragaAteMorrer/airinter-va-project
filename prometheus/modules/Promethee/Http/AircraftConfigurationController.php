<?php

namespace Modules\Promethee\Http;

use App\Http\Controllers\Controller;
use App\Models\Aircraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Promethee\Services\AircraftConfigurationResolver;

class AircraftConfigurationController extends Controller
{
    public function resolved(string $registration, Request $request, AircraftConfigurationResolver $resolver)
    {
        $data = $request->validate([
            'date' => 'nullable|date',
            'historical' => 'nullable|boolean',
        ]);

        return response()->json(['data' => $resolver->resolveByRegistration(
            $registration,
            $data['date'] ?? null,
            !($data['historical'] ?? false)
        )]);
    }

    public function adminIndex()
    {
        return view('promethee::admin-aircraft-configurations', [
            'aircraft' => Aircraft::query()->with('subfleet')->orderBy('registration')->get(),
            'variants' => DB::table('promethee_aircraft_variants')->orderBy('aircraft_type_key')->orderBy('name')->get(),
            'configurations' => DB::table('promethee_airframe_configurations')->orderBy('name')->get(),
            'assignments' => DB::table('promethee_aircraft_configuration_assignments as a')
                ->leftJoin('aircraft as ac', 'ac.id', '=', 'a.aircraft_id')
                ->leftJoin('promethee_aircraft_variants as v', 'v.id', '=', 'a.variant_id')
                ->leftJoin('promethee_airframe_configurations as c', 'c.id', '=', 'a.configuration_id')
                ->orderBy('ac.registration')
                ->orderByDesc('a.valid_from')
                ->get([
                    'a.*','ac.registration','v.name as variant_name','c.name as configuration_name',
                ]),
            'profiles' => DB::table('promethee_aircraft_simulator_profiles as p')
                ->leftJoin('promethee_aircraft_variants as v', 'v.id', '=', 'p.variant_id')
                ->leftJoin('promethee_airframe_configurations as c', 'c.id', '=', 'p.configuration_id')
                ->orderBy('p.simulator')->orderBy('p.addon_name')
                ->get(['p.*','v.name as variant_name','c.name as configuration_name']),
        ]);
    }

    public function saveVariant(Request $request)
    {
        $data = $request->validate($this->variantRules());
        $this->assertPeriod($data);
        $this->assertMasses($data);

        $id = DB::table('promethee_aircraft_variants')->insertGetId(array_merge(
            $this->normaliseTechnical($data),
            [
                'aircraft_type_key' => strtoupper(preg_replace('/[^A-Z0-9]/', '', $data['aircraft_type_key'])),
                'name' => $data['name'],
                'short_name' => $data['short_name'] ?? null,
                'icao_type' => isset($data['icao_type']) ? strtoupper($data['icao_type']) : null,
                'manufacturer_variant' => $data['manufacturer_variant'] ?? null,
                'operator_variant' => $data['operator_variant'] ?? null,
                'description' => $data['description'] ?? null,
                'valid_from' => $data['valid_from'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'phase' => $data['phase'] ?? null,
                'active' => $request->boolean('active', true),
                'source' => $data['source'] ?? null,
                'source_url' => $data['source_url'] ?? null,
                'notes' => $data['notes'] ?? null,
                'historical_confidence' => $data['historical_confidence'] ?? 'va_configuration',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ));

        return back()->with('success', 'Variante créée (#'.$id.').');
    }

    public function saveConfiguration(Request $request)
    {
        $data = $request->validate(array_merge($this->technicalRules(), [
            'variant_id' => 'nullable|integer',
            'code' => 'required|string|max:80|unique:promethee_airframe_configurations,code',
            'name' => 'required|string|max:160',
            'description' => 'nullable|string|max:4000',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'phase' => 'nullable|string|max:80',
            'kind' => 'required|in:historical,va_operational',
            'active' => 'nullable|boolean',
            'source' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:2000',
            'notes' => 'nullable|string|max:4000',
            'historical_confidence' => 'required|in:confirmed,probable,va_configuration',
        ]));
        $this->assertPeriod($data);
        $this->assertMasses($data);

        DB::table('promethee_airframe_configurations')->insert(array_merge(
            $this->normaliseTechnical($data),
            [
                'variant_id' => $data['variant_id'] ?? null,
                'code' => strtoupper(trim($data['code'])),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'valid_from' => $data['valid_from'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'phase' => $data['phase'] ?? null,
                'kind' => $data['kind'],
                'active' => $request->boolean('active', true),
                'source' => $data['source'] ?? null,
                'source_url' => $data['source_url'] ?? null,
                'notes' => $data['notes'] ?? null,
                'historical_confidence' => $data['historical_confidence'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ));

        return back()->with('success', 'Configuration créée.');
    }

    public function assign(Request $request)
    {
        $data = $request->validate([
            'aircraft_ids' => 'required|array|min:1',
            'aircraft_ids.*' => 'integer|exists:aircraft,id',
            'variant_id' => 'nullable|integer',
            'configuration_id' => 'nullable|integer',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'active_for_va' => 'nullable|boolean',
            'source' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:2000',
            'notes' => 'nullable|string|max:4000',
            'historical_confidence' => 'required|in:confirmed,probable,va_configuration',
        ]);
        $this->assertPeriod($data);

        DB::transaction(function () use ($data, $request) {
            foreach ($data['aircraft_ids'] as $aircraftId) {
                if ($request->boolean('active_for_va')) {
                    DB::table('promethee_aircraft_configuration_assignments')
                        ->where('aircraft_id', $aircraftId)
                        ->update(['active_for_va' => false, 'updated_at' => now()]);
                }

                DB::table('promethee_aircraft_configuration_assignments')->insert([
                    'aircraft_id' => $aircraftId,
                    'variant_id' => $data['variant_id'] ?? null,
                    'configuration_id' => $data['configuration_id'] ?? null,
                    'valid_from' => $data['valid_from'] ?? null,
                    'valid_until' => $data['valid_until'] ?? null,
                    'active_for_va' => $request->boolean('active_for_va'),
                    'source' => $data['source'] ?? null,
                    'source_url' => $data['source_url'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'historical_confidence' => $data['historical_confidence'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Configuration appliquée à '.count($data['aircraft_ids']).' appareil(s).');
    }

    public function saveSimulatorProfile(Request $request)
    {
        $data = $request->validate([
            'variant_id' => 'nullable|integer',
            'configuration_id' => 'nullable|integer',
            'simulator' => 'required|in:fs2004,fsx,p3d,msfs2020,msfs2024,xplane',
            'addon_name' => 'required|string|max:160',
            'addon_version' => 'nullable|string|max:80',
            'aircraft_identifier' => 'nullable|string|max:160',
            'simbrief_airframe' => 'nullable|string|max:120',
            'telemetry_profile' => 'nullable|string|max:120',
            'active' => 'nullable|boolean',
        ]);

        abort_if(empty($data['variant_id']) && empty($data['configuration_id']), 422, 'Choisissez une variante ou une configuration.');

        DB::table('promethee_aircraft_simulator_profiles')->insert([
            ...$data,
            'active' => $request->boolean('active', true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Profil simulateur ajouté.');
    }

    private function variantRules(): array
    {
        return array_merge($this->technicalRules(), [
            'aircraft_type_key' => 'required|string|max:32',
            'name' => 'required|string|max:120',
            'short_name' => 'nullable|string|max:80',
            'icao_type' => 'nullable|string|max:12',
            'manufacturer_variant' => 'nullable|string|max:120',
            'operator_variant' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:4000',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'phase' => 'nullable|string|max:80',
            'active' => 'nullable|boolean',
            'source' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:2000',
            'notes' => 'nullable|string|max:4000',
            'historical_confidence' => 'required|in:confirmed,probable,va_configuration',
        ]);
    }

    private function technicalRules(): array
    {
        return [
            'max_pax' => 'nullable|integer|min:0|max:1000',
            'seat_configuration' => 'nullable|string|max:120',
            'oew' => 'nullable|integer|min:0',
            'mzfw' => 'nullable|integer|min:0',
            'mtow' => 'nullable|integer|min:0',
            'mlw' => 'nullable|integer|min:0',
            'max_fuel' => 'nullable|integer|min:0',
            'max_cargo' => 'nullable|integer|min:0',
            'engine_manufacturer' => 'nullable|string|max:120',
            'engine_model' => 'nullable|string|max:120',
            'engine_variant' => 'nullable|string|max:120',
            'engine_count' => 'nullable|integer|min:1|max:8',
            'engine_simbrief_label' => 'nullable|string|max:120',
            'cruise_speed' => 'nullable|integer|min:0|max:2000',
            'cruise_mach' => 'nullable|numeric|min:0|max:2',
            'ceiling' => 'nullable|integer|min:0|max:100000',
            'range_nm' => 'nullable|integer|min:0',
            'equipment' => 'nullable|string|max:120',
            'transponder' => 'nullable|string|max:60',
            'pbn' => 'nullable|string|max:120',
            'simbrief_strategy' => 'nullable|in:type,internal_id,proxy',
            'simbrief_type' => 'nullable|string|max:32',
            'simbrief_internal_id' => 'nullable|string|max:120',
            'simbrief_proxy_type' => 'nullable|string|max:32',
            'fuel_factor' => 'nullable|numeric|min:0|max:10',
            'climb_profile' => 'nullable|string|max:120',
            'cruise_profile' => 'nullable|string|max:120',
            'descent_profile' => 'nullable|string|max:120',
        ];
    }

    private function normaliseTechnical(array $data): array
    {
        return collect($this->technicalRules())
            ->keys()
            ->mapWithKeys(fn ($key) => [$key => $data[$key] ?? null])
            ->all();
    }

    private function assertPeriod(array $data): void
    {
        if (!empty($data['valid_from']) && !empty($data['valid_until'])) {
            abort_if($data['valid_until'] < $data['valid_from'], 422, 'valid_until doit être postérieur ou égal à valid_from.');
        }
    }

    private function assertMasses(array $data): void
    {
        $mtow = (int) ($data['mtow'] ?? 0);
        if ($mtow <= 0) return;
        foreach (['oew','mzfw','mlw'] as $field) {
            abort_if((int) ($data[$field] ?? 0) > $mtow, 422, strtoupper($field).' ne peut pas dépasser le MTOW.');
        }
    }
}
