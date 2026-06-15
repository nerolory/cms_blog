<?php

namespace App\Http\Requests\Api;

use App\DTO\PostData;
use App\Http\Requests\Concerns\GeneratesPostSlug;
use App\Http\Requests\Concerns\NormalizesInput;
use App\Models\Post;
use App\Support\PostSlugRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация запроса store post.
 */
class StorePostRequest extends FormRequest
{
    use GeneratesPostSlug;
    use NormalizesInput;

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
        return ['title' => ['required', 'string', 'min:'.PostSlugRules::MIN_LENGTH, 'max:'.PostSlugRules::MAX_LENGTH],
            'slug' => PostSlugRules::optional(), 'excerpt' => ['required', 'string', 'min:10'], 'body' => ['required',
                'string', 'min:10']];
    }

    /**
     * to dto.

     *
     * @return PostData
     */
    public function toDto(): PostData
    {
        return PostData::fromValidated(title: $this->string('title')->toString(),
            slug: $this->string('slug')->toString(), excerpt: $this->string('excerpt')->toString(),
            body: $this->string('body')->toString(), isPublished: false, userId: null);
    }

    /**
     * prepare for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->trimInput(['title', 'slug', 'excerpt', 'body']);
        $this->nullIfBlankAfterTrim(['slug']);
    }

    /**
     * passed validation.
     */
    protected function passedValidation(): void
    {
        $this->mergeGeneratedPostSlug();
    }
}
