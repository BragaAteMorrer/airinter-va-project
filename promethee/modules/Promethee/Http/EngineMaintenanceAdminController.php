<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\Subfleet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Promethee\Services\EngineMaintenanceService;

class EngineMaintenanceAdminController extends Controller
{
    public function syncFleet(EngineMaintenanceService $engineService)
    {
        $references = (array) config('promethee.engine-profiles', []);
        $createdProfiles = $updatedProfiles = 0;

        Subfleet::with('airline')->orderBy('id')->get()->each(function (Subfleet $subfleet) use ($references, &$createdProfiles, &$updatedProfiles) {
            $reference = $references[strtoupper((string) ($subfleet->airline?->icao ?: '')).'|'.(string) $subfleet->type] ?? null;
            if (!is_array($reference)) {
                return;
            }

            $existing = DB::table('promethee_engine_profiles')
                ->where('subfleet_id', $subfleet->id)
                ->first();

            $values = [
                'engine_type' => (string) $reference['engine_type'],
                'engine_count' => (int) $reference['engine_count'],
                'tbo_hours' => $reference['tbo_hours'] ?? null,
                'warning_hours' => (float) ($reference['warning_hours'] ?? 100),
                'itva_overhaul_cost' => $reference['itva_tbo_cost'] ?? null,
                'updated_at' => now(),
            ];

            if (array_key_exists('tbo_cycles', $reference)) {
                $values['tbo_cycles'] = $reference['tbo_cycles'];
            }
            if (array_key_exists('warning_cycles', $reference)) {
                $values['warning_cycles'] = $reference['warning_cycles'];
            }

            if ($existing) {
                DB::table('promethee_engine_profiles')->where('id', $existing->id)->update($values);
                $updatedProfiles++;
                return;
            }

            DB::table('promethee_engine_profiles')->insert($values + [
                'subfleet_id' => $subfleet->id,
                'tbo_cycles' => $reference['tbo_cycles'] ?? null,
                'warning_cycles' => $reference['warning_cycles'] ?? null,
                'active' => true,
                'created_at' => now(),
            ]);
            $createdProfiles++;
        });

        $synced = 0;
        foreach (DB::table('promethee_engine_profiles')->where('active', true)->pluck('subfleet_id') as $subfleetId) {
            $synced += $engineService->syncSubfleet((int) $subfleetId);
        }

        return back()->with(
            'success',
            $createdProfiles.' profil(s) ITVA créé(s) · '
            .$updatedProfiles.' profil(s) ITVA remis à niveau · '
            .$synced.' position(s) moteur vérifiée(s) / synchronisée(s).'
        );
    }

    public function saveProfile(Request $request, EngineMaintenanceService $engineService)
    {
        $itvaCategories = array_map(
            'strval',
            (array) data_get(config('promethee.engine-profiles', []), '__meta.itva_categories', [])
        );
        $hoursRule = 'nullable|numeric|min:1|max:100000';
        if ($itvaCategories !== []) {
            $hoursRule .= '|in:'.implode(',', $itvaCategories);
        }

        $data = $request->validate([
            'subfleet_id' => 'required|integer|exists:subfleets,id',
            'engine_type' => 'required|string|max:80',
            'engine_count' => 'required|integer|min:1|max:4',
            'tbo_hours' => $hoursRule,
            'tbo_cycles' => 'nullable|integer|min:1|max:100000',
            'warning_hours' => 'required|numeric|min:0|max:10000',
            'warning_cycles' => 'nullable|integer|min:0|max:10000',
            'active' => 'nullable|boolean',
        ]);

        if (!$request->filled('tbo_hours') && !$request->filled('tbo_cycles')) {
            return back()
                ->withErrors(['tbo_hours' => 'Renseignez au moins une limite de potentiel ITVA en heures ou en cycles.'])
                ->withInput();
        }

        DB::table('promethee_engine_profiles')->updateOrInsert(
            ['subfleet_id' => $data['subfleet_id']],
            [
                'engine_type' => trim($data['engine_type']),
                'engine_count' => $data['engine_count'],
                'tbo_hours' => $data['tbo_hours'] ?? null,
                'tbo_cycles' => $data['tbo_cycles'] ?? null,
                'warning_hours' => $data['warning_hours'],
                'warning_cycles' => $data['warning_cycles'] ?? null,
                'active' => $request->boolean('active'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $engineService->syncSubfleet((int) $data['subfleet_id']);

        return back()->with('success', 'Profil moteur ITVA enregistré et flotte correspondante synchronisée.');
    }

    public function createUnit(Request $request, EngineMaintenanceService $engineService)
    {
        $data = $request->validate([
            'engine_profile_id' => 'required|integer|exists:promethee_engine_profiles,id',
            'serial_number' => 'required|string|max:96|unique:promethee_engines,serial_number',
            'hours_since_overhaul' => 'nullable|numeric|min:0|max:100000',
            'cycles_since_overhaul' => 'nullable|integer|min:0|max:100000',
        ]);

        $profile = DB::table('promethee_engine_profiles')
            ->where('id', $data['engine_profile_id'])
            ->first();
        abort_unless($profile, 404);

        $engineId = DB::table('promethee_engines')->insertGetId([
            'engine_profile_id' => $profile->id,
            'serial_number' => strtoupper(trim($data['serial_number'])),
            'engine_type' => $profile->engine_type,
            'tbo_hours' => $profile->tbo_hours,
            'tbo_cycles' => $profile->tbo_cycles,
            'hours_since_overhaul' => $data['hours_since_overhaul'] ?? 0,
            'cycles_since_overhaul' => $data['cycles_since_overhaul'] ?? 0,
            'status' => 'serviceable',
            'last_overhaul_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $engineService->refreshStatus((int) $engineId);

        return back()->with('success', 'Moteur ajouté au stock.');
    }

    public function overhaul(int $engine, Request $request, EngineMaintenanceService $engineService)
    {
        $data = $request->validate(['notes' => 'nullable|string|max:2000']);

        try {
            $engineService->overhaul($engine, (int) $request->user()->id, $data['notes'] ?? null);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['engine' => $exception->getMessage()]);
        }

        return back()->with('success', 'Révision moteur enregistrée ; compteurs remis à zéro et potentiel ITVA restauré.');
    }

    public function install(int $engine, Request $request, EngineMaintenanceService $engineService)
    {
        $data = $request->validate([
            'aircraft_id' => 'required|integer|exists:aircraft,id',
            'position' => 'required|integer|min:1|max:4',
        ]);

        try {
            $engineService->install(
                $engine,
                (int) $data['aircraft_id'],
                (int) $data['position'],
                (int) $request->user()->id
            );
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['engine' => $exception->getMessage()]);
        }

        return back()->with('success', 'Moteur installé ; l’ancien moteur de la position est revenu au stock.');
    }
}
