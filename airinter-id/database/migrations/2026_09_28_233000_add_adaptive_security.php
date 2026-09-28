<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('name', 120)->nullable();
            $table->string('user_agent_hash', 64);
            $table->string('last_ip_address', 45)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'revoked_at', 'expires_at']);
        });

        Schema::table('security_events', function (Blueprint $table) {
            $table->unsignedSmallInteger('risk_score')->default(0)->after('type')->index();
            $table->string('severity', 16)->default('info')->after('risk_score')->index();
        });
    }

    public function down(): void
    {
        Schema::table('security_events', function (Blueprint $table) {
            $table->dropColumn(['risk_score', 'severity']);
        });

        Schema::dropIfExists('trusted_devices');
    }
};
