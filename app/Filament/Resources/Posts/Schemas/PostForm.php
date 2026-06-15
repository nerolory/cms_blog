<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostEditorMode;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Support\FilamentPostBody;
use App\Support\PostTheme;
use App\Support\TypeCast;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

/**
 * Компонент Filament post form.
 */
class PostForm
{
    /**
     * configure.
     *
     * @param  Schema  $schema  схема Filament

     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([TextInput::make('title')->label(__('admin.posts.fields.title'))
            ->required()->maxLength(255)->columnSpanFull(), TextInput::make('slug')
            ->label(__('admin.posts.fields.slug'))->required()->maxLength(255)
            ->columnSpanFull(), Textarea::make('excerpt')->label(__('admin.posts.fields.excerpt'))
            ->columnSpanFull(), FileUpload::make('featured_image_path')->label(__('admin.posts.fields.featured_image'))
            ->disk('public')->directory('posts/featured')->image()->imageEditor()->nullable()
            ->columnSpanFull(), FileUpload::make('background_image_path')
            ->label(__('admin.posts.fields.background_image'))->disk('public')->directory('posts/background')->image()
            ->imageEditor()->nullable()->columnSpanFull(), ColorPicker::make('theme_primary_color')
            ->label(__('admin.posts.fields.theme_primary_color')), ColorPicker::make('theme_accent_color')
            ->label(__('admin.posts.fields.theme_accent_color')), Slider::make('content_opacity')
            ->label(__('admin.posts.fields.content_opacity'))
            ->range(minValue: PostTheme::MIN_CONTENT_OPACITY, maxValue: PostTheme::MAX_CONTENT_OPACITY)
            ->default(PostTheme::MAX_CONTENT_OPACITY)->step(1), Select::make('editor_mode')
            ->label(__('admin.posts.fields.editor_mode'))->options(self::editorModeOptions())
            ->default(PostEditorMode::Simple->value)->required()->live()->native(false)->selectablePlaceholder(false)
            ->afterStateHydrated(function (Select $component, $state): void {
                if (blank($state)) {
                    $component->state(PostEditorMode::Simple->value);
                }
            })->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                if ($state === PostEditorMode::Pro->value) {
                    $set('body_html', FilamentPostBody::documentToHtml($get('body')));

                    return;
                }
                if ($state === PostEditorMode::Simple->value) {
                    $html = trim(TypeCast::string($get('body_html') ?? ''));
                    if ($html !== '') {
                        $set('body', $html);
                    }
                }
            }), RichEditor::make('body')->label(__('admin.posts.fields.body'))
            ->required(fn (Get $get): bool => ($get('editor_mode') ?? PostEditorMode::Simple
                ->value) !== PostEditorMode::Pro->value)
            ->dehydrated(fn (Get $get): bool => ($get('editor_mode') ?? PostEditorMode::Simple
                ->value) !== PostEditorMode::Pro->value)->columnSpanFull()
            ->visible(fn (Get $get): bool => ($get('editor_mode') ?? PostEditorMode::Simple
                ->value) !== PostEditorMode::Pro->value), CodeEditor::make('body_html')
            ->label(__('admin.posts.fields.body'))->language(Language::Html)
            ->required(fn (Get $get): bool => $get('editor_mode') === PostEditorMode::Pro->value)
            ->dehydrated(fn (Get $get): bool => $get('editor_mode') === PostEditorMode::Pro->value)
            ->afterStateHydrated(function (CodeEditor $component, $state): void {
                if (! is_string($state)) {
                    $component->state(FilamentPostBody::documentToHtml($state));
                }
            })->columnSpanFull()->visible(fn (Get $get): bool => $get('editor_mode') === PostEditorMode::Pro
            ->value), Select::make('user_id')->label(__('admin.posts.fields.author'))->relationship('user', 'name')
            ->searchable()->preload(), Select::make('status')->label(__('admin.posts.fields.status'))
            ->options(self::statusOptions())->default(PostStatus::Draft->value)->required(), Select::make('visibility')
            ->label(__('admin.posts.fields.visibility'))->options(self::visibilityOptions())
            ->default(PostVisibility::Guest->value)->required()->live()
            ->columnSpanFull(), Select::make('required_permission_id')
            ->label(__('admin.posts.fields.required_permission'))->options(self::permissionOptions())->searchable()
            ->required(fn (Get $get): bool => $get('visibility') === PostVisibility::Permission->value)
            ->visible(fn (Get $get): bool => $get('visibility') === PostVisibility::Permission->value)
            ->columnSpanFull(), DateTimePicker::make('published_at')
            ->label(__('admin.posts.fields.published_at')), DateTimePicker::make('scheduled_publish_at')
            ->label(__('admin.posts.fields.scheduled_publish_at')), Select::make('category_id')
            ->label(__('admin.posts.fields.category'))->relationship('category', 'name')->searchable()->preload()
            ->required(), Select::make('tags')->label(__('admin.posts.fields.tags'))->relationship('tags', 'name')
            ->multiple()->searchable()->preload(), Textarea::make('rejection_reason')
            ->label(__('admin.posts.fields.rejection_reason'))->columnSpanFull()
            ->disabled(fn (?string $state): bool => blank($state)), ...PostSeoSection::components()]);
    }

    /**
     * @return array<string, string>
     */
    private static function editorModeOptions(): array
    {
        $options = [];
        foreach (PostEditorMode::cases() as $mode) {
            $options[$mode->value] = __('posts.editor_mode.'.$mode->value);
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        $options = [];
        foreach (PostStatus::cases() as $status) {
            $options[$status->value] = __('posts.status.'.$status->value);
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private static function visibilityOptions(): array
    {
        $options = [];
        foreach (PostVisibility::cases() as $visibility) {
            $options[$visibility->value] = __('posts.visibility.'.$visibility->value);
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    private static function permissionOptions(): array
    {
        /** @var array<int, string> $options */
        $options = [];
        foreach (Permission::query()->orderBy('name')->get(['id', 'name']) as $permission) {
            $options[(int) $permission->id] = (string) $permission->name;
        }

        return $options;
    }
}
