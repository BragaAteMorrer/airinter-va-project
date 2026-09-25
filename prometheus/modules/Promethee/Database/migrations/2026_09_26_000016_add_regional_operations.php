<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_operational_bases')) {
            Schema::create('promethee_operational_bases', function (Blueprint $table) {
                $table->id();
                $table->string('airport_id', 8)->unique();
                $table->string('kind', 16)->default('regional');
                $table->boolean('small_maintenance')->default(true);
                $table->boolean('heavy_maintenance')->default(false);
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->index(['kind', 'active']);
            });
        }

        if (!Schema::hasTable('promethee_aircraft_bases')) {
            Schema::create('promethee_aircraft_bases', function (Blueprint $table) {
                $table->unsignedInteger('aircraft_id')->primary();
                $table->string('base_airport_id', 8);
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('away_since')->nullable();
                $table->unsignedBigInteger('repatriation_mission_id')->nullable();
                $table->timestamp('last_auto_return_at')->nullable();
                $table->timestamps();
                $table->index(['base_airport_id', 'away_since']);
            });
        }

        if (!Schema::hasTable('promethee_airline_access_rules')) {
            Schema::create('promethee_airline_access_rules', function (Blueprint $table) {
                $table->unsignedInteger('airline_id')->primary();
                $table->unsignedInteger('min_flight_hours')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('promethee_mission_bookings')) {
            Schema::create('promethee_mission_bookings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('mission_id');
                $table->unsignedInteger('user_id');
                $table->string('status', 16)->default('reserved');
                $table->unsignedInteger('jumpseat_amount')->default(0);
                $table->unsignedInteger('bonus_amount')->default(0);
                $table->timestamp('bonus_paid_at')->nullable();
                $table->timestamp('reserved_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->unique(['mission_id', 'user_id'], 'prom_mission_booking_unique');
                $table->index(['mission_id', 'status']);
            });
        }

        if (Schema::hasTable('promethee_missions')) {
            Schema::table('promethee_missions', function (Blueprint $table) {
                if (!Schema::hasColumn('promethee_missions', 'mission_type')) $table->string('mission_type', 24)->default('standard')->after('description');
                if (!Schema::hasColumn('promethee_missions', 'aircraft_id')) $table->unsignedInteger('aircraft_id')->nullable()->after('flight_id');
                if (!Schema::hasColumn('promethee_missions', 'reward_multiplier')) $table->decimal('reward_multiplier', 5, 2)->default(1)->after('aircraft_id');
                if (!Schema::hasColumn('promethee_missions', 'auto_generated')) $table->boolean('auto_generated')->default(false)->after('reward_multiplier');
            });
        }

        $now = now();
        foreach ([
            ['airport_id' => 'LFPO', 'kind' => 'hub', 'small_maintenance' => true, 'heavy_maintenance' => true],
            ['airport_id' => 'LFPG', 'kind' => 'regional', 'small_maintenance' => true, 'heavy_maintenance' => false],
            ['airport_id' => 'LFMN', 'kind' => 'regional', 'small_maintenance' => true, 'heavy_maintenance' => false],
            ['airport_id' => 'LFML', 'kind' => 'regional', 'small_maintenance' => true, 'heavy_maintenance' => false],
            ['airport_id' => 'LFBD', 'kind' => 'regional', 'small_maintenance' => true, 'heavy_maintenance' => false],
        ] as $base) {
            DB::table('promethee_operational_bases')->updateOrInsert(
                ['airport_id' => $base['airport_id']],
                $base + ['active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        foreach ([
            'regional.repatriation_mission_after_days' => '10',
            'regional.auto_return_after_days' => '20',
            'regional.repatriation_reward_multiplier' => '2',
        ] as $key => $value) {
            DB::table('promethee_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        if (Schema::hasTable('aircraft')) {
            $validBases = DB::table('promethee_operational_bases')->pluck('airport_id')->all();
            DB::table('aircraft')->select(['id', 'hub_id'])->orderBy('id')->chunkById(200, function ($aircraft) use ($validBases, $now) {
                foreach ($aircraft as $plane) {
                    $base = in_array($plane->hub_id, $validBases, true) ? $plane->hub_id : 'LFPO';
                    DB::table('promethee_aircraft_bases')->updateOrInsert(
                        ['aircraft_id' => $plane->id],
                        ['base_airport_id' => $base, 'assigned_at' => $now, 'created_at' => $now, 'updated_at' => $now]
                    );
                }
            });
        }

        if (Schema::hasTable('airlines')) {
            DB::table('airlines')->select(['id'])->orderBy('id')->chunkById(100, function ($airlines) use ($now) {
                foreach ($airlines as $airline) {
                    DB::table('promethee_airline_access_rules')->updateOrInsert(
                        ['airline_id' => $airline->id],
                        ['min_flight_hours' => 0, 'created_at' => $now, 'updated_at' => $now]
                    );
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('promethee_missions')) {
            Schema::table('promethee_missions', function (Blueprint $table) {
                foreach (['mission_type', 'aircraft_id', 'reward_multiplier', 'auto_generated'] as $column) {
                    if (Schema::hasColumn('promethee_missions', $column)) $table->dropColumn($column);
                }
            });
        }

        Schema::dropIfExists('promethee_mission_bookings');
        Schema::dropIfExists('promethee_airline_access_rules');
        Schema::dropIfExists('promethee_aircraft_bases');
        Schema::dropIfExists('promethee_operational_bases');
    }
};
