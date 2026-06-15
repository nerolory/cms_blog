<?php

namespace App\Listeners;

use App\Events\PostSubmitted;
use App\Notifications\PostSubmittedForModerationNotification;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Support\Logging\StructuredLogContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Обработчик события record post submitted listener.
 *
 * @property-read UserRepositoryContract $users
 */
final class RecordPostSubmittedListener implements ShouldQueue
{
    public function __construct(private UserRepositoryContract $users) {}

    /**
     * Обрабатывает запрос или задачу.

     *
     * @return bool
     */
    public function handle(PostSubmitted $event): bool
    {
        Log::info('Post submitted for moderation.', StructuredLogContext::forPostEvent(post: $event->post,
            actor: $event->actor, event: 'post.submitted'));
        foreach ($this->users->moderatorsForNotification() as $moderator) {
            if ($moderator->id === $event->actor->id) {
                continue;
            }
            $moderator->notify(new PostSubmittedForModerationNotification($event->post));
        }

        return true;
    }
}
