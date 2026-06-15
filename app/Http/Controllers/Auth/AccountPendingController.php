<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * HTTP-контроллер account pending.
 */
class AccountPendingController extends Controller
{
    /**
     * Page shown while waiting for manual admin activation.

     *
     * @return View|RedirectResponse
     */
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isAccountActive()) {
            return redirect()->route('posts.index');
        }

        return view('pages.auth.account-pending');
    }
}
