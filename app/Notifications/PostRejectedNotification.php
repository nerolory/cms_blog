<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Класс post rejected notification.
 *
 * @property-read Post $post
 * @property-read string $reason
 */
class PostRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Post $post, protected string $reason) {}

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
        return (new MailMessage)->subject(__('notifications.post_rejected.subject',
            ['title' => $this->post->title]))->line(__('notifications.post_rejected.line1',
                ['title' => $this->post->title]))->line(__('notifications.post_rejected.reason',
                    ['reason' => $this->reason]));
    }
}
