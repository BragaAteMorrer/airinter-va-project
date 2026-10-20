<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('promethee_missions') && !Schema::hasColumn('promethee_missions', 'image_url')) {
            Schema::table('promethee_missions', function (Blueprint $table) {
                $table->string('image_url', 2048)->nullable();
            });
        }
    }
    public function down(): void {
        if (Schema::hasTable('promethee_missions') && Schema::hasColumn('promethee_missions', 'image_url')) {
            Schema::table('promethee_missions', function (Blueprint $table) { $table->dropColumn('image_url'); });
        }
    }
};