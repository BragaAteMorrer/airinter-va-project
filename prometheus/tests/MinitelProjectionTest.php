<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Route;

final class MinitelProjectionTest extends TestCase
{
    public function test_minitel_bootstrap_exposes_only_read_only_surfaces(): void
    {
        $pilot = User::factory()->create();

        $response = $this->actingAs($pilot, 'web')->getJson('/minitel/bootstrap');

        $response->assertOk()
            ->assertJsonPath('service', '3615 AIRINTER')
            ->assertJsonPath('product', 'PROMETHEE')
            ->assertJsonPath('pilot.id', $pilot->id)
            ->assertJsonStructure([
                'pilot' => ['id', 'pilot_id', 'name', 'home_airport'],
                'stats' => ['flights', 'pilots', 'active', 'today', 'personal'],
                'endpoints' => ['departures', 'flights', 'routes', 'fleet', 'pilots', 'calendar', 'profile'],
                'updated_at',
            ]);

        foreach ($response->json('endpoints') as $url) {
            $this->assertIsString($url);
            $this->assertStringNotContainsString('/reserve', $url);
            $this->assertStringNotContainsString('/briefing', $url);
            $this->assertStringNotContainsString('/simbrief', $url);
        }
    }

    public function test_m2_routes_are_get_only(): void
    {
        foreach ([
            'promethee.minitel.bootstrap',
            'promethee.minitel.flights',
            'promethee.minitel.routes',
            'promethee.minitel.fleet',
            'promethee.minitel.pilots',
            'promethee.minitel.calendar',
            'promethee.minitel.profile',
        ] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, $name.' route missing');
            $this->assertContains('GET', $route->methods());
            $this->assertNotContains('POST', $route->methods());
            $this->assertNotContains('PUT', $route->methods());
            $this->assertNotContains('PATCH', $route->methods());
            $this->assertNotContains('DELETE', $route->methods());
        }
    }
}
