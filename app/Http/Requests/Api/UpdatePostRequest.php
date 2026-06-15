<?php

namespace App\Http\Requests\Api;

use App\DTO\PostData;
use App\Http\Requests\Concerns\InjectsPostService;
use App\Http\Requests\Concerns\NormalizesInput;
use App\Http\Requests\Concerns\ResolvesPostFromApiRoute;
use App\Models\Post;
use App\Support\PostSlugRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация запроса update post.
 */
class UpdatePostRequest extends FormRequest
{
    use InjectsPostService;
    use NormalizesInput;
    use ResolvesPostFromApiRoute;

    /**
     * Проверяет право на выполнение запроса.

     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->postFromApiRoute()) ?? false;
    }

    /**
     * Возвращает правила валидации.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $post = $this->postFromApiRoute();

        return ['title' => ['required', 'string', 'min:'.PostSlugRules::MIN_LENGTH, 'max:'.PostSlugRules::MAX_LENGTH],
            'slug' => PostSlugRules::optional($post->id), 'excerpt' => ['nullable', 'string'], 'body' => ['required',
                'string'], 'is_published' => ['sometimes', 'boolean']];
    }

    /**
     * to dto.

     *
     * @return PostData
     */
    public function toDto(): PostData
    {
        $post = $this->postFromApiRoute();

        return PostData::fromValidatedUpdate(title: $this->string('title')->toString(), slug: $this->input('slug'),
            excerpt: $this->input('excerpt'), body: $this->string('body')->toString(),
            isPublished: $this->boolean('is_published'), userId: null, status: $post->status,
            visibility: $post->visibility, requiredPermissionId: $post->required_permission_id, existing: $post);
    }

    /**
     * prepare for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->trimInput(['title', 'slug', 'excerpt', 'body']);
        $this->nullIfBlankAfterTrim(['slug', 'excerpt']);
    }
}
