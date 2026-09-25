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
                'endpoints' => [
                    'departures', 'flights', 'routes', 'fleet', 'pilots', 'calendar', 'profile',
                    'operations', 'operation_search', 'reserve_base', 'operation_base',
                ],
                'updated_at',
            ]);

        foreach (['departures', 'flights', 'routes', 'fleet', 'pilots', 'calendar', 'profile'] as $key) {
            $url = $response->json('endpoints.'.$key);
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

    public function test_m3_routes_use_explicit_http_verbs(): void
    {
        $expectations = [
            'promethee.minitel.operations' => 'GET',
            'promethee.minitel.operations.search-flights' => 'GET',
            'promethee.minitel.operations.reserve' => 'POST',
            'promethee.minitel.operations.show' => 'GET',
            'promethee.minitel.operations.aircraft' => 'GET',
            'promethee.minitel.operations.aircraft.select' => 'PUT',
            'promethee.minitel.operations.briefing' => 'GET',
            'promethee.minitel.operations.dispatch' => 'GET',
            'promethee.minitel.operations.pirep' => 'POST',
            'promethee.minitel.operations.simbrief.redirect' => 'POST',
            'promethee.minitel.operations.simbrief.import-account' => 'POST',
            'promethee.minitel.operations.simbrief.session' => 'POST',
            'promethee.minitel.operations.simbrief.import' => 'POST',
        ];

        foreach ($expectations as $name => $verb) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, $name.' route missing');
            $this->assertContains($verb, $route->methods(), $name.' must allow '.$verb);

            foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $mutation) {
                if ($mutation === $verb) continue;
                $this->assertNotContains($mutation, $route->methods(), $name.' unexpectedly allows '.$mutation);
            }
        }
    }
}
