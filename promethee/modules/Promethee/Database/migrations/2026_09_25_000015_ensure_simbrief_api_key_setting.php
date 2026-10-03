<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $existing = DB::table('settings')->where('key', 'simbrief.api_key')->first();
        if ($existing) {
            DB::table('settings')->where('key', 'simbrief.api_key')->update([
                'name' => 'SimBrief Company API Key',
                'group' => 'simbrief',
                'type' => 'secret',
                'description' => 'Company SimBrief API key used server-side by Prométhée. It is never sent to Hermès.',
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table('settings')->insert([
            'id' => 'simbrief_api_key',
            'offset' => 0,
            'order' => 99,
            'key' => 'simbrief.api_key',
            'name' => 'SimBrief Company API Key',
            'value' => '',
            'default' => null,
            'group' => 'simbrief',
            'type' => 'secret',
            'options' => '',
            'description' => 'Company SimBrief API key used server-side by Prométhée. It is never sent to Hermès.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Keep the credential/settings row on rollback to avoid destructive secret deletion.
    }
};
