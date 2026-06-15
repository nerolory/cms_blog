<?php

namespace App\Services\Contracts;

use App\Models\User;
use App\ViewModels\PostShowViewModel;

/**
 * Сборка данных страницы публичного просмотра поста.
 */
interface PostShowPageServiceContract
{
    /**
     * Формирует ViewModel для show по slug и текущему зрителю.
     *
     * @return PostShowViewModel
     */
    public function build(string $postSlug, ?User $viewer): PostShowViewModel;
}
