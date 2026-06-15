<?php

namespace App\Listeners;

use App\Events\PostPublished;
use App\Support\Logging\StructuredLogContext;
use Illuminate\Support\Facades\Log;

/**
 * Обработчик события record post published listener.
 */
final class RecordPostPublishedListener
{
    /**
     * Обрабатывает запрос или задачу.

     *
     * @return bool
     */
    public function handle(PostPublished $event): bool
    {
        Log::info('Post published.', StructuredLogContext::forPostEvent(post: $event->post, actor: $event->actor,
            event: 'post.published'));

        return true;
    }
}
