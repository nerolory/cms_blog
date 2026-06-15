<?php

return [
    'cache' => [
        'max_age' => (int) env('SEO_CACHE_MAX_AGE', 3600),
    ],

    'defaults' => [
        'robots' => 'index,follow',
        'private_robots' => 'noindex,nofollow',
    ],

    'description_max_length' => 160,

    'robots_txt' => [
        'disallow' => [
            '/admin',
            '/posts/preview/',
        ],
    ],
];
