<?php

namespace App\Listeners;

use App\Enums\PostStatus;
use App\Events\PostPublished;
use App\Events\PostUpdated;
use App\Jobs\IndexPostForSearchJob;

/**
 * Обработчик события queue post search index listener.
 */
final class QueuePostSearchIndexListener
{
    /**
     * Обрабатывает запрос или задачу.

     *
     * @return bool
     */
    public function handle(PostPublished|PostUpdated $event): bool
    {
        if ($event->post->status !== PostStatus::Published) {
            return true;
        }
        IndexPostForSearchJob::dispatch($event->post->id);

        return true;
    }
}
