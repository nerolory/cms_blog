<?php

namespace App\Presenters;

use App\DTO\PostPreviewData;
use App\Models\Post;

/**
 * Presentation layer for cached post previews (reuses PostPresenter).
 *
 * @property-read PostPreviewData $preview
 * @property-read PostPresenter $postPresenter
 * @property-read Post $post
 */
final readonly class PostPreviewPresenter
{
    public function __construct(private PostPreviewData $preview, private PostPresenter $postPresenter,
        private Post $post) {}

    /**
     * post.

     *
     * @return Post
     */
    public function post(): Post
    {
        return $this->post;
    }

    /**
     * preview.

     *
     * @return PostPreviewData
     */
    public function preview(): PostPreviewData
    {
        return $this->preview;
    }

    /**
     * Проверяет preview.

     *
     * @return bool
     */
    public function isPreview(): bool
    {
        return true;
    }

    /**
     * back url.

     *
     * @return ?string
     */
    public function backUrl(): ?string
    {
        return $this->preview->backUrl;
    }

    /**
     * featured image url.

     *
     * @return ?string
     */
    public function featuredImageUrl(): ?string
    {
        return $this->postPresenter->featuredImageUrl();
    }

    /**
     * background image url.

     *
     * @return ?string
     */
    public function backgroundImageUrl(): ?string
    {
        return $this->postPresenter->backgroundImageUrl();
    }

    /**
     * Проверяет наличие featured image.

     *
     * @return bool
     */
    public function hasFeaturedImage(): bool
    {
        return $this->postPresenter->hasFeaturedImage();
    }

    /**
     * Проверяет наличие background image.

     *
     * @return bool
     */
    public function hasBackgroundImage(): bool
    {
        return $this->postPresenter->hasBackgroundImage();
    }

    /**
     * Проверяет наличие custom theme.

     *
     * @return bool
     */
    public function hasCustomTheme(): bool
    {
        return $this->postPresenter->hasCustomTheme();
    }

    /**
     * theme style string.

     *
     * @return string
     */
    public function themeStyleString(): string
    {
        return $this->postPresenter->themeStyleString();
    }
}
