<?php
namespace Modules\Promethee\Providers;

use App\Contracts\Modules\ServiceProvider;
use App\Events\PirepAccepted;
use App\Events\UserStatsChanged;
use App\Services\ModuleService;
use Illuminate\Support\Facades\Event;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Promethee\Console\BulletinCommand;
use Modules\Promethee\Console\CheckPrometheeTranslations;
use Modules\Promethee\Console\CheckTranslations;
use Modules\Promethee\Console\LocalUserCommand;
use Modules\Promethee\Console\RecalculateProgressionCommand;
use Modules\Promethee\Console\SyncRegionalOperationsCommand;
use Modules\Promethee\Listeners\ProgressionEventListener;

class PrometheeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/departure-board.php', 'departure-board');
        $this->mergeConfigFrom(__DIR__.'/../Config/maintenance-warning.php', 'maintenance-warning');
        $this->mergeConfigFrom(__DIR__.'/../Config/acars.php', 'acars');
        $this->mergeConfigFrom(__DIR__.'/../Config/cabin-profiles.php', 'promethee.cabin-profiles');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'promethee');
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes.php');
        Event::listen(PirepAccepted::class, [ProgressionEventListener::class, 'onPirepAccepted']);
        Event::listen(UserStatsChanged::class, [ProgressionEventListener::class, 'onUserStatsChanged']);
        if ($this->app->runningInConsole()) {
            $this->commands([BulletinCommand::class, CheckPrometheeTranslations::class, CheckTranslations::class, LocalUserCommand::class, RecalculateProgressionCommand::class, SyncRegionalOperationsCommand::class]);
        }
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('promethee:bulletin')->monthlyOn(1, '06:00')->timezone('Europe/Paris')->withoutOverlapping();
            $schedule->command('promethee:progression-recalculate')->everyFiveMinutes()->withoutOverlapping();
            $schedule->command('promethee:regional-operations-sync')->hourly()->withoutOverlapping();
        });
    }
    public function registerLinks(): void
    {
        app(ModuleService::class)->addFrontendLink('Prométhée', '/', 'fas fa-plane', true);
        app(ModuleService::class)->addAdminLink('Prométhée · Pilotes', '/admin/promethee/users', 'pe-7s-users');
        app(ModuleService::class)->addAdminLink('Prométhée · Grades', '/admin/promethee/ranks', 'pe-7s-medal');
        app(ModuleService::class)->addAdminLink('Prométhée · Identité', '/admin/promethee/identite', 'pe-7s-photo');
        app(ModuleService::class)->addAdminLink('Prométhée · SimBrief', '/admin/promethee/simbrief', 'pe-7s-plane');
        app(ModuleService::class)->addAdminLink('Prométhée · Tarifs BBR', '/admin/promethee/tarifs-bbr', 'pe-7s-ticket');
        app(ModuleService::class)->addAdminLink('Prométhée · SOP', '/admin/promethee/sop', 'pe-7s-shield');
        app(ModuleService::class)->addAdminLink('Prométhée · Network', '/admin/promethee/network', 'pe-7s-global');
        app(ModuleService::class)->addAdminLink('Prométhée · Dispatch', '/admin/promethee/dispatch', 'pe-7s-monitor');
    }
}
