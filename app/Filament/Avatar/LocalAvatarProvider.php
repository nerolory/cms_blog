<?php

namespace App\Filament\Avatar;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Локальный fallback-аватар (data URI SVG) вместо ui-avatars.com.
 */
final class LocalAvatarProvider implements AvatarProvider
{
    /**
     * Возвращает .
     *
     * @param  Model|Authenticatable  $record
     * @return string
     */
    public function get(Model|Authenticatable $record): string
    {
        $label = str(Filament::getNameForDefaultAvatar($record))
            ->trim()
            ->explode(' ')
            ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
            ->join(' ');

        $gray950 = FilamentColor::getColor('gray')[950] ?? Color::Gray[950];
        $background = Color::convertToHex(is_string($gray950) ? $gray950 : (string) $gray950);
        $background = ltrim($background, '#');
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" fill="#%s"/>'
            .'<text x="50%%" y="50%%" dominant-baseline="central" text-anchor="middle" '
            .'fill="#ffffff" font-family="system-ui,sans-serif" font-size="24" font-weight="600">%s</text></svg>',
            $background,
            htmlspecialchars($label !== '' ? $label : '?', ENT_XML1 | ENT_QUOTES, 'UTF-8'),
        );

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
