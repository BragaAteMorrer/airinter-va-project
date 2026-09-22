<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('promethee_operation_links')) {
            return;
        }

        Schema::create('promethee_operation_links', function (Blueprint $table) {
            $table->id();
            $table->uuid('operation_id')->unique();
            $table->string('bid_id', 64)->unique();
            $table->unsignedInteger('user_id')->index();
            $table->string('flight_id', 64)->index();
            $table->unsignedBigInteger('aircraft_id')->nullable()->index();
            $table->string('simbrief_id', 128)->nullable()->index();
            $table->string('pirep_id', 64)->nullable()->unique();
            $table->string('status', 32)->default('reserved')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_operation_links');
    }
};
