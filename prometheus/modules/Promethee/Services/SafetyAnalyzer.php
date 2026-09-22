<?php
namespace Modules\Promethee\Services;

/**
 * Virtual-airline indicators, not an aircraft maintenance determination.
 * Null means unavailable. Never infer compliance from absent events.
 * Units: feet AGL/MSL, knots IAS, ft/min vertical speed, degrees bank.
 */
class SafetyAnalyzer
{
    public const VERSION = 1;
    public function analyze(?float $landingRate, array $samples, float $hardThreshold = 600): array
    {
        usort($samples, fn ($a,$b) => strcmp($a['recorded_at'], $b['recorded_at']));
        $approaches = [];
        $previous = null;
        $overspeed = null;
        $speedEvents = 0;
        $above = false;
        foreach ($samples as $s) {
            if (isset($s['ias'], $s['max_ias']) && $s['max_ias'] > 0) {
                $exceeded = $s['ias'] > $s['max_ias'];
                $overspeed = ($overspeed ?? false) || $exceeded;
                if ($exceeded && !$above) $speedEvents++;
                $above = $exceeded;
            } else {
                $above = false;
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
                    $s['vref'] > 0 && $s['ias'] >= $s['vref'] - 5 && $s['ias'] <= $s['vref'] + 20
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
            'overspeed' => $overspeed, 'speed_events' => $speedEvents,
            'approaches' => $approaches,
        ];
    }

    public function debrief(?float $landingRate, array $samples): array
    {
        $analysis = $this->analyze($landingRate, $samples);
        $facts = [];

        if ($analysis['hard_landing'] === true) $facts[] = ['level' => 'warning', 'code' => 'HARD_LANDING', 'label' => 'Touchdown à surveiller', 'value' => $landingRate];
        elseif ($analysis['hard_landing'] === false) $facts[] = ['level' => 'ok', 'code' => 'LANDING_RATE', 'label' => 'Touchdown dans le seuil observé', 'value' => $landingRate];

        if ($analysis['overspeed'] === true) $facts[] = ['level' => 'warning', 'code' => 'OVERSPEED', 'label' => $analysis['speed_events'].' dépassement(s) de vitesse observé(s)'];
        elseif ($analysis['overspeed'] === false) $facts[] = ['level' => 'ok', 'code' => 'SPEED', 'label' => 'Aucun dépassement de vitesse observé'];

        $knownApproaches = array_values(array_filter($analysis['approaches'], fn ($value) => $value !== null));
        $approachConform = $knownApproaches ? !in_array(false, $knownApproaches, true) : null;
        if ($approachConform === true) $facts[] = ['level' => 'ok', 'code' => 'STABLE_APPROACH', 'label' => 'Approche stabilisée'];
        elseif ($approachConform === false) $facts[] = ['level' => 'warning', 'code' => 'UNSTABLE_APPROACH', 'label' => 'Approche à surveiller'];

        $safetyWarning = $analysis['hard_landing'] === true || $analysis['overspeed'] === true;
        $safetyKnown = $analysis['hard_landing'] !== null || $analysis['overspeed'] !== null;

        return [
            'safety' => ['label' => $safetyKnown ? ($safetyWarning ? 'À surveiller' : 'Conforme') : 'Données insuffisantes'],
            'operations' => ['label' => $approachConform === null ? 'Données insuffisantes' : ($approachConform ? 'Conforme' : 'À surveiller')],
            'flight' => ['label' => $analysis['hard_landing'] === null ? 'Données insuffisantes' : ($analysis['hard_landing'] ? 'À surveiller' : 'Conforme')],
            'facts' => $facts,
            'timeline' => $this->timeline($samples),
            'analysis_version' => self::VERSION,
        ];
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
        $report = ['month'=>$month,'version'=>self::VERSION,'flights'=>0,
            'hard_landing'=>['evaluated'=>0,'exceeded'=>0,'unknown'=>0],
            'overspeed'=>['evaluated'=>0,'exceeded'=>0,'unknown'=>0],
            'approach'=>['stable'=>0,'unstable'=>0,'unknown'=>0,'flights_without_checkpoint'=>0],
            'speed_events'=>0, 'thresholds'=>['hard_landing_fpm'=>600,'checkpoint_agl_ft'=>1000]];
        foreach ($flights as $f) {
            $report['flights']++;
            $r = $this->analyze($f['landing_rate'], $f['samples']);
            foreach (['hard_landing','overspeed'] as $key) {
                if ($r[$key] === null) $report[$key]['unknown']++;
                else { $report[$key]['evaluated']++; if ($r[$key]) $report[$key]['exceeded']++; }
            }
            $report['speed_events'] += $r['speed_events'];
            if (!$r['approaches']) $report['approach']['flights_without_checkpoint']++;
            foreach ($r['approaches'] as $stable) {
                $report['approach'][$stable === null ? 'unknown' : ($stable ? 'stable' : 'unstable')]++;
            }
        }
        return $report;
    }
}
