<?php

namespace App\Providers;

use App\Services\AiGatewayHttpClient;
use App\Services\AiGatewayService;
use App\Services\Contracts\AiGatewayHttpClientContract;
use App\Services\Contracts\AiGatewayServiceContract;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider ai gateway service provider.
 */
class AiGatewayServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы контейнера.
     */
    public function register(): void
    {
        $this->app->bind(AiGatewayHttpClientContract::class, AiGatewayHttpClient::class);
        $this->app->bind(AiGatewayServiceContract::class, AiGatewayService::class);
    }
}
