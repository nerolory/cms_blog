<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\Contracts\LocaleServiceContract;
use App\Support\Cache\CacheVersionManager;
use App\Support\Locale\LocaleResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Смена локали: сессия, cookie и сохранение в профиле
 * авторизованного пользователя.
 *
 * @property-read UserRepositoryContract $users
 * @property-read CacheVersionManager $cacheVersions
 */
class LocaleService implements LocaleServiceContract
{
    public function __construct(protected UserRepositoryContract $users,
        protected CacheVersionManager $cacheVersions) {}

    /**
     * Обновляет from.
     */
    public function updateFromRequest(Request $request, string $locale): void
    {
        $request->session()->put('locale', $locale);
        Cookie::queue(LocaleResolver::COOKIE, $locale, 60 * 24 * 365, '/', null, false, false, false, 'lax');
        $user = $request->user();
        if ($user instanceof User) {
            $this->updateForUser($user, $locale);
        }
        $this->cacheVersions->bumpPreferences();
    }

    /**
     * Обновляет for user.
     */
    public function updateForUser(User $user, string $locale): void
    {
        $this->users->updateLocale($user, $locale);
    }
}
