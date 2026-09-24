<?php
namespace Modules\Promethee\Providers;

use App\Contracts\Modules\ServiceProvider;
use App\Services\ModuleService;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Promethee\Console\BulletinCommand;
use Modules\Promethee\Console\LocalUserCommand;
use Modules\Promethee\Console\RecalculateProgressionCommand;

class PrometheeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/departure-board.php', 'departure-board');
        $this->mergeConfigFrom(__DIR__.'/../Config/acars.php', 'acars');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'promethee');
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes.php');
        if ($this->app->runningInConsole()) {
            $this->commands([BulletinCommand::class, LocalUserCommand::class, RecalculateProgressionCommand::class]);
        }
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('promethee:bulletin')->monthlyOn(1, '06:00')->timezone('Europe/Paris')->withoutOverlapping();
            $schedule->command('promethee:progression-recalculate')->hourly()->withoutOverlapping();
        });
    }
    public function registerLinks(): void
    {
        app(ModuleService::class)->addFrontendLink('Prométhée', '/', 'fas fa-plane', true);
        app(ModuleService::class)->addAdminLink('Prométhée · Identité', '/admin/promethee/identite', 'pe-7s-photo');
        app(ModuleService::class)->addAdminLink('Prométhée · SimBrief', '/admin/promethee/simbrief', 'pe-7s-plane');
    }
}
