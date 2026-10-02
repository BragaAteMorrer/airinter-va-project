<?php

namespace Modules\Promethee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AirframeConfiguration extends Model
{
    protected $table = 'promethee_airframe_configurations';
    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'active' => 'boolean',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(AircraftHistoricalVariant::class, 'variant_id');
    }

    public function simulatorProfiles(): HasMany
    {
        return $this->hasMany(AirframeSimulatorProfile::class, 'configuration_id');
    }
}
