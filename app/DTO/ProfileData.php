<?php

namespace App\DTO;

use App\Enums\UserTheme;
use App\Support\TypeCast;

/**
 * DTO profile.

 *
 * @property-read string $name
 * @property-read string $email
 * @property-read ?string $password
 * @property-read UserTheme $theme
 */
readonly class ProfileData extends AbstractData
{
    public function __construct(public string $name, public string $email, public ?string $password,
        public UserTheme $theme) {}

    /**
     * from validated.

     *
     * @return self
     */
    public static function fromValidated(string $name, string $email, ?string $password, string $theme): self
    {
        return new self(name: TypeCast::trimRequired($name), email: mb_strtolower(TypeCast::trimRequired($email)),
            password: TypeCast::trimToNull($password), theme: UserTheme::tryFrom($theme) ?? UserTheme::Default);
    }

    /**
     * to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = ['name' => $this->name, 'email' => $this->email, 'theme' => $this->theme->value];
        if ($this->password !== null) {
            $data['password'] = $this->password;
        }

        return $data;
    }
}
