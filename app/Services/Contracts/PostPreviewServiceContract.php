<?php

namespace App\Services\Contracts;

use App\DTO\PostData;
use App\DTO\PostPreviewMediaInput;
use App\Models\Post;
use App\Models\User;
use App\Presenters\PostPreviewPresenter;

/**
 * Контракт сервиса post preview.
 */
interface PostPreviewServiceContract
{
    /**
     * Сохраняет preview и возвращает подписанный URL для просмотра.
     *
     * @return string
     */
    public function store(PostData $data, User $actor, ?Post $post, string $backUrl,
        PostPreviewMediaInput $media): string;

    /**
     * Resolves a cached preview for display.

     *
     * @return PostPreviewPresenter
     */
    public function resolve(string $token, User $viewer): PostPreviewPresenter;

    /**
     * Removes expired temporary preview media from storage.

     *
     * @return int
     */
    public function cleanupExpiredMedia(): int;
}
