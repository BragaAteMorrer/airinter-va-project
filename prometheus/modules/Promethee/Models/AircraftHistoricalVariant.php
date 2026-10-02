<?php

namespace Modules\Promethee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AircraftHistoricalVariant extends Model
{
    protected $table = 'promethee_aircraft_historical_variants';
    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'active' => 'boolean',
    ];

    public function configurations(): HasMany
    {
        return $this->hasMany(AirframeConfiguration::class, 'variant_id');
    }
}
