<?php

use App\Contracts\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Defines Air Inter's stabilized-approach scoring criterion for Hermès.
 *
 * Detection is performed locally by Hermès at simulator sampling rate:
 * from 1,000 ft AGL to touchdown, a descent rate below -1,000 ft/min for
 * four continuous seconds emits one SOP fact. Prométhée only owns the points.
 */
return new class() extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vmsacars_rules')) return;

        DB::table('vmsacars_rules')->where('id', 'STABILIZED_APPROACH')->update([
            'name' => 'Approche stabilisée · taux de descente',
            'description' => 'Entre 1000 ft AGL et le toucher : VS >= -1000 ft/min. Dépassement continu pendant 4 s = pénalité.',
            'parameter' => null,
            'has_parameter' => false,
            'delay' => 4,
            'repeatable' => false,
            'cooldown' => 0,
            'enabled' => true,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('vmsacars_rules')) return;

        DB::table('vmsacars_rules')->where('id', 'STABILIZED_APPROACH')->update([
            'name' => 'Stabilized Approach',
            'description' => 'Stabilized approach gate',
            'parameter' => 1000,
            'has_parameter' => true,
            'delay' => 0,
            'repeatable' => false,
            'cooldown' => 0,
            'updated_at' => now(),
        ]);
    }
};
