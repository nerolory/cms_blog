<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Детерминированные фикстуры для Playwright E2E (dirty-state и auth).
 */
class E2eFixturesSeeder extends Seeder
{
    public const AUTHOR_EMAIL = 'author1@example.com';

    public const AUTHOR_PASSWORD = 'password';

    public const DIRTY_STATE_POST_SLUG = 'e2e-dirty-state';

    /**
     * Создаёт автора и пост с фиксированным slug для E2E.
     */
    public function run(): void
    {
        $this->call([RolePermissionSeeder::class, MailSettingsSeeder::class, SiteTemplateSeeder::class,
            CategorySeeder::class]);

        /** @var User $author */
        $author = User::query()->updateOrCreate(['email' => self::AUTHOR_EMAIL], ['name' => 'E2E Author',
            'password' => self::AUTHOR_PASSWORD, 'email_verified_at' => now(),
            'account_status' => AccountStatus::Active]);
        if (! $author->hasRole('user')) {
            $author->assignRole('user');
        }

        $categoryId = Category::query()->value('id');
        Post::query()->updateOrCreate(['slug' => self::DIRTY_STATE_POST_SLUG], [
            'title' => 'E2E dirty state fixture',
            'excerpt' => 'Fixture excerpt for Playwright dirty-state tests.',
            'body' => 'Fixture body content for Playwright dirty-state tests.',
            'user_id' => $author->id,
            'category_id' => $categoryId,
            'status' => PostStatus::PendingModeration->value,
            'visibility' => 'guest',
            'is_published' => false,
            'editor_mode' => 'simple',
        ]);
    }
}
