<?php

namespace Modules\Promethee\Models;

use App\Models\SimBriefAirframe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirframeSimulatorProfile extends Model
{
    protected $table = 'promethee_airframe_simulator_profiles';
    protected $guarded = [];
    protected $casts = ['active' => 'boolean'];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(AircraftHistoricalVariant::class, 'variant_id');
    }

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(AirframeConfiguration::class, 'configuration_id');
    }

    public function simbriefAirframe(): BelongsTo
    {
        return $this->belongsTo(SimBriefAirframe::class, 'simbrief_airframe_id');
    }
}
