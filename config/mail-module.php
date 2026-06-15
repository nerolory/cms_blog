<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Почтовый модуль
    |--------------------------------------------------------------------------
    |
    | Дефолты до сохранения настроек в админке; runtime mailer для applyConfiguration().
    |
    */

    'runtime_mailer' => 'runtime',

    'defaults' => [
        'mode' => env('MAIL_MODULE_MODE', 'preset'),
        'preset' => env('MAIL_MODULE_PRESET', 'mailpit'),
        'host' => env('MAIL_HOST', '127.0.0.1'),
        'port' => (int) env('MAIL_PORT', 587),
        'scheme' => env('MAIL_SCHEME'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
        'from_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],

    'require_email_verification_default' => (bool) env('REQUIRE_EMAIL_VERIFICATION', false),

    'encrypted_keys' => [
        'mail.password',
    ],

];
