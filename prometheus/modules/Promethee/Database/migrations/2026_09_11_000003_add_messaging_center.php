<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('promethee_messages')) {
            return;
        }

        Schema::create('promethee_messages', function (Blueprint $t) {
            $t->id(); $t->unsignedInteger('sender_id')->nullable(); $t->unsignedInteger('recipient_id')->nullable();
            $t->string('recipient_email', 191)->nullable(); $t->string('audience', 16)->default('user');
            $t->string('subject', 191); $t->text('body'); $t->boolean('shared_staff')->default(false);
            $t->string('direction', 12)->default('outbound'); $t->string('status', 20)->default('queued');
            $t->timestamp('sent_at')->nullable(); $t->timestamps();
            $t->index(['recipient_id','created_at']); $t->index(['shared_staff','created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('promethee_messages'); }
};
