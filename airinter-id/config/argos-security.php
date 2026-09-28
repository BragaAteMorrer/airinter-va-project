<?php

return [
    'trusted_device_days' => (int) env('ARGOS_TRUSTED_DEVICE_DAYS', 30),
    'admin_subjects' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ARGOS_SECURITY_ADMIN_SUBJECTS', ''))
    ))),
    'risk' => [
        'medium' => 30,
        'high' => 60,
    ],
];
