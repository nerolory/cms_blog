<?php

namespace App\Services\Contracts;

/**
 * Контракт асинхронной оптимизации сохранённых изображений.
 */
interface StoredImageOptimizationServiceContract
{
    /**
     * Оптимизирует файл в public storage и инвалидирует кэш медиа.
     */
    public function optimizeStored(string $storagePath, int $maxWidth): void;
}
