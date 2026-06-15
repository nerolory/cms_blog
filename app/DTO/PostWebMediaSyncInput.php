<?php

namespace App\DTO;

use Illuminate\Http\UploadedFile;

/**
 * Входные данные синхронизации featured/background медиа из web-формы.
 *
 * @property-read ?UploadedFile $featuredImage
 * @property-read ?UploadedFile $backgroundImage
 * @property-read bool $removeFeaturedImage
 * @property-read bool $removeBackgroundImage
 */
readonly class PostWebMediaSyncInput
{
    public function __construct(public ?UploadedFile $featuredImage = null,
        public ?UploadedFile $backgroundImage = null, public bool $removeFeaturedImage = false,
        public bool $removeBackgroundImage = false) {}
}
