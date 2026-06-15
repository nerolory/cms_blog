<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Класс post submitted for moderation notification.
 *
 * @property-read Post $post
 */
class PostSubmittedForModerationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Post $post) {}

    /**
     * Каналы доставки уведомления.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Payload для database-канала.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return ['post_id' => $this->post->id, 'post_title' => $this->post->title, 'post_slug' => $this->post->slug,
            'message' => __('notifications.post_submitted_moderation.message', ['title' => $this->post->title])];
    }
}
