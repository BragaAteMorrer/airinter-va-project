<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['promethee_missions', 'promethee_circuits'] as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'award_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedInteger('award_id')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['promethee_missions', 'promethee_circuits'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'award_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('award_id');
                });
            }
        }
    }
};
