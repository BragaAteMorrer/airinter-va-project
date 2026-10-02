<?php

use App\Contracts\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pireps', 'block_time')) {
            Schema::table('pireps', function (Blueprint $table) {
                $table->unsignedInteger('block_time')->nullable()->after('flight_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pireps', 'block_time')) {
            Schema::table('pireps', function (Blueprint $table) {
                $table->dropColumn('block_time');
            });
        }
    }
};
