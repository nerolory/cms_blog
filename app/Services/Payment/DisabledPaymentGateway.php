<?php

namespace App\Services\Payment;

use App\DTO\PaymentChargeData;
use App\DTO\PaymentResultData;
use App\Services\Contracts\PaymentGatewayContract;

/**
 * Заглушка: платёжный шлюз не настроен или недоступен.
 */
class DisabledPaymentGateway implements PaymentGatewayContract
{
    /**
     * {@inheritdoc}
     */
    public function charge(PaymentChargeData $charge): PaymentResultData
    {
        return new PaymentResultData(successful: false, transactionId: '', gateway: 'disabled',
            failureReason: __('tokens.errors.gateway_unavailable'));
    }
}
