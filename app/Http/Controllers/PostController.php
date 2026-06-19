<?php

namespace App\Http\Controllers;

use App\Enums\PostVisibility;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\PostServiceContract;
use App\Services\Contracts\PostShowPageServiceContract;
use App\Services\Contracts\PostVersionServiceContract;
use App\Services\Contracts\PostViewServiceContract;
use App\Services\Contracts\SeoServiceContract;
use App\Services\Contracts\UserServiceContract;
use App\Support\Http\HttpCacheRequestAttributes;
use App\Support\TypeCast;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Web CRUD controller for posts.

 *
 * @property-read PostServiceContract $postService
 * @property-read UserServiceContract $userService
 * @property-read SeoServiceContract $seoService
 * @property-read PostShowPageServiceContract $postShowPageService
 * @property-read PostVersionServiceContract $versionService

 * @property-read PostViewServiceContract $postViewService
 */
class PostController extends Controller
{
    public function __construct(protected PostServiceContract $postService, protected UserServiceContract $userService,
        protected SeoServiceContract $seoService, protected PostShowPageServiceContract $postShowPageService,
        protected PostVersionServiceContract $versionService, protected PostViewServiceContract $postViewService) {}

    /**
     * Displays the public post listing.

     *
     * @return View
     */
    public function index(Request $request): View
    {
        /** @var User|null $user */
        $user = auth()->user();
        $posts = $this->postService->getPublicListing($user);
        $listingEngagement = $this->postViewService->getListingEngagementForPostIds(
            $posts->getCollection()->pluck('id')->map(fn (mixed $id): int => TypeCast::int($id))->values(),
        );
        $request->attributes->set(HttpCacheRequestAttributes::CACHE_CONTEXT,
            $this->seoService->httpCacheContextForListing($posts, $user, $listingEngagement));

        return view('pages.posts.index', compact('posts', 'listingEngagement'));
    }

    /**
     * Displays the post creation form.

     *
     * @return View
     */
    public function create(): View
    {
        $this->authorize('create', Post::class);

        return view('pages.posts.create', ['visibilityOptions' => $this->authorVisibilityOptions()]);
    }

    /**
     * Stores a new post and redirects to the show page.

     *
     * @return RedirectResponse
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $author = $request->user();
        if (! $author instanceof User) {
            abort(403);
        }
        $post = $this->postService->createForAuthor($request->toDto(), $author);
        $post = $this->postService->syncWebMedia($post, $request->webMediaSyncInput());

        return redirect()->route('posts.show', $post)->with('success', __('posts.messages.submitted_for_moderation'));
    }

    /**
     * Displays a single post page.

     *
     * @return View
     */
    public function show(Request $request, string $postSlug): View
    {
        /** @var User|null $user */
        $user = auth()->user();
        $viewModel = $this->postShowPageService->build($postSlug, $user);
        $request->attributes->set(HttpCacheRequestAttributes::CACHE_CONTEXT, $viewModel->httpCacheContext);
        $request->attributes->set(HttpCacheRequestAttributes::SEO_META, $viewModel->seo);

        return view('pages.posts.show', $viewModel->toViewVariables());
    }

    /**
     * Displays the post edit form.

     *
     * @return View
     */
    public function edit(string $postSlug): View
    {
        $post = $this->postService->getPostBySlug($postSlug);
        $this->authorize('update', $post);
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $canManageAuthors = $currentUser->canManageAllPosts();
        $authorOptions = $canManageAuthors ? $this->userService->authorOptionsFor($post->user_id) : collect();
        $showVisibilityField = ! $canManageAuthors && $post->status->allowsAuthorVisibilityEdit();
        $versions = $this->versionService->historyFor($post);

        return view('pages.posts.edit', compact('post', 'canManageAuthors', 'authorOptions', 'showVisibilityField',
            'versions') + ['visibilityOptions' => $this->authorVisibilityOptions()]);
    }

    /**
     * Updates the post; on success redirects with a flash message.

     *
     * @return RedirectResponse
     */
    public function update(UpdatePostRequest $request, string $postSlug): RedirectResponse
    {
        /** @var User $editor */
        $editor = $request->user();
        $post = $this->postService->getPostBySlug($postSlug);
        $isSaved = $this->postService->updateFromWeb($request->toDto(), $post, $editor);
        if ($isSaved) {
            $this->postService->syncWebMedia($post, $request->webMediaSyncInput());

            return redirect()->route('posts.show', $post)->with('success', __('posts.messages.updated'));
        }

        return back()->withInput()->withErrors(['form_error' => __('posts.messages.update_failed')]);
    }

    /**
     * Soft-deletes the post and redirects to the listing.

     *
     * @return RedirectResponse
     */
    public function destroy(string $postSlug): RedirectResponse
    {
        $post = $this->postService->getPostBySlug($postSlug);
        $this->authorize('delete', $post);
        $isDeleted = $this->postService->destroy($post);
        if ($isDeleted) {
            return redirect()->route('posts.index')->with('success', __('posts.messages.deleted'));
        }

        return back()->withErrors(['form_error' => __('posts.messages.delete_failed')]);
    }

    /**
     * @return array<string, string>
     */
    private function authorVisibilityOptions(): array
    {
        $options = [];
        foreach (PostVisibility::authorWebValues() as $value) {
            $options[$value] = __('posts.visibility.'.$value);
        }

        return $options;
    }
}
