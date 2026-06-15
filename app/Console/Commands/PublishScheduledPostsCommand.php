<?php

namespace App\Console\Commands;

use App\Services\Contracts\ScheduledPublishServiceContract;
use Illuminate\Console\Command;

/**
 * Artisan-команда publish scheduled posts command.
 */
class PublishScheduledPostsCommand extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Publish posts whose scheduled_publish_at has passed';

    /**
     * Выполняет команду.

     *
     * @return int
     */
    public function handle(ScheduledPublishServiceContract $service): int
    {
        $count = $service->publishDuePosts();
        $this->info("Published {$count} scheduled post(s).");

        return self::SUCCESS;
    }
}
