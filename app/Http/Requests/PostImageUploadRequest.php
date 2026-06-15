<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация запроса post image upload.
 */
class PostImageUploadRequest extends FormRequest
{
    /**
     * Проверяет право на выполнение запроса.

     *
     * @return bool
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('posts.create');
    }

    /**
     * Возвращает правила валидации.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ['file' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:15360']];
    }

    /**
     * messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['file.required' => __('posts.validation.content_image_required'),
            'file.image' => __('posts.validation.content_image_image'),
            'file.mimes' => __('posts.validation.content_image_mimes'),
            'file.max' => __('posts.validation.content_image_max')];
    }
}
