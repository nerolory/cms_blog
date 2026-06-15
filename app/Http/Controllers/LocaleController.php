<?php

namespace App\Http\Controllers;

use App\Services\Contracts\LocaleServiceContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * HTTP-контроллер смены локали интерфейса.
 *
 * @property-read LocaleServiceContract $locales
 */
class LocaleController extends Controller
{
    public function __construct(protected LocaleServiceContract $locales) {}

    /**
     * Обновляет .

     *
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate(['locale' => ['required', 'in:ru,en']]);
        $this->locales->updateFromRequest($request, $validated['locale']);

        return back();
    }
}
