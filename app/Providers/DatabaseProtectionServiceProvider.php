<?php

namespace App\Providers;

use App\Support\Database\DatabaseProtection;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider database protection service provider.
 */
class DatabaseProtectionServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует зависимости при загрузке.
     */
    public function boot(): void
    {
        DatabaseProtection::registerArtisanGuards();
    }
}
