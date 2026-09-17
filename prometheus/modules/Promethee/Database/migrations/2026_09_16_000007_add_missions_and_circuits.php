<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('promethee_missions')) Schema::create('promethee_missions', function (Blueprint $t) {
            $t->id(); $t->unsignedInteger('created_by')->nullable(); $t->string('title', 160); $t->text('description')->nullable();
            $t->string('dpt_airport_id', 8)->nullable(); $t->string('arr_airport_id', 8)->nullable(); $t->string('flight_id', 36)->nullable();
            $t->date('starts_on')->nullable(); $t->date('ends_on')->nullable(); $t->boolean('active')->default(true); $t->timestamps();
            $t->index(['active','starts_on','ends_on']);
        });
        if (!Schema::hasTable('promethee_circuits')) Schema::create('promethee_circuits', function (Blueprint $t) {
            $t->id(); $t->unsignedInteger('created_by')->nullable(); $t->string('title', 160); $t->text('description')->nullable();
            $t->date('starts_on')->nullable(); $t->date('ends_on')->nullable(); $t->boolean('active')->default(true); $t->timestamps();
            $t->index(['active','starts_on','ends_on']);
        });
        if (!Schema::hasTable('promethee_circuit_legs')) Schema::create('promethee_circuit_legs', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('circuit_id'); $t->unsignedSmallInteger('position'); $t->string('dpt_airport_id', 8); $t->string('arr_airport_id', 8); $t->string('flight_id', 36)->nullable(); $t->timestamps();
            $t->unique(['circuit_id','position'], 'prom_circuit_leg_position_unique');
            $t->index(['circuit_id','dpt_airport_id','arr_airport_id'], 'prom_circuit_leg_route_index');
        });
    }
    public function down(): void { Schema::dropIfExists('promethee_circuit_legs'); Schema::dropIfExists('promethee_circuits'); Schema::dropIfExists('promethee_missions'); }
};
