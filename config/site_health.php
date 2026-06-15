<?php

return [
    'context' => env('SITE_HEALTH_CONTEXT', 'native'),

    'profiles' => [
        'default' => [
            'php',
            'laravel',
            'database',
            'redis',
            'nginx',
            'security_headers',
        ],
        'install-check' => [
            'app_key',
            'migrations',
            'owner_exists',
            'storage_writable',
            'redis',
            'queue_worker_heartbeat',
        ],
    ],

    'php' => [
        'min_version' => '8.2.0',
    ],

    'laravel' => [
        'min_version' => '11.0.0',
    ],

    'nginx' => [
        'docker_host' => env('SITE_HEALTH_NGINX_HOST', 'nginx'),
        'docker_port' => env('SITE_HEALTH_NGINX_PORT', 80),
        'config_path' => env('SITE_HEALTH_NGINX_CONFIG', null),
    ],
];
