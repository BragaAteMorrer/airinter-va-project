<?php

namespace Modules\Promethee\Models;

use Illuminate\Database\Eloquent\Model;

class PirepAircraftProfile extends Model
{
    protected $table = 'promethee_pirep_aircraft_profiles';
    protected $primaryKey = 'pirep_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = ['snapshot' => 'array'];
}
