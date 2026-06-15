<?php

namespace App\Filament\Pages;

use App\Jobs\RunSiteHealthCheckJob;
use App\Models\SiteHealthReport;
use App\Services\Contracts\SiteHealthServiceContract;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Компонент Filament site health page.
 */
class SiteHealthPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $slug = 'site-health';

    protected static ?int $navigationSort = 95;

    protected string $view = 'filament.pages.site-health';

    public ?SiteHealthReport $latestReport = null;

    /**
     * Возвращает navigation label.

     *
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.site_health.navigation');
    }

    /**
     * Возвращает navigation group.

     *
     * @return ?string
     */
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.system');
    }

    /**
     * Возвращает title.

     *
     * @return string|Htmlable
     */
    public function getTitle(): string|Htmlable
    {
        return __('admin.site_health.title');
    }

    /**
     * Проверяет возможность access.

     *
     * @return bool
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->can('site.health') || $user->hasRole('owner'));
    }

    /**
     * mount.
     *
     * @param  SiteHealthServiceContract  $siteHealthService  health
     */
    public function mount(SiteHealthServiceContract $siteHealthService): void
    {
        $this->latestReport = $siteHealthService->latestReport();
    }

    /**
     * Возвращает header actions.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [Action::make('runCheck')->label(__('admin.site_health.actions.run'))->action(function (): void {
            $userId = auth()->id();
            RunSiteHealthCheckJob::dispatch(is_int($userId) ? $userId : null);
            Notification::make()->success()->title(__('admin.site_health.notifications.queued'))->send();
        })];
    }
}
