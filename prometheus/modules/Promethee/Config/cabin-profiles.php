<?php

return [
    /*
     * Air Inter cabin profiles are deliberately code/config driven.
     * They do not alter phpVMS tables, subfleet fares or aircraft records.
     */
    'profiles' => [
        'air_inter_a320_172' => [
            'label' => 'Air Inter A320 · 172 sièges',
            'capacity' => 172,
        ],
    ],

    /*
     * Highest priority: exact physical aircraft.
     * Example:
     * 'F-GPMB' => 'air_inter_a320_172',
     */
    'by_registration' => [
    ],

    /*
     * Then SimBrief type / ICAO. These can be extended as historical or
     * VA-specific cabin references are validated.
     */
    'by_simbrief_type' => [
        'A320' => 'air_inter_a320_172',
        'A320-214' => 'air_inter_a320_172',
    ],

    'by_icao' => [
        'A320' => 'air_inter_a320_172',
    ],
];
