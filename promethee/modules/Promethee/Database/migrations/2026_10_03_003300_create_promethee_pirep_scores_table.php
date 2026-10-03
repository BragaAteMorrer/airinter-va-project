<?php

use App\Contracts\Migration;
use App\Contracts\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promethee_pirep_scores')) return;

        Schema::create('promethee_pirep_scores', function (Blueprint $table) {
            $table->string('pirep_id', Model::ID_MAX_LENGTH)->primary();
            $table->smallInteger('score');
            $table->smallInteger('starting_score')->default(100);
            $table->smallInteger('penalty_total')->default(0);
            $table->longText('breakdown');
            $table->longText('rules_snapshot');
            $table->unsignedSmallInteger('scoring_version')->default(1);
            $table->dateTime('calculated_at');
            $table->timestamps();

            $table->foreign('pirep_id')->references('id')->on('pireps')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_pirep_scores');
    }
};
