<?php

namespace App\DTO;

use App\Support\TypeCast;

/**
 * DTO настроек брендинга сайта.
 *
 * @property-read string $siteName
 */
readonly class SiteSettingsData extends AbstractData
{
    public function __construct(public string $siteName) {}

    /**
     * Собирает DTO из состояния формы Filament.
     *
     * @param  array<string, mixed>  $state
     * @return self
     */
    public static function fromFilament(array $state): self
    {
        return new self(
            siteName: TypeCast::string($state['site_name'] ?? ''),
        );
    }

    /**
     * to form state.
     *
     * @return array<string, mixed>
     */
    public function toFormState(): array
    {
        return ['site_name' => $this->siteName];
    }
}
