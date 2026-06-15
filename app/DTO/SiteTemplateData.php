<?php

namespace App\DTO;

use App\Support\TypeCast;

/**
 * DTO site template.

 *
 * @property-read string $slug
 * @property-read string $name
 * @property-read string $viewPrefix
 * @property-read bool $isDefault
 */
readonly class SiteTemplateData
{
    public function __construct(public string $slug, public string $name, public string $viewPrefix,
        public bool $isDefault = false) {}

    /**
     * from array.
     *
     * @param  array<string, mixed>  $data  данные формы

     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(slug: TypeCast::string($data['slug'] ?? ''), name: TypeCast::string($data['name'] ?? ''),
            viewPrefix: TypeCast::string($data['view_prefix'] ?? 'themes.default'),
            isDefault: (bool) ($data['is_default'] ?? false));
    }
}
