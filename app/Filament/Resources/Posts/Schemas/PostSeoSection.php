<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

/**
 * Компонент Filament post seo section.
 */
class PostSeoSection
{
    /**
     * components.
     *
     * @return array<int, Section>
     */
    public static function components(): array
    {
        return [Section::make(__('admin.posts.seo.title'))->description(__('admin.posts.seo.description'))
            ->schema([TextInput::make('meta_title')->label(__('admin.posts.seo.fields.meta_title'))->maxLength(255)
                ->columnSpanFull(), Textarea::make('meta_description')
                ->label(__('admin.posts.seo.fields.meta_description'))
                ->rows(3)->maxLength(500)->columnSpanFull(), FileUpload::make('og_image_path')
                ->label(__('admin.posts.seo.fields.og_image'))->disk('public')->directory('posts/og')->image()
                ->imageEditor()->nullable()->columnSpanFull(), TextInput::make('canonical_url')
                ->label(__('admin.posts.seo.fields.canonical_url'))->url()->maxLength(500)
                ->columnSpanFull(), Select::make('robots')->label(__('admin.posts.seo.fields.robots'))
                ->options(self::robotsOptions())->nullable()->native(false)
                ->placeholder(__('admin.posts.seo.fields.robots_auto'))->columnSpanFull()])->columnSpanFull()
            ->collapsed()];
    }

    /**
     * @return array<string, string>
     */
    private static function robotsOptions(): array
    {
        return ['index,follow' => 'index, follow', 'noindex,follow' => 'noindex, follow',
            'index,nofollow' => 'index, nofollow', 'noindex,nofollow' => 'noindex, nofollow'];
    }
}
