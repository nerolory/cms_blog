<?php

use App\Providers\AiGatewayServiceProvider;
use App\Providers\AiServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\DatabaseProtectionServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\SearchServiceProvider;
use App\Providers\SiteHealthServiceProvider;

return [
    AppServiceProvider::class,
    AiGatewayServiceProvider::class,
    DatabaseProtectionServiceProvider::class,
    SearchServiceProvider::class,
    SiteHealthServiceProvider::class,
    AiServiceProvider::class,
    AdminPanelProvider::class,
];
