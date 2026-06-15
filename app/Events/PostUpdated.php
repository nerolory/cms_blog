<?php

namespace App\Events;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Класс post updated.

 *
 * @property-read Post $post
 * @property-read User $actor
 */
final class PostUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Post $post, public User $actor) {}
}
