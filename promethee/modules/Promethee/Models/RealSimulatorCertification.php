<?php

namespace Modules\Promethee\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealSimulatorCertification extends Model
{
    protected $table = 'promethee_real_simulator_certifications';

    protected $guarded = [];

    protected $casts = [
        'completed_on' => 'date',
        'valid_until' => 'date',
        'verified_at' => 'datetime',
    ];

    public function pilot(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isExpired(): bool
    {
        return $this->valid_until !== null && $this->valid_until->isPast();
    }
}
