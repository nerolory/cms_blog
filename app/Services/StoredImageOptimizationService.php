<?php

namespace App\Services;

use App\Repositories\Contracts\FileRepositoryContract;
use App\Services\Contracts\StoredImageOptimizationServiceContract;
use App\Support\Cache\CacheVersionManager;
use App\Support\Media\StoredImageOptimizer;

/**
 * Оптимизация уже сохранённых изображений в public storage.
 *
 * @property-read FileRepositoryContract $files
 * @property-read StoredImageOptimizer $optimizer
 * @property-read CacheVersionManager $cacheVersions
 */
class StoredImageOptimizationService implements StoredImageOptimizationServiceContract
{
    public function __construct(protected FileRepositoryContract $files, protected StoredImageOptimizer $optimizer,
        protected CacheVersionManager $cacheVersions) {}

    /**
     * {@inheritdoc}
     */
    public function optimizeStored(string $storagePath, int $maxWidth): void
    {
        $optimized = $this->optimizer->optimizeAtPath($storagePath, $maxWidth);
        if ($optimized === null) {
            return;
        }
        $this->files->overwritePublicBinary($storagePath, $optimized);
        $this->cacheVersions->bumpMediaFor($storagePath);
    }
}
