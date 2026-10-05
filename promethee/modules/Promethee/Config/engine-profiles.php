<?php

/*
|--------------------------------------------------------------------------
| Prométhée engine reference profiles — Air Inter VA
|--------------------------------------------------------------------------
|
| The legacy database column `tbo_hours` is intentionally populated with
| the ITVA gameplay potential from the fleet reference workbook, NOT the
| real/manufacturer TBO. Real-world TBO values must never drive maintenance
| status, warnings or overhaul due dates in Prométhée.
|
| Key format: AIRLINE_ICAO|SUBFLEET_TYPE
|
*/

return [
    '__meta' => [
        'source' => 'ENGINES TBO & COST.xlsx',
        'policy' => 'ITVA',
        'itva_categories' => [150, 160, 170, 180, 250, 275, 300, 325, 350, 400, 425, 450, 500, 550, 650, 700, 750, 800, 850, 900, 1000],
    ],

    'ITF|L-1049G' => ['engine_type' => 'Wright R-3350-972TC18DA3/EA-series Turbo-Compound', 'engine_count' => 4, 'tbo_hours' => 170, 'warning_hours' => 17, 'itva_tbo_cost' => 396250],
    'ITF|DC-3' => ['engine_type' => 'Pratt & Whitney R-1830-92 Twin Wasp', 'engine_count' => 2, 'tbo_hours' => 150, 'warning_hours' => 15, 'itva_tbo_cost' => 118125],
    'ITF|DC-4' => ['engine_type' => 'Pratt & Whitney R-2000-7M2 Twin Wasp', 'engine_count' => 4, 'tbo_hours' => 160, 'warning_hours' => 16, 'itva_tbo_cost' => 284400],
    'ITF|Viscount-708' => ['engine_type' => 'Rolls-Royce Dart RDa.6 Mk 510', 'engine_count' => 4, 'tbo_hours' => 300, 'warning_hours' => 30, 'itva_tbo_cost' => 577500],
    'ITF|DC-6' => ['engine_type' => 'Pratt & Whitney R-2800-CB16/CB17 Double Wasp', 'engine_count' => 4, 'tbo_hours' => 250, 'warning_hours' => 25, 'itva_tbo_cost' => 357500],
    'ITF|Caravelle-III' => ['engine_type' => 'Rolls-Royce Avon RA.29 Mk 527B', 'engine_count' => 2, 'tbo_hours' => 350, 'warning_hours' => 35, 'itva_tbo_cost' => 502500],
    'ITF|Alouette-II' => ['engine_type' => 'Turbomeca Artouste IIIB', 'engine_count' => 1, 'tbo_hours' => 180, 'warning_hours' => 18, 'itva_tbo_cost' => 119250],
    'ITF|Viking' => ['engine_type' => 'Bristol Hercules 634', 'engine_count' => 2, 'tbo_hours' => 150, 'warning_hours' => 15, 'itva_tbo_cost' => 141750],
    'ITF|Nord-262' => ['engine_type' => 'Turbomeca Bastan VII', 'engine_count' => 2, 'tbo_hours' => 275, 'warning_hours' => 27.5, 'itva_tbo_cost' => 286562.5],
    'ITF|Fokker-27' => ['engine_type' => 'Rolls-Royce Dart RDa.7 Mk 532-7', 'engine_count' => 2, 'tbo_hours' => 325, 'warning_hours' => 32.5, 'itva_tbo_cost' => 290937.5],
    'ITF|B747-1series' => ['engine_type' => 'Pratt & Whitney JT9D-7A/-7F series', 'engine_count' => 4, 'tbo_hours' => 550, 'warning_hours' => 55, 'itva_tbo_cost' => 2130000],
    'ITF|A300-B4' => ['engine_type' => 'General Electric CF6-50C series', 'engine_count' => 2, 'tbo_hours' => 650, 'warning_hours' => 65, 'itva_tbo_cost' => 1095000],
    'ICS|B737-2series' => ['engine_type' => 'Pratt & Whitney JT8D-15/-17 series', 'engine_count' => 2, 'tbo_hours' => 500, 'warning_hours' => 50, 'itva_tbo_cost' => 700000],
    'ITF|A320-211' => ['engine_type' => 'CFM International CFM56-5A1', 'engine_count' => 2, 'tbo_hours' => 800, 'warning_hours' => 80, 'itva_tbo_cost' => 1615000],
    'ITF|A333' => ['engine_type' => 'General Electric CF6-80E1A2', 'engine_count' => 2, 'tbo_hours' => 800, 'warning_hours' => 80, 'itva_tbo_cost' => 1615000],
    'ITF|A321' => ['engine_type' => 'CFM International CFM56-5B1', 'engine_count' => 2, 'tbo_hours' => 900, 'warning_hours' => 90, 'itva_tbo_cost' => 1657500],
    'ITF|A319' => ['engine_type' => 'CFM International CFM56-5A4', 'engine_count' => 2, 'tbo_hours' => 800, 'warning_hours' => 80, 'itva_tbo_cost' => 1615000],
    'ITF|Fokker-100' => ['engine_type' => 'Rolls-Royce Tay 650-15', 'engine_count' => 2, 'tbo_hours' => 700, 'warning_hours' => 70, 'itva_tbo_cost' => 1295000],
    'ACF|B737-400' => ['engine_type' => 'CFM International CFM56-3C1', 'engine_count' => 2, 'tbo_hours' => 700, 'warning_hours' => 70, 'itva_tbo_cost' => 1295000],
    'ITF|DC-8-63PF' => ['engine_type' => 'Pratt & Whitney JT3D-7', 'engine_count' => 4, 'tbo_hours' => 400, 'warning_hours' => 40, 'itva_tbo_cost' => 1360000],
    'ACF|A310' => ['engine_type' => 'GE CF6-80C2 / P&W PW4000 (variant-dependent)', 'engine_count' => 2, 'tbo_hours' => 750, 'warning_hours' => 75, 'itva_tbo_cost' => 1593750],
    'ACF|Fokker-28' => ['engine_type' => 'Rolls-Royce Spey Mk 555-15', 'engine_count' => 2, 'tbo_hours' => 400, 'warning_hours' => 40, 'itva_tbo_cost' => 680000],
    'ITF|Mercure-100' => ['engine_type' => 'Pratt & Whitney JT8D-15', 'engine_count' => 2, 'tbo_hours' => 500, 'warning_hours' => 50, 'itva_tbo_cost' => 700000],
    'ACF|Mercure-100-leased' => ['engine_type' => 'Pratt & Whitney JT8D-15', 'engine_count' => 2, 'tbo_hours' => 500, 'warning_hours' => 50, 'itva_tbo_cost' => 700000],
    'ICS|Vanguard' => ['engine_type' => 'Rolls-Royce Tyne RTy.11 Mk 512', 'engine_count' => 4, 'tbo_hours' => 325, 'warning_hours' => 32.5, 'itva_tbo_cost' => 748125],
    'ICS|L-100-30' => ['engine_type' => 'Allison 501-D22A', 'engine_count' => 4, 'tbo_hours' => 425, 'warning_hours' => 42.5, 'itva_tbo_cost' => 770625],
    'ACF|L-1049C' => ['engine_type' => 'Wright R-3350-972TC18DA3 / 988TC18EA series', 'engine_count' => 4, 'tbo_hours' => 170, 'warning_hours' => 17, 'itva_tbo_cost' => 396250],
    'ITF|L-749' => ['engine_type' => 'Wright R-3350-749C18BD-series', 'engine_count' => 4, 'tbo_hours' => 160, 'warning_hours' => 16, 'itva_tbo_cost' => 347600],
    'ITF|Viscount-724' => ['engine_type' => 'Rolls-Royce Dart RDa.6 Mk 506', 'engine_count' => 4, 'tbo_hours' => 300, 'warning_hours' => 30, 'itva_tbo_cost' => 577500],
    'ITF|A300-B2' => ['engine_type' => 'General Electric CF6-50A', 'engine_count' => 2, 'tbo_hours' => 650, 'warning_hours' => 65, 'itva_tbo_cost' => 1095000],
    'ITF|Caravelle-12' => ['engine_type' => 'Pratt & Whitney JT8D-9', 'engine_count' => 2, 'tbo_hours' => 500, 'warning_hours' => 50, 'itva_tbo_cost' => 700000],
    'ITF|Caravelle-VI-R' => ['engine_type' => 'Rolls-Royce Avon RA.29 Mk 533R', 'engine_count' => 2, 'tbo_hours' => 350, 'warning_hours' => 35, 'itva_tbo_cost' => 502500],
    'ITF|A320-111' => ['engine_type' => 'CFM International CFM56-5A1', 'engine_count' => 2, 'tbo_hours' => 800, 'warning_hours' => 80, 'itva_tbo_cost' => 1615000],
    'ITF|B747-2B4B' => ['engine_type' => 'P&W JT9D / GE CF6 / RR RB211 (variant-dependent)', 'engine_count' => 4, 'tbo_hours' => 550, 'warning_hours' => 55, 'itva_tbo_cost' => 2130000],
    'ACF|Caravelle-III-ACF' => ['engine_type' => 'Rolls-Royce Avon RA.29 Mk 527B', 'engine_count' => 2, 'tbo_hours' => 350, 'warning_hours' => 35, 'itva_tbo_cost' => 502500],
    'ACF|Caravelle-10B' => ['engine_type' => 'Pratt & Whitney JT8D-7/-9', 'engine_count' => 2, 'tbo_hours' => 500, 'warning_hours' => 50, 'itva_tbo_cost' => 700000],
    'ACF|B727' => ['engine_type' => 'Pratt & Whitney JT8D-15/-17 series', 'engine_count' => 3, 'tbo_hours' => 500, 'warning_hours' => 50, 'itva_tbo_cost' => 1050000],
    'ACF|A300-B4' => ['engine_type' => 'General Electric CF6-50C2', 'engine_count' => 2, 'tbo_hours' => 650, 'warning_hours' => 65, 'itva_tbo_cost' => 1095000],
    'ACF|B737-700' => ['engine_type' => 'CFM International CFM56-7B', 'engine_count' => 2, 'tbo_hours' => 1000, 'warning_hours' => 100, 'itva_tbo_cost' => 2000000],
    'ACF|B737-500' => ['engine_type' => 'CFM International CFM56-3C1', 'engine_count' => 2, 'tbo_hours' => 700, 'warning_hours' => 70, 'itva_tbo_cost' => 1295000],
    'ACF|B777' => ['engine_type' => 'GE90 / P&W PW4000 / RR Trent 800 (variant-dependent)', 'engine_count' => 2, 'tbo_hours' => 850, 'warning_hours' => 85, 'itva_tbo_cost' => 2695000],
    'ACF|MD-11' => ['engine_type' => 'GE CF6-80C2 / P&W PW4460 / RR Trent 700 (variant-dependent)', 'engine_count' => 3, 'tbo_hours' => 750, 'warning_hours' => 75, 'itva_tbo_cost' => 2390625],
    'ACF|A343' => ['engine_type' => 'CFM International CFM56-5C4', 'engine_count' => 4, 'tbo_hours' => 900, 'warning_hours' => 90, 'itva_tbo_cost' => 3315000],
    'ACF|B747-200' => ['engine_type' => 'P&W JT9D / GE CF6 / RR RB211 (variant-dependent)', 'engine_count' => 4, 'tbo_hours' => 550, 'warning_hours' => 55, 'itva_tbo_cost' => 2130000],
    'ACF|Dash-7' => ['engine_type' => 'Pratt & Whitney Canada PT6A-50', 'engine_count' => 4, 'tbo_hours' => 450, 'warning_hours' => 45, 'itva_tbo_cost' => 776250],
    'ACF|B757' => ['engine_type' => 'Rolls-Royce RB211-535E4', 'engine_count' => 2, 'tbo_hours' => 700, 'warning_hours' => 70, 'itva_tbo_cost' => 1665000],
    'ACF|B767' => ['engine_type' => 'GE CF6-80A / P&W JT9D (variant-dependent)', 'engine_count' => 2, 'tbo_hours' => 700, 'warning_hours' => 70, 'itva_tbo_cost' => 1572500],
    'ITF|Nord-260' => ['engine_type' => 'Turbomeca Bastan VI', 'engine_count' => 2, 'tbo_hours' => 275, 'warning_hours' => 27.5, 'itva_tbo_cost' => 286562.5],
    'ACF|Fokker-70' => ['engine_type' => 'Rolls-Royce Tay 620-15', 'engine_count' => 2, 'tbo_hours' => 700, 'warning_hours' => 70, 'itva_tbo_cost' => 1295000],
    'ACF|L-1011-500' => ['engine_type' => 'Rolls-Royce RB211-524B4', 'engine_count' => 3, 'tbo_hours' => 700, 'warning_hours' => 70, 'itva_tbo_cost' => 2497500],
    'ACF|A346' => ['engine_type' => 'Rolls-Royce Trent 556', 'engine_count' => 4, 'tbo_hours' => 750, 'warning_hours' => 75, 'itva_tbo_cost' => 5250000],
];
