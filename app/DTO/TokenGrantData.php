<?php

namespace App\DTO;

/**
 * DTO token grant.

 *
 * @property-read int $userId
 * @property-read int $amount
 * @property-read int $grantedByUserId
 * @property-read ?string $note
 */
readonly class TokenGrantData
{
    public function __construct(public int $userId, public int $amount, public int $grantedByUserId,
        public ?string $note = null) {}
}
