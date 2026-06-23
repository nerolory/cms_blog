<?php

namespace App\Filament\Pages;

use App\DTO\MailSettingsData;
use App\Services\Contracts\MailSettingsServiceContract;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
 * Компонент Filament mail settings page.
 *
 * @property Schema $form
 * @property-read MailSettingsServiceContract $mailSettingsService
 */
class MailSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected MailSettingsServiceContract $mailSettingsService;

    /**
     * Внедряет сервис настроек почты для form().
     */
    public function boot(MailSettingsServiceContract $mailSettingsService): void
    {
        $this->mailSettingsService = $mailSettingsService;
    }

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected static ?string $slug = 'mail-settings';

    protected static ?int $navigationSort = 90;

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
        return __('admin.mail.navigation');
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
        return __('admin.mail.title');
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
     * @param  MailSettingsServiceContract  $mailSettingsService  сервис настроек почты
     */
    public function mount(MailSettingsServiceContract $mailSettingsService): void
    {
        $this->form->fill($mailSettingsService->settings()->toFormState());
    }

    /**
     * save.
     *
     * @param  MailSettingsServiceContract  $mailSettingsService  сервис настроек почты
     */
    public function save(MailSettingsServiceContract $mailSettingsService): void
    {
        /** @var array<string, mixed> $state */
        $state = $this->form->getState();
        $mailSettingsService->saveSettings(MailSettingsData::fromFilament($state));
        Notification::make()->success()->title(__('admin.mail.notifications.saved'))->send();
    }

    /**
     * send test mail.
     *
     * @param  MailSettingsServiceContract  $mailSettingsService  сервис настроек почты
     */
    public function sendTestMail(MailSettingsServiceContract $mailSettingsService): void
    {
        $recipient = auth()->user()?->email;
        if ($recipient === null) {
            return;
        }
        $mailSettingsService->sendTestMessage($recipient);
        Notification::make()->success()->title(__('admin.mail.notifications.test_sent',
            ['email' => $recipient]))->send();
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
        $mailSettingsService = $this->mailSettingsService;

        return $schema->components([
            Section::make(__('admin.mail.sections.general'))
                ->schema([
                    Toggle::make('require_email_verification')
                        ->label(__('admin.mail.fields.require_email_verification')),
                    TextInput::make('from_address')
                        ->label(__('admin.mail.fields.from_address'))
                        ->email()
                        ->required(),
                    TextInput::make('from_name')
                        ->label(__('admin.mail.fields.from_name'))
                        ->required(),
                ]),
            Section::make(__('admin.mail.sections.transport'))
                ->schema([
                    Select::make('mode')
                        ->label(__('admin.mail.fields.mode'))
                        ->options([
                            'preset' => __('admin.mail.modes.preset'),
                            'smtp' => __('admin.mail.modes.smtp'),
                        ])
                        ->required()
                        ->live(),
                    Select::make('preset')
                        ->label(__('admin.mail.fields.preset'))
                        ->options($mailSettingsService->availablePresets()->all())
                        ->visible(fn (callable $get): bool => $get('mode') === 'preset')
                        ->required(fn (callable $get): bool => $get('mode') === 'preset'),
                    TextInput::make('host')
                        ->label(__('admin.mail.fields.host'))
                        ->visible(fn (callable $get): bool => $get('mode') === 'smtp')
                        ->required(fn (callable $get): bool => $get('mode') === 'smtp'),
                    TextInput::make('port')
                        ->label(__('admin.mail.fields.port'))
                        ->numeric()
                        ->visible(fn (callable $get): bool => $get('mode') === 'smtp'),
                    Select::make('scheme')
                        ->label(__('admin.mail.fields.scheme'))
                        ->options([
                            '' => __('admin.mail.schemes.none'),
                            'tls' => 'TLS',
                            'smtps' => 'SMTPS',
                        ])
                        ->visible(fn (callable $get): bool => $get('mode') === 'smtp'),
                    TextInput::make('username')
                        ->label(__('admin.mail.fields.username'))
                        ->visible(fn (callable $get): bool => in_array($get('mode'), ['smtp', 'preset'], true)),
                    TextInput::make('password')
                        ->label(__('admin.mail.fields.password'))
                        ->password()
                        ->revealable()
                        ->autocomplete('current-password')
                        ->visible(fn (callable $get): bool => in_array($get('mode'), ['smtp', 'preset'], true)),
                ]),
        ]);
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
        return Form::make([EmbeddedSchema::make('form')])->id('mail-settings-form')->livewireSubmitHandler('save')
            ->footer([Actions::make([Action::make('save')->label(__('admin.mail.actions.save'))
                ->submit('save'), Action::make('sendTest')->label(__('admin.mail.actions.send_test'))
                ->action('sendTestMail')->color('gray')])]);
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
