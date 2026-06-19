<?php

namespace App\ViewModels;

use App\DTO\HttpCacheContext;
use App\DTO\PostAiInsights;
use App\DTO\PostEngagementData;
use App\DTO\PostShowContentData;
use App\DTO\SeoMetaData;
use App\Models\Post;

/**
 * Данные страницы публичного просмотра поста.

 *
 * @property-read Post $post
 * @property-read SeoMetaData $seo
 * @property-read PostEngagementData $engagement
 * @property-read PostAiInsights $aiInsights
 * @property-read bool $canUpdatePost
 * @property-read bool $canOpenAdmin
 * @property-read PostShowContentData $content
 * @property-read HttpCacheContext $httpCacheContext
 * @property-read string $engagementVersion
 */
readonly class PostShowViewModel
{
    public function __construct(public Post $post, public SeoMetaData $seo, public PostEngagementData $engagement,
        public PostAiInsights $aiInsights, public bool $canUpdatePost, public bool $canOpenAdmin,
        public PostShowContentData $content, public HttpCacheContext $httpCacheContext,
        public string $engagementVersion = '0') {}

    /**
     * Переменные для view pages.posts.show.
     *
     * @return array<string, mixed>
     */
    public function toViewVariables(): array
    {
        return ['post' => $this->post, 'seo' => $this->seo, 'engagement' => $this->engagement,
            'aiInsights' => $this->aiInsights, 'canUpdatePost' => $this->canUpdatePost,
            'canOpenAdmin' => $this->canOpenAdmin, 'engagementVersion' => $this->engagementVersion]
            + $this->content->toViewVariables();
    }
}
