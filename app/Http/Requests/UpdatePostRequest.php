<?php

namespace App\Http\Requests;

use App\DTO\PostData;
use App\Enums\PostEditorMode;
use App\Enums\PostVisibility;
use App\Http\Requests\Concerns\BuildsPostWebMediaSyncInput;
use App\Http\Requests\Concerns\InjectsPostService;
use App\Http\Requests\Concerns\NormalizesInput;
use App\Http\Requests\Concerns\ResolvesPostFromRoute;
use App\Http\Requests\Concerns\ValidatesPostMediaAndTheme;
use App\Models\Post;
use App\Support\PostSlugRules;
use App\Support\PostTheme;
use App\Support\TypeCast;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Валидация запроса update post.
 */
class UpdatePostRequest extends FormRequest
{
    use BuildsPostWebMediaSyncInput;
    use InjectsPostService;
    use NormalizesInput;
    use ResolvesPostFromRoute;
    use ValidatesPostMediaAndTheme;

    /**
     * Проверяет право на выполнение запроса.

     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->postFromRoute()) ?? false;
    }

    /**
     * Возвращает правила валидации.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $post = $this->postFromRoute();
        $rules = array_merge(['title' => ['required', 'string', 'min:'.PostSlugRules::MIN_LENGTH,
            'max:'.PostSlugRules::MAX_LENGTH], 'slug' => PostSlugRules::optional($post->id), 'excerpt' => ['nullable',
                'string'], 'body' => ['required', 'string'], 'is_published' => ['sometimes', 'boolean']],
            $this->postMediaAndThemeRules());
        if ($this->user()?->canManageAllPosts()) {
            $rules['user_id'] = ['nullable', 'integer', 'exists:users,id'];
        }
        if ($this->allowsAuthorVisibilityEdit($post)) {
            $rules['visibility'] = ['required', 'string', Rule::in(PostVisibility::authorWebValues())];
        }

        return $rules;
    }

    private function allowsAuthorVisibilityEdit(Post $post): bool
    {
        $user = $this->user();

        return $user !== null && ! $user->canManageAllPosts() && $post->status->allowsAuthorVisibilityEdit();
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
        $post = $this->postFromRoute();
        $visibility = $post->visibility;
        $requiredPermissionId = $post->required_permission_id;
        if ($this->allowsAuthorVisibilityEdit($post)) {
            $visibility = $this->string('visibility')->toString();
            $requiredPermissionId = null;
        }
        $userId = null;
        if ($this->user()?->canManageAllPosts() && $this->filled('user_id')) {
            $userId = $this->integer('user_id');
        }

        return PostData::fromValidatedUpdate(title: $this->string('title')->toString(), slug: $this->input('slug'),
            excerpt: $this->input('excerpt'), body: $this->string('body')->toString(),
            isPublished: $this->boolean('is_published'), userId: $userId, status: $post->status,
            visibility: $visibility, requiredPermissionId: $requiredPermissionId, existing: $post,
            themePrimaryColor: TypeCast::nullableString($this->input('theme_primary_color')),
            themeAccentColor: TypeCast::nullableString($this->input('theme_accent_color')),
            contentOpacity: $this->integer('content_opacity', PostTheme::normalizeOpacity($post->content_opacity)),
            editorMode: PostEditorMode::from($this->string('editor_mode')->toString()));
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
        $this->nullIfBlankAfterTrim(['slug', 'excerpt']);
        $this->merge(['user_id' => $this->input('user_id') ?: null, 'is_published' => $this->boolean('is_published'),
            'remove_featured_image' => $this->boolean('remove_featured_image'),
            'remove_background_image' => $this->boolean('remove_background_image'),
            'use_article_theme' => $this->boolean('use_article_theme'),
            'content_opacity' => $this->input('content_opacity', PostTheme::MAX_CONTENT_OPACITY),
            'editor_mode' => $this->input('editor_mode', PostEditorMode::Simple->value)]);
        $this->preparePostThemeInput();
    }
}
