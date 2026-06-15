<?php

namespace App\DTO;

use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO abstract.
 *
 * @implements Arrayable<string, mixed>
 */
abstract readonly class AbstractData implements Arrayable
{
    /**
     * Converts all public properties of the child class into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
