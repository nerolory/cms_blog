<?php

namespace App\Http\Requests\Auth;

use App\DTO\ResetPasswordCredentials;
use App\Http\Requests\Concerns\NormalizesInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация запроса reset password.
 */
class ResetPasswordRequest extends FormRequest
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
        return ['token' => ['required', 'string'], 'email' => ['required', 'email'], 'password' => ['required',
            'string', 'min:8', 'confirmed']];
    }

    /**
     * credentials.

     *
     * @return ResetPasswordCredentials
     */
    public function credentials(): ResetPasswordCredentials
    {
        return new ResetPasswordCredentials(email: $this->string('email')->toString(),
            password: $this->string('password')->toString(),
            password_confirmation: $this->string('password_confirmation')->toString(),
            token: $this->string('token')->toString());
    }

    /**
     * prepare for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->trimInput(['email', 'password', 'password_confirmation', 'token']);
    }
}
