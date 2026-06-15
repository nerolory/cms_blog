<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Concerns\NormalizesInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация запроса login.
 */
class LoginRequest extends FormRequest
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
        return ['email' => ['required', 'string', 'email'], 'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean']];
    }

    /**
     * messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['email.required' => __('auth.validation.email_required'),
            'password.required' => __('auth.validation.password_required')];
    }

    /**
     * prepare for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->trimInput(['email']);
    }
}
