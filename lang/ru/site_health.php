<?php

return [
    'checks' => [
        'php' => [
            'passed' => 'PHP :version — OK',
            'failed' => 'PHP :current ниже требуемой :required',
        ],
        'laravel' => [
            'passed' => 'Laravel :version — OK',
            'failed' => 'Laravel :current ниже требуемой :required',
        ],
        'database' => [
            'passed' => 'База данных доступна',
            'failed' => 'База данных недоступна',
        ],
        'redis' => [
            'passed' => 'Redis доступен',
            'failed' => 'Redis недоступен',
            'not_configured' => 'Redis не используется для cache/queue',
        ],
        'nginx' => [
            'passed' => 'Nginx / HTTP OK',
            'unreachable' => 'Nginx недоступен',
            'unhealthy' => 'Nginx вернул ошибку',
            'config_found' => 'Конфиг nginx найден',
        ],
        'security_headers' => [
            'passed' => 'Security headers на месте',
            'missing' => 'Отсутствуют security headers',
            'failed' => 'Не удалось проверить headers',
        ],
        'app_key' => [
            'passed' => 'APP_KEY задан',
            'failed' => 'APP_KEY не задан или использует значение по умолчанию',
        ],
        'migrations' => [
            'passed' => 'Все миграции применены',
            'pending' => 'Есть неприменённые миграции',
        ],
        'owner_exists' => [
            'passed' => 'Пользователь owner существует',
            'failed' => 'Пользователь owner не найден',
        ],
        'storage_writable' => [
            'passed' => 'Хранилище доступно для записи',
            'failed' => 'Хранилище недоступно для записи',
        ],
        'queue_worker_heartbeat' => [
            'passed' => 'Heartbeat воркера очереди свежий',
            'stale' => 'Heartbeat воркера очереди устарел или отсутствует',
        ],
    ],
];
