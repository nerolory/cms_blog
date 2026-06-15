<?php

namespace App\Services\Payment;

use App\DTO\PaymentChargeData;
use App\DTO\PaymentResultData;
use App\Services\Contracts\PaymentGatewayContract;
use Illuminate\Support\Str;

/**
 * Сервис mock payment gateway.
 */
class MockPaymentGateway implements PaymentGatewayContract
{
    /**
     * charge.

     *
     * @return PaymentResultData
     */
    public function charge(PaymentChargeData $charge): PaymentResultData
    {
        return new PaymentResultData(successful: true, transactionId: 'mock_'.Str::uuid()->toString(), gateway: 'mock');
    }
}
