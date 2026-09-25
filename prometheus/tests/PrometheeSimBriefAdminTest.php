<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

final class PrometheeSimBriefAdminTest extends TestCase
{
    public function test_admin_can_store_company_api_key_without_echoing_it_back(): void
    {
        $admin = $this->createAdminUser();
        $secret = 'SIMBRIEF_TEST_SECRET_'.bin2hex(random_bytes(8));

        $this->actingAs($admin, 'web')
            ->post('/admin/promethee/simbrief/settings', [
                'api_key' => $secret,
            ])
            ->assertRedirect();

        $this->assertSame(
            $secret,
            (string) DB::table('settings')->where('key', 'simbrief.api_key')->value('value')
        );

        $response = $this->actingAs($admin, 'web')->get('/admin/promethee/simbrief');

        $response->assertOk();
        $response->assertSee('CONFIGURÉE', false);
        $response->assertSee('type="password"', false);
        $response->assertDontSee($secret, false);
    }

    public function test_blank_api_key_keeps_existing_secret(): void
    {
        $admin = $this->createAdminUser();
        $secret = 'SIMBRIEF_KEEP_'.bin2hex(random_bytes(8));
        $this->putSecret($secret);

        $this->actingAs($admin, 'web')
            ->post('/admin/promethee/simbrief/settings', [
                'api_key' => '',
            ])
            ->assertRedirect();

        $this->assertSame(
            $secret,
            (string) DB::table('settings')->where('key', 'simbrief.api_key')->value('value')
        );
    }

    public function test_admin_must_explicitly_clear_company_api_key(): void
    {
        $admin = $this->createAdminUser();
        $this->putSecret('SIMBRIEF_TO_CLEAR');

        $this->actingAs($admin, 'web')
            ->post('/admin/promethee/simbrief/settings', [
                'api_key' => '',
                'clear_api_key' => '1',
            ])
            ->assertRedirect();

        $this->assertSame(
            '',
            (string) DB::table('settings')->where('key', 'simbrief.api_key')->value('value')
        );
    }

    private function putSecret(string $value): void
    {
        $now = now();
        $existing = DB::table('settings')->where('key', 'simbrief.api_key')->first();

        if ($existing) {
            DB::table('settings')->where('key', 'simbrief.api_key')->update([
                'value' => $value,
                'type' => 'secret',
                'updated_at' => $now,
            ]);

            return;
        }

        DB::table('settings')->insert([
            'id' => 'simbrief_api_key',
            'offset' => 0,
            'order' => 99,
            'key' => 'simbrief.api_key',
            'name' => 'SimBrief Company API Key',
            'value' => $value,
            'default' => null,
            'group' => 'simbrief',
            'type' => 'secret',
            'options' => '',
            'description' => 'Company SimBrief API key used server-side by Prométhée. It is never sent to Hermès.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
