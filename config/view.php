<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | On Docker Desktop for Windows, bind-mounted storage/framework/views
    | may reject touch()/utime. Use VIEW_COMPILED_PATH=/tmp/laravel-views
    | or the laravel_blade_views volume from docker-compose.yml.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views')) ?: storage_path('framework/views'),
    ),

];
