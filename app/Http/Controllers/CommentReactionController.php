<?php

namespace App\Http\Controllers;

use App\DTO\CommentReactionData;
use App\Http\Requests\StoreCommentReactionRequest;
use App\Http\Support\EngagementMutationResponder;
use App\Models\User;
use App\Services\Contracts\CommentReactionServiceContract;
use App\Services\Contracts\CommentServiceContract;
use App\Services\Contracts\PostServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Реакции на комментарии.
 */
class CommentReactionController extends Controller
{
    public function __construct(
        protected CommentReactionServiceContract $commentReactionService,
        protected CommentServiceContract $commentService,
        protected PostServiceContract $postService,
        protected EngagementMutationResponder $engagementResponder,
    ) {}

    public function store(
        StoreCommentReactionRequest $request,
        string $postSlug,
        int $commentId,
    ): JsonResponse|RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $post = $this->postService->getVisiblePostBySlug($postSlug, $user);
        $this->commentService->findForPost($post->id, $commentId);
        $this->commentReactionService->toggle(CommentReactionData::fromValidated(
            commentId: $commentId,
            userId: $user->id,
            type: $request->string('type')->toString(),
        ));
        $this->commentService->forgetSectionCacheForPost($post->id);

        return $this->engagementResponder->respond($request, $post, $user);
    }
}
