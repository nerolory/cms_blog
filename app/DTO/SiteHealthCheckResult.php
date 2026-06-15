<?php

namespace App\DTO;

use App\Enums\SiteHealthSeverity;
use App\Support\TypeCast;

/**
 * DTO site health check result.

 *
 * @property-read string $name
 * @property-read SiteHealthSeverity $severity
 * @property-read string $message
 * @property-read ?string $details
 */
readonly class SiteHealthCheckResult
{
    public function __construct(public string $name, public SiteHealthSeverity $severity, public string $message,
        public ?string $details = null) {}

    /**
     * to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['name' => $this->name, 'severity' => $this->severity->value, 'message' => $this->message,
            'details' => $this->details];
    }

    /**
     * from array.
     *
     * @param  array<string, mixed>  $data  данные формы

     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(name: TypeCast::string($data['name'] ?? ''),
            severity: SiteHealthSeverity::from(TypeCast::string($data['severity'] ?? 'passed')),
            message: TypeCast::string($data['message'] ?? ''),
            details: TypeCast::nullableString($data['details'] ?? null));
    }
}
