<?php

namespace App\Http\Controllers;

use App\DTO\PostPreviewMediaInput;
use App\Http\Requests\PreviewPostRequest;
use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\PostPreviewServiceContract;
use App\Services\Contracts\PostServiceContract;
use App\Services\Contracts\SeoServiceContract;
use App\Support\Post\PostShowContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

/**
 * Handles cached post previews with signed URLs.

 *
 * @property-read PostPreviewServiceContract $previewService
 * @property-read SeoServiceContract $seoService
 * @property-read PostServiceContract $postService
 */
class PostPreviewController extends Controller
{
    public function __construct(protected PostPreviewServiceContract $previewService,
        protected SeoServiceContract $seoService, protected PostServiceContract $postService) {}

    /**
     * Stores a preview from the create form and opens a signed show URL.

     *
     * @return RedirectResponse
     */
    public function store(PreviewPostRequest $request): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $url = $this->previewService->store($request->toPreviewPostData(null), $actor, null,
            $request->previewBackUrl(null), new PostPreviewMediaInput(featuredImage: $request->file('featured_image'),
                backgroundImage: $request->file('background_image'),
                removeFeaturedImage: $request->shouldRemoveFeaturedImage(),
                removeBackgroundImage: $request->shouldRemoveBackgroundImage()));

        return redirect()->away($url);
    }

    /**
     * Stores a preview from the edit form and opens a signed show URL.

     *
     * @return RedirectResponse
     */
    public function storeForPost(PreviewPostRequest $request, string $postSlug): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $post = $this->postService->getPostBySlug($postSlug);
        $this->authorize('update', $post);
        $url = $this->previewService->store($request->toPreviewPostData($post), $actor, $post,
            $request->previewBackUrl($post), new PostPreviewMediaInput(featuredImage: $request->file('featured_image'),
                backgroundImage: $request->file('background_image'),
                removeFeaturedImage: $request->shouldRemoveFeaturedImage(),
                removeBackgroundImage: $request->shouldRemoveBackgroundImage()));

        return redirect()->away($url);
    }

    /**
     * Displays a cached preview (signed URL, no-store, noindex).

     *
     * @return View
     */
    public function show(Request $request, string $token): View
    {
        if (! $request->hasValidSignature()) {
            throw new InvalidSignatureException;
        }
        /** @var User $viewer */
        $viewer = $request->user();
        $presenter = $this->previewService->resolve($token, $viewer);
        $post = $presenter->post();
        $seo = $this->seoService->resolveForPreview($post);
        $showContent = PostShowContent::fromBody($post->body);

        return view('pages.posts.show', ['post' => $post, 'previewPresenter' => $presenter, 'seo' => $seo,
            'isPreview' => true, 'canUpdatePost' => false, 'canOpenAdmin' => false] + $showContent->toViewVariables());
    }
}
