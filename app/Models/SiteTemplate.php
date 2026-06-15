<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Eloquent-модель шаблона оформления сайта.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $view_prefix
 * @property bool $is_active
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, SiteTemplateTheme> $themes
 */
class SiteTemplate extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['slug', 'name', 'view_prefix', 'is_active', 'is_default'];

    /**
     * casts.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_default' => 'boolean'];
    }

    /**
     * Темы шаблона.
     *
     * @return HasMany<SiteTemplateTheme, $this>
     */
    public function themes(): HasMany
    {
        return $this->hasMany(SiteTemplateTheme::class);
    }

    /**
     * Возвращает route key name.

     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
