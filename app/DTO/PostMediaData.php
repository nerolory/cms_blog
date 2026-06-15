<?php

namespace App\DTO;

/**
 * DTO post media.

 *
 * @property-read ?string $featuredImagePath
 * @property-read ?string $backgroundImagePath
 */
readonly class PostMediaData
{
    public function __construct(public ?string $featuredImagePath, public ?string $backgroundImagePath) {}
}
