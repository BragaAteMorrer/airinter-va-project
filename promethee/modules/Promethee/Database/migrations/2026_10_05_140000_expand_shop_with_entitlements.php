<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('promethee_shop_items', function (Blueprint $t) {
            $t->string('type', 40)->default('generic')->after('description')->index();
            $t->string('target_type', 40)->nullable()->after('type');
            $t->string('target_id', 120)->nullable()->after('target_type');
            $t->unsignedInteger('duration_hours')->nullable()->after('price');
            $t->json('metadata')->nullable()->after('duration_hours');
        });
        Schema::table('promethee_shop_orders', function (Blueprint $t) {
            $t->string('target_id', 120)->nullable()->after('item_id');
            $t->timestamp('expires_at')->nullable()->after('purchased_at')->index();
        });
        Schema::create('promethee_shop_entitlements', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('user_id')->index();
            $t->unsignedBigInteger('item_id');
            $t->string('type', 40)->index();
            $t->string('target_type', 40)->nullable();
            $t->string('target_id', 120)->nullable();
            $t->timestamp('starts_at');
            $t->timestamp('expires_at')->nullable()->index();
            $t->json('metadata')->nullable();
            $t->timestamps();
            $t->index(['user_id','type','target_id'], 'prom_shop_entitlement_lookup');
        });
        Schema::create('promethee_maintenance_priorities', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('user_id')->index();
            $t->unsignedInteger('aircraft_id')->index();
            $t->unsignedBigInteger('order_id')->nullable();
            $t->string('status', 20)->default('queued')->index();
            $t->timestamp('requested_at');
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('promethee_maintenance_priorities');
        Schema::dropIfExists('promethee_shop_entitlements');
        Schema::table('promethee_shop_orders', fn(Blueprint $t) => $t->dropColumn(['target_id','expires_at']));
        Schema::table('promethee_shop_items', fn(Blueprint $t) => $t->dropColumn(['type','target_type','target_id','duration_hours','metadata']));
    }
};