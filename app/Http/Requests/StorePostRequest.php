<?php

namespace App\Http\Requests;

use App\DTO\PostData;
use App\Enums\PostEditorMode;
use App\Enums\PostVisibility;
use App\Http\Requests\Concerns\BuildsPostWebMediaSyncInput;
use App\Http\Requests\Concerns\GeneratesPostSlug;
use App\Http\Requests\Concerns\NormalizesInput;
use App\Http\Requests\Concerns\ValidatesPostMediaAndTheme;
use App\Models\Post;
use App\Support\PostSlugRules;
use App\Support\PostTheme;
use App\Support\TypeCast;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Валидация запроса store post.
 */
class StorePostRequest extends FormRequest
{
    use BuildsPostWebMediaSyncInput;
    use GeneratesPostSlug;
    use NormalizesInput;
    use ValidatesPostMediaAndTheme;

    /**
     * Проверяет право на выполнение запроса.

     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Post::class) ?? false;
    }

    /**
     * Возвращает правила валидации.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(['title' => ['required', 'string', 'min:'.PostSlugRules::MIN_LENGTH,
            'max:'.PostSlugRules::MAX_LENGTH], 'slug' => PostSlugRules::optional(), 'excerpt' => ['required', 'string',
                'min:10'], 'body' => ['required', 'string', 'min:10'], 'visibility' => ['required', 'string',
                    Rule::in(PostVisibility::authorWebValues())],
            'is_published' => 'nullable',
            'published_at' => 'nullable',
            'user_id' => 'nullable',
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'tags' => ['nullable', 'array'], 'tags.*' => ['integer', 'exists:tags,id']],
            $this->postMediaAndThemeRules());
    }

    /**
     * messages.
     *
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return array_merge(['title.required' => __('posts.validation.title_required'),
            'title.min' => __('posts.validation.title_min'), 'title.max' => __('posts.validation.title_max'),
            'body.required' => __('posts.validation.body_required'),
            'excerpt.required' => __('posts.validation.excerpt_required'),
            'slug.unique' => __('posts.validation.slug_unique'), 'slug.min' => __('posts.validation.slug_min'),
            'slug.max' => __('posts.validation.slug_max'), 'slug.regex' => __('posts.validation.slug_format'),
            'user_id.exists' => __('posts.validation.user_id_exists'),
            'visibility.required' => __('posts.validation.visibility_required'),
            'visibility.in' => __('posts.validation.visibility_invalid')], $this->postMediaAndThemeMessages());
    }

    /**
     * Builds a post DTO from validated request data.

     *
     * @return PostData
     */
    public function toDto(): PostData
    {
        return PostData::fromValidated(title: $this->string('title')->toString(),
            slug: $this->string('slug')->toString(), excerpt: $this->string('excerpt')->toString(),
            body: $this->string('body')->toString(), isPublished: $this->boolean('is_published'),
            userId: $this->filled('user_id') ? $this->integer('user_id') : null,
            visibility: $this->string('visibility')->toString(),
            themePrimaryColor: TypeCast::nullableString($this->input('theme_primary_color')),
            themeAccentColor: TypeCast::nullableString($this->input('theme_accent_color')),
            contentOpacity: $this->integer('content_opacity', PostTheme::MAX_CONTENT_OPACITY),
            editorMode: PostEditorMode::from($this->string('editor_mode')->toString()),
            categoryId: $this->integer('category_id'), tagIds: $this->tagIdsFromInput());
    }

    /**
     * @return Collection<int, int>
     */
    private function tagIdsFromInput(): Collection
    {
        $tags = $this->input('tags', []);
        if (! is_array($tags)) {
            return collect();
        }

        /** @var Collection<int, int> */
        return collect($tags)->map(fn (mixed $id): int => TypeCast::int($id))->filter(fn (int $id): bool => $id > 0)
            ->values();
    }

    /**
     * should remove featured image.

     *
     * @return bool
     */
    public function shouldRemoveFeaturedImage(): bool
    {
        return $this->boolean('remove_featured_image');
    }

    /**
     * should remove background image.

     *
     * @return bool
     */
    public function shouldRemoveBackgroundImage(): bool
    {
        return $this->boolean('remove_background_image');
    }

    /**
     * prepare for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->trimInput(['title', 'slug', 'excerpt', 'body']);
        $this->nullIfBlankAfterTrim(['slug']);
        $this->merge(['user_id' => $this->input('user_id') ?: null, 'is_published' => $this->boolean('is_published'),
            'remove_featured_image' => $this->boolean('remove_featured_image'),
            'remove_background_image' => $this->boolean('remove_background_image'),
            'use_article_theme' => $this->boolean('use_article_theme'),
            'content_opacity' => $this->input('content_opacity', PostTheme::MAX_CONTENT_OPACITY),
            'editor_mode' => $this->input('editor_mode', PostEditorMode::Simple->value),
            'visibility' => $this->input('visibility', PostVisibility::Guest->value)]);
        $this->preparePostThemeInput();
    }

    /**
     * passed validation.
     */
    protected function passedValidation(): void
    {
        $this->mergeGeneratedPostSlug();
    }
}
