<?php

namespace App\DTO;

/**
 * DTO reset password credentials.

 *
 * @property-read string $email
 * @property-read string $password
 * @property-read string $password_confirmation
 * @property-read string $token
 */
readonly class ResetPasswordCredentials
{
    public function __construct(public string $email, public string $password, public string $password_confirmation,
        public string $token) {}

    /**
     * to password broker payload.
     *
     * @return array<string, mixed>
     */
    public function toPasswordBrokerPayload(): array
    {
        return ['email' => $this->email, 'password' => $this->password,
            'password_confirmation' => $this->password_confirmation, 'token' => $this->token];
    }
}
