<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegacyIdentity extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'external_user_id',
        'external_ident',
        'external_email',
        'metadata',
        'linked_at',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'linked_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
