<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements OAuthenticatable, MustVerifyEmailContract, PasskeyUser
{
    use HasApiTokens;
    use PasskeyAuthenticatable;
    use MustVerifyEmailTrait;
    use Notifiable;

    protected $fillable = [
        'subject',
        'display_name',
        'email',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'password_changed_at',
        'preferred_locale',
        'timezone',
        'country',
        'home_airport_id',
        'vatsim_id',
        'ivao_id',
        'avatar_path',
        'state',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'array',
            'two_factor_confirmed_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function identities(): HasMany
    {
        return $this->hasMany(LegacyIdentity::class);
    }

    public function oauthTokenFamilies(): HasMany
    {
        return $this->hasMany(OauthTokenFamily::class);
    }

    public function trustedDevices(): HasMany
    {
        return $this->hasMany(TrustedDevice::class);
    }

    public function securityEvents(): HasMany
    {
        return $this->hasMany(SecurityEvent::class);
    }

    public function getPasskeyDisplayName(): string
    {
        return $this->display_name;
    }

    public function getPasskeyUsername(): string
    {
        return $this->email;
    }

    public function avatarUrl(): ?string
    {
        if (blank($this->avatar_path)) {
            return null;
        }

        $url = Storage::disk('public')->url($this->avatar_path);

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://')
            ? $url
            : url($url);
    }

    public function canUseSso(): bool
    {
        return in_array($this->state, ['active', 'on_leave'], true);
    }
}
