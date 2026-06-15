<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateAvatarRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;
use App\Services\Contracts\UserServiceContract;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * HTTP-контроллер profile.

 *
 * @property-read UserServiceContract $userService
 */
class ProfileController extends Controller
{
    public function __construct(protected UserServiceContract $userService) {}

    /**
     * edit.

     *
     * @return View
     */
    public function edit(): View
    {
        /** @var User $user */
        $user = auth()->user();

        return view('pages.profile.edit', compact('user'));
    }

    /**
     * Обновляет .
     *
     * @param  UpdateProfileRequest  $request  HTTP-запрос

     * @return RedirectResponse
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->userService->updateProfile($user, $request->toDto());

        return redirect()->route('profile.edit')->with('success', __('profile.messages.updated'));
    }

    /**
     * store avatar.
     *
     * @param  UpdateAvatarRequest  $request  HTTP-запрос

     * @return RedirectResponse
     */
    public function storeAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->userService->uploadAvatar($user, $request->file('avatar'));

        return redirect()->route('profile.edit')->with('success', __('profile.messages.avatar_updated'));
    }

    /**
     * destroy avatar.

     *
     * @return RedirectResponse
     */
    public function destroyAvatar(): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $this->userService->deleteAvatar($user);

        return redirect()->route('profile.edit')->with('success', __('profile.messages.avatar_deleted'));
    }
}
