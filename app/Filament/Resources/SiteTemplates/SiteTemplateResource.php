<?php

namespace App\Filament\Resources\SiteTemplates;

use App\Filament\Resources\SiteTemplates\Pages\CreateSiteTemplate;
use App\Filament\Resources\SiteTemplates\Pages\EditSiteTemplate;
use App\Filament\Resources\SiteTemplates\Pages\ListSiteTemplates;
use App\Filament\Resources\SiteTemplates\RelationManagers\ThemesRelationManager;
use App\Filament\Resources\SiteTemplates\Schemas\SiteTemplateForm;
use App\Filament\Resources\SiteTemplates\Tables\SiteTemplatesTable;
use App\Models\SiteTemplate;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Компонент Filament site template resource.
 */
class SiteTemplateResource extends Resource
{
    protected static ?string $model = SiteTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static ?int $navigationSort = 30;

    /**
     * Возвращает navigation label.

     *
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.site_templates.navigation');
    }

    /**
     * Возвращает model label.

     *
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.site_templates.model');
    }

    /**
     * Возвращает plural model label.

     *
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.site_templates.plural');
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
     * form.
     *
     * @param  Schema  $schema  схема Filament

     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return SiteTemplateForm::configure($schema);
    }

    /**
     * table.

     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return SiteTemplatesTable::configure($table);
    }

    /**
     * Возвращает relations.
     *
     * @return array<string, class-string<RelationManager>>
     */
    public static function getRelations(): array
    {
        return ['themes' => ThemesRelationManager::class];
    }

    /**
     * Возвращает pages.
     *
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return ['index' => ListSiteTemplates::route('/'), 'create' => CreateSiteTemplate::route('/create'),
            'edit' => EditSiteTemplate::route('/{record}/edit')];
    }
}
