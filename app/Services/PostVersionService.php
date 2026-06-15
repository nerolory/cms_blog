<?php

namespace App\Services;

use App\DTO\PostVersionSnapshot;
use App\Enums\PostModerationAction;
use App\Models\Post;
use App\Models\PostVersion;
use App\Models\User;
use App\Repositories\Contracts\PostModerationLogRepositoryContract;
use App\Repositories\Contracts\PostRepositoryContract;
use App\Repositories\Contracts\PostVersionRepositoryContract;
use App\Services\Contracts\PostVersionServiceContract;
use App\Support\Cache\CacheVersionManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * Сервис post version.
 *
 * @property-read PostVersionRepositoryContract $versionRepository
 * @property-read PostRepositoryContract $postRepository
 * @property-read PostModerationLogRepositoryContract $moderationLogRepository
 * @property-read CacheVersionManager $cacheVersions
 */
class PostVersionService implements PostVersionServiceContract
{
    private const MAX_VERSIONS = 2;

    public function __construct(protected PostVersionRepositoryContract $versionRepository,
        protected PostRepositoryContract $postRepository,
        protected PostModerationLogRepositoryContract $moderationLogRepository,
        protected CacheVersionManager $cacheVersions) {}

    /**
     * snapshot before update.
     *
     * @param  Post  $post  пост

     * @return PostVersion
     */
    public function snapshotBeforeUpdate(Post $post, User $actor): PostVersion
    {
        $version = $this->versionRepository->createSnapshot($post, PostVersionSnapshot::fromPost($post), $actor);
        $this->versionRepository->pruneBeyondLimit($post, self::MAX_VERSIONS);

        return $version;
    }

    /**
     * Возвращает историю версий поста.
     *
     * @return Collection<int, PostVersion>
     */
    public function historyFor(Post $post): Collection
    {
        return $this->versionRepository->listForPost($post);
    }

    /**
     * restore.

     *
     * @return Post
     */
    public function restore(PostVersion $version, User $actor): Post
    {
        $post = $version->post;
        if ($post === null) {
            throw (new ModelNotFoundException)->setModel(Post::class, [$version->post_id]);
        }
        $snapshot = PostVersionSnapshot::fromArray($version->snapshot ?? []);
        $this->postRepository->update($snapshot->toPostData($post), $post);
        $restored = $this->postRepository->refreshWithRelations($post);
        $this->moderationLogRepository->record($restored, PostModerationAction::Restored, $actor);
        $this->cacheVersions->bumpPosts();

        return $restored;
    }
}
