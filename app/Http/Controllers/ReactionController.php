<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReactionRequest;
use App\Http\Support\EngagementMutationResponder;
use App\Models\User;
use App\Services\Contracts\PostServiceContract;
use App\Services\Contracts\ReactionServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * HTTP-контроллер reaction.
 *
 * @property-read ReactionServiceContract $reactionService
 * @property-read PostServiceContract $postService

 * @property-read EngagementMutationResponder $engagementResponder
 */
class ReactionController extends Controller
{
    public function __construct(
        protected ReactionServiceContract $reactionService,
        protected PostServiceContract $postService,
        protected EngagementMutationResponder $engagementResponder,
    ) {}

    /**
     * store.
     *
     * @return JsonResponse|RedirectResponse
     */
    public function store(StoreReactionRequest $request, string $postSlug): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $post = $this->postService->getVisiblePostBySlug($postSlug, $user);
        $this->reactionService->toggle($request->toDto($post, $user));

        return $this->engagementResponder->respond($request, $post, $user);
    }
}
