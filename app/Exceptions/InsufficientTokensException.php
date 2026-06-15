<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Исключение домена insufficient tokens.
 */
class InsufficientTokensException extends RuntimeException
{
    /**
     * for balance.

     *
     * @return self
     */
    public static function forBalance(int $required, int $available): self
    {
        return new self(__('tokens.errors.insufficient', ['required' => $required, 'available' => $available]));
    }
}
