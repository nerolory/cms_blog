<?php

namespace App\Http\Controllers;

use App\Services\Contracts\PostEngagementVersionServiceContract;
use App\Services\Contracts\PostServiceContract;
use App\Services\Contracts\PostShowEngagementServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * HTML-фрагмент и SSE-поток engagement-блока поста.

 *
 * @property-read PostServiceContract $postService
 * @property-read PostShowEngagementServiceContract $engagement
 * @property-read PostEngagementVersionServiceContract $versions
 */
class PostShowEngagementController extends Controller
{
    public function __construct(
        protected PostServiceContract $postService,
        protected PostShowEngagementServiceContract $engagement,
        protected PostEngagementVersionServiceContract $versions,
    ) {}

    /**
     * show.
     *
     * @param  Request  $request
     * @param  string  $postSlug
     * @return JsonResponse
     */
    public function show(Request $request, string $postSlug): JsonResponse
    {
        $user = $request->user();
        $post = $this->postService->getVisiblePostBySlug($postSlug, $user);

        return response()->json([
            'engagement_html' => $this->engagement->renderAppInnerHtml($post, $user),
            'version' => $this->versions->get($post->id),
        ]);
    }

    /**
     * stream.
     *
     * @param  Request  $request
     * @param  string  $postSlug
     * @return StreamedResponse
     */
    public function stream(Request $request, string $postSlug): StreamedResponse
    {
        $user = $request->user();
        $post = $this->postService->getVisiblePostBySlug($postSlug, $user);
        $postId = $post->id;
        $versions = $this->versions;

        return response()->stream(function () use ($postId, $versions): void {
            $lastVersion = $versions->get($postId);
            $startedAt = time();
            $lastHeartbeatAt = time();

            while (time() - $startedAt < 90) {
                if (connection_aborted()) {
                    break;
                }

                $currentVersion = $versions->get($postId);
                if ($currentVersion !== $lastVersion) {
                    echo "event: engagement.updated\n";
                    echo 'data: '.json_encode(['version' => $currentVersion], JSON_THROW_ON_ERROR)."\n\n";
                    $lastVersion = $currentVersion;
                }

                if (time() - $lastHeartbeatAt >= 15) {
                    echo ": heartbeat\n\n";
                    $lastHeartbeatAt = time();
                }

                @ob_flush();
                flush();
                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
