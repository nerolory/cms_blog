<?php

namespace App\Jobs;

use App\Services\Contracts\ScheduledPublishServiceContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Задача очереди publish scheduled posts job.
 */
class PublishScheduledPostsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Обрабатывает запрос или задачу.
     */
    public function handle(ScheduledPublishServiceContract $scheduledPublishService): void
    {
        $scheduledPublishService->publishDuePosts();
    }
}
