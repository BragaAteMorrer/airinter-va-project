<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OauthRefreshTokenLineage extends Model
{
    protected $table = 'oauth_refresh_token_lineage';

    protected $fillable = [
        'family_id',
        'token_hash',
        'access_token_id',
        'status',
        'used_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(OauthTokenFamily::class, 'family_id');
    }
}
