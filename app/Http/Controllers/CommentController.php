<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidCommentException;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Support\EngagementMutationResponder;
use App\Models\User;
use App\Services\Contracts\CommentReactionServiceContract;
use App\Services\Contracts\CommentServiceContract;
use App\Services\Contracts\PostServiceContract;
use App\Support\Http\EngagementSpaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * HTTP-контроллер comment.
 */
class CommentController extends Controller
{
    public function __construct(
        protected CommentServiceContract $commentService,
        protected CommentReactionServiceContract $commentReactionService,
        protected PostServiceContract $postService,
        protected EngagementMutationResponder $engagementResponder,
    ) {}

    public function index(Request $request, string $postSlug): JsonResponse
    {
        $user = $request->user();
        $post = $this->postService->getVisiblePostBySlug($postSlug, $user);
        $offset = max(0, $request->integer('offset'));
        $roots = $this->commentService->getRootPage($post->id, $offset);
        $total = $this->commentService->countRootsForPost($post->id);
        $replyCounts = $this->commentService->replyCountsForRoots($roots->pluck('id')->map(fn (mixed $id): int => (int) $id)->all());
        $summaries = $this->commentReactionService->summariesForComments($roots->pluck('id')->all(), $user?->id);

        return response()->json([
            'html' => view('components.post.comments-roots-chunk', [
                'post' => $post,
                'roots' => $roots,
                'replyCounts' => $replyCounts,
                'reactionSummaries' => $summaries,
            ])->render(),
            'has_more' => $total > $offset + $roots->count(),
            'next_offset' => $offset + $roots->count(),
        ]);
    }

    public function threadReplies(Request $request, string $postSlug, int $threadId): JsonResponse
    {
        $user = $request->user();
        $post = $this->postService->getVisiblePostBySlug($postSlug, $user);
        $this->commentService->findForPost($post->id, $threadId);
        $offset = max(0, $request->integer('offset'));
        $page = $this->commentService->getThreadRepliesPage($threadId, $offset);
        $summaries = $this->commentReactionService->summariesForComments(
            $page['replies']->pluck('id')->all(),
            $user?->id,
        );

        return response()->json([
            'html' => view('components.post.comments-replies-chunk', [
                'post' => $post,
                'threadId' => $threadId,
                'replies' => $page['replies'],
                'reactionSummaries' => $summaries,
            ])->render(),
            'has_more' => $page['hasMore'],
            'next_offset' => $offset + $page['replies']->count(),
        ]);
    }

    public function store(StoreCommentRequest $request, string $postSlug): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $post = $this->postService->getVisiblePostBySlug($postSlug, $user);
        try {
            $this->commentService->create($request->toDto($post, $user));
        } catch (InvalidCommentException $exception) {
            if (EngagementSpaRequest::matches($request)) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->withErrors(['body' => $exception->getMessage()]);
        }

        return $this->engagementResponder->respond(
            $request,
            $post,
            $user,
            'engagement.comment.messages.created',
        );
    }

    public function destroy(Request $request, string $postSlug, int $commentId): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $post = $this->postService->getVisiblePostBySlug($postSlug, $user);
        $comment = $this->commentService->findForPost($post->id, $commentId);
        $this->authorize('delete', $comment);
        $this->commentService->delete($comment);

        return $this->engagementResponder->respond(
            $request,
            $post,
            $user,
            'engagement.comment.messages.deleted',
        );
    }
}
