<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryContract;
use Illuminate\Support\Collection;

/**
 * Репозиторий category.
 *
 * @property-read Category $category
 */
class CategoryRepository implements CategoryRepositoryContract
{
    public function __construct(protected Category $category) {}

    /**
     * Возвращает записи в порядке сортировки.
     *
     * @return Collection<int, Category>
     */
    public function allOrdered(): Collection
    {
        return $this->category->newQuery()->orderBy('sort_order')->orderBy('name')->get();
    }

    /**
     * Находит by id.

     *
     * @return ?Category
     */
    public function findById(int $id): ?Category
    {
        return $this->category->newQuery()->find($id);
    }

    /**
     * Находит by slug.

     *
     * @return ?Category
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->category->newQuery()->where('slug', $slug)->first();
    }

    /**
     * Создаёт .
     *
     * @param  int  $sortOrder  order

     * @return Category
     */
    public function create(string $name, string $slug, ?string $description, int $sortOrder): Category
    {
        return $this->category->newQuery()->create(['name' => $name, 'slug' => $slug, 'description' => $description,
            'sort_order' => $sortOrder]);
    }

    /**
     * Обновляет .
     *
     * @param  int  $sortOrder  order

     * @return Category
     */
    public function update(Category $category, string $name, string $slug, ?string $description,
        int $sortOrder): Category
    {
        $category->update(['name' => $name, 'slug' => $slug, 'description' => $description,
            'sort_order' => $sortOrder]);

        return $category->fresh() ?? $category;
    }

    /**
     * Удаляет .

     *
     * @return bool
     */
    public function delete(Category $category): bool
    {
        return (bool) $category->delete();
    }
}
