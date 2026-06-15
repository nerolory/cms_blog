<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Contracts\UserServiceContract;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

/**
 * HTTP-контроллер reset password.

 *
 * @property-read UserServiceContract $userService
 */
class ResetPasswordController extends Controller
{
    public function __construct(protected UserServiceContract $userService) {}

    /**
     * Создаёт .
     *
     * @param  Request  $request  HTTP-запрос

     * @return View
     */
    public function create(Request $request, string $token): View
    {
        return view('pages.auth.reset-password', ['token' => $token, 'email' => $request->string('email')->toString()]);
    }

    /**
     * store.
     *
     * @param  ResetPasswordRequest  $request  HTTP-запрос

     * @return RedirectResponse
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $status = $this->userService->resetPassword($request->credentials());

        return $status === Password::PASSWORD_RESET ? redirect()->route('login')->with('success',
            __($status)) : back()->withErrors(['email' => __($status)]);
    }
}
