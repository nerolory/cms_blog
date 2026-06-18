<?php

namespace App\Http\Support;

use App\Models\Post;
use App\Models\User;
use App\Services\Contracts\PostEngagementVersionServiceContract;
use App\Services\Contracts\PostShowEngagementServiceContract;
use App\Support\Http\EngagementSpaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * JSON-ответ с HTML engagement-фрагмента после мутаций.
 */
final class EngagementMutationResponder
{
    public function __construct(
        protected PostShowEngagementServiceContract $engagement,
        protected PostEngagementVersionServiceContract $versions,
    ) {}

    public function respond(
        Request $request,
        Post $post,
        ?User $user,
        ?string $flashTranslationKey = null,
    ): JsonResponse|RedirectResponse {
        if (EngagementSpaRequest::matches($request)) {
            return response()->json([
                'engagement_html' => $this->engagement->renderAppInnerHtml($post, $user),
                'version' => $this->versions->get($post->id),
            ]);
        }

        $redirect = back();
        if ($flashTranslationKey !== null) {
            $redirect = $redirect->with('success', __($flashTranslationKey));
        }

        return $redirect;
    }
}
