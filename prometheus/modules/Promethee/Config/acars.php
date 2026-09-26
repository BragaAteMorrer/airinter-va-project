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
    ],,
    /*
     * Simulator variants are separate from the physical phpVMS fleet.
     * A pilot may fly the assigned Air Inter aircraft with any compatible
     * add-on below. The selected variant drives SimBrief performance and lets
     * Hermès compare the expected add-on with the simulator adapter it detects.
     */
    'variants' => [
        'A319' => [
            ['id' => 'generic-a319', 'label' => 'Airbus A319 (générique)', 'vendor' => 'Generic', 'simbrief_type' => 'A319', 'simulators' => ['msfs2020','msfs2024','fsx','p3d','xplane'], 'adapter_ids' => [], 'default' => true],
            ['id' => 'fenix-a319', 'label' => 'Fenix A319', 'vendor' => 'Fenix Simulations', 'simbrief_type' => 'A319', 'simulators' => ['msfs2020','msfs2024'], 'adapter_ids' => ['fenix-a319']],
        ],
        'A320' => [
            ['id' => 'generic-a320', 'label' => 'Airbus A320 (générique)', 'vendor' => 'Generic', 'simbrief_type' => 'A320', 'simulators' => ['msfs2020','msfs2024','fsx','p3d','xplane'], 'adapter_ids' => [], 'default' => true],
            ['id' => 'fenix-a320', 'label' => 'Fenix A320', 'vendor' => 'Fenix Simulations', 'simbrief_type' => 'A320', 'simulators' => ['msfs2020','msfs2024'], 'adapter_ids' => ['fenix-a320']],
            ['id' => 'fbw-a32nx', 'label' => 'FlyByWire A32NX', 'vendor' => 'FlyByWire', 'simbrief_type' => 'A20N', 'simulators' => ['msfs2020','msfs2024'], 'adapter_ids' => ['fbw-a32nx']],
            ['id' => 'inibuilds-a320neo', 'label' => 'iniBuilds A320neo', 'vendor' => 'iniBuilds', 'simbrief_type' => 'A20N', 'simulators' => ['msfs2024'], 'adapter_ids' => ['inibuilds-a320']],
        ],
        'A321' => [
            ['id' => 'generic-a321', 'label' => 'Airbus A321 (générique)', 'vendor' => 'Generic', 'simbrief_type' => 'A321', 'simulators' => ['msfs2020','msfs2024','fsx','p3d','xplane'], 'adapter_ids' => [], 'default' => true],
            ['id' => 'fenix-a321', 'label' => 'Fenix A321', 'vendor' => 'Fenix Simulations', 'simbrief_type' => 'A321', 'simulators' => ['msfs2020','msfs2024'], 'adapter_ids' => ['fenix-a321']],
        ],
        'A310' => [
            ['id' => 'generic-a310', 'label' => 'Airbus A310 (générique)', 'vendor' => 'Generic', 'simbrief_type' => 'A310', 'simulators' => ['msfs2020','msfs2024','xplane'], 'adapter_ids' => [], 'default' => true],
            ['id' => 'inibuilds-a310', 'label' => 'iniBuilds A310-300', 'vendor' => 'iniBuilds', 'simbrief_type' => 'A310', 'simulators' => ['msfs2020','msfs2024'], 'adapter_ids' => ['inibuilds-a310']],
        ],
        'B737' => [
            ['id' => 'generic-b737', 'label' => 'Boeing 737 (générique)', 'vendor' => 'Generic', 'simbrief_type' => 'B738', 'simulators' => ['msfs2020','msfs2024','fsx','p3d','xplane'], 'adapter_ids' => [], 'default' => true],
            ['id' => 'pmdg-b737', 'label' => 'PMDG 737', 'vendor' => 'PMDG', 'simbrief_type' => 'B738', 'simulators' => ['msfs2020','msfs2024'], 'adapter_ids' => ['pmdg']],
        ],
        'MD11' => [
            ['id' => 'generic-md11', 'label' => 'McDonnell Douglas MD-11 (générique)', 'vendor' => 'Generic', 'simbrief_type' => 'MD11', 'simulators' => ['msfs2020','msfs2024','xplane'], 'adapter_ids' => [], 'default' => true],
            ['id' => 'tfdi-md11', 'label' => 'TFDi MD-11', 'vendor' => 'TFDi Design', 'simbrief_type' => 'MD11', 'simulators' => ['msfs2020','msfs2024'], 'adapter_ids' => ['tfdi-md11']],
        ],
    ]
];
