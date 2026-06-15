<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dev-seeding (TD-10). В production пароль проверяет ProductionConfigGuard.
    |--------------------------------------------------------------------------
    */

    'owner' => [
        'email' => env('SEED_OWNER_EMAIL', 'owner@example.com'),
        'password' => env('SEED_OWNER_PASSWORD', 'password'),
        'name' => env('SEED_OWNER_NAME', 'Owner'),
    ],

];
