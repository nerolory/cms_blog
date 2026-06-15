<?php

namespace Database\Seeders;

use App\DTO\SiteTemplateData;
use App\DTO\SiteTemplateThemeData;
use App\Repositories\Contracts\SiteTemplateRepositoryContract;
use App\Services\Contracts\SiteTemplateServiceContract;
use Illuminate\Database\Seeder;

/**
 * Default site template and themes (parity with legacy UserTheme UX).
 */
class SiteTemplateSeeder extends Seeder
{
    /**
     * run.
     */
    public function run(): void
    {
        $repository = app(SiteTemplateRepositoryContract::class);
        if ($repository->findBySlug('default') !== null) {
            return;
        }
        $service = app(SiteTemplateServiceContract::class);
        $template = $service->createTemplate(new SiteTemplateData(slug: 'default', name: 'Default',
            viewPrefix: 'themes.default', isDefault: true));
        $themes = [new SiteTemplateThemeData(slug: 'default', name: 'Default', bootstrapTheme: null, bodyClass: '',
            isDefault: true), new SiteTemplateThemeData(slug: 'light', name: 'Light', bootstrapTheme: 'light',
                bodyClass: ''), new SiteTemplateThemeData(slug: 'dark', name: 'Dark', bootstrapTheme: 'dark',
                    bodyClass: ''), new SiteTemplateThemeData(slug: 'minimal', name: 'Minimal', bootstrapTheme: 'light',
                        bodyClass: 'theme-minimal')];
        foreach ($themes as $theme) {
            $service->createTheme($template, $theme);
        }
        $service->setActiveTemplate($template);
    }
}
