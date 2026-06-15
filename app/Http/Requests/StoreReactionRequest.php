<?php

namespace App\Http\Requests;

use App\DTO\ReactionData;
use App\Enums\ReactionType;
use App\Http\Requests\Concerns\InjectsPostService;
use App\Http\Requests\Concerns\ResolvesPostFromRoute;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Валидация запроса store reaction.
 */
class StoreReactionRequest extends FormRequest
{
    use InjectsPostService;
    use ResolvesPostFromRoute;

    /**
     * Проверяет право на выполнение запроса.

     *
     * @return bool
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        return $user->can('view', $this->postFromRoute());
    }

    /**
     * Возвращает правила валидации.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ['type' => ['required', 'string', Rule::in(array_map(fn (ReactionType $t): string => $t->value,
            ReactionType::all()))]];
    }

    /**
     * to dto.
     *
     * @param  Post  $post  пост
     * @param  User  $user  пользователь

     * @return ReactionData
     */
    public function toDto(Post $post, User $user): ReactionData
    {
        return ReactionData::fromValidated(postId: $post->id, userId: $user->id,
            type: $this->string('type')->toString());
    }
}
