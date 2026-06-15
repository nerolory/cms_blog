<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidCommentException;
use App\Http\Requests\StoreCommentRequest;
use App\Models\User;
use App\Services\Contracts\CommentServiceContract;
use App\Services\Contracts\PostServiceContract;
use Illuminate\Http\RedirectResponse;

/**
 * HTTP-контроллер comment.
 *
 * @property-read CommentServiceContract $commentService
 * @property-read PostServiceContract $postService
 */
class CommentController extends Controller
{
    public function __construct(protected CommentServiceContract $commentService,
        protected PostServiceContract $postService) {}

    /**
     * store.

     *
     * @return RedirectResponse
     */
    public function store(StoreCommentRequest $request, string $postSlug): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $post = $this->postService->getVisiblePostBySlug($postSlug, $user);
        try {
            $this->commentService->create($request->toDto($post, $user));
        } catch (InvalidCommentException $exception) {
            return back()->withErrors(['body' => $exception->getMessage()]);
        }

        return back()->with('success', __('engagement.comment.messages.created'));
    }

    /**
     * destroy.

     *
     * @return RedirectResponse
     */
    public function destroy(string $postSlug, int $commentId): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $post = $this->postService->getVisiblePostBySlug($postSlug, $user);
        $comment = $this->commentService->findForPost($post->id, $commentId);
        $this->authorize('delete', $comment);
        $this->commentService->delete($comment);

        return back()->with('success', __('engagement.comment.messages.deleted'));
    }
}
