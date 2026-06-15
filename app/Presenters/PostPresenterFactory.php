<?php

namespace App\Presenters;

use App\Models\Post;
use App\Repositories\Contracts\FileRepositoryContract;
use App\Support\Cache\CacheVersionManager;

/**
 * Презентер post presenter factory.

 *
 * @property-read FileRepositoryContract $files
 * @property-read CacheVersionManager $cacheVersions
 */
final class PostPresenterFactory
{
    public function __construct(private FileRepositoryContract $files, private CacheVersionManager $cacheVersions) {}

    /**
     * for.
     *
     * @param  Post  $post  пост

     * @return PostPresenter
     */
    public function for(Post $post): PostPresenter
    {
        return new PostPresenter($post, $this->files, $this->cacheVersions);
    }
}
