<?php

namespace App\Services\Contracts;

/**
 * Контракт сервиса scheduled publish.
 */
interface ScheduledPublishServiceContract
{
    /**
     * publish due posts.

     *
     * @return int
     */
    public function publishDuePosts(): int;
}
