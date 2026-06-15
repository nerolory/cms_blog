<?php

return [
    'checks' => [
        'queue_worker' => [
            'fresh' => 'Воркер очереди активен',
            'stale' => 'Нет свежего heartbeat воркера очереди',
        ],
    ],
];
