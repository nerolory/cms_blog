<?php

namespace App\Providers\Filament;

use App\Filament\Avatar\LocalAvatarProvider;
use App\Filament\Widgets\ModerationSlaWidget;
use App\Http\Middleware\SetLocale;
use App\Services\Contracts\MailSettingsServiceContract;
use App\Services\Contracts\SiteSettingsServiceContract;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use App\Support\Database\SchemaInspector;
use Illuminate\Contracts\View\View;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Service provider admin panel provider.
 */
class AdminPanelProvider extends PanelProvider
{
    /**
     * panel.

     *
     * @return Panel
     */
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName(fn (): string => $this->app->make(SiteSettingsServiceContract::class)->siteName())
            ->defaultAvatarProvider(LocalAvatarProvider::class)
            ->login()
            ->colors(['primary' => Color::Amber])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([AccountWidget::class, ModerationSlaWidget::class])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup(__('admin.navigation.access'))
                    ->navigationLabel(__('admin.roles.navigation')),
            ])
            ->authMiddleware([Authenticate::class])
            ->userMenuItems([
                MenuItem::make()
                    ->label(__('layout.nav.public_site'))
                    ->url(fn (): string => route('home'))
                    ->icon('heroicon-o-globe-alt')
                    ->sort(-10),
            ])
            ->renderHook(
                PanelsRenderHook::SIDEBAR_LOGO_AFTER,
                fn (): View => view('filament.hooks.public-site-link'),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
                fn (): View => view('filament.hooks.public-site-link'),
            );
        if ($this->shouldEnableFilamentEmailVerification()) {
            $panel->emailVerification();
        }

        return $panel;
    }

    private function shouldEnableFilamentEmailVerification(): bool
    {
        try {
            if (! SchemaInspector::hasSettingsTable()) {
                return (bool) config('mail-module.require_email_verification_default');
            }
        } catch (\Throwable) {
            return (bool) config('mail-module.require_email_verification_default');
        }

        /** @var MailSettingsServiceContract $mailSettingsService */
        $mailSettingsService = $this->app->make(MailSettingsServiceContract::class);

        return $mailSettingsService->isEmailVerificationRequired();
    }
}
