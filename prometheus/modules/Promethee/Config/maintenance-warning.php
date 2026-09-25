<?php

return [
    // Initial beta thresholds: deliberately narrow so the maintenance page does
    // not become a second full fleet list. They can be tuned without code changes.
    'hours' => (int) env('PROMETHEE_MAINTENANCE_WARNING_HOURS', 100),
    'cycles' => (int) env('PROMETHEE_MAINTENANCE_WARNING_CYCLES', 2),
];
