<?php

namespace App\Http\Requests;

use App\Enums\ReactionType;
use App\Http\Requests\Concerns\InjectsPostService;
use App\Http\Requests\Concerns\ResolvesPostFromRoute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Валидация реакции на комментарий.
 */
class StoreCommentReactionRequest extends FormRequest
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

        return $user !== null && $user->can('view', $this->postFromRoute());
    }

    /**
     * Возвращает правила валидации.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ['type' => ['required', 'string', Rule::in(array_map(
            fn (ReactionType $type): string => $type->value,
            ReactionType::all(),
        ))]];
    }
}
