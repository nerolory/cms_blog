<?php

namespace App\DTO;

/**
 * DTO operational probe.

 *
 * @property-read bool $ok
 * @property-read ?string $message
 * @property-read ?float $latencyMs
 */
readonly class OperationalProbeData
{
    public function __construct(public bool $ok, public ?string $message = null, public ?float $latencyMs = null) {}
}
