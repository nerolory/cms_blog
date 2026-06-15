<?php

namespace App\Services;

use App\Repositories\Contracts\FileRepositoryContract;
use App\Services\Contracts\PostContentImageServiceContract;
use App\Services\Contracts\StoredImageOptimizationServiceContract;
use App\Support\Cache\CacheVersionManager;
use Illuminate\Http\UploadedFile;

/**
 * Загрузка изображений контента: быстрый ответ HTTP, оптимизация в
 * очереди.
 *
 * @property-read FileRepositoryContract $files
 * @property-read CacheVersionManager $cacheVersions
 * @property-read StoredImageOptimizationServiceContract $imageOptimizer
 */
class PostContentImageService implements PostContentImageServiceContract
{
    private const CONTENT_MAX_WIDTH = 1600;

    public function __construct(protected FileRepositoryContract $files,
        protected CacheVersionManager $cacheVersions,
        protected StoredImageOptimizationServiceContract $imageOptimizer) {}

    /**
     * store and get url.

     *
     * @return string
     */
    public function storeAndGetUrl(UploadedFile $file): string
    {
        $binary = $this->readUploadBytes($file);
        $path = $this->files->storePublicBinary('posts/content', $binary);
        $this->imageOptimizer->optimizeStored($path, self::CONTENT_MAX_WIDTH);
        $this->cacheVersions->bumpMediaFor($path);
        $url = $this->files->publicUrl($path, $this->cacheVersions->mediaVersionFor($path));
        if ($url === null) {
            throw new \RuntimeException('Unable to resolve public URL for uploaded image.');
        }

        return $url;
    }

    private function readUploadBytes(UploadedFile $file): string
    {
        $realPath = $file->getRealPath();
        if (is_string($realPath) && is_readable($realPath)) {
            $contents = file_get_contents($realPath);
            if (is_string($contents) && $contents !== '') {
                return $contents;
            }
        }
        $contents = $file->get();

        return is_string($contents) ? $contents : '';
    }
}
