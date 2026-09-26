<?php

return [
    'past_minutes' => 45,
    'future_minutes' => 90,
    'max_rows' => 20,
    'future_fallback' => true,

    // Legacy fallback. The browser now uses one-shot adaptive refreshes and
    // updates palettes only when the returned revision changes.
    'refresh_seconds' => 30,

    // Operational status windows for timetable rows without an active PIREP.
    // They only drive the public split-flap display and never mutate phpVMS.
    'status' => [
        'boarding_before_minutes' => 30,
        'gate_closed_before_minutes' => 10,
        'en_route_after_minutes' => 15,
        'approach_before_minutes' => 25,
        'arrived_after_minutes' => 10,
    ],

    /*
     * Short labels for the physical split-flap board.
     * This is the file to edit when an airport name needs a clearer/shorter
     * wording. Keys are phpVMS airport IDs/ICAO codes. Unlisted names are
     * automatically clipped to 18 characters for display only.
     */
    'airport_labels' => [
        'LFPO' => 'PARIS ORLY',
        'LFPG' => 'PARIS CDG',
        'LFML' => 'MARSEILLE',
        'LFMN' => 'NICE',
        'LFBD' => 'BORDEAUX',
        'LFBO' => 'TOULOUSE',
        'LFMP' => 'PERPIGNAN',
        'LFQQ' => 'LILLE',
    ],
];
