<?php

namespace App\DTO;

/**
 * DTO author option.

 *
 * @property-read int $id
 * @property-read string $label
 */
readonly class AuthorOption
{
    public function __construct(public int $id, public string $label) {}
}
