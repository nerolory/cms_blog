<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReactionRequest;
use App\Models\User;
use App\Services\Contracts\PostServiceContract;
use App\Services\Contracts\ReactionServiceContract;
use Illuminate\Http\RedirectResponse;

/**
 * HTTP-контроллер reaction.
 *
 * @property-read ReactionServiceContract $reactionService
 * @property-read PostServiceContract $postService
 */
class ReactionController extends Controller
{
    public function __construct(protected ReactionServiceContract $reactionService,
        protected PostServiceContract $postService) {}

    /**
     * store.

     *
     * @return RedirectResponse
     */
    public function store(StoreReactionRequest $request, string $postSlug): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $post = $this->postService->getVisiblePostBySlug($postSlug, $user);
        $this->reactionService->toggle($request->toDto($post, $user));

        return back();
    }
}
