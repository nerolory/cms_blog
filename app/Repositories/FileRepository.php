<?php

namespace App\Repositories;

use App\Repositories\Contracts\FileRepositoryContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Репозиторий file.
 */
class FileRepository implements FileRepositoryContract
{
    /**
     * store public.

     *
     * @return string
     */
    public function storePublic(string $directory, UploadedFile $file): string
    {
        $filename = Str::uuid()->toString().'.'.$file->guessExtension();
        $path = trim($directory, '/').'/'.$filename;
        Storage::disk('public')->putFileAs(trim($directory, '/'), $file, $filename);

        return $path;
    }

    /**
     * store public binary.

     *
     * @return string
     */
    public function storePublicBinary(string $directory, string $binary, string $extension = 'jpg'): string
    {
        $filename = Str::uuid()->toString().'.'.ltrim($extension, '.');
        $path = trim($directory, '/').'/'.$filename;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    /**
     * Удаляет public.

     *
     * @return bool
     */
    public function deletePublic(?string $path): bool
    {
        if ($path === null || $path === '') {
            return true;
        }

        return Storage::disk('public')->delete($path);
    }

    /**
     * exists public.

     *
     * @return bool
     */
    public function existsPublic(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        return Storage::disk('public')->exists($path);
    }

    /**
     * public url.

     *
     * @return ?string
     */
    public function publicUrl(?string $path, ?string $version = null): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }
        $url = Storage::disk('public')->url($path);
        if ($version === null || $version === '') {
            return $url;
        }

        return $url.'?v='.urlencode($version);
    }

    /**
     * overwrite public binary.
     */
    public function overwritePublicBinary(string $path, string $binary): void
    {
        Storage::disk('public')->put($path, $binary);
    }
}
