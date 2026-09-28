<?php

namespace App\Models;

use App\Notifications\SecurityAlertNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityEvent extends Model
{
    protected static function booted(): void
    {
        static::created(function (SecurityEvent $event): void {
            if (!in_array($event->type, [
                'password.changed',
                'mfa.enabled',
                'mfa.disabled',
                'passkey.registered',
                'passkey.deleted',
                'oauth.refresh.reuse_detected',
                'login.new_context',
            ], true)) {
                return;
            }

            $event->loadMissing('user');
            $event->user?->notify(new SecurityAlertNotification($event));
        });
    }

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
