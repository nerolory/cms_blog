<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация запроса update avatar.
 */
class UpdateAvatarRequest extends FormRequest
{
    /**
     * Проверяет право на выполнение запроса.

     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Возвращает правила валидации.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ['avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:30720']];
    }

    /**
     * attributes.
     *
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return ['avatar' => __('profile.fields.avatar')];
    }

    /**
     * messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['avatar.required' => __('profile.validation.avatar_required'),
            'avatar.image' => __('profile.validation.avatar_image'),
            'avatar.mimes' => __('profile.validation.avatar_mimes'),
            'avatar.max' => __('profile.validation.avatar_max'),
            'avatar.uploaded' => __('profile.validation.avatar_uploaded')];
    }
}
