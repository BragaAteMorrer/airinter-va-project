<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
    if (!Schema::hasTable('promethee_seasons')) {
        Schema::create('promethee_seasons', function (Blueprint $t) {
            $t->id();
            $t->string('name', 80);
            $t->date('starts_on');
            $t->date('ends_on');
            $t->boolean('active')->default(false);
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }
    if (!Schema::hasTable('promethee_badges')) {
        Schema::create('promethee_badges', function (Blueprint $t) {
            $t->id();
            $t->string('code', 40)->unique();
            $t->string('name', 80);
            $t->string('description', 255);
            $t->unsignedInteger('threshold')->default(1);
            $t->timestamps();
        });
    }
    if (!Schema::hasTable('promethee_pilot_badges')) {
        Schema::create('promethee_pilot_badges', function (Blueprint $t) {
            $t->unsignedInteger('user_id');
            $t->unsignedBigInteger('badge_id');
            $t->timestamp('earned_at');
            $t->primary(['user_id', 'badge_id']);
        });
    }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_pilot_badges');
        Schema::dropIfExists('promethee_badges');
        Schema::dropIfExists('promethee_seasons');
    }
};
