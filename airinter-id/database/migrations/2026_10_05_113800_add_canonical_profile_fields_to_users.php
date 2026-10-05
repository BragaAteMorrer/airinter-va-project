<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('country', 2)->nullable()->after('timezone');
            $table->string('home_airport_id', 10)->nullable()->after('country');
            $table->string('vatsim_id', 32)->nullable()->after('home_airport_id');
            $table->string('ivao_id', 32)->nullable()->after('vatsim_id');
            $table->string('avatar_path')->nullable()->after('ivao_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'country',
                'home_airport_id',
                'vatsim_id',
                'ivao_id',
                'avatar_path',
            ]);
        });
    }
};
