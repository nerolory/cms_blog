<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Сидер БД post seeder.
 */
class PostSeeder extends Seeder
{
    /**
     * run.
     */
    public function run(): void
    {
        Post::query()->forceDelete();
        $defaultCategoryId = Category::query()->where('slug', 'general')->value('id') ?? Category::query()->value('id');
        $authors = $this->resolveAuthors();
        $authorIds = $authors->pluck('id')->all();
        $withAuthor = fn (): array => ['user_id' => fake()->randomElement($authorIds),
            'category_id' => $defaultCategoryId];
        Post::factory(25)->published()->state($withAuthor)->create();
        Post::factory(10)->published()->authenticatedVisibility()->state($withAuthor)->create();
        Post::factory(5)->published()->withPermissionVisibility('posts.view.shareholders')->state($withAuthor)
            ->create();
        Post::factory(5)->published()->adminVisibility()->state($withAuthor)->create();
        Post::factory(5)->pendingModeration()->state($withAuthor)->create();
        Post::factory(3)->rejected()->state($withAuthor)->create();
        Post::factory(2)->draft()->state($withAuthor)->create();
    }

    /**
     * @return Collection<int, User>
     */
    private function resolveAuthors(): Collection
    {
        $authors = collect();
        $owner = User::query()->where('email', config('seeding.owner.email'))->first();
        if ($owner !== null) {
            $authors->push($owner);
        }
        for ($i = 1; $i <= 4; $i++) {
            $user = User::query()->firstOrCreate(['email' => "author{$i}@example.com"], ['name' => "Author {$i}",
                'password' => 'password', 'email_verified_at' => now()]);
            if (! $user->hasRole('user')) {
                $user->assignRole('user');
            }
            $authors->push($user);
        }

        return $authors;
    }
}
