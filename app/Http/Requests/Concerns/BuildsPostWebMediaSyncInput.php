<?php

namespace App\Http\Requests\Concerns;

use App\DTO\PostWebMediaSyncInput;

/**
 * Собирает DTO синхронизации медиа из validated web-формы поста.
 */
trait BuildsPostWebMediaSyncInput
{
    /**
     * DTO featured/background для syncWebMedia.
     *
     * @return PostWebMediaSyncInput
     */
    public function webMediaSyncInput(): PostWebMediaSyncInput
    {
        return new PostWebMediaSyncInput(featuredImage: $this->file('featured_image'),
            backgroundImage: $this->file('background_image'),
            removeFeaturedImage: $this->shouldRemoveFeaturedImage(),
            removeBackgroundImage: $this->shouldRemoveBackgroundImage());
    }
}
