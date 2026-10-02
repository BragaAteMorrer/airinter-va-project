<?php

namespace Modules\Promethee\Models;

use Illuminate\Database\Eloquent\Model;

class AircraftModification extends Model
{
    protected $table = 'promethee_aircraft_modifications';
    protected $guarded = [];
    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];
}
