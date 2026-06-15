<?php

namespace App\Repositories;

use App\DTO\PostVersionSnapshot;
use App\Models\Post;
use App\Models\PostVersion;
use App\Models\User;
use App\Repositories\Contracts\PostVersionRepositoryContract;
use App\Support\TypeCast;
use Illuminate\Support\Collection;

/**
 * Репозиторий post version.
 *
 * @property-read PostVersion $postVersion
 */
class PostVersionRepository implements PostVersionRepositoryContract
{
    public function __construct(protected PostVersion $postVersion) {}

    /**
     * Создаёт snapshot.
     *
     * @param  Post  $post  пост

     * @return PostVersion
     */
    public function createSnapshot(Post $post, PostVersionSnapshot $snapshot, User $actor): PostVersion
    {
        $nextNumber = TypeCast::int($this->postVersion->newQuery()->where('post_id',
            $post->id)->max('version_number')) + 1;

        return $this->postVersion->newQuery()->create(['post_id' => $post->id, 'created_by' => $actor->id,
            'version_number' => $nextNumber, 'snapshot' => $snapshot->toArray()]);
    }

    /**
     * {@inheritdoc}
     */ /**
     * Возвращает версии поста.
     *
     * @return Collection<int, PostVersion>
     */
    public function listForPost(Post $post): Collection
    {
        return $this->postVersion->newQuery()->with('author')->where('post_id',
            $post->id)->orderByDesc('version_number')->get();
    }

    /**
     * Находит for post.
     *
     * @param  Post  $post  пост
     * @param  int  $versionId  id

     * @return ?PostVersion
     */
    public function findForPost(Post $post, int $versionId): ?PostVersion
    {
        return $this->postVersion->newQuery()->where('post_id', $post->id)->where('id', $versionId)->first();
    }

    /**
     * prune beyond limit.
     *
     * @param  Post  $post  пост
     * @param  int  $maxVersions  versions

     * @return int
     */
    public function pruneBeyondLimit(Post $post, int $maxVersions): int
    {
        $idsToKeep = $this->postVersion->newQuery()->where('post_id',
            $post->id)->orderByDesc('version_number')->limit($maxVersions)->pluck('id');

        return TypeCast::int($this->postVersion->newQuery()->where('post_id', $post->id)->whereNotIn('id',
            $idsToKeep)->delete());
    }

    /**
     * Удаляет all for post.
     *
     * @param  Post  $post  пост

     * @return int
     */
    public function deleteAllForPost(Post $post): int
    {
        return TypeCast::int($this->postVersion->newQuery()->where('post_id', $post->id)->delete());
    }
}
