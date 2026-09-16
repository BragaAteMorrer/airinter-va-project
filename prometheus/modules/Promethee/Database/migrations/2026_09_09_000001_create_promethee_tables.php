<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
    if (!Schema::hasTable('promethee_events')) {
        Schema::create('promethee_events', function (Blueprint $t) {
            $t->id(); $t->string('title'); $t->text('description')->nullable();
            $t->dateTime('starts_at'); $t->dateTime('ends_at'); $t->string('departure', 8)->nullable();
            $t->string('arrival', 8)->nullable(); $t->unsignedInteger('created_by'); $t->timestamps();
        });
    }
    if (!Schema::hasTable('promethee_members')) {
        Schema::create('promethee_members', function (Blueprint $t) {
            $t->unsignedInteger('user_id')->primary(); $t->string('status', 16); $t->timestamps();
        });
    }
    if (!Schema::hasTable('promethee_pricing')) {
        Schema::create('promethee_pricing', function (Blueprint $t) {
            $t->id(); $t->string('flight_id',36); $t->unsignedInteger('fare_id');
            $t->string('band',8)->default('rouge'); $t->decimal('red_price',10,2);
            $t->decimal('multiplier',6,4)->default(1); $t->timestamps(); $t->unique(['flight_id','fare_id']);
        });
    }
    if (!Schema::hasTable('promethee_changes')) {
        Schema::create('promethee_changes', function (Blueprint $t) {
            $t->id(); $t->unsignedInteger('user_id'); $t->string('target',16);
            $t->json('filters'); $t->json('changes'); $t->timestamp('reverted_at')->nullable(); $t->timestamps();
        });
    }
    if (!Schema::hasTable('promethee_telemetry')) {
        Schema::create('promethee_telemetry', function (Blueprint $t) {
            $t->id(); $t->string('pirep_id',36); $t->uuid('sample_id')->unique();
            $t->dateTime('recorded_at'); $t->json('payload'); $t->timestamps();
            $t->index(['pirep_id','recorded_at']);
        });
    }
    if (!Schema::hasTable('promethee_bulletins')) {
        Schema::create('promethee_bulletins', function (Blueprint $t) {
            $t->id(); $t->string('month',7)->unique(); $t->json('report'); $t->timestamps();
        });
    }
    }
    public function down(): void
    {
        foreach (['bulletins','telemetry','changes','pricing','members','events'] as $name) {
            Schema::dropIfExists('promethee_'.$name);
        }
    }
};
