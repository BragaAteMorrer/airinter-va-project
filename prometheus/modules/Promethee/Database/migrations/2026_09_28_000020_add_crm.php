<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('promethee_crm_senders')) {
            Schema::create('promethee_crm_senders', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('email', 191)->unique();
                $table->string('reply_to', 191)->nullable();
                $table->boolean('active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('promethee_crm_campaigns')) {
            Schema::create('promethee_crm_campaigns', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('created_by');
                $table->unsignedBigInteger('sender_id')->nullable();
                $table->string('subject', 191);
                $table->longText('body');
                $table->json('audience');
                $table->string('status', 24)->default('draft');
                $table->unsignedInteger('recipient_count')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
                $table->index(['status','created_at'], 'prom_crm_campaign_status_idx');
            });
        }

        if (!Schema::hasTable('promethee_crm_recipients')) {
            Schema::create('promethee_crm_recipients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campaign_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('email', 191);
                $table->string('name', 191)->nullable();
                $table->string('status', 24)->default('queued');
                $table->text('error')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
                $table->unique(['campaign_id','email'], 'prom_crm_campaign_email_unique');
                $table->index(['campaign_id','status'], 'prom_crm_recipient_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promethee_crm_recipients');
        Schema::dropIfExists('promethee_crm_campaigns');
        Schema::dropIfExists('promethee_crm_senders');
    }
};
