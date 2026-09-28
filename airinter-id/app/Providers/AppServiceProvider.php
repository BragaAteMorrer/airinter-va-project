<?php

namespace App\Providers;

use App\Console\Commands\ArgosDoctor;
use App\Console\Commands\ConfigureFirstPartyClients;
use App\Console\Commands\ImportPrometheeUsers;
use App\Console\Commands\RotateArgosKeys;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Laravel\Passkeys\Events\PasskeyDeleted;
use Laravel\Passkeys\Events\PasskeyRegistered;
use Laravel\Passkeys\Events\PasskeyVerified;
use Laravel\Passkeys\Passkeys;
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

        Passkeys::authorizeLoginUsing(function ($request, $user): bool {
            if (!method_exists($user, 'canUseSso') || !$user->canUseSso()) {
                throw ValidationException::withMessages([
                    'credential' => ['Ce compte ne peut pas actuellement utiliser Argos.'],
                ]);
            }

            return true;
        });

        Event::listen(PasskeyRegistered::class, function (PasskeyRegistered $event): void {
            \App\Models\SecurityEvent::create([
                'user_id' => $event->user->id,
                'type' => 'passkey.registered',
                'metadata' => ['name' => $event->passkey->name],
                'created_at' => now(),
            ]);
        });

        Event::listen(PasskeyVerified::class, function (PasskeyVerified $event): void {
            \App\Models\SecurityEvent::create([
                'user_id' => $event->user->id,
                'type' => 'passkey.verified',
                'metadata' => ['name' => $event->passkey->name],
                'created_at' => now(),
            ]);
        });

        Event::listen(PasskeyDeleted::class, function (PasskeyDeleted $event): void {
            \App\Models\SecurityEvent::create([
                'user_id' => $event->user->id,
                'type' => 'passkey.deleted',
                'metadata' => ['name' => $event->passkey->name],
                'created_at' => now(),
            ]);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                ArgosDoctor::class,
                ConfigureFirstPartyClients::class,
                ImportPrometheeUsers::class,
                RotateArgosKeys::class,
            ]);
        }
    }
}
