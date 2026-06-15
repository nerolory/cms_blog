<?php

namespace App\Http\Resources;

use App\Enums\PostVisibility;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JSON-представление поста для API.
 *
 * @mixin Post
 */
class PostResource extends JsonResource
{
    /**
     * to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'title' => $this->title, 'slug' => $this->slug, 'excerpt' => $this->excerpt,
            'body' => $this->body, 'status' => $this->status->value, 'visibility' => $this->visibility,
            'required_permission' => $this->when($this->visibility === PostVisibility::Permission->value,
                fn (): ?string => $this->requiredPermission?->name), 'author' => $this->whenLoaded('user',
                    fn (): ?array => $this->user === null ? null : ['id' => $this->user->id,
                        'name' => $this->user->name]),
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String()];
    }
}
