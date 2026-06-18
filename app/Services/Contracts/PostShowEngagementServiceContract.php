<?php

namespace App\Services\Contracts;

use App\Models\Post;
use App\Models\User;

/**
 * Рендер HTML-фрагмента engagement-блока поста.
 */
interface PostShowEngagementServiceContract
{
    /**
     * Содержимое внутри data-post-engagement-app (без обёртки).
     */
    public function renderAppInnerHtml(Post $post, ?User $viewer): string;
}
