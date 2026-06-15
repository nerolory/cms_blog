<?php

namespace App\Filament\Pages;

use App\DTO\SearchSettingsData;
use App\Enums\SearchDriver;
use App\Services\Contracts\SearchSettingsServiceContract;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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
 * Компонент Filament search settings page.
 *
 * @property Schema $form
 * @property-read SearchSettingsServiceContract $searchSettingsService
 */
class SearchSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected SearchSettingsServiceContract $searchSettingsService;

    /**
     * Внедряет сервис настроек поиска для form().
     */
    public function boot(SearchSettingsServiceContract $searchSettingsService): void
    {
        $this->searchSettingsService = $searchSettingsService;
    }

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected static ?string $slug = 'search-settings';

    protected static ?int $navigationSort = 91;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * Возвращает navigation label.

     *
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.search.navigation');
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
        return __('admin.search.title');
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
     * @param  SearchSettingsServiceContract  $searchSettingsService  сервис настроек поиска
     */
    public function mount(SearchSettingsServiceContract $searchSettingsService): void
    {
        $this->form->fill($searchSettingsService->settings()->toFormState());
    }

    /**
     * save.
     *
     * @param  SearchSettingsServiceContract  $searchSettingsService  сервис настроек поиска
     */
    public function save(SearchSettingsServiceContract $searchSettingsService): void
    {
        /** @var array<string, mixed> $state */
        $state = $this->form->getState();
        $searchSettingsService->saveSettings(SearchSettingsData::fromFilament($state));
        Notification::make()->success()->title(__('admin.search.notifications.saved'))->send();
    }

    /**
     * default form.
     *
     * @param  Schema  $schema  схема Filament

     * @return Schema
     */
    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    /**
     * form.
     *
     * @param  Schema  $schema  схема Filament

     * @return Schema
     */
    public function form(Schema $schema): Schema
    {
        $searchSettingsService = $this->searchSettingsService;
        /** @var array<string, string> $driverOptions */
        $driverOptions = $searchSettingsService->availableDrivers()->all();

        return $schema->components([Section::make(__('admin.search.sections.driver'))
            ->description(__('admin.search.sections.driver_help'))->schema([Select::make('driver')
            ->label(__('admin.search.fields.driver'))->options($driverOptions)->required()
            ->helperText(function (callable $get): ?string {
                if (SearchDriver::tryFromString($get('driver')) !== SearchDriver::Database) {
                    return null;
                }

                return (string) __('admin.search.hints.database_not_recommended');
            })])]);
    }

    /**
     * content.
     *
     * @param  Schema  $schema  схема Filament

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
        return Form::make([EmbeddedSchema::make('form')])->id('search-settings-form')->livewireSubmitHandler('save')
            ->footer([Actions::make([Action::make('save')->label(__('admin.search.actions.save'))->submit('save')])]);
    }

    /**
     * Возвращает header actions.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
