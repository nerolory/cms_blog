<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Класс post approved notification.
 *
 * @property-read Post $post
 */
class PostApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Post $post) {}

    /**
     * via.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * to mail.

     *
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject(__('notifications.post_approved.subject',
            ['title' => $this->post->title]))->line(__('notifications.post_approved.line1',
                ['title' => $this->post->title]))->action(__('notifications.post_approved.action'), route('posts.show',
                    $this->post));
    }
}
