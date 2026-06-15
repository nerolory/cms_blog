<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\Contracts\UserServiceContract;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

/**
 * HTTP-контроллер forgot password.

 *
 * @property-read UserServiceContract $userService
 */
class ForgotPasswordController extends Controller
{
    public function __construct(protected UserServiceContract $userService) {}

    /**
     * Создаёт .

     *
     * @return View
     */
    public function create(): View
    {
        return view('pages.auth.forgot-password');
    }

    /**
     * store.
     *
     * @param  ForgotPasswordRequest  $request  HTTP-запрос

     * @return RedirectResponse
     */
    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = $this->userService->sendPasswordResetLink($request->string('email')->toString());

        return $status === Password::RESET_LINK_SENT ? back()->with('success',
            __($status)) : back()->withErrors(['email' => __($status)]);
    }
}
