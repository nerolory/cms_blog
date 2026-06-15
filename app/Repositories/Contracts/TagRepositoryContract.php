<?php

namespace App\Repositories\Contracts;

use App\Models\Tag;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория tag.
 */
interface TagRepositoryContract
{
    /**
     * all ordered.
     */
    /**
     * all ordered.
     */
    /**
     * Возвращает записи в порядке сортировки.
     *
     * @return Collection<int, Tag>
     */
    public function allOrdered(): Collection;

    /**
     * Находит by id.

     *
     * @return ?Tag
     */
    public function findById(int $id): ?Tag;

    /**
     * Создаёт .

     *
     * @return Tag
     */
    public function create(string $name, string $slug): Tag;

    /**
     * Обновляет .

     *
     * @return Tag
     */
    public function update(Tag $tag, string $name, string $slug): Tag;

    /**
     * Удаляет .

     *
     * @return bool
     */
    public function delete(Tag $tag): bool;

    /**
     * Синхронизирует теги поста.
     *
     * @param  Collection<int, int>  $tagIds
     */
    public function syncForPost(int $postId, Collection $tagIds): void;
}
