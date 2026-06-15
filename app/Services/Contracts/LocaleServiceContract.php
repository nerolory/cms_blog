<?php

namespace App\Services\Contracts;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Контракт смены локали сессии и профиля пользователя.
 */
interface LocaleServiceContract
{
    /**
     * Обновляет from.
     */
    public function updateFromRequest(Request $request, string $locale): void;

    /**
     * Обновляет for user.
     */
    public function updateForUser(User $user, string $locale): void;
}
