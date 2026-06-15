<?php

namespace App\DTO;

/**
 * DTO seo.

 *
 * @property-read ?string $metaTitle
 * @property-read ?string $metaDescription
 * @property-read ?string $ogImagePath
 * @property-read ?string $canonicalUrl
 * @property-read ?string $robots
 */
readonly class SeoData extends AbstractData
{
    public function __construct(public ?string $metaTitle, public ?string $metaDescription, public ?string $ogImagePath,
        public ?string $canonicalUrl, public ?string $robots) {}

    /**
     * from filament.
     *
     * @param  array<string, mixed>  $data  данные формы

     * @return self
     */
    public static function fromFilament(array $data): self
    {
        return FilamentPostFormState::fromArray($data)->toSeoData();
    }

    /**
     * to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['meta_title' => $this->metaTitle, 'meta_description' => $this->metaDescription,
            'og_image_path' => $this->ogImagePath, 'canonical_url' => $this->canonicalUrl, 'robots' => $this->robots];
    }
}
