<?php

namespace App\Console\Commands;

use App\Services\Contracts\PostPreviewServiceContract;
use Illuminate\Console\Command;

/**
 * Artisan-команда cleanup post previews command.
 */
class CleanupPostPreviewsCommand extends Command
{
    protected $signature = 'posts:cleanup-previews';

    protected $description = 'Remove expired temporary media for post previews';

    /**
     * Выполняет команду.

     *
     * @return int
     */
    public function handle(PostPreviewServiceContract $previewService): int
    {
        $removed = $previewService->cleanupExpiredMedia();
        $this->info("Removed {$removed} expired preview media file(s).");

        return self::SUCCESS;
    }
}
