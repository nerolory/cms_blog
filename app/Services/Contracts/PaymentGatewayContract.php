<?php

namespace App\Services\Contracts;

use App\DTO\PaymentChargeData;
use App\DTO\PaymentResultData;

/**
 * Контракт сервиса payment gateway.
 */
interface PaymentGatewayContract
{
    /**
     * charge.

     *
     * @return PaymentResultData
     */
    public function charge(PaymentChargeData $charge): PaymentResultData;
}
