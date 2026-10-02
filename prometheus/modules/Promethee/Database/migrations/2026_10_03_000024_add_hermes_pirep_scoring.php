<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('promethee_pirep_scores')) {
            return;
        }

        Schema::create('promethee_pirep_scores', function (Blueprint $table) {
            $table->string('pirep_id', 36)->primary();
            $table->smallInteger('score');
            $table->json('score_data');
            $table->string('engine_version', 16);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
            $table->index(['score', 'calculated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_pirep_scores');
    }
};
