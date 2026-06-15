<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Contracts\MailSettingsServiceContract;
use App\Services\Contracts\UserServiceContract;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * HTTP-контроллер register.

 *
 * @property-read UserServiceContract $userService
 * @property-read MailSettingsServiceContract $mailSettingsService
 */
class RegisterController extends Controller
{
    public function __construct(protected UserServiceContract $userService,
        protected MailSettingsServiceContract $mailSettingsService) {}

    /**
     * Создаёт .

     *
     * @return View
     */
    public function create(): View
    {
        return view('pages.auth.register');
    }

    /**
     * store.
     *
     * @param  RegisterRequest  $request  HTTP-запрос

     * @return RedirectResponse
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = $this->userService->register($request->toDto());
        Auth::login($user);
        $request->session()->regenerate();
        if ($this->mailSettingsService->isEmailVerificationRequired()) {
            event(new Registered($user));

            return redirect()->route('verification.notice')->with('success', __('auth.registered_verify'));
        }

        return redirect()->route('account.pending')->with('success', __('auth.registered_pending'));
    }
}
