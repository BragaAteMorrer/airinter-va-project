<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_settings')) {
            Schema::create('promethee_settings', function (Blueprint $table) {
                $table->string('key', 80)->primary();
                $table->string('value', 191);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_settings');
    }
};
