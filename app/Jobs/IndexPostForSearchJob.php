<?php

namespace App\Jobs;

use App\Services\Contracts\SearchServiceContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Задача очереди index post for search job.
 *
 * @property-read int $postId
 */
class IndexPostForSearchJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $postId) {}

    /**
     * Обрабатывает запрос или задачу.
     */
    public function handle(SearchServiceContract $searchService): void
    {
        $searchService->indexPublishedPostById($this->postId);
    }
}
