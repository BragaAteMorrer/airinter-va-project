<?php

namespace App\Providers;

use App\Console\Commands\ArgosDoctor;
use App\Console\Commands\ConfigureFirstPartyClients;
use App\Console\Commands\ImportPrometheeUsers;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Passport::authorizationView('oauth.authorize');
        Passport::tokensExpireIn(now()->addHour());
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::tokensCan([
            'openid' => 'Vous authentifier avec Argos',
            'profile' => 'Lire votre profil Argos',
            'email' => 'Lire votre adresse e-mail',
            'promethee:read' => 'Accéder à votre espace pilote Prométhée',
            'hermes:operate' => 'Utiliser Hermès pour vos opérations de vol',
        ]);
        Passport::defaultScopes([]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ArgosDoctor::class,
                ConfigureFirstPartyClients::class,
                ImportPrometheeUsers::class,
            ]);
        }
    }
}
