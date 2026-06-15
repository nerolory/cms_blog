<?php

namespace Tests\Unit;

use App\Models\SiteTemplate;
use App\Repositories\Contracts\SiteTemplateRepositoryContract;
use App\Services\Contracts\SiteTemplateServiceContract;
use Database\Seeders\SiteTemplateSeeder;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

/**
 * Класс site template service.
 */
class SiteTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * test resolve layout falls back when template view missing.
     */
    public function test_resolve_layout_falls_back_when_template_view_missing(): void
    {
        $this->seed(SiteTemplateSeeder::class);
        $template = SiteTemplate::query()->create(['slug' => 'missing-views', 'name' => 'Missing Views',
            'view_prefix' => 'themes.nonexistent', 'is_active' => false, 'is_default' => false]);
        app(SiteTemplateRepositoryContract::class)->markAsActive($template);
        $layout = app(SiteTemplateServiceContract::class)->resolveLayout('app');
        $this->assertSame('themes.default.layouts.app', $layout);
    }

    /**
     * test resolve layout falls back guest to default app.
     */
    public function test_resolve_layout_falls_back_guest_to_default_app(): void
    {
        $this->seed(SiteTemplateSeeder::class);
        $layout = app(SiteTemplateServiceContract::class)->resolveLayout('guest');
        $this->assertSame('themes.default.layouts.app', $layout);
    }

    /**
     * test resolve layout uses active template view when present.
     */
    public function test_resolve_layout_uses_active_template_view_when_present(): void
    {
        $this->seed(SiteTemplateSeeder::class);
        $layout = app(SiteTemplateServiceContract::class)->resolveLayout('app');
        $this->assertSame('themes.default.layouts.app', $layout);
    }
}
