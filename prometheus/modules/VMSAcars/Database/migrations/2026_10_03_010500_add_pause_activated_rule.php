<?php

use App\Contracts\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vmsacars_rules')) return;

        DB::table('vmsacars_rules')->updateOrInsert(
            ['id' => 'PAUSE_ACTIVATED'],
            [
                'name' => 'Simulator Pause Activated',
                'description' => 'Detects simulator pause, Active Pause, or simulator menu/dialog pause while recording',
                'parameter' => null,
                'points' => 2,
                'has_parameter' => false,
                'repeatable' => true,
                'delay' => 1,
                'cooldown' => 60,
                'enabled' => true,
                'order' => 72,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('vmsacars_rules')) return;
        DB::table('vmsacars_rules')->where('id', 'PAUSE_ACTIVATED')->delete();
    }
};
