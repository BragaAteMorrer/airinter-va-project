<?php

namespace Modules\Promethee\Services;

use App\Models\Pirep;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PirepJournalService
{
    /**
     * Build one chronological operations log from phpVMS ACARS logs, Hermès
     * telemetry and company scoring facts.
     *
     * Telemetry fields are intentionally treated as nullable. Old Hermès
     * versions did not archive every aircraft-system signal, so absence means
     * "unknown", never "off".
     */
    public function build(Pirep $pirep, array $companyScore = []): Collection
    {
        $entries = collect();
        $explicitEvents = [];

        $append = function ($occurredAt, string $code, string $message, string $source, ?string $detail = null)
            use (&$entries): ?CarbonImmutable {
            $date = $this->asDate($occurredAt);
            if (!$date) return null;

            $normalizedCode = strtoupper(trim($code));
            $entries->push([
                'occurred_at' => $date,
                'code' => $normalizedCode !== '' ? $normalizedCode : 'ACARS',
                'message' => $message,
                'source' => $source,
                'detail' => $detail,
            ]);

            return $date;
        };

        $hasNearbyExplicitEvent = function (string $code, $occurredAt) use (&$explicitEvents): bool {
            $date = $this->asDate($occurredAt);
            $code = strtoupper(trim($code));
            if (!$date || !isset($explicitEvents[$code])) return false;

            foreach ($explicitEvents[$code] as $explicitAt) {
                if (abs($explicitAt->diffInSeconds($date, false)) <= 90) return true;
            }

            return false;
        };

        foreach ($pirep->acars_logs->sortBy(fn ($log) => $log->sim_time ?: $log->created_at) as $log) {
            $raw = trim((string) $log->log);
            if ($raw === '') continue;

            $code = preg_match('/^[A-Z0-9_\-]+$/i', $raw)
                ? strtoupper($raw)
                : 'ACARS_LOG_'.strtoupper(substr(sha1($raw), 0, 8));

            $loggedAt = $append(
                $log->sim_time ?: $log->created_at,
                $code,
                $this->label($raw),
                'ACARS'
            );

            if ($loggedAt) $explicitEvents[$code][] = $loggedAt;
        }

        if (Schema::hasTable('promethee_telemetry')) {
            $rows = DB::table('promethee_telemetry')
                ->where('pirep_id', $pirep->id)
                ->orderBy('recorded_at')
                ->get(['recorded_at', 'payload']);

            $previous = null;
            $previousPhase = null;
            $phaseMoments = [];
            $landingRateAdded = false;
            $touchdownAt = null;
            $aircraftIdentified = false;
            $weatherCaptured = false;
            $arrivalWeatherCaptured = false;
            $initialSystemsCaptured = false;

            $booleanEvents = [
                'parking_brake' => [
                    true => ['PARKING_BRAKE_SET', 'Frein de parc serré'],
                    false => ['PARKING_BRAKE_RELEASED', 'Frein de parc desserré'],
                ],
                'gear_down' => [
                    true => ['GEAR_DOWN', 'Train d’atterrissage sorti'],
                    false => ['GEAR_UP', 'Train d’atterrissage rentré'],
                ],
                'spoilers_armed' => [
                    true => ['SPOILERS_ARMED', 'Spoilers armés'],
                    false => ['SPOILERS_DISARMED', 'Spoilers désarmés'],
                ],
                'beacon_light' => [
                    true => ['BEACON_ON', 'Beacon allumé'],
                    false => ['BEACON_OFF', 'Beacon éteint'],
                ],
                'navigation_light' => [
                    true => ['NAV_LIGHTS_ON', 'Feux de navigation allumés'],
                    false => ['NAV_LIGHTS_OFF', 'Feux de navigation éteints'],
                ],
                'strobe_light' => [
                    true => ['STROBE_LIGHTS_ON', 'Strobes allumés'],
                    false => ['STROBE_LIGHTS_OFF', 'Strobes éteints'],
                ],
                'landing_light' => [
                    true => ['LANDING_LIGHTS_ON', 'Feux d’atterrissage allumés'],
                    false => ['LANDING_LIGHTS_OFF', 'Feux d’atterrissage éteints'],
                ],
                'taxi_light' => [
                    true => ['TAXI_LIGHTS_ON', 'Feux de roulage allumés'],
                    false => ['TAXI_LIGHTS_OFF', 'Feux de roulage éteints'],
                ],
                'logo_light' => [
                    true => ['LOGO_LIGHT_ON', 'Éclairage logo allumé'],
                    false => ['LOGO_LIGHT_OFF', 'Éclairage logo éteint'],
                ],
                'wing_light' => [
                    true => ['WING_LIGHTS_ON', 'Feux d’aile allumés'],
                    false => ['WING_LIGHTS_OFF', 'Feux d’aile éteints'],
                ],
                'apu_running' => [
                    true => ['APU_ON', 'APU en fonctionnement'],
                    false => ['APU_OFF', 'APU arrêté'],
                ],
                'battery_on' => [
                    true => ['BATTERY_ON', 'Batterie avion connectée'],
                    false => ['BATTERY_OFF', 'Batterie avion coupée'],
                ],
                'external_power_on' => [
                    true => ['EXTERNAL_POWER_ON', 'Alimentation externe connectée'],
                    false => ['EXTERNAL_POWER_OFF', 'Alimentation externe déconnectée'],
                ],
                'seatbelt_sign' => [
                    true => ['SEATBELTS_ON', 'Consigne ceintures allumée'],
                    false => ['SEATBELTS_OFF', 'Consigne ceintures éteinte'],
                ],
                'doors_open' => [
                    true => ['DOORS_OPEN', 'Portes ouvertes'],
                    false => ['DOORS_CLOSED', 'Portes fermées'],
                ],
                'autopilot_enabled' => [
                    true => ['AUTOPILOT_ON', 'Pilote automatique activé'],
                    false => ['AUTOPILOT_OFF', 'Pilote automatique désactivé'],
                ],
                'autothrottle_armed' => [
                    true => ['AUTOTHROTTLE_ARMED', 'Automanette armée'],
                    false => ['AUTOTHROTTLE_DISARMED', 'Automanette désarmée'],
                ],
                'slew_active' => [
                    true => ['SLEW_STARTED', 'Mode Slew activé'],
                    false => ['SLEW_ENDED', 'Mode Slew désactivé'],
                ],
                'overspeed_warning' => [
                    true => ['OVERSPEED_WARNING', 'Alerte survitesse active'],
                    false => ['OVERSPEED_CLEAR', 'Alerte survitesse terminée'],
                ],
                'stall_warning' => [
                    true => ['STALL_WARNING', 'Alerte décrochage active'],
                    false => ['STALL_CLEAR', 'Alerte décrochage terminée'],
                ],
            ];

            $initialSystemFields = [
                'parking_brake', 'gear_down', 'spoilers_armed',
                'beacon_light', 'navigation_light', 'strobe_light', 'landing_light', 'taxi_light',
                'logo_light', 'wing_light', 'apu_running', 'battery_on', 'external_power_on',
                'seatbelt_sign', 'doors_open', 'autopilot_enabled', 'autothrottle_armed',
            ];

            $describeSnapshot = function (array $payload): ?string {
                $detail = [];
                $numeric = [
                    'altitude_msl' => ['ft MSL', 0],
                    'agl' => ['ft AGL', 0],
                    'ias' => ['kt IAS', 0],
                    'gs' => ['kt GS', 0],
                    'vs' => ['ft/min VS', 0],
                    'heading' => ['° HDG', 0],
                    'pitch' => ['° pitch', 1],
                    'bank' => ['° bank', 1],
                    'g_force' => ['G', 2],
                    'fuel' => ['fuel', 0],
                    'gross_weight' => ['gross', 0],
                    'flaps_percent' => ['% flaps', 0],
                    'touchdown_rate' => ['ft/min touchdown', 0],
                ];
                foreach ($numeric as $field => [$unit, $precision]) {
                    if (!isset($payload[$field]) || !is_numeric($payload[$field])) continue;
                    $detail[] = number_format((float) $payload[$field], $precision, ',', ' ').' '.$unit;
                }

                return $detail ? implode(' · ', $detail) : null;
            };

            foreach ($rows as $row) {
                $payload = json_decode((string) $row->payload, true);
                if (!is_array($payload)) continue;

                $date = $this->asDate($row->recorded_at);
                if (!$date) continue;

                $phase = strtoupper(trim((string) ($payload['phase'] ?? '')));
                if ($phase !== '') $phaseMoments[$phase] ??= $date;

                if ($phase !== '' && $phase !== $previousPhase) {
                    if (!$hasNearbyExplicitEvent($phase, $date)) {
                        $metrics = [];
                        if (isset($payload['altitude_msl']) && is_numeric($payload['altitude_msl'])) {
                            $metrics[] = number_format((float) $payload['altitude_msl'], 0, ',', ' ').' ft';
                        }
                        if (isset($payload['gs']) && is_numeric($payload['gs'])) {
                            $metrics[] = number_format((float) $payload['gs'], 0, ',', ' ').' kt GS';
                        }
                        if (isset($payload['ias']) && is_numeric($payload['ias'])) {
                            $metrics[] = number_format((float) $payload['ias'], 0, ',', ' ').' kt IAS';
                        }

                        $append(
                            $date,
                            $phase,
                            $this->label($phase),
                            'TÉLÉMÉTRIE HERMÈS',
                            $metrics ? implode(' · ', $metrics) : null
                        );

                        if (in_array($phase, ['TAKEOFF', 'LANDING'], true)) {
                            $snapshotDetail = $describeSnapshot($payload);
                            if ($snapshotDetail) {
                                $append(
                                    $date,
                                    $phase.'_DATA',
                                    $phase === 'TAKEOFF' ? 'Paramètres au décollage' : 'Paramètres à l’atterrissage',
                                    'TÉLÉMÉTRIE HERMÈS',
                                    $snapshotDetail
                                );
                            }
                        }
                    }

                    if (in_array($previousPhase, ['APPROACH', 'FINAL', 'LANDING'], true)
                        && in_array($phase, ['CLIMB', 'ENROUTE'], true)) {
                        $append($date, 'GO_AROUND', 'Remise de gaz détectée', 'TÉLÉMÉTRIE HERMÈS');
                    }

                    $previousPhase = $phase;
                }

                if (!$aircraftIdentified) {
                    $aircraft = array_values(array_filter([
                        trim((string) ($payload['aircraft_icao'] ?? '')),
                        trim((string) ($payload['aircraft_model'] ?? '')),
                        trim((string) ($payload['aircraft_title'] ?? '')),
                    ], fn ($value) => $value !== ''));

                    if ($aircraft) {
                        $append($date, 'AIRCRAFT_IDENTIFIED', 'Appareil détecté', 'TÉLÉMÉTRIE HERMÈS', implode(' · ', array_unique($aircraft)));
                        $aircraftIdentified = true;
                    }
                }

                $weather = [];
                if (isset($payload['qnh_hpa']) && is_numeric($payload['qnh_hpa'])) {
                    $weather[] = 'QNH '.number_format((float) $payload['qnh_hpa'], 0, ',', ' ').' hPa';
                }
                if (isset($payload['oat_c']) && is_numeric($payload['oat_c'])) {
                    $weather[] = 'OAT '.number_format((float) $payload['oat_c'], 0, ',', ' ').' °C';
                }
                if (isset($payload['wind_direction'], $payload['wind_speed'])
                    && is_numeric($payload['wind_direction']) && is_numeric($payload['wind_speed'])) {
                    $weather[] = 'Vent '.str_pad((string) ((int) round((float) $payload['wind_direction'])), 3, '0', STR_PAD_LEFT)
                        .'° / '.number_format((float) $payload['wind_speed'], 0, ',', ' ').' kt';
                }

                if (!$weatherCaptured && $weather) {
                    $append($date, 'WEATHER_INITIAL', 'Conditions météo départ (simulateur)', 'TÉLÉMÉTRIE HERMÈS', implode(' · ', $weather));
                    $weatherCaptured = true;
                }

                if (!$arrivalWeatherCaptured && $weather && in_array($phase, ['APPROACH', 'FINAL', 'LANDING', 'TAXI_IN', 'IN'], true)) {
                    $append($date, 'WEATHER_ARRIVAL', 'Conditions météo arrivée (simulateur)', 'TÉLÉMÉTRIE HERMÈS', implode(' · ', $weather));
                    $arrivalWeatherCaptured = true;
                }

                if (!$initialSystemsCaptured) {
                    foreach ($initialSystemFields as $field) {
                        if (!array_key_exists($field, $payload) || !is_bool($payload[$field])) continue;
                        [$code, $message] = $booleanEvents[$field][$payload[$field]];
                        $append($date, $code, $message, 'TÉLÉMÉTRIE HERMÈS', 'État initial');
                    }

                    if (isset($payload['engines_running']) && is_array($payload['engines_running'])) {
                        foreach ($payload['engines_running'] as $index => $running) {
                            if (!is_bool($running)) continue;
                            $engine = $index + 1;
                            $append(
                                $date,
                                'ENGINE_'.$engine.'_'.($running ? 'ON' : 'OFF'),
                                'Moteur '.$engine.($running ? ' en fonctionnement' : ' arrêté'),
                                'TÉLÉMÉTRIE HERMÈS',
                                'État initial'
                            );
                        }
                    }

                    if (isset($payload['flaps_percent']) && is_numeric($payload['flaps_percent'])) {
                        $flaps = max(0, min(100, (float) $payload['flaps_percent']));
                        $append(
                            $date,
                            $flaps <= 0.5 ? 'FLAPS_UP' : 'FLAPS_SET',
                            $flaps <= 0.5 ? 'Volets rentrés' : 'Volets réglés à '.number_format($flaps, 0, ',', ' ').' %',
                            'TÉLÉMÉTRIE HERMÈS',
                            'État initial'
                        );
                    }

                    if (isset($payload['transponder_code']) && is_numeric($payload['transponder_code'])) {
                        $append(
                            $date,
                            'TRANSPONDER_SET',
                            'Transpondeur réglé sur '.str_pad((string) ((int) $payload['transponder_code']), 4, '0', STR_PAD_LEFT),
                            'TÉLÉMÉTRIE HERMÈS',
                            'État initial'
                        );
                    }

                    if (isset($payload['simulation_rate']) && is_numeric($payload['simulation_rate'])) {
                        $append(
                            $date,
                            'SIM_RATE_INITIAL',
                            'Vitesse simulation x'.rtrim(rtrim(number_format((float) $payload['simulation_rate'], 2, '.', ''), '0'), '.'),
                            'TÉLÉMÉTRIE HERMÈS',
                            'État initial'
                        );
                    }

                    if (isset($payload['apu_rpm_percent']) && is_numeric($payload['apu_rpm_percent'])) {
                        $append(
                            $date,
                            'APU_RPM',
                            'APU '.number_format((float) $payload['apu_rpm_percent'], 0, ',', ' ').' %',
                            'TÉLÉMÉTRIE HERMÈS',
                            'État initial'
                        );
                    }

                    $initialSystemsCaptured = true;
                }

                if (is_array($previous)) {
                    foreach ($booleanEvents as $field => $states) {
                        if (!array_key_exists($field, $previous) || !array_key_exists($field, $payload)) continue;
                        if (!is_bool($previous[$field]) || !is_bool($payload[$field]) || $previous[$field] === $payload[$field]) continue;

                        [$code, $message] = $states[$payload[$field]];
                        $append($date, $code, $message, 'TÉLÉMÉTRIE HERMÈS');
                    }

                    if (isset($previous['engines_running'], $payload['engines_running'])
                        && is_array($previous['engines_running']) && is_array($payload['engines_running'])) {
                        $count = max(count($previous['engines_running']), count($payload['engines_running']));
                        for ($index = 0; $index < $count; $index++) {
                            $before = $previous['engines_running'][$index] ?? null;
                            $after = $payload['engines_running'][$index] ?? null;
                            if (!is_bool($before) || !is_bool($after) || $before === $after) continue;

                            $engine = $index + 1;
                            $append(
                                $date,
                                'ENGINE_'.$engine.'_'.($after ? 'ON' : 'OFF'),
                                'Moteur '.$engine.($after ? ' démarré' : ' arrêté'),
                                'TÉLÉMÉTRIE HERMÈS'
                            );
                        }
                    }

                    if (isset($previous['flaps_percent'], $payload['flaps_percent'])
                        && is_numeric($previous['flaps_percent']) && is_numeric($payload['flaps_percent'])
                        && abs((float) $payload['flaps_percent'] - (float) $previous['flaps_percent']) >= 1) {
                        $flaps = max(0, min(100, (float) $payload['flaps_percent']));
                        $append(
                            $date,
                            $flaps <= 0.5 ? 'FLAPS_UP' : 'FLAPS_SET',
                            $flaps <= 0.5 ? 'Volets rentrés' : 'Volets réglés à '.number_format($flaps, 0, ',', ' ').' %',
                            'TÉLÉMÉTRIE HERMÈS'
                        );
                    }

                    if (array_key_exists('transponder_code', $previous) && array_key_exists('transponder_code', $payload)
                        && is_numeric($payload['transponder_code'])
                        && (string) $previous['transponder_code'] !== (string) $payload['transponder_code']) {
                        $append(
                            $date,
                            'TRANSPONDER_CHANGED',
                            'Transpondeur réglé sur '.str_pad((string) ((int) $payload['transponder_code']), 4, '0', STR_PAD_LEFT),
                            'TÉLÉMÉTRIE HERMÈS'
                        );
                    }

                    if (isset($previous['simulation_rate'], $payload['simulation_rate'])
                        && is_numeric($previous['simulation_rate']) && is_numeric($payload['simulation_rate'])
                        && abs((float) $previous['simulation_rate'] - (float) $payload['simulation_rate']) >= 0.01) {
                        $append(
                            $date,
                            'SIM_RATE_CHANGED',
                            'Vitesse simulation x'.rtrim(rtrim(number_format((float) $payload['simulation_rate'], 2, '.', ''), '0'), '.'),
                            'TÉLÉMÉTRIE HERMÈS'
                        );
                    }

                    if (array_key_exists('paused', $previous) && array_key_exists('paused', $payload)
                        && is_bool($previous['paused']) && is_bool($payload['paused'])
                        && $previous['paused'] !== $payload['paused']) {
                        $append(
                            $date,
                            $payload['paused'] ? 'PAUSE_STARTED' : 'PAUSE_ENDED',
                            $payload['paused'] ? 'Pause simulateur' : 'Reprise du simulateur',
                            'TÉLÉMÉTRIE HERMÈS',
                            $payload['paused'] && !empty($payload['pause_kind']) ? (string) $payload['pause_kind'] : null
                        );
                    }

                    if (isset($previous['altitude_msl'], $payload['altitude_msl'])
                        && is_numeric($previous['altitude_msl']) && is_numeric($payload['altitude_msl'])) {
                        $beforeAltitude = (float) $previous['altitude_msl'];
                        $afterAltitude = (float) $payload['altitude_msl'];

                        if ($beforeAltitude < 10000 && $afterAltitude >= 10000) {
                            $append($date, 'CROSS_10000_UP', 'Passage au-dessus de 10 000 ft', 'TÉLÉMÉTRIE HERMÈS');
                        } elseif ($beforeAltitude > 10000 && $afterAltitude <= 10000) {
                            $append($date, 'CROSS_10000_DOWN', 'Passage sous 10 000 ft', 'TÉLÉMÉTRIE HERMÈS');
                        }
                    }

                    $beforeGround = $previous['on_ground'] ?? null;
                    $afterGround = $payload['on_ground'] ?? null;
                    if ($beforeGround === false && $afterGround === true) {
                        $touchdownAt = $date;
                        if (!$landingRateAdded) {
                            $rate = isset($payload['touchdown_rate']) && is_numeric($payload['touchdown_rate'])
                                ? (float) $payload['touchdown_rate']
                                : $pirep->landing_rate;
                            if ($rate !== null) {
                                $append(
                                    $date,
                                    'LANDING_RATE',
                                    'Taux d’atterrissage : '.number_format((float) $rate, 0, ',', ' ').' ft/min',
                                    'TÉLÉMÉTRIE HERMÈS'
                                );
                                $landingRateAdded = true;
                            }
                        }
                    } elseif ($beforeGround === true && $afterGround === false && $touchdownAt
                        && abs($touchdownAt->diffInSeconds($date, false)) <= 15
                        && (!isset($payload['agl']) || !is_numeric($payload['agl']) || (float) $payload['agl'] <= 60)) {
                        $append($date, 'BOUNCE', 'Rebond détecté après le toucher', 'TÉLÉMÉTRIE HERMÈS');
                    }
                }

                $previous = $payload;
            }

            $takeoffAt = $phaseMoments['TAKEOFF'] ?? $phaseMoments['CLIMB'] ?? null;
            $taxiOutAt = $phaseMoments['TAXI_OUT'] ?? $phaseMoments['PUSHBACK'] ?? $this->asDate($pirep->block_off_time);
            if ($takeoffAt && $taxiOutAt && $takeoffAt->greaterThanOrEqualTo($taxiOutAt)) {
                $append(
                    $takeoffAt,
                    'TAXI_OUT_TIME',
                    'Temps de roulage départ',
                    'TÉLÉMÉTRIE HERMÈS',
                    $this->duration($taxiOutAt->diffInSeconds($takeoffAt))
                );
            }

            $taxiInAt = $phaseMoments['TAXI_IN'] ?? $phaseMoments['LANDING'] ?? null;
            $inAt = $phaseMoments['IN'] ?? $this->asDate($pirep->block_on_time);
            if ($taxiInAt && $inAt && $inAt->greaterThanOrEqualTo($taxiInAt)) {
                $append(
                    $inAt,
                    'TAXI_IN_TIME',
                    'Temps de roulage arrivée',
                    'TÉLÉMÉTRIE HERMÈS',
                    $this->duration($taxiInAt->diffInSeconds($inAt))
                );
            }

            if (!$landingRateAdded && $pirep->landing_rate !== null) {
                $landingAt = $phaseMoments['LANDING'] ?? $phaseMoments['TAXI_IN'] ?? $this->asDate($pirep->block_on_time);
                if ($landingAt) {
                    $append(
                        $landingAt,
                        'LANDING_RATE',
                        'Taux d’atterrissage : '.number_format((float) $pirep->landing_rate, 0, ',', ' ').' ft/min',
                        'PIREP'
                    );
                }
            }
        }

        foreach (($companyScore['items'] ?? []) as $item) {
            $ruleName = trim((string) ($item['name'] ?? $item['rule_id'] ?? 'Règle compagnie'));
            $ruleId = preg_replace('/[^A-Z0-9_]+/', '_', strtoupper((string) ($item['rule_id'] ?? 'RULE')));
            $pointsEach = max(0, (int) ($item['points_each'] ?? 0));

            foreach (($item['events'] ?? []) as $event) {
                $at = $event['at'] ?? null;
                if (!$at) continue;

                $detailParts = [];
                if (array_key_exists('value', $event) && $event['value'] !== null && $event['value'] !== '') {
                    $detailParts[] = (string) $event['value'].(!empty($event['unit']) ? ' '.$event['unit'] : '');
                }
                if ($pointsEach > 0) $detailParts[] = '−'.$pointsEach.' pts';

                $append(
                    $at,
                    'RULE_'.$ruleId,
                    'Règle déclenchée — '.$ruleName,
                    'FDM · BARÈME',
                    $detailParts ? implode(' · ', $detailParts) : null
                );
            }
        }

        if (!$hasNearbyExplicitEvent('OUT', $pirep->block_off_time)) {
            $append($pirep->block_off_time, 'OUT', 'Départ du bloc (OUT)', 'PIREP');
        }
        if (!$hasNearbyExplicitEvent('IN', $pirep->block_on_time)
            && !$entries->contains(fn (array $entry) => $entry['code'] === 'IN')) {
            $append($pirep->block_on_time, 'IN', 'Arrivée au bloc (IN)', 'PIREP');
        }

        return $entries
            ->sortBy(fn (array $entry) => $entry['occurred_at']->getTimestamp())
            ->values();
    }

    private function label(string $event): string
    {
        $code = strtoupper(trim($event));
        $labels = [
            'BOARDING' => 'Préparation et embarquement',
            'OUT' => 'Départ du bloc (OUT)',
            'PUSHBACK' => 'Repoussage',
            'TAXI_OUT' => 'Roulage départ',
            'TAKEOFF' => 'Décollage',
            'OFF' => 'Décollage (OFF)',
            'CLIMB' => 'Montée',
            'CRUISE' => 'Croisière',
            'ENROUTE' => 'En route',
            'DESCENT' => 'Descente',
            'APPROACH' => 'Approche',
            'FINAL' => 'Finale',
            'LANDING' => 'Atterrissage',
            'ON' => 'Toucher des roues (ON)',
            'TAXI_IN' => 'Roulage arrivée',
            'IN' => 'Arrivée au bloc (IN)',
            'GO_AROUND' => 'Remise de gaz',
            'PAUSE_STARTED' => 'Pause simulateur',
            'PAUSE_ENDED' => 'Reprise du simulateur',
            'NETWORK_LOST' => 'Connexion Prométhée interrompue',
            'NETWORK_RECOVERED' => 'Connexion Prométhée rétablie',
        ];

        return $labels[$code] ?? trim($event);
    }

    private function asDate($value): ?CarbonImmutable
    {
        if (!$value) return null;

        try {
            return $value instanceof \DateTimeInterface
                ? CarbonImmutable::instance($value)
                : CarbonImmutable::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function duration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $minutes = intdiv($seconds, 60);
        $remaining = $seconds % 60;

        return $minutes.' min '.str_pad((string) $remaining, 2, '0', STR_PAD_LEFT).' s';
    }
}
