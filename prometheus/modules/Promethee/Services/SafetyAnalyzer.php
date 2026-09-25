<?php
namespace Modules\Promethee\Services;

/**
 * Virtual-airline indicators, not an aircraft maintenance determination.
 * Null means unavailable. Never infer compliance from absent events.
 * Units: feet AGL/MSL, knots IAS, ft/min vertical speed, degrees bank.
 */
class SafetyAnalyzer
{
    public const VERSION = 2;
    public function analyze(?float $landingRate, array $samples, float $hardThreshold = 600): array
    {
        usort($samples, fn ($a,$b) => strcmp((string) ($a['recorded_at'] ?? ''), (string) ($b['recorded_at'] ?? '')));
        $approaches = [];
        $previous = null;
        $overspeed = null;
        $speedEvents = 0;
        $above = false;

        $known250 = $exceeded250 = false;
        $knownTaxi = $exceededTaxi = false;
        $knownBank = $exceededBank = false;
        $knownPitch = $exceededPitch = false;

        foreach ($samples as $s) {
            if (isset($s['ias'], $s['max_ias']) && $s['max_ias'] > 0) {
                $exceeded = $s['ias'] > $s['max_ias'];
                $overspeed = ($overspeed ?? false) || $exceeded;
                if ($exceeded && !$above) $speedEvents++;
                $above = $exceeded;
            } else {
                $above = false;
            }

            $airborne = ($s['on_ground'] ?? null) === false;
            if ($airborne && isset($s['ias'], $s['altitude_msl'])) {
                $known250 = true;
                if ((float) $s['altitude_msl'] < 10000 && (float) $s['ias'] > 250) $exceeded250 = true;
            }
            if (($s['on_ground'] ?? null) === true && isset($s['gs'])) {
                $knownTaxi = true;
                if ((float) $s['gs'] > 30) $exceededTaxi = true;
            }
            if ($airborne && isset($s['bank'])) {
                $knownBank = true;
                if (abs((float) $s['bank']) > 35) $exceededBank = true;
            }
            if ($airborne && isset($s['pitch'])) {
                $knownPitch = true;
                if (abs((float) $s['pitch']) > 20) $exceededPitch = true;
            }

            // A checkpoint needs two nearby samples bracketing 1000 ft on descent.
            if ($previous && isset($previous['agl'], $s['agl'])
                && $previous['agl'] > 1000 && $s['agl'] <= 1000
                && $previous['agl'] <= 1150 && $s['agl'] >= 850
                && strtotime($s['recorded_at']) - strtotime($previous['recorded_at']) <= 10
                && strtotime($s['recorded_at']) > strtotime($previous['recorded_at'])
                && ($s['on_ground'] ?? true) === false) {
                $required = ['ias','vref','vs','bank','gear_down','landing_flaps','localizer_dots','glideslope_dots','thrust_stable','checklist_complete'];
                $known = true;
                foreach ($required as $key) if (!isset($s[$key])) $known = false;
                $stable = $known ? (
                    $s['vref'] > 0 && $s['ias'] >= $s['vref'] - 5 && $s['ias'] <= $s['vref'] + 10
                    && $s['vs'] >= -1000 && $s['vs'] <= 0 && abs($s['bank']) <= 15
                    && $s['gear_down'] && $s['landing_flaps'] && $s['thrust_stable'] && $s['checklist_complete']
                    && abs($s['localizer_dots']) <= 1 && abs($s['glideslope_dots']) <= 1
                ) : null;
                $approaches[] = $stable;
            }
            $previous = $s;
        }

        return [
            'hard_landing' => $landingRate === null ? null : abs($landingRate) >= $hardThreshold,
            'overspeed' => $overspeed,
            'speed_events' => $speedEvents,
            'speed_250_below_10000' => $known250 ? $exceeded250 : null,
            'taxi_overspeed' => $knownTaxi ? $exceededTaxi : null,
            'bank_exceedance' => $knownBank ? $exceededBank : null,
            'pitch_exceedance' => $knownPitch ? $exceededPitch : null,
            'approaches' => $approaches,
        ];
    }

    public function debrief(?float $landingRate, array $samples): array
    {
        usort($samples, fn ($a, $b) => strcmp((string) ($a['recorded_at'] ?? ''), (string) ($b['recorded_at'] ?? '')));
        $analysis = $this->analyze($landingRate, $samples);
        $timeline = $this->timeline($samples);
        $first = $samples[0] ?? null;
        $last = $samples ? $samples[array_key_last($samples)] : null;

        $knownApproaches = array_values(array_filter($analysis['approaches'], fn ($value) => $value !== null));
        $approachConform = $knownApproaches ? !in_array(false, $knownApproaches, true) : null;

        $safetyEvents = [];
        if ($analysis['hard_landing'] === true) {
            $safetyEvents[] = $this->event('HARD_LANDING', 'warning', 'Touchdown supérieur au seuil observé.', null, ['landing_rate_fpm' => $landingRate]);
        } elseif ($analysis['hard_landing'] === false) {
            $safetyEvents[] = $this->event('LANDING_RATE', 'info', 'Taux de toucher observé.', null, ['landing_rate_fpm' => $landingRate]);
        }
        if ($analysis['overspeed'] === true) {
            $safetyEvents[] = $this->event('OVERSPEED', 'warning', 'Dépassement de la limite VNE/VMO/MMO transmise.', null, ['events' => $analysis['speed_events']]);
        }
        if ($analysis['speed_250_below_10000'] === true) {
            $safetyEvents[] = $this->event('SPEED_250_BELOW_10000', 'warning', 'IAS supérieure à 250 kt sous 10 000 ft MSL.');
        }
        if ($analysis['taxi_overspeed'] === true) {
            $safetyEvents[] = $this->event('TAXI_OVERSPEED', 'warning', 'Vitesse sol supérieure à 30 kt au sol.');
        }
        if ($analysis['bank_exceedance'] === true) {
            $safetyEvents[] = $this->event('BANK_EXCEEDANCE', 'warning', 'Inclinaison supérieure à 35° en vol.');
        }
        if ($analysis['pitch_exceedance'] === true) {
            $safetyEvents[] = $this->event('PITCH_EXCEEDANCE', 'warning', 'Assiette absolue supérieure à 20° en vol.');
        }

        $operationsEvents = [];
        if ($approachConform === false) {
            $operationsEvents[] = $this->event('UNSTABLE_APPROACH', 'warning', 'Un checkpoint d’approche connu ne satisfait pas les critères observés.');
        } elseif ($approachConform === true) {
            $operationsEvents[] = $this->event('STABLE_APPROACH', 'info', 'Les checkpoints d’approche évaluables satisfont les critères observés.');
        }

        $flightEvents = array_map(fn ($event) => [
            'code' => strtoupper(str_replace([' ', 'é', 'è', '→'], ['_', 'E', 'E', '_'], $event['type'])),
            'level' => 'info',
            'label' => $event['detail'],
            'at' => $event['at'],
            'values' => [],
        ], $timeline);

        return [
            'contract_version' => '1.0',
            'analysis_version' => self::VERSION,
            'data_quality' => [
                'samples' => count($samples),
                'first_at' => $first['recorded_at'] ?? null,
                'last_at' => $last['recorded_at'] ?? null,
                'landing_rate_available' => $landingRate !== null,
                'approach_checkpoints_evaluable' => count($knownApproaches),
            ],
            'safety' => [
                'status' => $this->sectionStatus($safetyEvents, collect(['hard_landing','overspeed','speed_250_below_10000','taxi_overspeed','bank_exceedance','pitch_exceedance'])->contains(fn ($key) => $analysis[$key] !== null)),
                'events' => $safetyEvents,
            ],
            'operations' => [
                'status' => $this->sectionStatus($operationsEvents, $approachConform !== null),
                'events' => $operationsEvents,
            ],
            'flight' => [
                'status' => $this->sectionStatus($flightEvents, count($samples) > 0),
                'events' => $flightEvents,
                'timeline' => $timeline,
            ],
            // Compatibility for existing Prométhée consumers. New clients should
            // consume the three sections above instead of interpreting a score.
            'facts' => array_values(array_merge($safetyEvents, $operationsEvents)),
            'timeline' => $timeline,
        ];
    }

    private function event(string $code, string $level, string $label, ?string $at = null, array $values = []): array
    {
        return compact('code', 'level', 'label', 'at', 'values');
    }

    private function sectionStatus(array $events, bool $known): string
    {
        if (!$known) return 'INSUFFICIENT_DATA';
        return collect($events)->contains(fn ($event) => ($event['level'] ?? null) === 'warning')
            ? 'ATTENTION'
            : 'OBSERVED';
    }

    /** A pedagogical score: unknown data is never turned into a penalty. */

    public function score(?float $landingRate, array $samples): array
    {
        $analysis = $this->analyze($landingRate, $samples);
        $score = 100;
        $items = [];
        if ($analysis['hard_landing'] !== null) {
            $penalty = $analysis['hard_landing'] ? 22 : 0;
            $score -= $penalty;
            $items[] = ['label' => 'Atterrissage', 'score' => 22 - $penalty, 'max' => 22, 'detail' => $analysis['hard_landing'] ? 'Taux d’atterrissage supérieur au seuil de 600 ft/min.' : 'Taux d’atterrissage dans le seuil.'];
        }
        if ($analysis['overspeed'] !== null) {
            $penalty = min(24, $analysis['speed_events'] * 8);
            $score -= $penalty;
            $items[] = ['label' => 'Vitesse', 'score' => 24 - $penalty, 'max' => 24, 'detail' => $penalty ? $analysis['speed_events'].' dépassement(s) de la limite transmise.' : 'Aucun dépassement détecté.'];
        }
        $knownApproaches = array_values(array_filter($analysis['approaches'], fn ($value) => $value !== null));
        if ($knownApproaches) {
            $stable = count(array_filter($knownApproaches));
            $ratio = $stable / count($knownApproaches);
            $points = (int) round(30 * $ratio);
            $score -= 30 - $points;
            $items[] = ['label' => 'Approche stabilisée', 'score' => $points, 'max' => 30, 'detail' => $stable.' checkpoint(s) stable(s) sur '.count($knownApproaches).'.'];
        }
        $events = $this->timeline($samples);
        return ['score' => max(0, $score), 'items' => $items, 'events' => $events, 'analysis' => $analysis];
    }

    /** Extracts flight phases from ACARS facts; no phase is inferred without telemetry. */
    public function timeline(array $samples): array
    {
        usort($samples, fn ($a, $b) => strcmp((string) ($a['recorded_at'] ?? ''), (string) ($b['recorded_at'] ?? '')));
        $events = [];
        $wasGround = null;
        $peakAltitude = 0;
        $descentMarked = false;
        foreach ($samples as $sample) {
            if (!isset($sample['recorded_at'])) continue;
            $ground = $sample['on_ground'] ?? null;
            $altitude = (float) ($sample['altitude_msl'] ?? 0);
            $verticalSpeed = (float) ($sample['vs'] ?? 0);
            if ($wasGround === true && $ground === false) $events[] = ['at' => $sample['recorded_at'], 'type' => 'Décollage', 'detail' => 'Transition sol → vol détectée.'];
            if ($wasGround === false && $ground === true) $events[] = ['at' => $sample['recorded_at'], 'type' => 'Atterrissage', 'detail' => 'Transition vol → sol détectée.'];
            if ($altitude > $peakAltitude) $peakAltitude = $altitude;
            if (!$descentMarked && $peakAltitude >= 5000 && $verticalSpeed < -400 && $altitude < $peakAltitude - 800) {
                $events[] = ['at' => $sample['recorded_at'], 'type' => 'Début de descente', 'detail' => 'Descente soutenue détectée après le point haut.'];
                $descentMarked = true;
            }
            if ($wasGround === null && $ground === true) $events[] = ['at' => $sample['recorded_at'], 'type' => 'Roulage', 'detail' => 'Premier échantillon au sol.'];
            $wasGround = $ground;
        }
        return $events;
    }
    public function aggregate(iterable $flights, string $month): array
    {
        $indicatorKeys = ['hard_landing','overspeed','speed_250_below_10000','taxi_overspeed','bank_exceedance','pitch_exceedance'];
        $report = [
            'month'=>$month,
            'version'=>self::VERSION,
            'flights'=>0,
            'approach'=>['stable'=>0,'unstable'=>0,'unknown'=>0,'flights_without_checkpoint'=>0],
            'speed_events'=>0,
            'thresholds'=>[
                'hard_landing_fpm'=>600,
                'checkpoint_agl_ft'=>1000,
                'vref_minus_kt'=>5,
                'vref_plus_kt'=>10,
                'below_10000_max_ias_kt'=>250,
                'taxi_max_gs_kt'=>30,
                'max_bank_deg'=>35,
                'max_pitch_deg'=>20,
            ],
        ];
        foreach ($indicatorKeys as $key) $report[$key] = ['evaluated'=>0,'exceeded'=>0,'unknown'=>0];

        foreach ($flights as $f) {
            $report['flights']++;
            $result = $this->analyze($f['landing_rate'], $f['samples']);
            foreach ($indicatorKeys as $key) {
                if ($result[$key] === null) $report[$key]['unknown']++;
                else {
                    $report[$key]['evaluated']++;
                    if ($result[$key]) $report[$key]['exceeded']++;
                }
            }
            $report['speed_events'] += $result['speed_events'];
            if (!$result['approaches']) $report['approach']['flights_without_checkpoint']++;
            foreach ($result['approaches'] as $stable) {
                $report['approach'][$stable === null ? 'unknown' : ($stable ? 'stable' : 'unstable')]++;
            }
        }
        return $report;
    }

}
