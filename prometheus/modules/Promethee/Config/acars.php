<?php

/*
 * Operational rules used by the first-party ACARS. The scheduled aircraft
 * remains the aircraft reported to the OCC; the selected simulator profile is
 * only used for SimBrief performance calculations and local simulator setup.
 */
return [
    'passenger_weight_kg' => 86, // 83 kg passenger + 3 kg cabin baggage
    'checked_baggage_kg' => 19,
    'load_factors' => [
        'air_inter' => 80,
        'air_charter_international' => 100,
        'inter_cargo_service' => 80,
    ],
    'cost_indexes' => [
        'A319' => 50,
        'A320' => 50,
        'A321' => 50,
        'A330_medium' => 60,
        'A330_long' => 30,
        'F100' => 40,
    ],
    // Authorised MSFS substitutions from MANOPS 4.3.9. Keys are normalised
    // subfleet names; set simbrief_type on the matching phpVMS aircraft or
    // subfleet to override these safe defaults.
    'substitutions' => [
        'caravelle iii' => ['msfs2020' => ['addon' => 'SkySimulations DC-9-30', 'simbrief_type' => 'DC93'], 'msfs2024' => ['addon' => 'SkySimulations DC-9-30', 'simbrief_type' => 'DC93']],
        'caravelle 12' => ['msfs2020' => ['addon' => 'Leonardo Maddog MD-82', 'simbrief_type' => 'MD82'], 'msfs2024' => ['addon' => 'Leonardo Maddog MD-82', 'simbrief_type' => 'MD82']],
        'mercure 100' => ['msfs2020' => ['addon' => '737-300', 'simbrief_type' => 'B733'], 'msfs2024' => ['addon' => '737-300', 'simbrief_type' => 'B733']],
        'a300-b2' => ['msfs2020' => ['addon' => 'iniBuilds A300-600R', 'simbrief_type' => 'A306'], 'msfs2024' => ['addon' => 'iniBuilds A300-600R', 'simbrief_type' => 'A306']],
        'a300-b4' => ['msfs2020' => ['addon' => 'iniBuilds A300-600R', 'simbrief_type' => 'A306'], 'msfs2024' => ['addon' => 'iniBuilds A300-600R', 'simbrief_type' => 'A306']],
        'dc-8-63pf' => ['msfs2020' => ['addon' => 'Aeroplane Heaven 707-320', 'simbrief_type' => 'B703'], 'msfs2024' => ['addon' => 'Aeroplane Heaven 707-320', 'simbrief_type' => 'B703']],
    ],
];
