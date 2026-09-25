<?php

return [
    'public_url' => env('AIRINTER_ID_PUBLIC_URL', 'https://www.airinter-va.org'),
    'promethee_url' => env('AIRINTER_ID_PROMETHEE_URL', 'https://promethee.airinter-va.org'),
    'hermes_name' => env('AIRINTER_ID_HERMES_NAME', 'Hermès'),
    'legacy' => [
        'pilot_id_prefix' => env('PROMETHEE_PILOT_ID_PREFIX', 'IT'),
        'pilot_id_length' => (int) env('PROMETHEE_PILOT_ID_LENGTH', 3),
    ],
    'clients' => [
        'promethee' => [
            'name' => 'Prométhée',
            'redirect_uri' => env('AIRINTER_ID_PROMETHEE_CALLBACK', 'https://promethee.airinter-va.org/auth/airinter-id/callback'),
            'confidential' => true,
        ],
        'hermes' => [
            'name' => 'Hermès',
            'redirect_uri' => env('AIRINTER_ID_HERMES_CALLBACK', 'http://127.0.0.1:47821/callback'),
            'confidential' => false,
        ],
        'website' => [
            'name' => 'Air Inter VA',
            'redirect_uri' => env('AIRINTER_ID_PUBLIC_CALLBACK', 'https://www.airinter-va.org/auth/airinter-id/callback'),
            'confidential' => true,
        ],
    ],
];
