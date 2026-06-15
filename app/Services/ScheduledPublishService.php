<?php

namespace App\Services;

use App\Repositories\Contracts\PostRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\Contracts\ScheduledPublishServiceContract;
use App\Services\Post\PostMutationPipeline;
use Illuminate\Support\Facades\Log;

/**
 * Сервис scheduled publish.

 *
 * @property-read PostRepositoryContract $posts
 * @property-read UserRepositoryContract $users
 * @property-read PostMutationPipeline $pipeline
 */
class ScheduledPublishService implements ScheduledPublishServiceContract
{
    public function __construct(
        protected PostRepositoryContract $posts,
        protected UserRepositoryContract $users,
        protected PostMutationPipeline $pipeline,
    ) {}

    /**
     * publish due posts.

     *
     * @return int
     */
    public function publishDuePosts(): int
    {
        $due = $this->posts->getDueForScheduledPublish();
        $count = 0;
        foreach ($due as $post) {
            $systemUser = $this->users->findFirstWithRole('owner');
            if ($systemUser === null) {
                Log::warning('Scheduled publish skipped: no owner user found.', ['post_id' => $post->id]);

                continue;
            }
            $this->pipeline->approve($post, $systemUser);
            $this->posts->clearScheduledPublishAt($post);
            $count++;
        }

        return $count;
    }
}
