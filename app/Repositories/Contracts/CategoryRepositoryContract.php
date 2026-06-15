<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория category.
 */
interface CategoryRepositoryContract
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
     * @return Collection<int, Category>
     */
    public function allOrdered(): Collection;

    /**
     * Находит by id.

     *
     * @return ?Category
     */
    public function findById(int $id): ?Category;

    /**
     * Находит by slug.

     *
     * @return ?Category
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Создаёт .
     *
     * @param  int  $sortOrder  order

     * @return Category
     */
    public function create(string $name, string $slug, ?string $description, int $sortOrder): Category;

    /**
     * Обновляет .
     *
     * @param  int  $sortOrder  order

     * @return Category
     */
    public function update(Category $category, string $name, string $slug, ?string $description,
        int $sortOrder): Category;

    /**
     * Удаляет .

     *
     * @return bool
     */
    public function delete(Category $category): bool;
}
