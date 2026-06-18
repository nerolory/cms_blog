<?php

namespace App\Http\Middleware;

use App\Enums\AccountStatus;
use App\Services\Contracts\MailSettingsServiceContract;
use App\Support\Http\EngagementSpaRequest;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP middleware ensure account is active.

 *
 * @property-read MailSettingsServiceContract $mailSettingsService
 */
class EnsureAccountIsActive
{
    public function __construct(protected MailSettingsServiceContract $mailSettingsService) {}

    /**
     * Allows users with an active account; otherwise redirects or aborts.

     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }
        $status = $user->account_status instanceof AccountStatus ? $user
            ->account_status : AccountStatus::tryFrom((string) $user->account_status) ?? AccountStatus::Pending;
        if ($status === AccountStatus::Active) {
            return $next($request);
        }
        if ($status === AccountStatus::Suspended) {
            return EngagementSpaRequest::matches($request) ? abort(403, __('auth.account.suspended')) : abort(403,
                __('auth.account.suspended'));
        }
        if ($this->mailSettingsService->isEmailVerificationRequired() && ! $user->hasVerifiedEmail()) {
            return EngagementSpaRequest::matches($request) ? abort(403,
                __('auth.verification.required')) : redirect()->route('verification.notice');
        }

        return EngagementSpaRequest::matches($request) ? abort(403, __('auth.pending.required')) : redirect()->route('account.pending');
    }
}
