<?php

use App\Contracts\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Air Inter evolution: under 10,000 ft, an IAS above 255 kt must persist
 * for 10 continuous seconds before SPEED_UNDER_10K is penalised.
 *
 * Current Hermès builds detect continuity locally at simulator sampling rate;
 * this database delay is retained for legacy telemetry fallback.
 */
return new class() extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vmsacars_rules')) return;

        DB::table('vmsacars_rules')->where('id', 'SPEED_UNDER_10K')->update([
            'description' => 'IAS above 255 kt below 10,000 ft for at least 10 continuous seconds',
            'parameter' => null,
            'has_parameter' => false,
            'delay' => 10,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('vmsacars_rules')) return;

        DB::table('vmsacars_rules')->where('id', 'SPEED_UNDER_10K')->update([
            'description' => "Speed of 250kts under 10,000' is exceeded",
            'parameter' => null,
            'has_parameter' => false,
            'delay' => 20,
            'updated_at' => now(),
        ]);
    }
};
