<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\RelationManagers\ModerationLogsRelationManager;
use App\Filament\Resources\Posts\RelationManagers\VersionsRelationManager;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Компонент Filament post resource.
 */
class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    /**
     * Возвращает navigation label.

     *
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.posts.navigation');
    }

    /**
     * Возвращает model label.

     *
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.posts.model');
    }

    /**
     * Возвращает plural model label.

     *
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.posts.plural');
    }

    /**
     * Возвращает navigation group.

     *
     * @return ?string
     */
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.content');
    }

    /**
     * form.
     *
     * @param  Schema  $schema  схема Filament

     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    /**
     * table.

     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    /**
     * Возвращает relations.
     *
     * @return array<string, class-string<RelationManager>>
     */
    public static function getRelations(): array
    {
        return ['versions' => VersionsRelationManager::class, 'moderationLogs' => ModerationLogsRelationManager::class];
    }

    /**
     * Возвращает pages.
     *
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return ['index' => ListPosts::route('/'), 'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit')];
    }

    /**
     * Возвращает record route binding eloquent query.
     *
     * @return Builder<Post>
     */
    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<Post> $query */
        $query = parent::getEloquentQuery()->with('user');

        return $query;
    }

    /**
     * Возвращает record route binding eloquent query.
     *
     * @return Builder<Post>
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        /** @var Builder<Post> $query */
        $query = parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);

        return $query;
    }
}
