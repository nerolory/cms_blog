<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentAwaitingWebhookException;
use App\Models\User;
use App\Services\Contracts\TokenWalletServiceContract;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Web controller for token wallet pages.
 *
 * @property-read TokenWalletServiceContract $wallets
 */
class TokenWalletController extends Controller
{
    public function __construct(protected TokenWalletServiceContract $wallets) {}

    /**
     * show.

     *
     * @return View
     */
    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('pages.tokens.show', ['balance' => $this->wallets->getBalance($user),
            'packages' => $this->wallets->listActivePackages()]);
    }

    /**
     * purchase.

     *
     * @return RedirectResponse
     */
    public function purchase(Request $request, int $packageId): RedirectResponse
    {
        $this->authorize('tokens.purchase');
        /** @var User $user */
        $user = $request->user();
        try {
            $transaction = $this->wallets->purchasePackageById($user, $packageId);
        } catch (PaymentAwaitingWebhookException) {
            return redirect()->route('tokens.show')
                ->with('success', __('tokens.messages.payment_pending'));
        }

        return redirect()->route('tokens.show')->with('success', __('tokens.messages.purchased',
            ['amount' => $transaction->amount]));
    }
}
