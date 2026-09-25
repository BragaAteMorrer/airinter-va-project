<?php

return [
    // The board is intentionally driven by a time range, not by an arbitrary
    // number of timetable rows. It is a departures board: only future flights
    // are shown by default, unless an operator explicitly opts into a past window.
    'past_minutes' => (int) env('DEPARTURE_BOARD_PAST_MINUTES', 45),
    'future_minutes' => (int) env('DEPARTURE_BOARD_FUTURE_MINUTES', 90),
    'max_rows' => (int) env('DEPARTURE_BOARD_MAX_ROWS', 20),
    // Avoid an empty board between timetable waves. It only exposes genuine
    // future schedule rows and is disabled with DEPARTURE_BOARD_FUTURE_FALLBACK=false.
    'future_fallback' => env('DEPARTURE_BOARD_FUTURE_FALLBACK', true),
    'refresh_seconds' => (int) env('DEPARTURE_BOARD_REFRESH_SECONDS', 30),
];
