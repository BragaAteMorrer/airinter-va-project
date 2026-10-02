<?php

namespace Modules\Promethee\Models;

use Illuminate\Database\Eloquent\Model;

class AircraftTypeProfile extends Model
{
    protected $table = 'promethee_aircraft_type_profiles';
    protected $guarded = [];
    protected $casts = ['data' => 'array'];
}
