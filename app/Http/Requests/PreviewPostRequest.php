<?php

namespace App\Http\Requests;

use App\DTO\PostData;
use App\Enums\PostEditorMode;
use App\Enums\PostStatus;
use App\Http\Requests\Concerns\GeneratesPostSlug;
use App\Http\Requests\Concerns\NormalizesInput;
use App\Http\Requests\Concerns\ValidatesPostMediaAndTheme;
use App\Models\Post;
use App\Support\PostSlugRules;
use App\Support\PostTheme;
use App\Support\TypeCast;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация запроса preview post.
 */
class PreviewPostRequest extends FormRequest
{
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
        /** @var Post|null $post */
        $post = $this->route('post');
        if ($post instanceof Post) {
            return $this->user()?->can('preview', $post) ?? false;
        }

        return $this->user()?->can('create', Post::class) ?? false;
    }

    /**
     * Возвращает правила валидации.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Post|null $post */
        $post = $this->route('post');
        $slugRules = $post instanceof Post ? PostSlugRules::optional($post->id) : PostSlugRules::optional();

        $rules = array_merge(['title' => ['required', 'string', 'min:'.PostSlugRules::MIN_LENGTH,
            'max:'.PostSlugRules::MAX_LENGTH], 'slug' => $slugRules,
            'excerpt' => [$post instanceof Post ? 'nullable' : 'required', 'string', 'min:10'], 'body' => ['required',
                'string', 'min:10'], 'is_published' => 'nullable'],
            $this->postMediaAndThemeRules());
        if ($this->user()?->canManageAllPosts()) {
            $rules['user_id'] = ['nullable', 'integer', 'exists:users,id'];
        }

        return $rules;
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
            'user_id.exists' => __('posts.validation.user_id_exists')], $this->postMediaAndThemeMessages());
    }

    /**
     * Builds post DTO for preview rendering (not persisted).

     *
     * @return PostData
     */
    public function toPreviewPostData(?Post $post): PostData
    {
        if ($post instanceof Post) {
            return PostData::fromValidatedUpdate(title: $this->string('title')->toString(), slug: $this->input('slug'),
                excerpt: $this->input('excerpt'), body: $this->string('body')->toString(), isPublished: false,
                userId: $this->previewUserId(), status: PostStatus::Draft, visibility: $post->visibility,
                requiredPermissionId: $post->required_permission_id, existing: $post,
                themePrimaryColor: TypeCast::nullableString($this->input('theme_primary_color')),
                themeAccentColor: TypeCast::nullableString($this->input('theme_accent_color')),
                contentOpacity: $this->integer('content_opacity', PostTheme::normalizeOpacity($post->content_opacity)),
                editorMode: PostEditorMode::from($this->string('editor_mode')->toString()));
        }

        return PostData::fromValidated(title: $this->string('title')->toString(),
            slug: $this->string('slug')->toString(), excerpt: $this->string('excerpt')->toString(),
            body: $this->string('body')->toString(), isPublished: false, userId: $this->user()?->id,
            status: PostStatus::Draft, themePrimaryColor: TypeCast::nullableString($this->input('theme_primary_color')),
            themeAccentColor: TypeCast::nullableString($this->input('theme_accent_color')),
            contentOpacity: $this->integer('content_opacity', PostTheme::MAX_CONTENT_OPACITY),
            editorMode: PostEditorMode::from($this->string('editor_mode')->toString()));
    }

    /**
     * preview back url.
     *
     * @param  ?Post  $post  пост

     * @return string
     */
    public function previewBackUrl(?Post $post): string
    {
        if ($post instanceof Post) {
            return route('posts.edit', $post);
        }

        return route('posts.create');
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
     * user_id из запроса — только для canManageAllPosts().
     *
     * @return ?int
     */
    private function previewUserId(): ?int
    {
        if ($this->user()?->canManageAllPosts() && $this->filled('user_id')) {
            return $this->integer('user_id');
        }

        return null;
    }

    /**
     * prepare for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->trimInput(['title', 'slug', 'excerpt', 'body']);
        $this->nullIfBlankAfterTrim(['slug', 'excerpt']);
        $this->merge(['user_id' => $this->input('user_id') ?: null, 'is_published' => $this->boolean('is_published'),
            'remove_featured_image' => $this->boolean('remove_featured_image'),
            'remove_background_image' => $this->boolean('remove_background_image'),
            'use_article_theme' => $this->boolean('use_article_theme'),
            'content_opacity' => $this->input('content_opacity', PostTheme::MAX_CONTENT_OPACITY),
            'editor_mode' => $this->input('editor_mode', PostEditorMode::Simple->value)]);
        $this->preparePostThemeInput();
    }

    /**
     * passed validation.
     */
    protected function passedValidation(): void
    {
        if ($this->route('post') === null) {
            $this->mergeGeneratedPostSlug();
        }
    }
}
