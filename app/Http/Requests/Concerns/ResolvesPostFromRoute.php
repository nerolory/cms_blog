<?php

namespace App\Http\Requests\Concerns;

use App\Models\Post;

/**
 * Загрузка поста из параметра маршрута postSlug (вариант A).
 */
trait ResolvesPostFromRoute
{
    /**
     * post from route.

     *
     * @return Post
     */
    protected function postFromRoute(): Post
    {
        $slug = (string) $this->route('postSlug');

        return $this->postService()->getPostBySlug($slug);
    }
}
