<?php

namespace App\Http\Requests\Concerns;

use App\Models\Post;

/**
 * Загрузка поста из параметра маршрута postId (API, вариант A).
 */
trait ResolvesPostFromApiRoute
{
    /**
     * post from api route.

     *
     * @return Post
     */
    protected function postFromApiRoute(): Post
    {
        $postId = (int) $this->route('postId');

        return $this->postService()->getPostById($postId);
    }
}
