<?php

namespace App\Services;

use App\Models\User;
use App\Services\Contracts\AiInsightServiceContract;
use App\Services\Contracts\PostServiceContract;
use App\Services\Contracts\PostShowPageServiceContract;
use App\Services\Contracts\PostViewServiceContract;
use App\Services\Contracts\SeoServiceContract;
use App\Support\Post\PostShowContent;
use App\ViewModels\PostShowViewModel;
use Filament\Facades\Filament;

/**
 * Страница show поста: просмотры, SEO, контент, права и AI-инсайты.
 *
 * @property-read PostServiceContract $postService
 * @property-read PostViewServiceContract $postViewService
 * @property-read SeoServiceContract $seoService
 * @property-read AiInsightServiceContract $aiInsightService
 */
class PostShowPageService implements PostShowPageServiceContract
{
    public function __construct(protected PostServiceContract $postService,
        protected PostViewServiceContract $postViewService, protected SeoServiceContract $seoService,
        protected AiInsightServiceContract $aiInsightService) {}

    /**
     * {@inheritdoc}
     */
    public function build(string $postSlug, ?User $viewer): PostShowViewModel
    {
        $post = $this->postService->getVisiblePostBySlug($postSlug, $viewer);
        $viewsCount = $this->postViewService->recordView($post);
        $engagement = $this->postViewService->getEngagement($post, $viewer?->id, $viewsCount);
        $seo = $this->seoService->resolveForPost($post);
        $content = PostShowContent::fromBody($post->body);
        $httpCacheContext = $this->seoService->httpCacheContextForPost($post, $viewer, $engagement->viewsCount);
        $canUpdatePost = $viewer instanceof User && $viewer->can('update', $post);
        $canOpenAdmin = $viewer instanceof User && $viewer->canAccessPanel(Filament::getPanel('admin'));
        $aiInsights = $this->aiInsightService->forPost($post, $viewer);

        return new PostShowViewModel(post: $post, seo: $seo, engagement: $engagement, aiInsights: $aiInsights,
            canUpdatePost: $canUpdatePost, canOpenAdmin: $canOpenAdmin, content: $content,
            httpCacheContext: $httpCacheContext);
    }
}
