<?php

namespace App\DTO;

/**
 * DTO post theme values.

 *
 * @property-read ?string $primaryColor
 * @property-read ?string $accentColor
 * @property-read int $contentOpacity
 */
readonly class PostThemeValues
{
    public function __construct(public ?string $primaryColor, public ?string $accentColor,
        public int $contentOpacity) {}
}
