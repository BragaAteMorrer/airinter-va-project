<?php

namespace Modules\Promethee\Models;

use App\Models\Aircraft;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AircraftConfigurationAssignment extends Model
{
    protected $table = 'promethee_aircraft_configuration_assignments';
    protected $guarded = [];
    protected $casts = [
        'overrides' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'active' => 'boolean',
    ];

    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(Aircraft::class, 'aircraft_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(AircraftHistoricalVariant::class, 'variant_id');
    }

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(AirframeConfiguration::class, 'configuration_id');
    }
}
