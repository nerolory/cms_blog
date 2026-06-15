<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\Tag;
use App\Repositories\Contracts\TagRepositoryContract;
use Illuminate\Support\Collection;

/**
 * Репозиторий tag.
 *
 * @property-read Tag $tag
 * @property-read Post $post
 */
class TagRepository implements TagRepositoryContract
{
    public function __construct(protected Tag $tag, protected Post $post) {}

    /**
     * all ordered.
     */ /**
     * Возвращает записи в порядке сортировки.
     *
     * @return Collection<int, Tag>
     */
    public function allOrdered(): Collection
    {
        return $this->tag->newQuery()->orderBy('name')->get();
    }

    /**
     * Находит by id.

     *
     * @return ?Tag
     */
    public function findById(int $id): ?Tag
    {
        return $this->tag->newQuery()->find($id);
    }

    /**
     * Создаёт .

     *
     * @return Tag
     */
    public function create(string $name, string $slug): Tag
    {
        return $this->tag->newQuery()->create(['name' => $name, 'slug' => $slug]);
    }

    /**
     * Обновляет .

     *
     * @return Tag
     */
    public function update(Tag $tag, string $name, string $slug): Tag
    {
        $tag->update(['name' => $name, 'slug' => $slug]);

        return $tag->fresh() ?? $tag;
    }

    /**
     * Удаляет .

     *
     * @return bool
     */
    public function delete(Tag $tag): bool
    {
        return (bool) $tag->delete();
    }

    /**
     * Синхронизирует теги поста.
     *
     * @param  Collection<int, int>  $tagIds
     */
    public function syncForPost(int $postId, Collection $tagIds): void
    {
        $post = $this->post->newQuery()->findOrFail($postId);
        $post->tags()->sync($tagIds->unique()->values()->all());
    }
}
