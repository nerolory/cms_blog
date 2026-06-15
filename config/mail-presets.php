<?php

$localDomain = env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST));

return [

    /*
    |--------------------------------------------------------------------------
    | Почтовые пресеты (ключ → transport + дефолты из env)
    |--------------------------------------------------------------------------
    |
    | Админка выбирает ключ; MailConfigurationResolver подставляет credentials.
    |
    */

    'mailpit' => [
        'label' => 'Mailpit',
        'requires_auth' => false,
        'mailer' => [
            'transport' => 'smtp',
            'host' => env('MAILPIT_HOST', 'mailpit'),
            'port' => (int) env('MAILPIT_PORT', 1025),
            'username' => null,
            'password' => null,
            'timeout' => null,
            'local_domain' => $localDomain,
        ],
    ],

    'mailbox' => [
        'label' => 'mailbox.org',
        'requires_auth' => true,
        'mailer' => [
            'transport' => 'smtp',
            'scheme' => env('MAILBOX_SCHEME', 'tls'),
            'host' => env('MAILBOX_HOST', 'smtp.mailbox.org'),
            'port' => (int) env('MAILBOX_PORT', 587),
            'username' => null,
            'password' => null,
            'timeout' => null,
            'local_domain' => $localDomain,
        ],
    ],

    'postfix' => [
        'label' => 'Postfix (local relay)',
        'requires_auth' => false,
        'mailer' => [
            'transport' => 'smtp',
            'host' => env('POSTFIX_HOST', '127.0.0.1'),
            'port' => (int) env('POSTFIX_PORT', 25),
            'username' => null,
            'password' => null,
            'timeout' => null,
            'local_domain' => $localDomain,
        ],
    ],

    'ssmtp' => [
        'label' => 'ssmtp / sendmail',
        'requires_auth' => false,
        'mailer' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],
    ],

];
