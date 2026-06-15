<?php

namespace App\DTO;

use App\Support\TypeCast;

/**
 * DTO mail settings.

 *
 * @property-read string $mode
 * @property-read ?string $preset
 * @property-read ?string $host
 * @property-read ?int $port
 * @property-read ?string $scheme
 * @property-read ?string $username
 * @property-read ?string $password
 * @property-read string $fromAddress
 * @property-read string $fromName
 * @property-read bool $requireEmailVerification
 */
readonly class MailSettingsData extends AbstractData
{
    public function __construct(public string $mode, public ?string $preset, public ?string $host, public ?int $port,
        public ?string $scheme, public ?string $username, public ?string $password, public string $fromAddress,
        public string $fromName, public bool $requireEmailVerification) {}

    /**
     * Builds DTO from Filament form state (array only at UI boundary).
     *
     * @param  array<string, mixed>  $state
     * @return self
     */
    public static function fromFilament(array $state): self
    {
        $port = $state['port'] ?? null;

        return new self(mode: TypeCast::string($state['mode'] ?? 'preset', 'preset'),
            preset: TypeCast::nullableString($state['preset'] ?? null),
            host: TypeCast::nullableString($state['host'] ?? null),
            port: $port !== null && $port !== '' ? TypeCast::nullableInt($port) : null,
            scheme: TypeCast::nullableString($state['scheme'] ?? null),
            username: TypeCast::nullableString($state['username'] ?? null),
            password: TypeCast::nullableString($state['password'] ?? null),
            fromAddress: TypeCast::string($state['from_address'] ?? config('mail-module.defaults.from_address')),
            fromName: TypeCast::string($state['from_name'] ?? config('mail-module.defaults.from_name')),
            requireEmailVerification: TypeCast::bool($state['require_email_verification'] ?? false));
    }

    /**
     * Converts DTO to Filament form state array.
     *
     * @return array<string, mixed>
     */
    public function toFormState(): array
    {
        return ['mode' => $this->mode, 'preset' => $this->preset, 'host' => $this->host, 'port' => $this->port,
            'scheme' => $this->scheme, 'username' => $this->username, 'password' => null,
            'from_address' => $this->fromAddress, 'from_name' => $this->fromName,
            'require_email_verification' => $this->requireEmailVerification];
    }
}
