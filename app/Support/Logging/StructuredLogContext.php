<?php

namespace App\Support\Logging;

use App\Http\Middleware\AssignRequestId;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Вспомогательный класс structured log context.
 */
final class StructuredLogContext
{
    /**
     * for post event.
     *
     * @return array<string, mixed>
     */
    public static function forPostEvent(Post $post, User $actor, string $event): array
    {
        return ['event' => $event, 'post_id' => $post->id, 'post_slug' => $post->slug,
            'post_status' => $post->status->value, 'actor_id' => $actor->id, 'actor_email' => $actor->email,
            'request_id' => self::requestId()];
    }

    /**
     * for.
     *
     * @return array<string, mixed>
     */
    public static function forRequest(Request $request): array
    {
        return ['request_id' => self::requestId($request), 'method' => $request->method(), 'path' => $request->path(),
            'user_id' => $request->user()?->id];
    }

    private static function requestId(?Request $request = null): ?string
    {
        $request ??= request();
        if ($request === null) {
            return null;
        }
        $value = $request->attributes->get(AssignRequestId::ATTRIBUTE);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
