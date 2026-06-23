<?php

namespace App\View\Components\Layout;

use App\Models\User;
use App\Services\Contracts\SiteSettingsServiceContract;
use App\Services\Contracts\TokenWalletServiceContract;
use App\Support\Formatting\CompactNumberFormatter;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Верхняя навигация публичного сайта.

 *
 * @property-read TokenWalletServiceContract $tokenWallets
 * @property-read SiteSettingsServiceContract $siteSettings
 */
class Navbar extends Component
{
    public function __construct(
        protected TokenWalletServiceContract $tokenWallets,
        protected SiteSettingsServiceContract $siteSettings,
    ) {}

    /**
     * Возвращает представление компонента.

     *
     * @return View
     */
    public function render(): View
    {
        $tokenBalance = null;
        $tokenBalanceAria = null;
        $canAccessAdmin = false;
        $user = auth()->user();

        if ($user instanceof User) {
            $rawBalance = $this->tokenWallets->getBalance($user);
            $tokenBalance = CompactNumberFormatter::format($rawBalance);
            $tokenBalanceAria = number_format($rawBalance);
            $canAccessAdmin = $user->canAccessPanel(Filament::getPanel('admin'));
        }

        return view('components.layout.navbar', [
            'siteName' => $this->siteSettings->siteName(),
            'tokenBalance' => $tokenBalance,
            'tokenBalanceAria' => $tokenBalanceAria ?? null,
            'canAccessAdmin' => $canAccessAdmin,
        ]);
    }
}
