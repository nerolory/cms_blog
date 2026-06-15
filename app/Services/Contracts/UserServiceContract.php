<?php

namespace App\Services\Contracts;

use App\DTO\AuthorOption;
use App\DTO\ProfileData;
use App\DTO\RegisterData;
use App\DTO\ResetPasswordCredentials;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

/**
 * Контракт сервиса user.
 */
interface UserServiceContract
{
    /**
     * authenticate.

     *
     * @return bool
     */
    public function authenticate(string $email, string $password, bool $remember = false): bool;

    /**
     * Регистрирует сервисы контейнера.
     *
     * @param  RegisterData  $data  данные формы

     * @return User
     */
    public function register(RegisterData $data): User;

    /**
     * Обновляет profile.
     *
     * @param  User  $user  пользователь
     * @param  ProfileData  $data  данные формы

     * @return User
     */
    public function updateProfile(User $user, ProfileData $data): User;

    /**
     * upload avatar.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function uploadAvatar(User $user, UploadedFile $file): User;

    /**
     * Удаляет avatar.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function deleteAvatar(User $user): User;

    /**
     * activate.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function activate(User $user): User;

    /**
     * deactivate.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function deactivate(User $user): User;

    /**
     * suspend.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function suspend(User $user): User;

    /**
     * send password reset link.

     *
     * @return string
     */
    public function sendPasswordResetLink(string $email): string;

    /**
     * reset password.

     *
     * @return string
     */
    public function resetPassword(ResetPasswordCredentials $credentials): string;

    /**
     * Returns author options for post edit forms.
     *
     * @return Collection<int, AuthorOption>
     */
    public function authorOptionsFor(?int $includeUserId = null): Collection;
}
