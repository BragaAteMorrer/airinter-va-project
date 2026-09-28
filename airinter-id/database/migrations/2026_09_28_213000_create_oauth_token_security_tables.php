<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('oauth_token_families', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('client_id', 191)->index();
            $table->string('status', 32)->default('active')->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoke_reason', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('oauth_refresh_token_lineage', function (Blueprint $table) {
            $table->id();
            $table->uuid('family_id');
            $table->string('token_hash', 64)->unique();
            $table->string('access_token_id', 191)->nullable()->index();
            $table->string('status', 32)->default('active')->index();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign('family_id')->references('id')->on('oauth_token_families')->cascadeOnDelete();
            $table->index(['family_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_refresh_token_lineage');
        Schema::dropIfExists('oauth_token_families');
    }
};
