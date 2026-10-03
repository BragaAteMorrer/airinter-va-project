<?php

namespace Tests;

use App\Models\Role;
use App\Models\Setting;
use App\Models\Subfleet;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class AdminControllerTests extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->addData('base');
    }

    private function addAdminUser(): User
    {
        $user = User::factory()->create();
        $role = Role::where(['name' => 'admin'])->first();
        $user->addRole($role);

        return $user;
    }

    /**
     * Test adding a subfleet, deleting it and seeing that the type
     * can be added again
     */
    public function test_simbrief_api_key_is_write_only_in_admin(): void
    {
        $user = $this->addAdminUser();
        $setting = Setting::where('key', 'simbrief.api_key')->firstOrFail();
        $setting->value = 'simbrief-test-secret-value';
        $setting->save();

        $response = $this->actingAs($user, 'web')->get('/admin/settings');

        $response->assertOk();
        $response->assertDontSee('simbrief-test-secret-value');
        $response->assertSee('Configured on the server');
    }

    public function test_simbrief_api_key_blank_keeps_value_and_clear_is_explicit(): void
    {
        $user = $this->addAdminUser();
        $setting = Setting::where('key', 'simbrief.api_key')->firstOrFail();
        $setting->value = 'existing-simbrief-secret';
        $setting->save();

        $this->actingAs($user, 'web')->post('/admin/settings', [
            $setting->id => '',
        ])->assertRedirect('/admin/settings');

        $this->assertSame(
            'existing-simbrief-secret',
            Setting::where('key', 'simbrief.api_key')->value('value')
        );

        $this->actingAs($user, 'web')->post('/admin/settings', [
            $setting->id => 'replacement-simbrief-secret',
        ])->assertRedirect('/admin/settings');

        $this->assertSame(
            'replacement-simbrief-secret',
            Setting::where('key', 'simbrief.api_key')->value('value')
        );
        $this->assertSame(
            0,
            DB::table('activity_log')
                ->where('properties', 'like', '%replacement-simbrief-secret%')
                ->count(),
            'Secret setting values must never be persisted in the activity log.'
        );

        $this->actingAs($user, 'web')->post('/admin/settings', [
            $setting->id => '',
            '_clear_secret' => [$setting->id => 1],
        ])->assertRedirect('/admin/settings');

        $this->assertSame('', Setting::where('key', 'simbrief.api_key')->value('value'));
    }

    public function test_add_subfleet(): void
    {
        $user = $this->addAdminUser();
        $add = Subfleet::factory()->make(['type' => 'B737'])->toArray();
        $this->actingAs($user, 'web')->post('/admin/subfleets', $add);

        $add = Subfleet::factory()->make(['type' => 'A320'])->toArray();
        $this->actingAs($user, 'web')->post('/admin/subfleets', $add);

        // Make sure it was added
        $sf = Subfleet::where(['type' => $add['type']])->first();
        $this->assertNotNull($sf);

        $original_sf_id = $sf->id;

        // delete it
        $resp = $this->actingAs($user, 'web')->delete('/admin/subfleets/'.$sf->id);
        $sf = Subfleet::where(['type' => $add['type']])->first();
        $this->assertNull($sf);

        // Try readding now, it shouldn't complain about the type being unique
        // Would throw a validation error
        $resp = $this->actingAs($user, 'web')->post('/admin/subfleets', $add);
        $resp->assertSessionDoesntHaveErrors();

        $sf = Subfleet::where(['type' => $add['type']])->first();
        $this->assertNotNull($sf);
    }
}
