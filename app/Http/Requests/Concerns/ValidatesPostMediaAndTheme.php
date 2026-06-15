<?php

namespace App\Http\Requests\Concerns;

use App\Enums\PostEditorMode;
use App\Support\PostTheme;
use Illuminate\Validation\Rule;

/**
 * Валидация запроса validates post media and theme.
 */
trait ValidatesPostMediaAndTheme
{
    /**
     * prepare post theme input.
     */
    protected function preparePostThemeInput(): void
    {
        if (! $this->boolean('use_article_theme')) {
            $this->merge(['theme_primary_color' => null, 'theme_accent_color' => null,
                'content_opacity' => PostTheme::MAX_CONTENT_OPACITY]);
        }
    }

    /**
     * post media and theme rules.
     *
     * @return array<string, mixed>
     */
    protected function postMediaAndThemeRules(): array
    {
        return ['featured_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:15360'],
            'background_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:15360'],
            'remove_featured_image' => ['sometimes', 'boolean'], 'remove_background_image' => ['sometimes', 'boolean'],
            'use_article_theme' => ['sometimes', 'boolean'], 'theme_primary_color' => ['nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/'], 'theme_accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'content_opacity' => ['nullable', 'integer', 'min:'.PostTheme::MIN_CONTENT_OPACITY,
                'max:'.PostTheme::MAX_CONTENT_OPACITY], 'editor_mode' => ['required',
                    Rule::enum(PostEditorMode::class)]];
    }

    /**
     * post media and theme messages.
     *
     * @return array<string, mixed>
     */
    protected function postMediaAndThemeMessages(): array
    {
        return ['featured_image.image' => __('posts.validation.featured_image_image'),
            'featured_image.mimes' => __('posts.validation.featured_image_mimes'),
            'featured_image.max' => __('posts.validation.featured_image_max'),
            'background_image.image' => __('posts.validation.background_image_image'),
            'background_image.mimes' => __('posts.validation.background_image_mimes'),
            'background_image.max' => __('posts.validation.background_image_max'),
            'theme_primary_color.regex' => __('posts.validation.theme_color_format'),
            'theme_accent_color.regex' => __('posts.validation.theme_color_format'),
            'content_opacity.min' => __('posts.validation.content_opacity_min'),
            'content_opacity.max' => __('posts.validation.content_opacity_max'),
            'editor_mode.required' => __('posts.validation.editor_mode_required')];
    }
}
