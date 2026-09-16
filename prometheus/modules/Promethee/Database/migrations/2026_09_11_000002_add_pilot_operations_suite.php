<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_briefings')) {
        Schema::create('promethee_briefings', function (Blueprint $t) {
            $t->id(); $t->unsignedInteger('user_id'); $t->string('flight_id', 36);
            $t->string('ofp_reference', 120)->nullable(); $t->string('alternate', 8)->nullable();
            $t->unsignedInteger('planned_fuel')->nullable(); $t->text('notes')->nullable();
            $t->timestamps(); $t->unique(['user_id', 'flight_id']);
        }); }
        if (!Schema::hasTable('promethee_event_rsvps')) {
        Schema::create('promethee_event_rsvps', function (Blueprint $t) {
            $t->unsignedBigInteger('event_id'); $t->unsignedInteger('user_id');
            $t->string('status', 16)->default('going'); $t->timestamps();
            $t->primary(['event_id', 'user_id']);
        }); }
        if (!Schema::hasTable('promethee_audit_logs')) {
        Schema::create('promethee_audit_logs', function (Blueprint $t) {
            $t->id(); $t->unsignedInteger('actor_id')->nullable(); $t->string('action', 80);
            $t->string('subject_type', 80)->nullable(); $t->string('subject_id', 80)->nullable();
            $t->json('context')->nullable(); $t->timestamps();
            $t->index(['action', 'created_at']);
        }); }
    }
    public function down(): void { Schema::dropIfExists('promethee_audit_logs'); Schema::dropIfExists('promethee_event_rsvps'); Schema::dropIfExists('promethee_briefings'); }
};
