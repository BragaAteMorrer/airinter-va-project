<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('oidc_authorization_requests', function (Blueprint $table) {
            $table->id();
            $table->string('client_id', 191)->index();
            $table->string('code_challenge', 191)->index();
            $table->string('nonce', 191);
            $table->text('scope');
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->unique(['client_id', 'code_challenge']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oidc_authorization_requests');
    }
};
