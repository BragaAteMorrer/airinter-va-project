<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('promethee_completion_bonus_payments')) {
            Schema::create('promethee_completion_bonus_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->string('subject_type', 16);
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('amount');
                $table->timestamps();
                $table->unique(['user_id', 'subject_type', 'subject_id'], 'prom_completion_bonus_unique');
            });
        }
    }
    public function down(): void { Schema::dropIfExists('promethee_completion_bonus_payments'); }
};