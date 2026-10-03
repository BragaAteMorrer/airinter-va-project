<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_operational_bases')) return;

        $now = now();

        $sites = [
            // Hub principal / maintenance complète
            'LFPO' => ['is_hub' => true,  'is_regional_platform' => false, 'is_technical_stop' => false, 'check_a' => true, 'check_b' => true, 'check_c' => true],
            // Bases pilotes / A CHECK
            'LFMN' => ['is_hub' => false, 'is_regional_platform' => true,  'is_technical_stop' => false, 'check_a' => true, 'check_b' => false, 'check_c' => false],
            'LFLL' => ['is_hub' => false, 'is_regional_platform' => true,  'is_technical_stop' => false, 'check_a' => true, 'check_b' => false, 'check_c' => false],
            'LFML' => ['is_hub' => false, 'is_regional_platform' => true,  'is_technical_stop' => false, 'check_a' => true, 'check_b' => false, 'check_c' => false],
            'LFBO' => ['is_hub' => false, 'is_regional_platform' => true,  'is_technical_stop' => false, 'check_a' => true, 'check_b' => false, 'check_c' => false],
            'LFBD' => ['is_hub' => false, 'is_regional_platform' => true,  'is_technical_stop' => false, 'check_a' => true, 'check_b' => false, 'check_c' => false],
            // Escales techniques / A CHECK
            'LFRS' => ['is_hub' => false, 'is_regional_platform' => false, 'is_technical_stop' => true,  'check_a' => true, 'check_b' => false, 'check_c' => false],
            'LFRB' => ['is_hub' => false, 'is_regional_platform' => false, 'is_technical_stop' => true,  'check_a' => true, 'check_b' => false, 'check_c' => false],
            'LFLC' => ['is_hub' => false, 'is_regional_platform' => false, 'is_technical_stop' => true,  'check_a' => true, 'check_b' => false, 'check_c' => false],
            'LFST' => ['is_hub' => false, 'is_regional_platform' => false, 'is_technical_stop' => true,  'check_a' => true, 'check_b' => false, 'check_c' => false],
            'LFKJ' => ['is_hub' => false, 'is_regional_platform' => false, 'is_technical_stop' => true,  'check_a' => true, 'check_b' => false, 'check_c' => false],
            'LFQQ' => ['is_hub' => false, 'is_regional_platform' => false, 'is_technical_stop' => true,  'check_a' => true, 'check_b' => false, 'check_c' => false],
            'LFKB' => ['is_hub' => false, 'is_regional_platform' => false, 'is_technical_stop' => true,  'check_a' => true, 'check_b' => false, 'check_c' => false],
            'LFMP' => ['is_hub' => false, 'is_regional_platform' => false, 'is_technical_stop' => true,  'check_a' => true, 'check_b' => false, 'check_c' => false],
            // CDG : escale technique avec A/B/C
            'LFPG' => ['is_hub' => false, 'is_regional_platform' => false, 'is_technical_stop' => true,  'check_a' => true, 'check_b' => true, 'check_c' => true],
        ];

        foreach ($sites as $airportId => $roles) {
            DB::table('promethee_operational_bases')->updateOrInsert(
                ['airport_id' => $airportId],
                [
                    'kind' => $roles['is_hub'] ? 'hub' : 'regional',
                    'is_hub' => $roles['is_hub'],
                    'is_regional_platform' => $roles['is_regional_platform'],
                    'is_technical_stop' => $roles['is_technical_stop'],
                    'check_a' => $roles['check_a'],
                    'check_b' => $roles['check_b'],
                    'check_c' => $roles['check_c'],
                    'small_maintenance' => $roles['check_a'],
                    'heavy_maintenance' => $roles['check_b'] || $roles['check_c'],
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        if (!Schema::hasTable('aircraft') || !Schema::hasTable('promethee_aircraft_bases')) return;

        // Affectations confirmées par l'exploitation. Duplicate lines from the
        // source list are intentionally collapsed by registration.
        $targetBases = [
            'F-BUAF' => 'LFPO',
            'F-BPNA' => 'LFPO',
            'F-BPNJ' => 'LFBD',
            'F-BPNH' => 'LFML',
            'F-BPNI' => 'LFBO',
            'F-BPNG' => 'LFLL',
            'F-BPNF' => 'LFMN',
            'F-BPNE' => 'LFPO',
            'F-BPND' => 'LFPO',
            'F-BNAX' => 'LFBD',
            'F-BMCH' => 'LFML',
        ];

        foreach ($targetBases as $registration => $base) {
            $plane = DB::table('aircraft')->where('registration', $registration)->first();
            if (!$plane) continue;

            $current = strtoupper((string) ($plane->airport_id ?? ''));
            $awaySince = $plane->landing_time ?: $now;

            DB::table('promethee_aircraft_bases')->updateOrInsert(
                ['aircraft_id' => $plane->id],
                [
                    'base_airport_id' => $base,
                    'assigned_at' => $now,
                    'away_since' => $current !== '' && $current !== $base ? $awaySince : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            DB::table('aircraft')->where('id', $plane->id)->update(['hub_id' => $base]);

            if ($current === '' || $current === $base || !Schema::hasTable('promethee_missions')) continue;

            $existingMission = DB::table('promethee_missions')
                ->where('mission_type', 'repatriation')
                ->where('aircraft_id', $plane->id)
                ->where('active', true)
                ->first();

            if ($existingMission) {
                DB::table('promethee_aircraft_bases')->where('aircraft_id', $plane->id)->update([
                    'repatriation_mission_id' => $existingMission->id,
                    'updated_at' => $now,
                ]);
                continue;
            }

            $missionId = DB::table('promethee_missions')->insertGetId([
                'created_by' => null,
                'title' => 'Rapatriement '.$registration.' vers '.$base,
                'description' => 'Mission de rapatriement flotte demandée par l’exploitation. Appareil '.$registration.' actuellement à '.$current.'.',
                'mission_type' => 'repatriation',
                'dpt_airport_id' => $current,
                'arr_airport_id' => $base,
                'flight_id' => null,
                'aircraft_id' => $plane->id,
                'reward_multiplier' => (float) (DB::table('promethee_settings')->where('key', 'regional.repatriation_reward_multiplier')->value('value') ?: 2),
                'auto_generated' => true,
                'starts_on' => today('Europe/Paris')->toDateString(),
                'ends_on' => null,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('promethee_aircraft_bases')->where('aircraft_id', $plane->id)->update([
                'repatriation_mission_id' => $missionId,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Operational assignments and generated missions may have evolved after
        // deployment; do not destructively roll them back.
    }
};
