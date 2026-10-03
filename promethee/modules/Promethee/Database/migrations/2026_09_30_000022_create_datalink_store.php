<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promethee_datalink_stores')) return;

        Schema::create('promethee_datalink_stores', function (Blueprint $table) {
            $table->string('operation_id', 128)->primary();
            $table->longText('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_datalink_stores');
    }
};
