<?php

namespace App\Services\Contracts;

use App\DTO\PostAiInsights;
use App\Models\Post;
use App\Models\User;

/**
 * Контракт сервиса ai insight.
 */
interface AiInsightServiceContract
{
    /**
     * for post.
     *
     * @param  Post  $post  пост

     * @return PostAiInsights
     */
    public function forPost(Post $post, ?User $viewer = null): PostAiInsights;
}
