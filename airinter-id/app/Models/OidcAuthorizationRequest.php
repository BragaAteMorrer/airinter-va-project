<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OidcAuthorizationRequest extends Model
{
    protected $fillable = [
        'client_id',
        'code_challenge',
        'nonce',
        'scope',
        'expires_at',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }
}
