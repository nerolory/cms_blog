<?php

namespace App\DTO;

/**
 * DTO payment result.

 *
 * @property-read bool $successful
 * @property-read string $transactionId
 * @property-read string $gateway
 * @property-read ?string $failureReason
 */
readonly class PaymentResultData
{
    public function __construct(public bool $successful, public string $transactionId, public string $gateway,
        public ?string $failureReason = null) {}
}
