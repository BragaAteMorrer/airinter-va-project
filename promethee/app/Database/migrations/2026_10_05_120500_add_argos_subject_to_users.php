<?php

use App\Contracts\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'argos_subject')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('argos_subject')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'argos_subject')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['argos_subject']);
            $table->dropColumn('argos_subject');
        });
    }
};
