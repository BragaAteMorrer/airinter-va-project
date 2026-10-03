<?php

/*
|--------------------------------------------------------------------------
| Prométhée engine reference profiles
|--------------------------------------------------------------------------
|
| This file provides VA reference values used to initialise missing engine
| profiles from the fleet reference workbook. For modern airline turbofans,
| tbo_hours is an Air Inter VA potential reference used by Prométhée rather
| than a regulatory manufacturer hard-TBO limit.
|
| Key format: AIRLINE_ICAO|SUBFLEET_TYPE
|
*/

return [
    'ITF|L-1049G' => ['engine_type' => 'Wright R-3350-972-TC18DA-3', 'engine_count' => 4, 'tbo_hours' => 1500, 'warning_hours' => 150],
    'ITF|DC-3' => ['engine_type' => 'Pratt & Whitney R-1830-S1C3-G', 'engine_count' => 2, 'tbo_hours' => 1500, 'warning_hours' => 150],
    'ITF|DC-4' => ['engine_type' => 'Pratt & Whitney R-2000-2SD-13G', 'engine_count' => 4, 'tbo_hours' => 1500, 'warning_hours' => 150],
    'ITF|Viscount-708' => ['engine_type' => 'Rolls-Royce Dart RDa.3 Mk 506', 'engine_count' => 4, 'tbo_hours' => 6000, 'warning_hours' => 600],
    'ITF|DC-6' => ['engine_type' => 'Pratt & Whitney R-2800-CB17', 'engine_count' => 4, 'tbo_hours' => 2300, 'warning_hours' => 230],
    'ITF|Caravelle-III' => ['engine_type' => 'Rolls-Royce Avon RA.29/3 Mk 527B', 'engine_count' => 2, 'tbo_hours' => 6000, 'warning_hours' => 600],
    'ITF|Alouette-II' => ['engine_type' => 'Turbomeca Artouste IIC', 'engine_count' => 1, 'tbo_hours' => 1750, 'warning_hours' => 175],
    'ITF|Viking' => ['engine_type' => 'Bristol Hercules 634', 'engine_count' => 2, 'tbo_hours' => 1200, 'warning_hours' => 120],
    'ITF|Nord-262' => ['engine_type' => 'Turbomeca Bastan VIC', 'engine_count' => 2, 'tbo_hours' => 3000, 'warning_hours' => 300],
    'ITF|Fokker-27' => ['engine_type' => 'Rolls-Royce Dart 532-7 / 532-7R', 'engine_count' => 2, 'tbo_hours' => 6000, 'warning_hours' => 600],
    'ITF|B747-1series' => ['engine_type' => 'Pratt & Whitney JT9D-7A', 'engine_count' => 4, 'tbo_hours' => 10000, 'warning_hours' => 1000],
    'ITF|A300-B4' => ['engine_type' => 'General Electric CF6-50C', 'engine_count' => 2, 'tbo_hours' => 12000, 'warning_hours' => 1200],
    'ICS|B737-2series' => ['engine_type' => 'Pratt & Whitney JT8D-15', 'engine_count' => 2, 'tbo_hours' => 6200, 'warning_hours' => 620],
    'ITF|A320-211' => ['engine_type' => 'CFM International CFM56-5A1', 'engine_count' => 2, 'tbo_hours' => 20000, 'warning_hours' => 2000],
    'ITF|A333' => ['engine_type' => 'General Electric CF6-80E1A2', 'engine_count' => 2, 'tbo_hours' => 18000, 'warning_hours' => 1800],
    'ITF|A321' => ['engine_type' => 'CFM International CFM56-5B1', 'engine_count' => 2, 'tbo_hours' => 20000, 'warning_hours' => 2000],
    'ITF|A319' => ['engine_type' => 'CFM International CFM56-5A4', 'engine_count' => 2, 'tbo_hours' => 20000, 'warning_hours' => 2000],
    'ITF|Fokker-100' => ['engine_type' => 'Rolls-Royce Tay 650-15', 'engine_count' => 2, 'tbo_hours' => 15000, 'warning_hours' => 1500],
    'ACF|B737-400' => ['engine_type' => 'CFM International CFM56-3C1', 'engine_count' => 2, 'tbo_hours' => 18000, 'warning_hours' => 1800],
    'ITF|DC-8-63PF' => ['engine_type' => 'Pratt & Whitney JT3D-7', 'engine_count' => 4, 'tbo_hours' => 6000, 'warning_hours' => 600],
    'ACF|A310' => ['engine_type' => 'General Electric CF6-80C2A2', 'engine_count' => 2, 'tbo_hours' => 18000, 'warning_hours' => 1800],
    'ACF|Fokker-28' => ['engine_type' => 'Rolls-Royce Spey 555-15H', 'engine_count' => 2, 'tbo_hours' => 8000, 'warning_hours' => 800],
    'ITF|Mercure-100' => ['engine_type' => 'Pratt & Whitney JT8D-15', 'engine_count' => 2, 'tbo_hours' => 6200, 'warning_hours' => 620],
    'ACF|Mercure-100-leased' => ['engine_type' => 'Pratt & Whitney JT8D-15', 'engine_count' => 2, 'tbo_hours' => 6200, 'warning_hours' => 620],
    'ICS|Vanguard' => ['engine_type' => 'Rolls-Royce Tyne RTy.11 Mk 512', 'engine_count' => 4, 'tbo_hours' => 5000, 'warning_hours' => 500],
    'ICS|L-100-30' => ['engine_type' => 'Allison 501-D22A', 'engine_count' => 4, 'tbo_hours' => 6000, 'warning_hours' => 600],
    'ACF|L-1049C' => ['engine_type' => 'Wright R-3350-972-TC18DA-1', 'engine_count' => 4, 'tbo_hours' => 1500, 'warning_hours' => 150],
    'ITF|L-749' => ['engine_type' => 'Wright R-3350-749C18BD-1', 'engine_count' => 4, 'tbo_hours' => 1500, 'warning_hours' => 150],
    'ITF|Viscount-724' => ['engine_type' => 'Rolls-Royce Dart RDa.3 Mk 506', 'engine_count' => 4, 'tbo_hours' => 6000, 'warning_hours' => 600],
    'ITF|A300-B2' => ['engine_type' => 'General Electric CF6-50C', 'engine_count' => 2, 'tbo_hours' => 12000, 'warning_hours' => 1200],
    'ITF|Caravelle-12' => ['engine_type' => 'Pratt & Whitney JT8D-9', 'engine_count' => 2, 'tbo_hours' => 5000, 'warning_hours' => 500],
    'ITF|Caravelle-VI-R' => ['engine_type' => 'Rolls-Royce Avon RA.29 Mk 533R', 'engine_count' => 2, 'tbo_hours' => 6000, 'warning_hours' => 600],
    'ITF|A320-111' => ['engine_type' => 'CFM International CFM56-5A1', 'engine_count' => 2, 'tbo_hours' => 20000, 'warning_hours' => 2000],
    'ITF|B747-2B4B' => ['engine_type' => 'Pratt & Whitney JT9D-7FW', 'engine_count' => 4, 'tbo_hours' => 10000, 'warning_hours' => 1000],
    'ACF|Caravelle-III-ACF' => ['engine_type' => 'Rolls-Royce Avon RA.29/3 Mk 527B', 'engine_count' => 2, 'tbo_hours' => 6000, 'warning_hours' => 600],
    'ACF|Caravelle-10B' => ['engine_type' => 'Pratt & Whitney JT8D-7', 'engine_count' => 2, 'tbo_hours' => 4200, 'warning_hours' => 420],
    'ACF|B727' => ['engine_type' => 'Pratt & Whitney JT8D-15', 'engine_count' => 3, 'tbo_hours' => 6200, 'warning_hours' => 620],
    'ACF|A300-B4' => ['engine_type' => 'General Electric CF6-50C2', 'engine_count' => 2, 'tbo_hours' => 12000, 'warning_hours' => 1200],
    'ACF|B737-700' => ['engine_type' => 'CFM International CFM56-7B24', 'engine_count' => 2, 'tbo_hours' => 22000, 'warning_hours' => 2200],
    'ACF|B737-500' => ['engine_type' => 'CFM International CFM56-3C1', 'engine_count' => 2, 'tbo_hours' => 18000, 'warning_hours' => 1800],
    'ACF|B777' => ['engine_type' => 'General Electric GE90-94B', 'engine_count' => 2, 'tbo_hours' => 25000, 'warning_hours' => 2500],
    'ACF|MD-11' => ['engine_type' => 'General Electric CF6-80C2D1F', 'engine_count' => 3, 'tbo_hours' => 18000, 'warning_hours' => 1800],
    'ACF|A343' => ['engine_type' => 'CFM International CFM56-5C4', 'engine_count' => 4, 'tbo_hours' => 20000, 'warning_hours' => 2000],
    'ACF|B747-200' => ['engine_type' => 'General Electric CF6-50E2', 'engine_count' => 4, 'tbo_hours' => 12000, 'warning_hours' => 1200],
    'ACF|Dash-7' => ['engine_type' => 'Pratt & Whitney Canada PT6A-50', 'engine_count' => 4, 'tbo_hours' => 5500, 'warning_hours' => 550],
    'ACF|B757' => ['engine_type' => 'Rolls-Royce RB211-535E4', 'engine_count' => 2, 'tbo_hours' => 20000, 'warning_hours' => 2000],
    'ACF|B767' => ['engine_type' => 'General Electric CF6-80A2', 'engine_count' => 2, 'tbo_hours' => 18000, 'warning_hours' => 1800],
    'ITF|Nord-260' => ['engine_type' => 'Turbomeca Bastan IV', 'engine_count' => 2, 'tbo_hours' => 2500, 'warning_hours' => 250],
    'ACF|Fokker-70' => ['engine_type' => 'Rolls-Royce Tay 620-15', 'engine_count' => 2, 'tbo_hours' => 15000, 'warning_hours' => 1500],
    'ACF|L-1011-500' => ['engine_type' => 'Rolls-Royce RB211-524B4', 'engine_count' => 3, 'tbo_hours' => 12000, 'warning_hours' => 1200],
    'ACF|A346' => ['engine_type' => 'Rolls-Royce Trent 556-61', 'engine_count' => 4, 'tbo_hours' => 20000, 'warning_hours' => 2000],
];
