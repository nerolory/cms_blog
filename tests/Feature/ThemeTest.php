<?php

namespace Tests\Feature;

use App\Enums\UserTheme;
use App\Models\User;
use Database\Seeders\SiteTemplateSeeder;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

/**
 * Класс theme.
 */
class ThemeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Подготавливает окружение теста.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteTemplateSeeder::class);
    }

    /**
     * test profile accepts each theme.
     */
    #[DataProvider('themeProvider')]
    public function test_profile_accepts_each_theme(string $theme): void
    {
        $user = User::factory()->create(['theme' => UserTheme::Default->value]);
        $this->actingAs($user)->patch(route('profile.update'), ['name' => $user->name, 'email' => $user->email,
            'password' => '', 'password_confirmation' => '', 'theme' => $theme])->assertRedirect(route('profile.edit'));
        $this->assertSame($theme, $user->fresh()?->theme);
    }

    /**
     * Поставщик данных для data provider.
     *
     * @return array<string, mixed>
     */
    public static function themeProvider(): array
    {
        return ['default' => [UserTheme::Default->value], 'light' => [UserTheme::Light->value],
            'dark' => [UserTheme::Dark->value], 'minimal' => [UserTheme::Minimal->value]];
    }
}
