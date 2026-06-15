<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

/**
 * Контракт загрузки и оптимизации изображений контента поста
 * (TinyMCE).
 */
interface PostContentImageServiceContract
{
    /**
     * Сохраняет изображение и возвращает публичный URL для
     * редактора.

     *
     * @return string
     */
    public function storeAndGetUrl(UploadedFile $file): string;
}
