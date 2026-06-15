<?php

namespace App\Http\Controllers;

use App\Exceptions\AiAnalysisOrderException;
use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\AiAnalysisOrderServiceContract;
use App\Services\Contracts\PostServiceContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Web controller for AI analysis order requests.

 *
 * @property-read AiAnalysisOrderServiceContract $orders
 * @property-read PostServiceContract $postService
 */
class AiAnalysisOrderController extends Controller
{
    public function __construct(protected AiAnalysisOrderServiceContract $orders,
        protected PostServiceContract $postService) {}

    /**
     * Creates a pending AI analysis order for the post.

     *
     * @return RedirectResponse
     */
    public function store(Request $request, string $postSlug): RedirectResponse
    {
        $this->authorize('ai.orders.request');
        /** @var User $user */
        $user = $request->user();
        $post = $this->postService->getVisiblePostBySlug($postSlug, $user);
        try {
            $this->orders->request($user, $post);
        } catch (AiAnalysisOrderException $exception) {
            return back()->withErrors(['ai_order' => $exception->getMessage()]);
        }

        return back()->with('success', __('ai.orders.requested'));
    }
}
