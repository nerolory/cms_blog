<?php

namespace Tests\Unit;

use App\Services\Payment\DisabledPaymentGateway;
use App\Services\Payment\HttpPaymentGateway;
use App\Services\Payment\MockPaymentGateway;
use App\Support\Payment\PaymentGatewayResolver;
use Tests\TestCase;

/**
 * Выбор payment gateway по config и окружению.
 */
class PaymentGatewayResolverTest extends TestCase
{
    /**
     * test mock gateway resolves outside production.
     */
    public function test_mock_gateway_resolves_outside_production(): void
    {
        $this->assertSame(
            MockPaymentGateway::class,
            PaymentGatewayResolver::resolveImplementation('mock', 'local'),
        );
    }

    /**
     * test mock gateway falls back to disabled in production without boot failure.
     */
    public function test_mock_gateway_is_disabled_in_production(): void
    {
        $this->assertSame(
            DisabledPaymentGateway::class,
            PaymentGatewayResolver::resolveImplementation('mock', 'production'),
        );
    }

    /**
     * test http gateway resolves in production.
     */
    public function test_http_gateway_resolves_in_production(): void
    {
        $this->assertSame(
            HttpPaymentGateway::class,
            PaymentGatewayResolver::resolveImplementation('http', 'production'),
        );
    }

    /**
     * test unknown gateway falls back to disabled.
     */
    public function test_unknown_gateway_falls_back_to_disabled(): void
    {
        $this->assertSame(
            DisabledPaymentGateway::class,
            PaymentGatewayResolver::resolveImplementation('stripe', 'local'),
        );
    }

    /**
     * test explicit disabled gateway value.
     */
    public function test_disabled_gateway_value(): void
    {
        $this->assertSame(
            DisabledPaymentGateway::class,
            PaymentGatewayResolver::resolveImplementation('none', 'local'),
        );
    }
}
