<?php

use App\Models\Enums\AirframeSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('simbrief_airframes')) {
            return;
        }

        $exists = DB::table('simbrief_airframes')
            ->where('source', AirframeSource::INTERNAL)
            ->whereRaw('UPPER(icao) = ?', ['N262'])
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('simbrief_airframes')->insert([
            'icao' => 'N262',
            'name' => 'NORD 262',
            'airframe_id' => null,
            'source' => AirframeSource::INTERNAL,
            'details' => json_encode([
                'manufacturer' => 'Nord Aviation',
                'default' => true,
                'notes' => 'Air Inter historical planning profile. Variant/performance values remain admin-calibratable.',
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'options' => json_encode([
                'simbrief' => [
                    'strategy' => 'proxy',
                    'proxy_type' => 'SH33',
                    'name' => 'NORD 262',
                    'engines' => 'BASTAN VIC',
                    'maxpax' => 29,
                    'weights_kg' => [],
                    'performance' => [],
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Deliberately non-destructive. Once administrators have calibrated an
        // historical airframe, rollback must not silently delete that VA data.
    }
};
