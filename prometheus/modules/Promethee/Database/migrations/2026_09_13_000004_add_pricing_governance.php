<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_price_history')) {
        Schema::create('promethee_price_history', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('change_id')->nullable();
            $t->string('target', 16);
            $t->string('subject_id', 80);
            $t->string('field', 80);
            $t->decimal('before_price', 12, 4)->nullable();
            $t->decimal('after_price', 12, 4);
            $t->json('context')->nullable();
            $t->timestamps();
            $t->index(['target', 'subject_id', 'field', 'created_at'], 'prom_price_history_lookup');
        }); }

        if (!Schema::hasTable('promethee_pricing_rules')) {
        Schema::create('promethee_pricing_rules', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('user_id');
            $t->string('name', 120);
            $t->string('target', 16);
            $t->json('definition');
            $t->boolean('active')->default(true);
            $t->unsignedTinyInteger('priority')->default(100);
            $t->timestamp('last_previewed_at')->nullable();
            $t->timestamp('last_applied_at')->nullable();
            $t->timestamps();
            $t->index(['active', 'priority']);
        }); }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_pricing_rules');
        Schema::dropIfExists('promethee_price_history');
    }
};
