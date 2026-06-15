<?php

namespace App\Jobs;

use App\Services\Contracts\StoredImageOptimizationServiceContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Асинхронная оптимизация уже сохранённого изображения в public
 * storage.
 *
 * @property-read string $storagePath
 * @property-read int $maxWidth
 */
class OptimizeStoredImageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $storagePath, public int $maxWidth) {}

    /**
     * Обрабатывает запрос или задачу.
     */
    public function handle(StoredImageOptimizationServiceContract $optimizer): void
    {
        $optimizer->optimizeStored($this->storagePath, $this->maxWidth);
    }
}
