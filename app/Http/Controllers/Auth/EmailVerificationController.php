<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Contracts\MailSettingsServiceContract;
use App\Services\Contracts\UserServiceContract;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * HTTP-контроллер email verification.

 *
 * @property-read MailSettingsServiceContract $mailSettingsService
 * @property-read UserServiceContract $userService
 */
class EmailVerificationController extends Controller
{
    public function __construct(protected MailSettingsServiceContract $mailSettingsService,
        protected UserServiceContract $userService) {}

    /**
     * "Verify your email" notice page.

     *
     * @return View|RedirectResponse
     */
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isAccountActive()) {
            return redirect()->route('posts.index');
        }

        return view('pages.auth.verify-email');
    }

    /**
     * Handles the signed email verification link.

     *
     * @return RedirectResponse
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(403);
        }
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('posts.index');
        }
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            $this->userService->activate($user);
        }

        return redirect()->route('posts.index')->with('success', __('auth.verification.verified'));
    }

    /**
     * Resends the verification email.

     *
     * @return RedirectResponse
     */
    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(403);
        }
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('posts.index');
        }
        $user->sendEmailVerificationNotification();

        return back()->with('status', __('auth.verification.sent'));
    }
}
