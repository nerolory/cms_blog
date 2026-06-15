<?php

namespace App\Jobs;

use App\Services\Contracts\PostViewServiceContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Задача очереди persist post view counts job.
 */
class PersistPostViewCountsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Обрабатывает запрос или задачу.
     */
    public function handle(PostViewServiceContract $views): void
    {
        $views->flushPendingCounts();
    }
}
