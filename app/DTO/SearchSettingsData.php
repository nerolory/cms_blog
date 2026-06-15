<?php

namespace App\DTO;

use App\Enums\SearchDriver;
use App\Support\TypeCast;

/**
 * DTO search settings.

 *
 * @property-read SearchDriver $driver
 */
readonly class SearchSettingsData extends AbstractData
{
    public function __construct(public SearchDriver $driver) {}

    /**
     * Builds DTO from Filament form state (array only at UI boundary).
     *
     * @param  array<string, mixed>  $state
     * @return self
     */
    public static function fromFilament(array $state): self
    {
        return new self(driver: SearchDriver::tryFromString(TypeCast::nullableString($state['driver'] ?? null)));
    }

    /**
     * to form state.
     *
     * @return array<string, mixed>
     */
    public function toFormState(): array
    {
        return ['driver' => $this->driver->value];
    }
}
