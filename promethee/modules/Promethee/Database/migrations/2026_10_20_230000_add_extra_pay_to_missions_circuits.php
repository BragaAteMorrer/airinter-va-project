<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        foreach (['promethee_missions', 'promethee_circuits'] as $name) {
            if (Schema::hasTable($name) && !Schema::hasColumn($name, 'extra_pay')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->decimal('extra_pay', 12, 2)->default(0);
                });
            }
        }
    }
    public function down(): void {
        foreach (['promethee_missions', 'promethee_circuits'] as $name) {
            if (Schema::hasTable($name) && Schema::hasColumn($name, 'extra_pay')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->dropColumn('extra_pay');
                });
            }
        }
    }
};