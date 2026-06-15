<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Исключение домена payment failed.
 */
class PaymentFailedException extends RuntimeException
{
    /**
     * for reason.

     *
     * @return self
     */
    public static function forReason(string $reason): self
    {
        return new self(__('tokens.errors.payment_failed', ['reason' => $reason]));
    }
}
