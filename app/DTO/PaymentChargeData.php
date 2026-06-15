<?php

namespace App\DTO;

/**
 * DTO payment charge.

 *
 * @property-read int $userId
 * @property-read int $amountCents
 * @property-read string $currency
 * @property-read string $description
 * @property-read string $reference
 */
readonly class PaymentChargeData
{
    public function __construct(public int $userId, public int $amountCents, public string $currency,
        public string $description, public string $reference) {}
}
