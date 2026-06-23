<?php

namespace App\Http\Requests\Profile;

use App\DTO\ProfileData;
use App\Http\Requests\Concerns\NormalizesInput;
use App\Models\User;
use App\Services\Contracts\SiteTemplateServiceContract;
use App\Support\TypeCast;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Валидация запроса update profile.

 *
 * @property-read SiteTemplateServiceContract $siteTemplateService
 */
class UpdateProfileRequest extends FormRequest
{
    use NormalizesInput;

    public function __construct(protected SiteTemplateServiceContract $siteTemplateService)
    {
        parent::__construct();
    }

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
        /** @var User $user */
        $user = $this->user();

        return ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'string', 'email', 'max:255',
            Rule::unique('users', 'email')->ignore($user->id)], 'password' => ['nullable', 'confirmed',
                Password::defaults()], 'theme' => ['required', 'string',
                    Rule::in($this->siteTemplateService->availableThemeSlugs()->all())]];
    }

    /**
     * messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['name.required' => __('profile.validation.name_required'),
            'email.required' => __('auth.validation.email_required'),
            'email.unique' => __('auth.validation.email_taken'),
            'theme.required' => __('profile.validation.theme_required'),
            'password.confirmed' => __('auth.validation.password_confirmed')];
    }

    /**
     * to dto.

     *
     * @return ProfileData
     */
    public function toDto(): ProfileData
    {
        return ProfileData::fromValidated(name: $this->string('name')->toString(),
            email: $this->string('email')->toString(), password: TypeCast::trimToNull($this->input('password')),
            theme: $this->string('theme')->toString());
    }

    /**
     * prepare for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->trimInput(['name', 'email', 'password', 'password_confirmation']);
        $this->nullIfBlankAfterTrim(['password', 'password_confirmation']);
    }
}
