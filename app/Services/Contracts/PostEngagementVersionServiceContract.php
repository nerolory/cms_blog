<?php

namespace App\Services\Contracts;

/**
 * Версия engagement-блока поста для SSE и SPA-обновлений.
 */
interface PostEngagementVersionServiceContract
{
    /**
     * Текущая версия engagement поста.

     *
     * @return string
     */
    public function get(int $postId): string;

    /**
     * Увеличивает версию и возвращает новое значение.

     *
     * @return string
     */
    public function bump(int $postId): string;
}
