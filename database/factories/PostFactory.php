<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * Фабрика модели Post.
 *
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Возвращает определение фабрики.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(mt_rand(3, 6));
        $slugBase = Str::slug($title);
        $slug = $slugBase.$this->faker->unique()->numberBetween(1000, 99999);

        return ['title' => $title, 'slug' => $slug, 'excerpt' => $this->faker->paragraph(mt_rand(1, 4)),
            'body' => collect(range(1, mt_rand(2, 4)))->map(fn (): string => $this->faker->paragraph(mt_rand(3,
                6)))->implode("\n\n"), 'status' => PostStatus::Published, 'visibility' => PostVisibility::Guest->value,
            'required_permission_id' => null, 'rejection_reason' => null, 'is_published' => true,
            'content_opacity' => 100, 'editor_mode' => 'simple',
            'published_at' => $this->faker->dateTimeBetween('-30 days', 'now'), 'user_id' => null,
            'category_id' => fn () => Category::query()->value('id') ?? Category::factory()];
    }

    /**
     * published.

     *
     * @return static
     */
    public function published(): static
    {
        return $this->state(fn (): array => ['status' => PostStatus::Published,
            'visibility' => PostVisibility::Guest->value, 'required_permission_id' => null, 'is_published' => true,
            'published_at' => now(), 'rejection_reason' => null]);
    }

    /**
     * draft.

     *
     * @return static
     */
    public function draft(): static
    {
        return $this->state(fn (): array => ['status' => PostStatus::Draft, 'is_published' => false,
            'published_at' => null, 'rejection_reason' => null]);
    }

    /**
     * pending moderation.

     *
     * @return static
     */
    public function pendingModeration(): static
    {
        return $this->state(fn (): array => ['status' => PostStatus::PendingModeration, 'is_published' => false,
            'published_at' => null, 'rejection_reason' => null]);
    }

    /**
     * rejected.

     *
     * @return static
     */
    public function rejected(): static
    {
        return $this->state(fn (): array => ['status' => PostStatus::Rejected, 'is_published' => false,
            'published_at' => null, 'rejection_reason' => $this->faker->sentence()]);
    }

    /**
     * authenticated visibility.

     *
     * @return static
     */
    public function authenticatedVisibility(): static
    {
        return $this->state(fn (): array => ['visibility' => PostVisibility::Authenticated->value,
            'required_permission_id' => null]);
    }

    /**
     * admin visibility.

     *
     * @return static
     */
    public function adminVisibility(): static
    {
        return $this->state(fn (): array => ['visibility' => PostVisibility::Admin->value,
            'required_permission_id' => null]);
    }

    /**
     * with permission visibility.

     *
     * @return static
     */
    public function withPermissionVisibility(string $permissionName): static
    {
        return $this->state(function () use ($permissionName): array {
            $permissionId = Permission::query()->where('name', $permissionName)->value('id');

            return ['visibility' => PostVisibility::Permission->value, 'required_permission_id' => $permissionId];
        });
    }
}
