<?php

$localDomain = env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST));

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | Переключение одной переменной MAIL_MAILER:
    |   mailpit   — локальный SMTP + UI (Docker: http://localhost:8025)
    |   smtp      — любой внешний SMTP (mailbox.org, Yandex, Postfix relay…)
    |   sendmail  — локальный sendmail / ssmtp / Postfix -bs
    |   log       — письма в storage/logs/laravel.log
    |   array     — без отправки (PHPUnit)
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => $localDomain,
        ],

        'mailpit' => [
            'transport' => 'smtp',
            'host' => env('MAILPIT_HOST', 'mailpit'),
            'port' => (int) env('MAILPIT_PORT', 1025),
            'username' => env('MAILPIT_USERNAME'),
            'password' => env('MAILPIT_PASSWORD'),
            'timeout' => null,
            'local_domain' => $localDomain,
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],

    'mailpit_ui_port' => (int) env('MAILPIT_UI_PORT', 8025),

];
