<?php

namespace App\DTO;

use App\Support\TypeCast;

/**
 * DTO register.

 *
 * @property-read string $name
 * @property-read string $email
 * @property-read string $password
 */
readonly class RegisterData extends AbstractData
{
    public function __construct(public string $name, public string $email, public string $password) {}

    /**
     * from validated.

     *
     * @return self
     */
    public static function fromValidated(string $name, string $email, string $password): self
    {
        return new self(name: TypeCast::trimRequired($name), email: mb_strtolower(TypeCast::trimRequired($email)),
            password: TypeCast::trimRequired($password));
    }
}
