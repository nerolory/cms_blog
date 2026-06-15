<?php

namespace App\Presenters;

use App\Models\User;
use App\Repositories\Contracts\FileRepositoryContract;
use App\Support\Cache\CacheVersionManager;

/**
 * Presentation helpers for user avatars.
 *
 * @property-read User $user
 * @property-read FileRepositoryContract $files
 * @property-read CacheVersionManager $cacheVersions
 */
final readonly class UserPresenter
{
    public function __construct(private User $user, private FileRepositoryContract $files,
        private CacheVersionManager $cacheVersions) {}

    /**
     * user.

     *
     * @return User
     */
    public function user(): User
    {
        return $this->user;
    }

    /**
     * avatar url.

     *
     * @return ?string
     */
    public function avatarUrl(): ?string
    {
        if (! $this->avatarAvailable()) {
            return null;
        }

        return $this->files->publicUrl($this->user->avatar_path,
            $this->cacheVersions->mediaVersionFor($this->user->avatar_path));
    }

    /**
     * avatar available.

     *
     * @return bool
     */
    public function avatarAvailable(): bool
    {
        if (! $this->hasAvatarRecord()) {
            return false;
        }

        return $this->files->existsPublic($this->user->avatar_path);
    }

    /**
     * Проверяет наличие avatar record.

     *
     * @return bool
     */
    public function hasAvatarRecord(): bool
    {
        return $this->user->avatar_path !== null && $this->user->avatar_path !== '';
    }
}
