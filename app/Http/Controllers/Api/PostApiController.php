<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePostRequest;
use App\Http\Requests\Api\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\User;
use App\Services\Contracts\PostServiceContract;
use App\Services\Contracts\SeoServiceContract;
use App\Support\Http\HttpCacheRequestAttributes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * HTTP-контроллер post api.
 *
 * @property-read PostServiceContract $postService
 * @property-read SeoServiceContract $seoService
 */
class PostApiController extends Controller
{
    public function __construct(protected PostServiceContract $postService, protected SeoServiceContract $seoService) {}

    /**
     * index.

     *
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User|null $user */
        $user = auth()->user();
        $posts = $this->postService->getPublicListing($user);
        $request->attributes->set(HttpCacheRequestAttributes::CACHE_CONTEXT,
            $this->seoService->httpCacheContextForListing($posts, $user));

        return PostResource::collection($posts);
    }

    /**
     * show.

     *
     * @return PostResource|JsonResponse
     */
    public function show(Request $request, int $postId): PostResource|JsonResponse
    {
        /** @var User|null $user */
        $user = auth()->user();
        $post = $this->postService->getPostForApi($postId, $user);
        $this->authorize('view', $post);
        $request->attributes->set(HttpCacheRequestAttributes::CACHE_CONTEXT,
            $this->seoService->httpCacheContextForPost($post, $user));

        return PostResource::make($post);
    }

    /**
     * store.

     *
     * @return JsonResponse
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        /** @var User $author */
        $author = $request->user();
        $post = $this->postService->createForAuthor($request->toDto(), $author);

        return PostResource::make($post)->response()->setStatusCode(201);
    }

    /**
     * Обновляет .

     *
     * @return PostResource
     */
    public function update(UpdatePostRequest $request, int $postId): PostResource
    {
        /** @var User $editor */
        $editor = $request->user();
        $post = $this->postService->getPostById($postId);
        $this->postService->updateFromWeb($request->toDto(), $post, $editor);

        return PostResource::make($this->postService->getPostById($postId));
    }

    /**
     * destroy.

     *
     * @return JsonResponse
     */
    public function destroy(int $postId): JsonResponse
    {
        $post = $this->postService->getPostById($postId);
        $this->authorize('delete', $post);
        $this->postService->destroy($post);

        return response()->json(null, 204);
    }
}
