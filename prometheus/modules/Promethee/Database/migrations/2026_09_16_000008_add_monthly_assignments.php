<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { if (Schema::hasTable('promethee_assignments')) return; Schema::create('promethee_assignments', function (Blueprint $t) { $t->id(); $t->unsignedInteger('user_id'); $t->string('flight_id',36); $t->char('month',7); $t->text('notes')->nullable(); $t->unsignedInteger('assigned_by')->nullable(); $t->timestamps(); $t->unique(['user_id','flight_id','month'],'prom_assignment_unique'); $t->index(['user_id','month'],'prom_assignment_pilot_month'); }); } public function down(): void { Schema::dropIfExists('promethee_assignments'); } };
