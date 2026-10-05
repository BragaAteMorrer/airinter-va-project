<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;

final class PrometheeRealSimulatorCertificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->addData('base');
    }

    public function test_admin_can_create_real_simulator_certification(): void
    {
        $admin = $this->adminUser();
        $pilot = User::factory()->create(['name' => 'Pilote Certifié']);

        $this->actingAs($admin, 'web')
            ->post('/admin/promethee/real-simulator-certifications', [
                'user_id' => $pilot->id,
                'certificate_name' => 'A320 · séance FFS',
                'aircraft_type' => 'a320',
                'simulator_level' => 'FFS_D',
                'device_name' => 'Airbus A320 Full Flight Simulator',
                'organisation' => 'Centre de formation test',
                'location' => 'Paris',
                'completed_on' => '2026-10-01',
                'valid_until' => '2027-10-01',
                'reference' => 'CERT-2026-001',
                'status' => 'verified',
            ])
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('promethee_real_simulator_certifications', [
            'user_id' => $pilot->id,
            'aircraft_type' => 'A320',
            'simulator_level' => 'FFS_D',
            'status' => 'verified',
            'verified_by' => $admin->id,
        ]);
    }

    public function test_regular_pilot_cannot_manage_real_simulator_certifications(): void
    {
        $pilot = User::factory()->create();

        $this->actingAs($pilot, 'web')
            ->get('/admin/promethee/real-simulator-certifications')
            ->assertForbidden();
    }

    public function test_verified_certification_is_visible_on_pilot_profile(): void
    {
        $viewer = User::factory()->create();
        $pilot = User::factory()->create(['name' => 'Pilote FFS']);
        $admin = $this->adminUser();

        $this->actingAs($admin, 'web')
            ->post('/admin/promethee/real-simulator-certifications', [
                'user_id' => $pilot->id,
                'certificate_name' => 'Concorde · séance FFS',
                'aircraft_type' => 'CONC',
                'simulator_level' => 'FFS_D',
                'device_name' => 'Concorde Full Flight Simulator',
                'organisation' => 'Centre historique',
                'location' => 'Toulouse',
                'completed_on' => '2026-09-20',
                'status' => 'verified',
            ])
            ->assertRedirect();

        $this->actingAs($viewer, 'web')
            ->get('/pilots/'.$pilot->id)
            ->assertOk()
            ->assertSee('Concorde · séance FFS')
            ->assertSee('Centre historique')
            ->assertSee('Toulouse');
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();
        $role = Role::where('name', 'admin')->firstOrFail();
        $user->addRole($role);

        return $user;
    }
}
