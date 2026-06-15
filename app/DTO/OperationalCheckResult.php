<?php

namespace App\DTO;

use App\Enums\OperationalService;
use App\Enums\ServiceCriticality;

/**
 * DTO operational check result.

 *
 * @property-read OperationalService $service
 * @property-read bool $ok
 * @property-read ServiceCriticality $criticality
 * @property-read ?string $message
 * @property-read ?float $latencyMs
 */
readonly class OperationalCheckResult
{
    public function __construct(public OperationalService $service, public bool $ok,
        public ServiceCriticality $criticality, public ?string $message = null, public ?float $latencyMs = null) {}

    /**
     * to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['name' => $this->service->value, 'status' => $this->ok ? 'ok' : 'fail',
            'criticality' => $this->criticality->value, 'message' => $this->message, 'latency_ms' => $this->latencyMs];
    }
}
