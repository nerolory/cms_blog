<?php

namespace App\Support\Auth;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Вспомогательный класс auth attempt diagnostics.

 *
 * @property-read UserRepositoryContract $userRepository
 */
final class AuthAttemptDiagnostics
{
    public function __construct(protected UserRepositoryContract $userRepository) {}

    /**
     * log failure.
     *
     * @param  bool  $authAttemptResult  attempt result
     */
    public function logFailure(string $email, string $password, bool $authAttemptResult): void
    {
        $normalizedEmail = mb_strtolower(trim($email));
        $user = $this->userRepository->findByEmail($normalizedEmail);
        $passwordMatches = $user instanceof User ? Hash::check($password, $user->password) : null;
        $reason = match (true) {
            $user === null => 'user_not_found',
            $passwordMatches === false => 'password_mismatch',
            $authAttemptResult === false => 'auth_attempt_rejected',
            default => 'unknown',
        };
        Log::warning('Login failed', [
            'email_normalized' => $normalizedEmail,
            'failure_reason' => $reason,
        ]);
    }
}
