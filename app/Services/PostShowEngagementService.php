<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\PostShowEngagementServiceContract;
use App\Services\Contracts\PostViewServiceContract;
use App\Services\Contracts\SeoServiceContract;
use App\Support\Post\PostShowContent;

/**
 * HTML-фрагмент engagement-блока для SPA и SSE.
 */
class PostShowEngagementService implements PostShowEngagementServiceContract
{
    public function __construct(
        protected PostViewServiceContract $postViewService,
        protected SeoServiceContract $seoService,
    ) {}

    public function renderAppInnerHtml(Post $post, ?User $viewer): string
    {
        $engagement = $this->postViewService->getEngagement($post, $viewer?->id);
        $seo = $this->seoService->resolveForPost($post);
        $readingMinutes = PostShowContent::fromBody($post->body)->readingMinutes;

        return view('components.post.engagement', [
            'engagement' => $engagement,
            'post' => $post,
            'seo' => $seo,
            'readingMinutes' => $readingMinutes,
            'fragmentOnly' => true,
        ])->render();
    }
}
