<?php

namespace App\Http\Requests;

use App\DTO\CommentData;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Валидация запроса store comment.
 */
class StoreCommentRequest extends FormRequest
{
    /**
     * Проверяет право на выполнение запроса.

     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', PostComment::class) ?? false;
    }

    /**
     * Возвращает правила валидации.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ['body' => ['required', 'string', 'min:2', 'max:2000'], 'parent_id' => ['nullable', 'integer',
            Rule::exists('post_comments', 'id')]];
    }

    /**
     * to dto.
     *
     * @param  Post  $post  пост
     * @param  User  $user  пользователь

     * @return CommentData
     */
    public function toDto(Post $post, User $user): CommentData
    {
        $parentId = $this->integer('parent_id') ?: null;

        return CommentData::fromValidated(postId: $post->id, userId: $user->id, body: $this->string('body')->toString(),
            parentId: $parentId);
    }
}
