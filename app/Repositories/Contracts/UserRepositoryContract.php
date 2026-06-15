<?php

namespace App\Repositories\Contracts;

use App\DTO\AuthorOption;
use App\DTO\ProfileData;
use App\DTO\RegisterData;
use App\Enums\AccountStatus;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Collection;

/**
 * Контракт репозитория user.
 */
interface UserRepositoryContract
{
    /**
     * Находит by email.

     *
     * @return ?User
     */
    public function findByEmail(string $email): ?User;

    /**
     * Находит by id.

     *
     * @return ?User
     */
    public function findById(int $id): ?User;

    /**
     * Находит by id or fail.

     *
     * @return User
     */
    public function findByIdOrFail(int $id): User;

    /**
     * count.

     *
     * @return int
     */
    public function count(): int;

    /**
     * exists by email.

     *
     * @return bool
     */
    public function existsByEmail(string $email): bool;

    /**
     * Находит first with role.

     *
     * @return ?User
     */
    public function findFirstWithRole(string $role): ?User;

    /**
     * Обновляет locale.

     *
     * @return User
     */
    public function updateLocale(User $user, string $locale): User;

    /**
     * Обновляет password.

     *
     * @return User
     */
    public function updatePassword(User $user, string $hashedPassword): User;

    /**
     * Создаёт from register.
     *
     * @param  RegisterData  $data  данные формы

     * @return User
     */
    public function createFromRegister(RegisterData $data): User;

    /**
     * Обновляет profile.
     *
     * @param  User  $user  пользователь
     * @param  ProfileData  $data  данные формы

     * @return User
     */
    public function updateProfile(User $user, ProfileData $data): User;

    /**
     * Обновляет avatar path.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function updateAvatarPath(User $user, ?string $path): User;

    /**
     * set email verified at.

     *
     * @return User
     */
    public function setEmailVerifiedAt(User $user, ?DateTimeInterface $verifiedAt): User;

    /**
     * set account status.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function setAccountStatus(User $user, AccountStatus $status): User;

    /**
     * Returns author options for post edit forms.
     *
     * @return Collection<int, AuthorOption>
     */
    public function authorOptionsFor(?int $includeUserId = null): Collection;

    /**
     * Returns users who should receive moderation notifications.
     *
     * @return Collection<int, User>
     */
    public function moderatorsForNotification(): Collection;

    /**
     * exists with role.

     *
     * @return bool
     */
    public function existsWithRole(string $role): bool;

    /**
     * Назначает пользователю роль Spatie.
     *
     * @return User
     */
    public function assignRole(User $user, string $roleName): User;
}
