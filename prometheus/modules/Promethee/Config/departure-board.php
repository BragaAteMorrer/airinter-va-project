<?php

return [
    // The board is intentionally driven by a time range, not by an arbitrary
    // number of upcoming timetable rows. Values are minutes around "now".
    'past_minutes' => (int) env('DEPARTURE_BOARD_PAST_MINUTES', 60),
    'future_minutes' => (int) env('DEPARTURE_BOARD_FUTURE_MINUTES', 60),
    'max_rows' => (int) env('DEPARTURE_BOARD_MAX_ROWS', 10),
    // Avoid an empty board between timetable waves. It only exposes genuine
    // future schedule rows and is disabled with DEPARTURE_BOARD_FUTURE_FALLBACK=false.
    'future_fallback' => env('DEPARTURE_BOARD_FUTURE_FALLBACK', true),
    'refresh_seconds' => (int) env('DEPARTURE_BOARD_REFRESH_SECONDS', 30),
];
