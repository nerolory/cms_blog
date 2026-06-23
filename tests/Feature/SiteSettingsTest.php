<?php

namespace Tests\Feature;

use App\DTO\SiteSettingsData;
use App\Filament\Pages\SiteSettingsPage;
use App\Models\Setting;
use App\Models\User;
use App\Services\Contracts\SiteSettingsServiceContract;
use App\Support\Cache\ApplicationCacheKeys;
use App\Support\Site\SiteSettingKey;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\Concerns\RefreshDatabase;
use Tests\Concerns\SeedsRoles;
use Tests\TestCase;

/**
 * Настройки брендинга сайта.
 */
class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;
    use SeedsRoles;

    /**
     * test custom site name appears on public navbar.
     */
    public function test_custom_site_name_appears_on_public_navbar(): void
    {
        Setting::query()->create([
            'key' => SiteSettingKey::SITE_NAME,
            'value' => 'Мой блог',
        ]);
        Cache::forget(ApplicationCacheKeys::SITE_BRANDING);

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('Мой блог', false)
            ->assertDontSee('navbar-brand">Laravel', false);
    }

    /**
     * test admin can save site name and cache is invalidated.
     */
    public function test_admin_can_save_site_name_and_cache_is_invalidated(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $service = app(SiteSettingsServiceContract::class);
        $service->saveSettings(new SiteSettingsData(siteName: 'Старое имя'));
        Cache::put(ApplicationCacheKeys::SITE_BRANDING, ['site_name' => 'Старое имя'], 31_536_000);

        $this->actingAs($admin)
            ->get('/admin/site-settings')
            ->assertOk();

        Livewire::actingAs($admin)
            ->test(SiteSettingsPage::class)
            ->fillForm(['site_name' => 'Новое имя'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Новое имя', $service->siteName());
        $cached = Cache::get(ApplicationCacheKeys::SITE_BRANDING);
        $this->assertIsArray($cached);
        $this->assertSame('Новое имя', $cached['site_name'] ?? null);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Новое имя', false);
    }
}
