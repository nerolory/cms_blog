<?php

namespace App\Listeners;

use App\Events\PostUpdated;
use App\Support\Logging\StructuredLogContext;
use Illuminate\Support\Facades\Log;

/**
 * Обработчик события record post updated listener.
 */
final class RecordPostUpdatedListener
{
    /**
     * Обрабатывает запрос или задачу.

     *
     * @return bool
     */
    public function handle(PostUpdated $event): bool
    {
        Log::info('Post updated.', StructuredLogContext::forPostEvent(post: $event->post, actor: $event->actor,
            event: 'post.updated'));

        return true;
    }
}
