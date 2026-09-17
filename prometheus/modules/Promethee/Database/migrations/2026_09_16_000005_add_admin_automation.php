<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if (!Schema::hasTable('promethee_badge_rules')) Schema::create('promethee_badge_rules', function (Blueprint $t) { $t->id(); $t->unsignedInteger('award_id'); $t->string('operator',3)->default('and'); $t->json('criteria'); $t->boolean('active')->default(false); $t->timestamps(); $t->index('award_id'); });
  if (!Schema::hasTable('promethee_rank_rules')) Schema::create('promethee_rank_rules', function (Blueprint $t) { $t->id(); $t->unsignedInteger('rank_id'); $t->string('operator',3)->default('and'); $t->json('criteria'); $t->boolean('active')->default(false); $t->boolean('allow_demotion')->default(false); $t->timestamps(); $t->index('rank_id'); });
  if (!Schema::hasTable('promethee_progression_history')) Schema::create('promethee_progression_history', function (Blueprint $t) { $t->id(); $t->unsignedInteger('user_id'); $t->string('kind',16); $t->string('subject_id',36); $t->string('before_value')->nullable(); $t->string('after_value')->nullable(); $t->string('origin',16); $t->text('reason')->nullable(); $t->timestamps(); $t->index(['user_id','kind']); });
 }
 public function down(): void { Schema::dropIfExists('promethee_progression_history'); Schema::dropIfExists('promethee_rank_rules'); Schema::dropIfExists('promethee_badge_rules'); }
};
