<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('promethee_members', function (Blueprint $table) {
            $table->string('memorial_portrait_url', 2000)->nullable()->after('status');
            $table->text('memorial_tribute')->nullable()->after('memorial_portrait_url');
        });

        DB::table('promethee_members')->where('status', 'retraite')->update(['status' => 'heaven']);
    }

    public function down(): void
    {
        DB::table('promethee_members')->where('status', 'heaven')->update(['status' => 'retraite']);
        Schema::table('promethee_members', function (Blueprint $table) {
            $table->dropColumn(['memorial_portrait_url', 'memorial_tribute']);
        });
    }
};
