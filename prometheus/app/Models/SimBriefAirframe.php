<?php

namespace App\Models;

use App\Contracts\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimBriefAirframe extends Model
{
    public $table = 'simbrief_airframes';

    public const SIMBRIEF_STRATEGIES = ['native', 'custom_airframe', 'proxy'];

    protected $fillable = [
        'id',
        'icao',
        'name',
        'airframe_id',
        'source',
        'details',
        'options',
    ];

    protected $casts = [
        'icao' => 'string',
        'name' => 'string',
    ];

    public static array $rules = [
        'icao' => 'required|string|max:16',
        'name' => 'required|string|max:100',
        'airframe_id' => 'nullable|string|max:64|required_if:simbrief_strategy,custom_airframe',
        'source' => 'nullable',
        'details' => 'nullable',
        'options' => 'nullable',
        'simbrief_strategy' => 'nullable|in:native,custom_airframe,proxy',
        'simbrief_proxy_type' => 'nullable|string|max:16|regex:/^[A-Za-z0-9_-]+$/|required_if:simbrief_strategy,proxy',
        'simbrief_name' => 'nullable|string|max:12',
        'simbrief_engines' => 'nullable|string|max:12|required_if:simbrief_strategy,proxy',
        'simbrief_cat' => 'nullable|in:L,M,H,J',
        'simbrief_equip' => 'nullable|string|max:64',
        'simbrief_transponder' => 'nullable|string|max:32',
        'simbrief_pbn' => 'nullable|string|max:128',
        'simbrief_extrarmk' => 'nullable|string|max:255',
        'simbrief_maxpax' => 'nullable|integer|min:0|max:999',
        'simbrief_oew_kg' => 'nullable|numeric|min:0',
        'simbrief_mzfw_kg' => 'nullable|numeric|min:0',
        'simbrief_mtow_kg' => 'nullable|numeric|min:0',
        'simbrief_mlw_kg' => 'nullable|numeric|min:0',
        'simbrief_maxfuel_kg' => 'nullable|numeric|min:0',
        'simbrief_maxcargo_kg' => 'nullable|numeric|min:0',
        'simbrief_hexcode' => 'nullable|string|max:16',
        'simbrief_per' => 'nullable|in:A,B,C,D,E',
        'simbrief_paxwgt' => 'nullable|numeric|min:0',
        'simbrief_bagwgt' => 'nullable|numeric|min:0',
        'simbrief_ceiling' => 'nullable|integer|min:0|max:70000',
        'simbrief_cruiseoffset' => 'nullable|string|max:16',
        'simbrief_fuelfactor' => 'nullable|string|max:16',
        'simbrief_climb' => 'nullable|string|max:32',
        'simbrief_cruise' => 'nullable|string|max:32',
        'simbrief_descent' => 'nullable|string|max:32',
    ];

    public function decodedOptions(): array
    {
        if (is_array($this->options)) {
            return $this->options;
        }

        $decoded = json_decode((string) $this->options, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Air Inter planning profile carried by sb-airframe.
     *
     * Legacy rows remain compatible: an airframe_id means custom_airframe;
     * otherwise the airframe keeps the historical native SimBrief behavior.
     */
    public function simbriefProfile(): array
    {
        $options = $this->decodedOptions();
        $profile = is_array($options['simbrief'] ?? null) ? $options['simbrief'] : [];
        $strategy = strtolower(trim((string) ($profile['strategy'] ?? '')));

        if (!in_array($strategy, self::SIMBRIEF_STRATEGIES, true)) {
            $strategy = filled($this->airframe_id) ? 'custom_airframe' : 'native';
        }

        return array_merge([
            'strategy' => $strategy,
            'proxy_type' => null,
            'name' => null,
            'engines' => null,
            'cat' => null,
            'equip' => null,
            'transponder' => null,
            'pbn' => null,
            'extrarmk' => null,
            'maxpax' => null,
            'hexcode' => null,
            'per' => null,
            'paxwgt' => null,
            'bagwgt' => null,
            'ceiling' => null,
            'cruiseoffset' => null,
            'weights_kg' => [],
            'performance' => [],
        ], $profile, [
            'strategy' => $strategy,
            'internal_id' => filled($this->airframe_id) ? (string) $this->airframe_id : null,
            'icao' => strtoupper((string) $this->icao),
            'name' => (string) $this->name,
        ]);
    }

    // Relationships
    public function sbaircraft(): BelongsTo
    {
        return $this->belongsTo(SimBriefAircraft::class, 'icao', 'icao');
    }
}
