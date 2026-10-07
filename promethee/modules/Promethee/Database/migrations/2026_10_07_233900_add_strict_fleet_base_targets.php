<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_fleet_base_targets')) {
            Schema::create('promethee_fleet_base_targets', function (Blueprint $table) {
                $table->id();
                $table->string('airline_icao', 8);
                $table->string('subfleet_type', 64);
                $table->string('base_airport_id', 8);
                $table->unsignedSmallInteger('target_count')->default(0);
                $table->string('source', 80)->default('Répartition Flotte 2026-10-07');
                $table->timestamps();
                $table->unique(['airline_icao', 'subfleet_type', 'base_airport_id'], 'prom_fleet_base_target_uq');
                $table->index('base_airport_id', 'prom_fleet_base_target_base_idx');
            });
        }

        $bases = ['LFPO', 'LFMN', 'LFLL', 'LFML', 'LFBO', 'LFBD'];
        $fleet = [
            ['ITF', 'L-1049G', [1, 1, 0, 0, 0, 1]],
            ['ITF', 'DC-3', [2, 0, 1, 0, 1, 0]],
            ['ITF', 'DC-4', [1, 0, 0, 1, 0, 0]],
            ['ITF', 'Viscount-708', [3, 2, 1, 2, 1, 2]],
            ['ITF', 'DC-6', [1, 0, 0, 1, 0, 0]],
            ['ITF', 'Caravelle-III', [8, 3, 4, 3, 4, 3]],
            ['ITF', 'Alouette-II', [1, 0, 0, 0, 0, 0]],
            ['ITF', 'Viking', [1, 0, 1, 0, 0, 0]],
            ['ITF', 'Nord-262', [2, 0, 1, 0, 1, 0]],
            ['ITF', 'Fokker-27', [5, 1, 1, 1, 1, 1]],
            ['ITF', 'B747-1series', [1, 1, 0, 0, 1, 0]],
            ['ITF', 'A300-B4', [3, 1, 0, 1, 0, 1]],
            ['ICS', 'B737-2series', [3, 1, 0, 1, 1, 0]],
            ['ITF', 'A320-211', [8, 5, 4, 5, 3, 4]],
            ['ITF', 'A333', [2, 1, 0, 0, 1, 0]],
            ['ITF', 'A321', [2, 1, 0, 1, 0, 1]],
            ['ITF', 'A319', [4, 1, 1, 1, 1, 1]],
            ['ITF', 'Fokker-100', [3, 1, 0, 0, 0, 1]],
            ['ACF', 'B737-400', [0, 0, 0, 0, 1, 0]],
            ['ITF', 'DC-8-63PF', [1, 0, 0, 0, 0, 0]],
            ['ACF', 'A310', [1, 0, 0, 0, 1, 0]],
            ['ACF', 'Fokker-28', [0, 0, 0, 0, 0, 1]],
            ['ITF', 'Mercure-100', [4, 1, 1, 2, 1, 1]],
            ['ACF', 'Mercure-100-leased', [0, 0, 1, 0, 0, 0]],
            ['ICS', 'Vanguard', [1, 0, 0, 1, 0, 0]],
            ['ICS', 'L-100-30', [1, 0, 0, 0, 0, 0]],
            ['ACF', 'L-1049C', [0, 0, 1, 0, 1, 0]],
            ['ITF', 'L-749', [1, 0, 0, 1, 1, 0]],
            ['ITF', 'Viscount-724', [2, 0, 0, 1, 0, 1]],
            ['ITF', 'A300-B2', [5, 2, 2, 2, 3, 2]],
            ['ITF', 'Caravelle-12', [4, 2, 2, 1, 1, 2]],
            ['ITF', 'Caravelle-VI-R', [1, 0, 0, 0, 0, 0]],
            ['ITF', 'A320-111', [2, 1, 1, 1, 1, 1]],
            ['ITF', 'B747-2B4B', [0, 0, 0, 1, 0, 0]],
            ['ACF', 'Caravelle-III-ACF', [0, 0, 1, 0, 0, 0]],
            ['ACF', 'Caravelle-10B', [0, 0, 1, 0, 1, 1]],
            ['ACF', 'B727', [1, 1, 0, 1, 0, 1]],
            ['ACF', 'A300-B4', [0, 0, 0, 1, 0, 0]],
            ['ACF', 'B737-700', [1, 1, 0, 0, 0, 0]],
            ['ACF', 'B737-500', [0, 0, 0, 1, 0, 0]],
            ['ACF', 'B777', [1, 1, 0, 0, 0, 0]],
            ['ACF', 'MD-11', [1, 0, 0, 0, 0, 0]],
            ['ACF', 'A343', [0, 0, 0, 1, 0, 0]],
            ['ACF', 'B747-200', [1, 0, 0, 0, 0, 0]],
            ['ACF', 'Dash-7', [1, 0, 0, 0, 0, 0]],
            ['ACF', 'B757', [1, 0, 0, 0, 0, 0]],
            ['ACF', 'B767', [0, 1, 0, 0, 0, 0]],
            ['ITF', 'Nord-260', [1, 0, 1, 0, 0, 0]],
            ['ACF', 'Fokker-70', [0, 0, 1, 0, 1, 1]],
            ['ACF', 'L-1011-500', [1, 0, 0, 0, 0, 0]],
            ['ACF', 'A346', [1, 0, 0, 0, 0, 0]]
        ];

        $now = now();
        foreach ($fleet as [$airline, $type, $counts]) {
            foreach ($bases as $index => $base) {
                DB::table('promethee_fleet_base_targets')->updateOrInsert(
                    [
                        'airline_icao' => $airline,
                        'subfleet_type' => $type,
                        'base_airport_id' => $base,
                    ],
                    [
                        'target_count' => (int) ($counts[$index] ?? 0),
                        'source' => 'Répartition Flotte 2026-10-07',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_fleet_base_targets');
    }
};
