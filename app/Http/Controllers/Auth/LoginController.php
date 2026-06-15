<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Contracts\UserServiceContract;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * HTTP-контроллер login.

 *
 * @property-read UserServiceContract $userService
 */
class LoginController extends Controller
{
    public function __construct(protected UserServiceContract $userService) {}

    /**
     * Создаёт .

     *
     * @return View
     */
    public function create(): View
    {
        return view('pages.auth.login');
    }

    /**
     * store.
     *
     * @param  LoginRequest  $request  HTTP-запрос

     * @return RedirectResponse
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        if (! $this->userService->authenticate($request->string('email')->toString(),
            $request->string('password')->toString(), $request->boolean('remember'))) {
            return back()->withInput($request->only('email', 'remember'))->withErrors(['email' => __('auth.failed')]);
        }
        $request->session()->regenerate();
        $user = Auth::user();
        if ($user instanceof User && $user->canAccessPanel(Filament::getPanel('admin'))) {
            return redirect()->intended('/admin');
        }

        return redirect()->intended(route('posts.index'));
    }

    /**
     * destroy.

     *
     * @return RedirectResponse
     */
    public function destroy(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}
