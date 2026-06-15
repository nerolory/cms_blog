<?php

namespace App\Presenters;

use App\Models\User;
use App\Repositories\Contracts\FileRepositoryContract;
use App\Support\Cache\CacheVersionManager;

/**
 * Презентер user presenter factory.

 *
 * @property-read FileRepositoryContract $files
 * @property-read CacheVersionManager $cacheVersions
 */
final class UserPresenterFactory
{
    public function __construct(private FileRepositoryContract $files, private CacheVersionManager $cacheVersions) {}

    /**
     * for.
     *
     * @param  User  $user  пользователь

     * @return UserPresenter
     */
    public function for(User $user): UserPresenter
    {
        return new UserPresenter($user, $this->files, $this->cacheVersions);
    }
}
