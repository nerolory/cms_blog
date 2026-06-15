<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Concerns\NormalizesInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация запроса forgot password.
 */
class ForgotPasswordRequest extends FormRequest
{
    use NormalizesInput;

    /**
     * Проверяет право на выполнение запроса.

     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Возвращает правила валидации.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ['email' => ['required', 'email']];
    }

    /**
     * prepare for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->trimInput(['email']);
    }
}
