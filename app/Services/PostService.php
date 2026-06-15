<?php

namespace App\Services;

use App\DTO\AbstractData;
use App\DTO\PostData;
use App\DTO\PostWebMediaSyncInput;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Contracts\PostRepositoryContract;
use App\Services\Contracts\PostServiceContract;
use App\Services\Post\PostMutationPipeline;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Post use-case orchestration service.

 *
 * @property-read PostMutationPipeline $pipeline
 * @property-read PostRepositoryContract $postRepository
 */
class PostService implements PostServiceContract
{
    public function __construct(protected PostMutationPipeline $pipeline,
        protected PostRepositoryContract $postRepository) {}

    /**
     * {@inheritdoc}
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function getPublicListing(?User $viewer): LengthAwarePaginator
    {
        return $this->postRepository->getPublicListing($viewer);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function create(AbstractData $data): Post
    {
        $actor = auth()->user();
        if (! $actor instanceof User) {
            abort(403);
        }

        /** @var PostData $data */
        return $this->createFromAdmin($data, $actor);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function createFromAdmin(AbstractData $data, User $actor): Post
    {
        /** @var PostData $data */
        return $this->pipeline->createFromAdmin($data, $actor);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function createForAuthor(AbstractData $data, User $author): Post
    {
        /** @var PostData $data */
        return $this->pipeline->createForAuthor($data, $author);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function getPost(Post $post): Post
    {
        return $this->postRepository->findById($post->id);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function getVisiblePost(Post $post, ?User $viewer): Post
    {
        $loaded = $this->postRepository->findById($post->id);
        if (! $this->postRepository->isVisibleToViewer($loaded, $viewer)) {
            abort(404);
        }

        return $loaded;
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function getVisiblePostBySlug(string $slug, ?User $viewer): Post
    {
        $post = $this->postRepository->findBySlug($slug);
        if (! $this->postRepository->isVisibleToViewer($post, $viewer)) {
            abort(404);
        }

        return $post;
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function getPostBySlug(string $slug): Post
    {
        return $this->postRepository->findBySlug($slug);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function getPostForApi(int $postId, ?User $viewer): Post
    {
        $post = $this->postRepository->findById($postId);
        if (! $this->postRepository->isVisibleToViewer($post, $viewer)) {
            abort(404);
        }

        return $post;
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function getPostById(int $postId): Post
    {
        return $this->postRepository->findById($postId);
    }

    /**
     * {@inheritdoc}

     *
     * @return bool
     */
    public function canView(Post $post, ?User $viewer): bool
    {
        return $this->postRepository->isVisibleToViewer($post, $viewer);
    }

    /**
     * {@inheritdoc}

     *
     * @return bool
     */
    public function update(AbstractData $data, Post $post): bool
    {
        $actor = auth()->user();
        if (! $actor instanceof User) {
            abort(403);
        }
        /** @var PostData $data */
        $this->updateFromAdmin($data, $post, $actor);

        return true;
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function updateFromAdmin(AbstractData $data, Post $post, User $actor): Post
    {
        /** @var PostData $data */
        return $this->pipeline->updateFromAdmin($data, $post, $actor);
    }

    /**
     * {@inheritdoc}

     *
     * @return bool
     */
    public function updateFromWeb(AbstractData $data, Post $post, User $editor): bool
    {
        /** @var PostData $data */
        return $this->pipeline->updateFromWeb($data, $post, $editor);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function approve(Post $post, User $moderator): Post
    {
        return $this->pipeline->approve($post, $moderator);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function reject(Post $post, User $moderator, string $reason): Post
    {
        return $this->pipeline->reject($post, $moderator, $reason);
    }

    /**
     * {@inheritdoc}

     *
     * @return ?bool
     */
    public function destroy(Post $post): ?bool
    {
        return $this->pipeline->destroy($post);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function restore(Post $post): Post
    {
        return $this->pipeline->restore($post);
    }

    /**
     * {@inheritdoc}

     *
     * @return Post
     */
    public function syncWebMedia(Post $post, PostWebMediaSyncInput $input): Post
    {
        return $this->pipeline->syncWebMedia($post, $input);
    }
}
