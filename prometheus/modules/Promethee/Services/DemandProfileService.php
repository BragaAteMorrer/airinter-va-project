<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Enums\FareType;
use App\Models\Flight;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemandProfileService
{
    private array $capacityCache = [];
    private array $bandCache = [];

    public function settings(): array
    {
        $values = DB::table('promethee_settings')
            ->whereIn('key', [
                'pricing.bands.enabled',
                'pricing.bands.blue',
                'pricing.bands.white',
                'pricing.demand.blue_min',
                'pricing.demand.blue_max',
                'pricing.demand.white_min',
                'pricing.demand.white_max',
                'pricing.demand.red_min',
                'pricing.demand.red_max',
            ])
            ->pluck('value', 'key');

        return [
            'enabled' => ($values['pricing.bands.enabled'] ?? '1') !== '0',
            'fares' => [
                'bleu' => (float) ($values['pricing.bands.blue'] ?? 50),
                'blanc' => (float) ($values['pricing.bands.white'] ?? 75),
                'rouge' => 100.0,
            ],
            'loads' => [
                'bleu' => [
                    'min' => (float) ($values['pricing.demand.blue_min'] ?? 55),
                    'max' => (float) ($values['pricing.demand.blue_max'] ?? 72),
                ],
                'blanc' => [
                    'min' => (float) ($values['pricing.demand.white_min'] ?? 70),
                    'max' => (float) ($values['pricing.demand.white_max'] ?? 87),
                ],
                'rouge' => [
                    'min' => (float) ($values['pricing.demand.red_min'] ?? 85),
                    'max' => (float) ($values['pricing.demand.red_max'] ?? 98),
                ],
            ],
        ];
    }

    public function bandForFlight(Flight $flight): string
    {
        $key = (string) $flight->id;
        if (isset($this->bandCache[$key])) {
            return $this->bandCache[$key];
        }

        $band = DB::table('promethee_pricing as pricing')
            ->join('fares', 'fares.id', '=', 'pricing.fare_id')
            ->where('pricing.flight_id', $flight->id)
            ->where('fares.type', FareType::PASSENGER)
            ->orderBy('fares.id')
            ->value('pricing.band');

        return $this->bandCache[$key] = in_array($band, ['bleu', 'blanc', 'rouge'], true)
            ? $band
            : 'rouge';
    }

    public function capacityFor(Aircraft $aircraft, Flight $flight): int
    {
        $key = $flight->id.'|'.$aircraft->subfleet_id;
        if (array_key_exists($key, $this->capacityCache)) {
            return $this->capacityCache[$key];
        }

        $rows = DB::table('subfleet_fare as subfare')
            ->join('fares', 'fares.id', '=', 'subfare.fare_id')
            ->leftJoin('flight_fare as flightfare', function ($join) use ($flight) {
                $join->on('flightfare.fare_id', '=', 'subfare.fare_id')
                    ->where('flightfare.flight_id', '=', $flight->id);
            })
            ->where('subfare.subfleet_id', $aircraft->subfleet_id)
            ->where('fares.type', FareType::PASSENGER)
            ->where('fares.active', true)
            ->get([
                'fares.capacity as base_capacity',
                'subfare.capacity as subfleet_capacity',
                'flightfare.capacity as flight_capacity',
            ]);

        $capacity = $rows->sum(function ($row) {
            foreach ([$row->flight_capacity, $row->subfleet_capacity, $row->base_capacity] as $candidate) {
                if ($candidate !== null && (int) $candidate > 0) {
                    return (int) $candidate;
                }
            }

            return 0;
        });

        if ($capacity <= 0) {
            $capacity = DB::table('flight_fare as flightfare')
                ->join('fares', 'fares.id', '=', 'flightfare.fare_id')
                ->where('flightfare.flight_id', $flight->id)
                ->where('fares.type', FareType::PASSENGER)
                ->get(['flightfare.capacity as flight_capacity', 'fares.capacity as base_capacity'])
                ->sum(fn ($row) => (int) ($row->flight_capacity ?: $row->base_capacity ?: 0));
        }

        return $this->capacityCache[$key] = max(0, (int) $capacity);
    }

    public function profile(Aircraft $aircraft, Flight $flight, string $operationId): array
    {
        $settings = $this->settings();
        $band = $settings['enabled'] ? $this->bandForFlight($flight) : 'rouge';
        $range = $settings['loads'][$band];

        if ((float) $flight->load_factor > 0) {
            $center = max(1.0, min(100.0, (float) $flight->load_factor));
            $variance = max(0.0, (float) $flight->load_factor_variance);
            $range = [
                'min' => max(1.0, $center - $variance),
                'max' => min(100.0, $center + $variance),
            ];
        }

        if ($range['min'] > $range['max']) {
            [$range['min'], $range['max']] = [$range['max'], $range['min']];
        }

        $load = $this->deterministicPercent(
            (float) $range['min'],
            (float) $range['max'],
            $operationId.'|'.$flight->id.'|'.$aircraft->id.'|'.$band
        );
        $capacity = $this->capacityFor($aircraft, $flight);
        $passengers = $capacity > 0
            ? min($capacity, max(0, (int) round($capacity * $load / 100)))
            : 0;

        return [
            'band' => $band,
            'band_label' => ucfirst($band),
            'fare_percent' => $settings['fares'][$band],
            'capacity' => $capacity,
            'load_factor_percent' => $load,
            'passengers' => $passengers,
            'load_range' => [
                'min' => round((float) $range['min'], 1),
                'max' => round((float) $range['max'], 1),
            ],
        ];
    }

    public function typeKey(Aircraft $aircraft): string
    {
        $label = $this->typeLabel($aircraft);
        $canonical = [
            'Airbus A319' => 'A319',
            'Airbus A320' => 'A320',
            'Airbus A321' => 'A321',
            'Airbus A300' => 'A300',
            'Airbus A310' => 'A310',
            'Airbus A330' => 'A330',
            'Airbus A340' => 'A340',
            'Fokker 100' => 'F100',
            'Fokker 27' => 'F27',
            'Sud Aviation Caravelle' => 'CARAVELLE',
            'Dassault Mercure' => 'MERCURE',
            'Nord 262' => 'N262',
            'Vickers Viscount' => 'VISCOUNT',
            'Boeing 747' => 'B747',
            'Douglas DC-8' => 'DC8',
        ];

        if (isset($canonical[$label])) {
            return $canonical[$label];
        }

        $key = strtoupper(trim((string) ($aircraft->icao ?: $aircraft->subfleet?->type ?: $label)));

        return preg_replace('/[^A-Z0-9]+/', '', $key) ?: 'AIRCRAFT';
    }

    public function typeLabel(Aircraft $aircraft): string
    {
        $icao = strtoupper((string) $aircraft->icao);
        $name = Str::lower((string) ($aircraft->subfleet?->name ?? $aircraft->name ?? ''));

        if (in_array($icao, ['A319', 'A19N'], true) || str_contains($name, 'a319')) return 'Airbus A319';
        if (in_array($icao, ['A320', 'A20N'], true) || str_contains($name, 'a320')) return 'Airbus A320';
        if (in_array($icao, ['A321', 'A21N'], true) || str_contains($name, 'a321')) return 'Airbus A321';
        if (str_starts_with($icao, 'A30') || str_contains($name, 'a300')) return 'Airbus A300';
        if ($icao === 'A310' || str_contains($name, 'a310')) return 'Airbus A310';
        if (str_starts_with($icao, 'A33') || str_contains($name, 'a330')) return 'Airbus A330';
        if (str_starts_with($icao, 'A34') || str_contains($name, 'a340')) return 'Airbus A340';
        if ($icao === 'F100' || str_contains($name, 'fokker 100')) return 'Fokker 100';
        if (str_starts_with($icao, 'F27') || str_contains($name, 'fokker 27')) return 'Fokker 27';
        if (str_contains($name, 'caravelle')) return 'Sud Aviation Caravelle';
        if (str_contains($name, 'mercure')) return 'Dassault Mercure';
        if (str_contains($name, 'nord 262') || str_contains($name, 'n262')) return 'Nord 262';
        if (str_contains($name, 'viscount')) return 'Vickers Viscount';
        if (str_starts_with($icao, 'B74') || str_contains($name, '747')) return 'Boeing 747';
        if (str_starts_with($icao, 'DC8') || str_contains($name, 'dc-8')) return 'Douglas DC-8';

        return trim((string) ($aircraft->subfleet?->name ?: $aircraft->name ?: $aircraft->icao ?: 'Appareil'));
    }

    private function deterministicPercent(float $min, float $max, string $seed): float
    {
        $min = max(1.0, min(100.0, $min));
        $max = max($min, min(100.0, $max));
        if (abs($max - $min) < 0.01) {
            return round($min, 1);
        }

        $raw = hexdec(substr(hash('sha256', $seed), 0, 8)) / 0xffffffff;

        return round($min + (($max - $min) * $raw), 1);
    }
}
