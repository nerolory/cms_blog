<?php

namespace App\Filament\Pages;

use App\DTO\SiteSettingsData;
use App\Services\Contracts\SiteSettingsServiceContract;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Страница настроек брендинга сайта (название в шапке и админке).
 *
 * @property Schema $form
 * @property-read SiteSettingsServiceContract $siteSettingsService
 */
class SiteSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected SiteSettingsServiceContract $siteSettingsService;

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected static ?string $slug = 'site-settings';

    protected static ?int $navigationSort = 88;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * Внедряет сервис настроек сайта для form().
     */
    public function boot(SiteSettingsServiceContract $siteSettingsService): void
    {
        $this->siteSettingsService = $siteSettingsService;
    }

    /**
     * Возвращает navigation label.
     *
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.site.navigation');
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
        return __('admin.site.title');
    }

    /**
     * Проверяет возможность access.
     *
     * @return bool
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->can('settings.manage');
    }

    /**
     * mount.
     *
     * @param  SiteSettingsServiceContract  $siteSettingsService
     */
    public function mount(SiteSettingsServiceContract $siteSettingsService): void
    {
        $this->form->fill($siteSettingsService->settings()->toFormState());
    }

    /**
     * save.
     *
     * @param  SiteSettingsServiceContract  $siteSettingsService
     */
    public function save(SiteSettingsServiceContract $siteSettingsService): void
    {
        /** @var array<string, mixed> $state */
        $state = $this->form->getState();
        $siteSettingsService->saveSettings(SiteSettingsData::fromFilament($state));
        Notification::make()->success()->title(__('admin.site.notifications.saved'))->send();
    }

    /**
     * default form.
     *
     * @param  Schema  $schema
     * @return Schema
     */
    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    /**
     * form.
     *
     * @param  Schema  $schema
     * @return Schema
     */
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.site.sections.branding'))
                ->description(__('admin.site.sections.branding_help'))
                ->schema([
                    TextInput::make('site_name')
                        ->label(__('admin.site.fields.site_name'))
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('admin.site.fields.site_name_help')),
                ]),
        ]);
    }

    /**
     * content.
     *
     * @param  Schema  $schema
     * @return Schema
     */
    public function content(Schema $schema): Schema
    {
        return $schema->components([$this->getFormContentComponent()]);
    }

    /**
     * Возвращает form content component.
     *
     * @return Component
     */
    protected function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('site-settings-form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label(__('admin.site.actions.save'))
                        ->submit('save'),
                ]),
            ]);
    }

    /**
     * Возвращает header actions.
     *
     * @return array<string, mixed>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
