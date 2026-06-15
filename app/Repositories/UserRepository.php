<?php

namespace App\Repositories;

use App\DTO\AuthorOption;
use App\DTO\ProfileData;
use App\DTO\RegisterData;
use App\Enums\AccountStatus;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use DateTimeInterface;
use Illuminate\Support\Collection;

/**
 * Репозиторий user.
 *
 * @property-read User $user
 */
class UserRepository implements UserRepositoryContract
{
    public function __construct(protected User $user) {}

    /**
     * Находит by email.

     *
     * @return ?User
     */
    public function findByEmail(string $email): ?User
    {
        return $this->user->newQuery()->where('email', mb_strtolower($email))->first();
    }

    /**
     * Находит by id.

     *
     * @return ?User
     */
    public function findById(int $id): ?User
    {
        return $this->user->newQuery()->find($id);
    }

    /**
     * Находит by id or fail.

     *
     * @return User
     */
    public function findByIdOrFail(int $id): User
    {
        return $this->user->newQuery()->findOrFail($id);
    }

    /**
     * count.

     *
     * @return int
     */
    public function count(): int
    {
        return $this->user->newQuery()->count();
    }

    /**
     * exists by email.

     *
     * @return bool
     */
    public function existsByEmail(string $email): bool
    {
        return $this->user->newQuery()->where('email', mb_strtolower($email))->exists();
    }

    /**
     * Находит first with role.

     *
     * @return ?User
     */
    public function findFirstWithRole(string $role): ?User
    {
        return $this->user->newQuery()->role($role)->first();
    }

    /**
     * Обновляет locale.

     *
     * @return User
     */
    public function updateLocale(User $user, string $locale): User
    {
        $user->forceFill(['locale' => $locale])->save();

        return $this->refresh($user);
    }

    /**
     * Перечитывает пользователя из БД после мутации.
     *
     * @return User
     */
    private function refresh(User $user): User
    {
        return $user->fresh() ?? $user;
    }

    /**
     * Обновляет password.

     *
     * @return User
     */
    public function updatePassword(User $user, string $hashedPassword): User
    {
        $user->forceFill(['password' => $hashedPassword])->save();

        return $this->refresh($user);
    }

    /**
     * Создаёт from register.
     *
     * @param  RegisterData  $data  данные формы

     * @return User
     */
    public function createFromRegister(RegisterData $data): User
    {
        return $this->user->newQuery()->create(['name' => $data->name, 'email' => $data->email,
            'password' => $data->password, 'theme' => 'default', 'account_status' => AccountStatus::Pending]);
    }

    /**
     * Обновляет profile.
     *
     * @param  User  $user  пользователь
     * @param  ProfileData  $data  данные формы

     * @return User
     */
    public function updateProfile(User $user, ProfileData $data): User
    {
        $user->name = $data->name;
        $user->email = $data->email;
        $user->theme = $data->theme->value;
        if ($data->password !== null) {
            $user->password = $data->password;
        }
        $user->save();

        return $this->refresh($user);
    }

    /**
     * Обновляет avatar path.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function updateAvatarPath(User $user, ?string $path): User
    {
        $user->update(['avatar_path' => $path]);

        return $this->refresh($user);
    }

    /**
     * set email verified at.

     *
     * @return User
     */
    public function setEmailVerifiedAt(User $user, ?DateTimeInterface $verifiedAt): User
    {
        $user->forceFill(['email_verified_at' => $verifiedAt])->save();

        return $this->refresh($user);
    }

    /**
     * set account status.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function setAccountStatus(User $user, AccountStatus $status): User
    {
        $user->forceFill(['account_status' => $status])->save();

        return $this->refresh($user);
    }

    /**
     * Возвращает варианты авторов для фильтров.
     *
     * @return Collection<int, AuthorOption>
     */
    public function authorOptionsFor(?int $includeUserId = null): Collection
    {
        $options = $this->user->newQuery()->role(['user', 'moderator', 'admin', 'owner'])->orderBy('name')->get(['id',
            'name', 'email'])->mapWithKeys(fn (User $user): array => [$user->id => new AuthorOption(id: (int) $user->id,
                label: "{$user->name} ({$user->email})")]);
        if ($includeUserId !== null && ! $options->has($includeUserId)) {
            $author = $this->user->newQuery()->find($includeUserId, ['id', 'name', 'email']);
            if ($author !== null) {
                $options->put($author->id, new AuthorOption(id: (int) $author->id,
                    label: "{$author->name} ({$author->email})"));
            }
        }

        return $options->sortBy(fn (AuthorOption $option): string => $option->label,
            SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    /**
     * Возвращает модераторов для уведомлений.
     *
     * @return Collection<int, User>
     */
    public function moderatorsForNotification(): Collection
    {
        return $this->user->newQuery()->where(function ($query): void {
            $query->whereHas('roles', fn ($roles) => $roles->whereIn('name', ['moderator', 'admin',
                'owner']))->orWhereHas('permissions', fn ($permissions) => $permissions->whereIn('name',
                    ['posts.moderate', 'panel.access.moderator', 'panel.access.admin']));
        })->get();
    }

    /**
     * exists with role.

     *
     * @return bool
     */
    public function existsWithRole(string $role): bool
    {
        return $this->user->newQuery()->role($role)->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function assignRole(User $user, string $roleName): User
    {
        $user->assignRole($roleName);

        return $user;
    }
}
