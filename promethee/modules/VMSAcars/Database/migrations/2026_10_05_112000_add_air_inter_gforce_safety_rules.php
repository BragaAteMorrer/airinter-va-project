<?php

use App\Contracts\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vmsacars_rules')) return;

        DB::table('vmsacars_rules')->where('id', 'EXCESS_GFORCE')->update([
            'name' => 'Facteur de charge hors enveloppe',
            'description' => 'En vol : >= +2.5 g ou <= -1.0 g, hors seuil de mise en maintenance.',
            'parameter' => null,
            'points' => 15,
            'has_parameter' => false,
            'repeatable' => false,
            'delay' => 0,
            'cooldown' => 0,
            'enabled' => true,
            'order' => 35,
            'updated_at' => now(),
        ]);

        DB::table('vmsacars_rules')->updateOrInsert(
            ['id' => 'EXCESS_GFORCE_MAINTENANCE'],
            [
                'name' => 'Facteur de charge · mise en maintenance',
                'description' => 'En vol : >= +2.9 g ou <= -1.2 g. Immobilisation technique automatique de l’appareil.',
                'parameter' => null,
                'points' => 50,
                'has_parameter' => false,
                'repeatable' => false,
                'delay' => 0,
                'cooldown' => 0,
                'enabled' => true,
                'order' => 36,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('vmsacars_rules')) return;

        DB::table('vmsacars_rules')->where('id', 'EXCESS_GFORCE_MAINTENANCE')->delete();

        DB::table('vmsacars_rules')->where('id', 'EXCESS_GFORCE')->update([
            'name' => 'Excess G-Forces',
            'description' => 'If an aircraft exceeds the max gforce turning flight',
            'parameter' => 2,
            'points' => 5,
            'has_parameter' => true,
            'repeatable' => true,
            'delay' => 10,
            'cooldown' => 60,
            'order' => 35,
            'updated_at' => now(),
        ]);
    }
};
