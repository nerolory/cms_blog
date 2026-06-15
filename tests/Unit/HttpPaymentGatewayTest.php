<?php

namespace Tests\Unit;

use App\DTO\PaymentChargeData;
use App\Services\Payment\HttpPaymentGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * HTTP payment gateway: charge через внешний REST API.
 */
class HttpPaymentGatewayTest extends TestCase
{
    /**
     * test charge returns success when api responds ok.
     */
    public function test_charge_returns_success_when_api_responds_ok(): void
    {
        config(['payment.http.base_url' => 'https://payments.test', 'payment.http.secret' => 'secret',
            'payment.http.timeout' => 5]);
        Http::fake([
            'https://payments.test/charges' => Http::response([
                'successful' => true,
                'transaction_id' => 'pay_123',
            ], 200),
        ]);
        $charge = new PaymentChargeData(userId: 1, amountCents: 499, currency: 'RUB',
            description: 'Starter pack', reference: 'token_package:1');
        $result = app(HttpPaymentGateway::class)->charge($charge);
        $this->assertTrue($result->successful);
        $this->assertSame('pay_123', $result->transactionId);
        $this->assertSame('http', $result->gateway);
    }

    /**
     * test charge fails when base url missing.
     */
    public function test_charge_fails_when_base_url_missing(): void
    {
        config(['payment.http.base_url' => '']);
        $charge = new PaymentChargeData(userId: 1, amountCents: 100, currency: 'RUB', description: 'Test',
            reference: 'token_package:1');
        $result = app(HttpPaymentGateway::class)->charge($charge);
        $this->assertFalse($result->successful);
        $this->assertSame('http', $result->gateway);
    }
}
