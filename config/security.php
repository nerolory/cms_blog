<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HTTP Strict Transport Security (HSTS)
    |--------------------------------------------------------------------------
    |
    | Enabled by default in production. Requires HTTPS termination in front
    | of the application (nginx, load balancer, CDN).
    |
    */

    'hsts' => [
        'enabled' => env('SECURITY_HSTS_ENABLED', env('APP_ENV') === 'production'),
        'max_age' => env('SECURITY_HSTS_MAX_AGE', 31_536_000),
        'include_subdomains' => env('SECURITY_HSTS_SUBDOMAINS', true),
    ],

    'frame_options' => env('SECURITY_FRAME_OPTIONS', 'SAMEORIGIN'),

    'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),

    'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', 'camera=(), microphone=(), geolocation=()'),

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | Учитывает Vite (self), TinyMCE и CropperJS (self + cdn.jsdelivr.net).
    | Filament: nonce на inline script/style в vendor-override, unsafe-eval для Alpine.
    | Отключить: SECURITY_CSP_ENABLED=false
    |
    */

    'csp' => [
        'enabled' => env('SECURITY_CSP_ENABLED', true),
    ],

];
