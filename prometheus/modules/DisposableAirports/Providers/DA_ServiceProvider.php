<?php

namespace Modules\DisposableAirports\Providers;

use App\Services\ModuleService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DA_ServiceProvider extends ServiceProvider
{
    protected $moduleSvc;

    // Boot the application events
    public function boot()
    {
        $this->moduleSvc = app(ModuleService::class);

        $this->registerRoutes();
        $this->registerConfig();
        $this->registerViews();
        $this->registerLinks();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
    }

    // Service Providers
    public function register() {}

    // Module Links
    public function registerLinks()
    {
        $this->moduleSvc->addAdminLink('Disposable Airports', '/admin/dairports', 'pe-7s-tools');
    }

    // Routes
    protected function registerRoutes()
    {
        // Admin
        Route::group([
            'as'         => 'DAirports.',
            'middleware' => ['web', 'auth', 'ability:admin,admin-access,addons,modules,airports'],
            'namespace'  => 'Modules\DisposableAirports\Http\Controllers',
            'prefix'     => 'admin',
        ], function () {
            // Airports
            Route::match(['get', 'post'], 'dairports', 'DA_AirportController@index')->name('index');
            Route::match(['get', 'post'], 'dairports/destroy/{id}', 'DA_AirportController@destroy')->name('destroy_airport');
            Route::match(['get', 'post'], 'dairports/restore/{id}', 'DA_AirportController@restore')->name('restore_airport');
            Route::match(['get', 'post'], 'dairports/update_all', 'DA_AirportController@update_all')->name('update_all');
            Route::match(['get', 'post'], 'dairports/update/{id}', 'DA_AirportController@update_airport')->name('update_airport');
            Route::match(['get', 'post'], 'disposableairports', 'DA_AirportController@index')->name('module_index');
            Route::match(['get', 'post'], 'dsettings_update', 'DA_AirportController@settings_update')->name('update_settings');
            Route::match(['get', 'post'], 'dairports/fix_uz', 'DA_AirportController@fix_uzbekistan_airports')->name('fix_uzbekistan');
            Route::match(['get', 'post'], 'dairports/cleanup', 'DA_AirportController@cleanup_airports')->name('cleanup_airports');
        });
    }

    // Config
    protected function registerConfig()
    {
        $this->publishes([__DIR__ . '/../Config/config.php' => config_path('DisposableAirports.php')], 'config');
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'DisposableAirports');
    }

    // Views
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/DisposableAirports');
        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([$sourcePath => $viewPath], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return str_replace('default', setting('general.theme'), $path) . '/modules/DisposableAirports';
        }, \Config::get('view.paths')), [$sourcePath]), 'DAirports');
    }

    public function provides(): array
    {
        return [];
    }
}
