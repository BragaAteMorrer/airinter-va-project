<?php

return [
    'name' => env('ARGOS_NAME', 'Argos'),
    'release' => env('ARGOS_RELEASE'),
    'brand_logo_url' => env('ARGOS_BRAND_LOGO_URL', 'https://promethee.airinter-va.org/promethee-assets/logos/air-inter-compact.png'),
    'favicon_url' => env('ARGOS_FAVICON_URL', env('ARGOS_BRAND_LOGO_URL', 'https://promethee.airinter-va.org/promethee-assets/logos/air-inter-compact.png')),
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
            'redirect_uris' => [env('AIRINTER_ID_PROMETHEE_CALLBACK', 'https://promethee.airinter-va.org/auth/airinter-id/callback')],
            'confidential' => true,
            'grant_types' => ['authorization_code', 'refresh_token'],
            'scopes' => ['openid', 'profile', 'email', 'promethee:read'],
            'security' => [
                'require_pkce' => true,
                'require_state' => true,
                'pkce_method' => 'S256',
            ],
        ],
        'hermes' => [
            'name' => 'Hermès',
            'redirect_uri' => env('AIRINTER_ID_HERMES_CALLBACK', 'http://127.0.0.1:47821/callback'),
            'redirect_uris' => [env('AIRINTER_ID_HERMES_CALLBACK', 'http://127.0.0.1:47821/callback')],
            'confidential' => false,
            'grant_types' => ['authorization_code', 'refresh_token'],
            'scopes' => ['openid', 'profile', 'email', 'hermes:operate'],
            'security' => [
                'require_pkce' => true,
                'require_state' => true,
                'pkce_method' => 'S256',
            ],
        ],
        'website' => [
            'name' => 'Air Inter VA',
            'redirect_uri' => env('AIRINTER_ID_PUBLIC_CALLBACK', 'https://www.airinter-va.org/auth/airinter-id/callback'),
            'redirect_uris' => [env('AIRINTER_ID_PUBLIC_CALLBACK', 'https://www.airinter-va.org/auth/airinter-id/callback')],
            'confidential' => true,
            'grant_types' => ['authorization_code', 'refresh_token'],
            'scopes' => ['openid', 'profile', 'email'],
            'security' => [
                'require_pkce' => true,
                'require_state' => true,
                'pkce_method' => 'S256',
            ],
        ],
    ],
];
