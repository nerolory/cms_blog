<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Eloquent-модель темы шаблона оформления сайта.
 *
 * @property int $id
 * @property int $site_template_id
 * @property string $slug
 * @property string $name
 * @property string $bootstrap_theme
 * @property string|null $body_class
 * @property string|null $css_entry
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SiteTemplate $template
 */
class SiteTemplateTheme extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['site_template_id', 'slug', 'name', 'bootstrap_theme', 'body_class', 'css_entry',
        'is_default'];

    /**
     * casts.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    /**
     * template.
     *
     * @return BelongsTo<SiteTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(SiteTemplate::class, 'site_template_id');
    }
}
