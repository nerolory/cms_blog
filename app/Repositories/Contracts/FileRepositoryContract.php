<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\UploadedFile;

/**
 * Контракт репозитория file.
 */
interface FileRepositoryContract
{
    /**
     * store public.

     *
     * @return string
     */
    public function storePublic(string $directory, UploadedFile $file): string;

    /**
     * store public binary.

     *
     * @return string
     */
    public function storePublicBinary(string $directory, string $binary, string $extension = 'jpg'): string;

    /**
     * Удаляет public.

     *
     * @return bool
     */
    public function deletePublic(?string $path): bool;

    /**
     * exists public.

     *
     * @return bool
     */
    public function existsPublic(?string $path): bool;

    /**
     * public url.

     *
     * @return ?string
     */
    public function publicUrl(?string $path, ?string $version = null): ?string;

    /**
     * Перезаписывает бинарное содержимое файла в public disk.
     */
    public function overwritePublicBinary(string $path, string $binary): void;
}
