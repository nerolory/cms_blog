<?php

namespace App\Filament\Resources\SiteTemplates\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Компонент Filament site template form.
 */
class SiteTemplateForm
{
    /**
     * configure.
     *
     * @param  Schema  $schema  схема Filament

     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('slug')->label(__('admin.site_templates.fields.slug'))->required()
            ->maxLength(255)->unique(ignoreRecord: true), TextInput::make('name')
            ->label(__('admin.site_templates.fields.name'))->required()->maxLength(255), TextInput::make('view_prefix')
            ->label(__('admin.site_templates.fields.view_prefix'))->required()->maxLength(255)
            ->default('themes.default')
            ->helperText(__('admin.site_templates.fields.view_prefix_help')), Toggle::make('is_default')
            ->label(__('admin.site_templates.fields.is_default'))]);
    }
}
