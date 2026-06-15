<?php

namespace App\DTO;

use App\Support\TypeCast;

/**
 * DTO site template theme.

 *
 * @property-read string $slug
 * @property-read string $name
 * @property-read ?string $bootstrapTheme
 * @property-read ?string $bodyClass
 * @property-read ?string $cssEntry
 * @property-read bool $isDefault
 */
readonly class SiteTemplateThemeData
{
    public function __construct(public string $slug, public string $name, public ?string $bootstrapTheme = null,
        public ?string $bodyClass = null, public ?string $cssEntry = null, public bool $isDefault = false) {}

    /**
     * from array.
     *
     * @param  array<string, mixed>  $data  данные формы

     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(slug: TypeCast::string($data['slug'] ?? ''), name: TypeCast::string($data['name'] ?? ''),
            bootstrapTheme: TypeCast::nullableString($data['bootstrap_theme'] ?? null),
            bodyClass: TypeCast::nullableString($data['body_class'] ?? null),
            cssEntry: TypeCast::nullableString($data['css_entry'] ?? null),
            isDefault: (bool) ($data['is_default'] ?? false));
    }
}
