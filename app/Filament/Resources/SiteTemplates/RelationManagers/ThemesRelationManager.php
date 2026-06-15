<?php

namespace App\Filament\Resources\SiteTemplates\RelationManagers;

use App\DTO\SiteTemplateThemeData;
use App\Models\SiteTemplate;
use App\Models\SiteTemplateTheme;
use App\Services\Contracts\SiteTemplateServiceContract;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Компонент Filament themes relation manager.
 */
class ThemesRelationManager extends RelationManager
{
    protected static string $relationship = 'themes';

    /**
     * Возвращает title.
     *
     * @param  Model  $ownerRecord  record
     * @param  string  $pageClass  class

     * @return string
     */
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.site_templates.themes.title');
    }

    /**
     * form.
     *
     * @param  Schema  $schema  схема Filament

     * @return Schema
     */
    public function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('slug')->label(__('admin.site_templates.themes.fields.slug'))
            ->required()->maxLength(255), TextInput::make('name')->label(__('admin.site_templates.themes.fields.name'))
            ->required()->maxLength(255), TextInput::make('bootstrap_theme')
            ->label(__('admin.site_templates.themes.fields.bootstrap_theme'))
            ->maxLength(255), TextInput::make('body_class')->label(__('admin.site_templates.themes.fields.body_class'))
            ->maxLength(255), TextInput::make('css_entry')->label(__('admin.site_templates.themes.fields.css_entry'))
            ->maxLength(255), Toggle::make('is_default')->label(__('admin.site_templates.themes.fields.is_default'))]);
    }

    /**
     * table.

     *
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('slug')
            ->label(__('admin.site_templates.themes.fields.slug')), TextColumn::make('name')
            ->label(__('admin.site_templates.themes.fields.name')), TextColumn::make('bootstrap_theme')
            ->label(__('admin.site_templates.themes.fields.bootstrap_theme'))
            ->placeholder('—'), TextColumn::make('body_class')
            ->label(__('admin.site_templates.themes.fields.body_class'))
            ->placeholder('—'), IconColumn::make('is_default')
            ->label(__('admin.site_templates.themes.fields.is_default'))->boolean()])
            ->headerActions([CreateAction::make()
                ->using(function (array $data, RelationManager $livewire,
                    SiteTemplateServiceContract $siteTemplateService): SiteTemplateTheme {
                    $owner = $livewire->getOwnerRecord();
                    if (! $owner instanceof SiteTemplate) {
                        throw new \RuntimeException('Invalid site template owner.');
                    }

                    return $siteTemplateService->createTheme($owner,
                        SiteTemplateThemeData::fromArray($data));
                })])->recordActions([EditAction::make()->using(function (SiteTemplateTheme $record,
                    array $data, SiteTemplateServiceContract $siteTemplateService): SiteTemplateTheme {
                    return $siteTemplateService->updateTheme($record,
                        SiteTemplateThemeData::fromArray($data));
                }), DeleteAction::make()->action(function (SiteTemplateTheme $record,
                    SiteTemplateServiceContract $siteTemplateService): void {
                    try {
                        $siteTemplateService->deleteTheme($record);
                    } catch (\Throwable $exception) {
                        Notification::make()->danger()->title($exception->getMessage())->send();

                        return;
                    }
                    Notification::make()->success()->title(__('admin.site_templates.themes.notifications.deleted'))
                        ->send();
                })]);
    }
}
