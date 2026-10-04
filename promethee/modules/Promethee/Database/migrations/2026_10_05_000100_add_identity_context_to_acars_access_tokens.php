<?php

use App\Contracts\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('acars_access_tokens')) return;

        Schema::table('acars_access_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('acars_access_tokens', 'auth_provider')) {
                $table->string('auth_provider', 32)->nullable()->index()->after('user_id');
            }
            if (!Schema::hasColumn('acars_access_tokens', 'identity_subject')) {
                $table->string('identity_subject', 191)->nullable()->index()->after('auth_provider');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('acars_access_tokens')) return;

        Schema::table('acars_access_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('acars_access_tokens', 'identity_subject')) {
                $table->dropColumn('identity_subject');
            }
            if (Schema::hasColumn('acars_access_tokens', 'auth_provider')) {
                $table->dropColumn('auth_provider');
            }
        });
    }
};
