<?php

use App\Contracts\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('promethee_airframe_maintenance')) return;

        Schema::table('promethee_airframe_maintenance', function (Blueprint $table) {
            if (!Schema::hasColumn('promethee_airframe_maintenance', 'ferry_status')) {
                $table->string('ferry_status', 20)->nullable();
            }
            if (!Schema::hasColumn('promethee_airframe_maintenance', 'ferry_origin_airport_id')) {
                $table->string('ferry_origin_airport_id', 8)->nullable();
            }
            if (!Schema::hasColumn('promethee_airframe_maintenance', 'ferry_destination_airport_id')) {
                $table->string('ferry_destination_airport_id', 8)->nullable();
            }
            if (!Schema::hasColumn('promethee_airframe_maintenance', 'ferry_flight_id')) {
                $table->string('ferry_flight_id', 36)->nullable();
            }
            if (!Schema::hasColumn('promethee_airframe_maintenance', 'ferry_mission_id')) {
                $table->unsignedBigInteger('ferry_mission_id')->nullable();
            }
            if (!Schema::hasColumn('promethee_airframe_maintenance', 'ferry_authorized_at')) {
                $table->timestamp('ferry_authorized_at')->nullable();
            }
            if (!Schema::hasColumn('promethee_airframe_maintenance', 'ferry_ready_at')) {
                $table->timestamp('ferry_ready_at')->nullable();
            }
            if (!Schema::hasColumn('promethee_airframe_maintenance', 'ferry_arrived_at')) {
                $table->timestamp('ferry_arrived_at')->nullable();
            }
            if (!Schema::hasColumn('promethee_airframe_maintenance', 'ferry_notes')) {
                $table->text('ferry_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('promethee_airframe_maintenance')) return;
        $columns = collect([
            'ferry_status', 'ferry_origin_airport_id', 'ferry_destination_airport_id',
            'ferry_flight_id', 'ferry_mission_id', 'ferry_authorized_at',
            'ferry_ready_at', 'ferry_arrived_at', 'ferry_notes',
        ])->filter(fn ($column) => Schema::hasColumn('promethee_airframe_maintenance', $column))->all();
        if ($columns) Schema::table('promethee_airframe_maintenance', fn (Blueprint $table) => $table->dropColumn($columns));
    }
};
