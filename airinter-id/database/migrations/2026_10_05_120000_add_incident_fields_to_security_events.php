<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('security_events', function (Blueprint $table) {
            $table->timestamp('reported_at')->nullable()->after('metadata')->index();
            $table->timestamp('resolved_at')->nullable()->after('reported_at');
        });
    }

    public function down(): void
    {
        Schema::table('security_events', function (Blueprint $table) {
            $table->dropColumn(['reported_at', 'resolved_at']);
        });
    }
};
