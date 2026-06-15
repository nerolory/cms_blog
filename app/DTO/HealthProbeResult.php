<?php

namespace App\DTO;

/**
 * DTO health probe result.
 *
 * @property-read string $name
 * @property-read string $status
 * @property-read ?string $message
 * @property-read ?float $latencyMs
 */
readonly class HealthProbeResult extends AbstractData
{
    public function __construct(public string $name, public string $status, public ?string $message = null,
        public ?float $latencyMs = null) {}

    /**
     * Проверяет healthy.

     *
     * @return bool
     */
    public function isHealthy(): bool
    {
        return $this->status === 'ok';
    }

    /**
     * to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['name' => $this->name, 'status' => $this->status, 'message' => $this->message,
            'latency_ms' => $this->latencyMs];
    }
}
