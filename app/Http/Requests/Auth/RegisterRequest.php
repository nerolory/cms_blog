<?php

namespace App\Http\Requests\Auth;

use App\DTO\RegisterData;
use App\Http\Requests\Concerns\NormalizesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Валидация запроса register.
 */
class RegisterRequest extends FormRequest
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
        return ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'string', 'email', 'max:255',
            'unique:users,email'], 'password' => ['required', 'confirmed', Password::defaults()]];
    }

    /**
     * messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['name.required' => __('auth.validation.name_required'),
            'email.required' => __('auth.validation.email_required'),
            'email.unique' => __('auth.validation.email_taken'),
            'password.required' => __('auth.validation.password_required'),
            'password.confirmed' => __('auth.validation.password_confirmed')];
    }

    /**
     * to dto.

     *
     * @return RegisterData
     */
    public function toDto(): RegisterData
    {
        return RegisterData::fromValidated(name: $this->string('name')->toString(),
            email: $this->string('email')->toString(), password: $this->string('password')->toString());
    }

    /**
     * prepare for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->trimInput(['name', 'email', 'password', 'password_confirmation']);
    }
}
