<?php

namespace Modules\Promethee\Services;

use App\Models\Airport;
use Carbon\CarbonImmutable;

class FlightOpsService
{
    public function liveFlight(array $flight, array $sample): array
    {
        $lat = isset($sample['lat']) ? (float) $sample['lat'] : null;
        $lon = isset($sample['lon']) ? (float) $sample['lon'] : null;
        $destinationLat = isset($flight['arrival_lat']) ? (float) $flight['arrival_lat'] : null;
        $destinationLon = isset($flight['arrival_lon']) ? (float) $flight['arrival_lon'] : null;
        if ($destinationLat === null || $destinationLon === null) {
            $destination = Airport::find($flight['arrival']);
            $destinationLat = $destination?->lat !== null ? (float) $destination->lat : null;
            $destinationLon = $destination?->lon !== null ? (float) $destination->lon : null;
        }
        $remaining = ($destinationLat !== null && $destinationLon !== null && $lat !== null && $lon !== null)
            ? $this->distanceNm($lat, $lon, $destinationLat, $destinationLon) : null;
        $groundSpeed = max(0, (float) ($sample['gs'] ?? 0));
        $eta = $remaining !== null && $groundSpeed >= 60
            ? now()->addMinutes((int) round($remaining / $groundSpeed * 60)) : null;
        $recordedAt = !empty($flight['recorded_at']) ? CarbonImmutable::parse($flight['recorded_at']) : null;
        $age = $recordedAt ? $recordedAt->diffInSeconds(now()) : null;
        $alerts = [];
        if ($age === null) $alerts[] = ['level' => 'warning', 'label' => 'Position indisponible'];
        elseif ($age > 180) $alerts[] = ['level' => 'danger', 'label' => 'Télémétrie absente depuis '.ceil($age / 60).' min'];
        elseif ($age > 60) $alerts[] = ['level' => 'warning', 'label' => 'Signal ancien ('.ceil($age / 60).' min)'];
        if (($sample['fuel'] ?? null) !== null && (float) $sample['fuel'] <= 0) $alerts[] = ['level' => 'danger', 'label' => 'Carburant invalide'];
        return $flight + [
            'remaining_nm' => $remaining === null ? null : round($remaining),
            'eta' => $eta?->toIso8601String(),
            'signal_age' => $age,
            'alerts' => $alerts,
        ];
    }

    private function distanceNm(float $aLat, float $aLon, float $bLat, float $bLon): float
    {
        $r = 3440.065;
        $lat = deg2rad($bLat - $aLat);
        $lon = deg2rad($bLon - $aLon);
        $h = sin($lat / 2) ** 2 + cos(deg2rad($aLat)) * cos(deg2rad($bLat)) * sin($lon / 2) ** 2;
        return $r * 2 * atan2(sqrt($h), sqrt(1 - $h));
    }
}
