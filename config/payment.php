<?php

return [
    /*
    | Supported: mock (local/staging only; disabled in production),
    | http (REST payment API — production), none|disabled|off (unavailable).
    | Unknown values fall back to disabled — the app must boot without payments.
    */
    'gateway' => env('PAYMENT_GATEWAY', 'mock'),

    'http' => [
        'base_url' => env('PAYMENT_HTTP_URL', ''),
        'secret' => env('PAYMENT_HTTP_SECRET', ''),
        'timeout' => (int) env('PAYMENT_HTTP_TIMEOUT', 10),
    ],

    'webhook' => [
        'secret' => env('PAYMENT_WEBHOOK_SECRET', ''),
    ],
];
