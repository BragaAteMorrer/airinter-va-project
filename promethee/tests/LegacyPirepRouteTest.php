<?php

namespace Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Promethee\Http\PortalController;

class LegacyPirepRouteTest extends TestCase
{
    public function test_legacy_pirep_url_is_handled_by_promethee_report_controller(): void
    {
        $route = Route::getRoutes()->match(
            Request::create('/legacy/pireps/OWGRmb3gZzODwnkX', 'GET')
        );

        $this->assertSame(
            PortalController::class.'@pirep',
            $route->getActionName()
        );

        $this->assertContains('web', $route->gatherMiddleware());
        $this->assertContains('auth', $route->gatherMiddleware());
    }
}
