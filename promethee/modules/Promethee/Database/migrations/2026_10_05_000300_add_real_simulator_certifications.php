<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promethee_real_simulator_certifications')) {
            return;
        }

        Schema::create('promethee_real_simulator_certifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('certificate_name', 160);
            $table->string('aircraft_type', 32)->nullable();
            $table->string('simulator_level', 32);
            $table->string('device_name', 160);
            $table->string('organisation', 160);
            $table->string('location', 160)->nullable();
            $table->date('completed_on');
            $table->date('valid_until')->nullable();
            $table->string('reference', 120)->nullable();
            $table->string('evidence_url', 1000)->nullable();
            $table->string('status', 20)->default('verified');
            $table->text('notes')->nullable();
            $table->unsignedInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'prom_real_sim_cert_user_status');
            $table->index(['completed_on', 'valid_until'], 'prom_real_sim_cert_dates');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_real_simulator_certifications');
    }
};
