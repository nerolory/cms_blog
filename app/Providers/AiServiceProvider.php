<?php

namespace App\Providers;

use App\Repositories\AiAnalysisOrderRepository;
use App\Repositories\AiSettingsRepository;
use App\Repositories\AiToolResultRepository;
use App\Repositories\Contracts\AiAnalysisOrderRepositoryContract;
use App\Repositories\Contracts\AiSettingsRepositoryContract;
use App\Repositories\Contracts\AiToolResultRepositoryContract;
use App\Repositories\Contracts\PaymentIntentRepositoryContract;
use App\Repositories\Contracts\TokenPackageRepositoryContract;
use App\Repositories\Contracts\UserTokenWalletRepositoryContract;
use App\Repositories\PaymentIntentRepository;
use App\Repositories\TokenPackageRepository;
use App\Repositories\UserTokenWalletRepository;
use App\Services\AiAnalysisOrderService;
use App\Services\AiInsightService;
use App\Services\AiSettingsService;
use App\Services\Contracts\AiAnalysisOrderServiceContract;
use App\Services\Contracts\AiInsightServiceContract;
use App\Services\Contracts\AiSettingsServiceContract;
use App\Services\Contracts\PaymentGatewayContract;
use App\Services\Contracts\PaymentWebhookServiceContract;
use App\Services\Contracts\TokenWalletServiceContract;
use App\Services\PaymentWebhookService;
use App\Services\TokenWalletService;
use App\Support\Payment\PaymentGatewayResolver;
use App\Support\TypeCast;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider ai service provider.
 */
class AiServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы контейнера.
     */
    public function register(): void
    {
        $this->app->bind(AiToolResultRepositoryContract::class, AiToolResultRepository::class);
        $this->app->bind(AiSettingsRepositoryContract::class, AiSettingsRepository::class);
        $this->app->singleton(AiSettingsServiceContract::class, AiSettingsService::class);
        $this->app->bind(AiAnalysisOrderRepositoryContract::class, AiAnalysisOrderRepository::class);
        $this->app->singleton(UserTokenWalletRepositoryContract::class, UserTokenWalletRepository::class);
        $this->app->bind(TokenPackageRepositoryContract::class, TokenPackageRepository::class);
        $this->app->bind(PaymentIntentRepositoryContract::class, PaymentIntentRepository::class);
        $this->app->bind(AiInsightServiceContract::class, AiInsightService::class);
        $this->registerPaymentGateway();
        $this->app->bind(PaymentWebhookServiceContract::class, PaymentWebhookService::class);
        $this->app->bind(TokenWalletServiceContract::class, TokenWalletService::class);
        $this->app->bind(AiAnalysisOrderServiceContract::class, AiAnalysisOrderService::class);
    }

    /**
     * Регистрирует реализацию payment gateway по config('payment.gateway').
     */
    private function registerPaymentGateway(): void
    {
        $implementation = PaymentGatewayResolver::resolveImplementation(
            TypeCast::string(config('payment.gateway', 'mock')),
            $this->app->environment(),
        );
        $this->app->bind(PaymentGatewayContract::class, $implementation);
    }
}
